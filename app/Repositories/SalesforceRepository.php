<?php

namespace App\Repositories;

use App\Interfaces\SalesforceRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SalesforceRepository implements SalesforceRepositoryInterface
{
    private $token = [];
    private $accessToken = null;
    private $apiUri = null;

    /**
     * @return string|null
     */
    public function getAccessToken(): string | null
    {
        return $this->accessToken;
    }

    /**
     * @return string|null
     */
    public function getApiUri(): string | null
    {
        return $this->apiUri;
    }

    /**
     * @param false $force
     */
    public function fetchToken($force = false): void
    {
        if (!empty($this->token) && !$force) {
            return;
        }

        $params = [
            'username' => config('salesforce.username'),
            'password' => config('salesforce.password') . config('salesforce.securityToken'),
            'grant_type' => 'password',
            'client_id' => config('salesforce.clientId'),
            'client_secret' => config('salesforce.clientSecret')
        ];

        try {
            $tokenResponse = Http::asForm()->post(config('salesforce.tokenUri'), $params);

            if ($tokenResponse->successful()) {
                $tokenData = $tokenResponse->json();
                $this->token = $tokenData;
                $this->accessToken = $tokenData['access_token'];
                $this->apiUri = $tokenData['instance_url'];
            }
        } catch (\Exception $e) {
            Log::error('Error occurred when fetching Salesforce access token: ' . $e->getMessage());
        }
    }
}
