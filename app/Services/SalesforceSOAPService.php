<?php

namespace App\Services;

use App\Exceptions\SalesforceSOAPLogoutException;
use App\Interfaces\SalesforceSOAPServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Phpforce\SoapClient\ClientBuilder;

class SalesforceSOAPService implements SalesforceSOAPServiceInterface
{
    private string $clientSessionKey = 'soapClient';

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
        $message = "";
        $userData = [];
        $wsdlFilePath = Storage::path('salesforce/salesforce-partner.wsdl');

        $builder = new ClientBuilder(
            $wsdlFilePath,
            $username,
            $password,
            $securityToken
        );

        $soapClient = $builder->build();
        session()->put($this->clientSessionKey, [
            'username' => $username,
            'password' => $password,
            'securityToken' => $securityToken
        ]);

        try {
            // Authenticate with Salesforce
            $soapClient->login(
                $this->decryptLoginData($username),
                $this->decryptLoginData($password),
                $this->decryptLoginData($securityToken)
            );

            $result = $soapClient->getLoginResult();

            if (!empty($result->getUserId())) {
                $success = true;
                $message = "User $username authenticated successfully.";
                $userData = [
                    'id' => $result->getUserId(),
                    'username' => $result->getUserInfo()->getUserName()
                ];
            }
        } catch (\Exception $exception) {
            Log::error("Error occurred when authenticating Salesforce account: " . $exception->getMessage());
            $message = $exception->getMessage();
        }

        return [
            'success' => $success,
            'message' => $message,
            'userData' => $userData
        ];
    }

    /**
     * @return array
     * @throws SalesforceSOAPLogoutException
     */
    public function accountLogout(): array
    {
        if (!session()->has($this->clientSessionKey)) {
            Log::error("Error occurred when closing Salesforce session, reason: Session " . $this->clientSessionKey . " exists? - " . session()->has($this->clientSessionKey));
            throw new SalesforceSOAPLogoutException('Failed to close Salesforce session');
        }

        $wsdlFilePath = Storage::path('salesforce/salesforce-partner.wsdl');
        [
            'username' => $username,
            'password' => $password,
            'securityToken' => $securityToken
        ] = session($this->clientSessionKey);

        $builder = new ClientBuilder(
            $wsdlFilePath,
            $this->decryptLoginData($username),
            $this->decryptLoginData($password),
            $this->decryptLoginData($securityToken)
        );

        $soapClient = $builder->build();
        $soapClient->logout();

        // Unset session
        session()->forget($this->clientSessionKey);

        return [
            'success' => true
        ];
    }
}
