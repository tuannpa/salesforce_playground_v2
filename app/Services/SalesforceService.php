<?php

namespace App\Services;

use App\Exceptions\SalesforceLogoutException;
use App\Exceptions\SalesforceSoqlFilterException;
use App\Interfaces\SalesforceServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SalesforceService implements SalesforceServiceInterface
{
    private static int $timeout = 30;

    private string $sfdcUserSessionKey = 'sfdcUserSession';

    private array $tokenData = [];

    /**
     * @param array $filter
     * @return string
     * @throws SalesforceSoqlFilterException
     */
    private function buildFilter(array $filter): string
    {
        $filterStr = '';

        foreach ($filter as $field => $props) {
            $operator = $props['operator'] ?? null;
            $value = $props['value'];

            if (empty($operator)) {
                throw new SalesforceSoqlFilterException('Must define the operator in the provided filter array');
            }

            if (!empty($filterStr)) {
                $filterStr .= ' and ';
            }

            $ucFirstField = ucfirst($field);
            $valueByType = is_string($value) ? "'$value'" : $value;

            switch ($operator) {
                case 'in':
                    $v = explode(',', $value);

                    $filterValue = implode(',', array_map(function($s) {
                        return "'$s'";
                    }, $v));

                    $filterStr .= "$ucFirstField in ($filterValue)";
                    break;
                default:
                    $filterStr .= "$ucFirstField $operator $valueByType";
                    break;
            }
        }

        $filterStr .= ' ';

        return $filterStr;
    }

    /**
     * @param string $encryptedString
     * @return string
     */
    public function decryptLoginData(string $encryptedString): string
    {
        [
            'key' => $cryptoKeyConfig,
            'iv' => $cryptoIvConfig
        ] = config('crypto.salesforce_login');

        $decrypted = openssl_decrypt($encryptedString, 'AES-128-CBC', hex2bin($cryptoKeyConfig), OPENSSL_ZERO_PADDING, hex2bin($cryptoIvConfig));

        return trim($decrypted);
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $securityToken
     * @return array
     */
    public function authenticate(string $username, string $password, string $securityToken): array
    {
        $success = false;
        $userInfo = [];
        $decryptedUsername = $this->decryptLoginData($username);

        $params = [
            'username' => $decryptedUsername,
            'password' => $this->decryptLoginData($password) . $this->decryptLoginData($securityToken),
            'grant_type' => 'password',
            'client_id' => config('salesforce.clientId'),
            'client_secret' => config('salesforce.clientSecret')
        ];

        try {
            $tokenResponse = Http::asForm()->post(config('salesforce.tokenUri'), $params);

            if ($tokenResponse->successful()) {
                $success = true;
                $message = 'Authenticated successfully';
                $tokenData = $tokenResponse->json();
                $this->tokenData = $tokenData;
                $userInfo = [
                    'username' => $decryptedUsername,
                    'token' => $this->tokenData['access_token'],
                    'instanceUri' => $this->tokenData['instance_url']
                ];

                session()->put($this->sfdcUserSessionKey, $this->tokenData);
            } else {
                $responseBody = $tokenResponse->json();
                $message = ucfirst($responseBody['error_description']) ?? "Error happened while authenticating the username $decryptedUsername";
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            Log::error('Error occurred when fetching Salesforce access token: ' . $e->getMessage());
        }

        return [
            'success' => $success,
            'message' => $message,
            'userInfo' => $userInfo
        ];
    }

    /**
     * @return array
     * @throws SalesforceLogoutException
     */
    public function accountLogout(): array
    {
        if (!session()->has($this->sfdcUserSessionKey)) {
            Log::error("Error occurred when closing Salesforce session, reason: Session " . $this->sfdcUserSessionKey . " exists? - " . session()->has($this->sfdcUserSessionKey));
            throw new SalesforceLogoutException('Failed to close Salesforce session');
        }

        $success = false;
        [
            'access_token' => $sfdcToken
        ] = session($this->sfdcUserSessionKey);

        try {
            $revokeResponse = Http::asForm()->post(config('salesforce.revokeUri'), [
                'token' => $sfdcToken
            ]);

            if ($revokeResponse->successful()) {
                $success = true;
                $message = "Log out successfully";

                // Unset session
                session()->forget($this->sfdcUserSessionKey);
            } else {
                $responseBody = $revokeResponse->json();
                $message = $responseBody['invalid_token'] ?? "Failed to log user out";
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            Log::error('Error occurred when revoking Salesforce access token: ' . $e->getMessage());
        }

        return [
            'success' => $success,
            'message' => $message
        ];
    }

    /**
     * @param string $entity
     * @return array
     * @throws \Exception
     */
    public function countAllRecords(string $entity): array
    {
        [
            'access_token' => $sfdcToken,
            'instance_url' => $sfdcApiUri
        ] = session($this->sfdcUserSessionKey);
        $result = [];
        $queryParams = "?q=select count() from $entity";
        $endpoint = $sfdcApiUri . config('salesforce.queryService') . $queryParams;

        try {
            $response = Http::connectTimeout(self::$timeout)->withToken($sfdcToken)->get($endpoint);
            if ($response->successful()) {
                $json = $response->json();
                $result['totalRecords'] = $json['totalSize'] ?? 0;
            }
        } catch (\Exception $e) {
            Log::error("Error occurred when fetching total records of $entity entity: " . $e->getMessage());
            throw $e;
        }

        return $result;
    }

    /**
     * @param string $entity
     * @param array $queryParams
     * @param array $options
     * @return array
     */
    public function fetchRecords(string $entity, array $queryParams, array $options = []): array
    {
        $page = $queryParams['page'] ?? 1;
        $fields = $queryParams['fields'] ?? 'Id, FirstName, LastName, Email, Phone';
        $itemsPerPage = $queryParams['itemsPerPage'] ??  10;
        $offset = intval($itemsPerPage) * (intval($page) - 1);
        $result = [];

        try {
            $query = "?q=select $fields from $entity ";

            if (!empty($options['where'])) {
                $query .= 'where ' . $this->buildFilter($options['where']);
            }

            $groupBy = $options['groupBy'] ?? null;
            if (!empty($groupBy)) {
                $query .= "group by $groupBy ";
            }

            $query .= "limit $itemsPerPage";
            [
                'access_token' => $sfdcToken,
                'instance_url' => $sfdcApiUri
            ] = session($this->sfdcUserSessionKey);

            if ($offset > 0) {
                $query .= " offset $offset";
            }

            $endpoint = $sfdcApiUri . config('salesforce.queryService') . $query;

            $response = Http::connectTimeout(self::$timeout)->withToken($sfdcToken)->get($endpoint);

            if ($response->successful()) {
                $json = $response->json();
                $result['records'] = $json['records'];
                if (!empty($options['where'])) {
                    $result['totalRecords'] = $json['totalSize'];
                }
            }
        } catch (\Exception $e) {
            Log::error("Error occurred when fetching Salesforce $entity data: " . $e->getMessage());
        }

        return $result;
    }

    /**
     * @param string $entity
     * @param string $projection
     * @param array $filter
     * @param $queryAll
     * @return array
     */
    public function export(string $entity, string $projection = '', array $filter = [], $queryAll = false): array
    {
        $success = true;
        $message = '';
        $result = [];

        [
            'access_token' => $sfdcToken,
            'instance_url' => $sfdcApiUri
        ] = session($this->sfdcUserSessionKey);
        $columns = !empty($projection) ? $projection : 'Id, FirstName, LastName, Email, Phone';
        $query = "SELECT $columns from $entity";
        if (!empty($filter['where'])) {
            $query .= 'WHERE ' . $filter['where'];
        }

        $params = [
            'operation' => !$queryAll ? 'query' : 'queryAll',
            'query' => $query
        ];

        $endpoint = $sfdcApiUri . config('salesforce.bulkApiService');

        try {
            $response = Http::connectTimeout(self::$timeout)->withToken($sfdcToken)->post($endpoint, $params);

            if ($response->successful()) {
                $json = $response->json();
                $message = "$entity data exported successfully. It will be downloaded automatically";
                $result['exportId'] = $json['id'];
            }
        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            Log::error("Error occurred when exporting Salesforce $entity data: " . $e->getMessage());
        }

        return [
            'success' => $success,
            'message' => $message,
            'result' => $result
        ];
    }

    /**
     * @param string $exportId
     * @return array
     */
    public function getExportResult(string $exportId): array
    {
        $success = true;
        $csvContent = '';
        [
            'access_token' => $sfdcToken,
            'instance_url' => $sfdcApiUri
        ] = session($this->sfdcUserSessionKey);

        $endpoint = $sfdcApiUri . config('salesforce.bulkApiService') . "/$exportId/results";

        try {
            $response = Http::withHeaders([
                'Accept' => 'text/csv'
            ])->connectTimeout(self::$timeout)->withToken($sfdcToken)->get($endpoint);

            if ($response->successful()) {
                $csvContent = $response->body();
            }
        } catch (\Exception $e) {
            $success = false;
            Log::error("Error occurred when getting export result: " . $e->getMessage());
        }

        return [
            'success' => $success,
            'content' => $csvContent
        ];
    }
}
