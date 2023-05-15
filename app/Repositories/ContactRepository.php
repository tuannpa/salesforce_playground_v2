<?php

namespace App\Repositories;

use App\Exceptions\ContactUpdateException;
use App\Interfaces\ContactRepositoryInterface;
use App\Interfaces\SalesforceRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContactRepository implements ContactRepositoryInterface
{
    private $salesforceRepository;

    /**
     * ContactRepository constructor.
     * @param SalesforceRepositoryInterface $salesforceRepository
     */
    public function __construct(SalesforceRepositoryInterface $salesforceRepository)
    {
        $this->salesforceRepository = $salesforceRepository;
    }

    private function retry(ClientResponse $response, $callback)
    {
        if ($response->failed() && Response::HTTP_UNAUTHORIZED === $response->status()) {
            $this->salesforceRepository->fetchToken(true);
            $callback();
        }
    }

    /**
     * @return array
     */
    public function countTotalContacts(): array
    {
        $accessToken = $this->salesforceRepository->getAccessToken();
        $queryParams = '?q=select count() from Contact';
        $contactApiUri = $this->salesforceRepository->getApiUri() . config('salesforce.queryService') . $queryParams;
        $result = [];

        try {
            $response = Http::withToken($accessToken)->get($contactApiUri);
            if ($response->successful()) {
                $result = $response->json();
            }

            if ($response->failed() && Response::HTTP_UNAUTHORIZED === $response->status()) {
                $this->salesforceRepository->fetchToken(true);
                $this->countTotalContacts();
            }
        } catch (\Exception $e) {
            Log::error('Error occurred when fetching Salesforce contacts data: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * @param $query
     * @return array
     */
    public function fetchContacts($query): array
    {
        $page = $query['page'] ?? 1;
        $fields = $query['fields'] ?? 'Id, FirstName, LastName, Email, Phone';
        $itemsPerPage = $query['itemsPerPage'] ??  10;
        $offset = intval($itemsPerPage) * (intval($page) - 1);
        $queryParams = "?q=select $fields from Contact limit $itemsPerPage";
        $accessToken = $this->salesforceRepository->getAccessToken();
        $result = [];

        if ($offset > 0) {
            $queryParams .= " offset $offset";
        }
        $contactApiUri = $this->salesforceRepository->getApiUri() . config('salesforce.queryService') . $queryParams;

        try {
            $response = Http::withToken($accessToken)->get($contactApiUri);

            if ($response->successful()) {
                $result = $response->json();
            }

            if ($response->failed() && Response::HTTP_UNAUTHORIZED === $response->status()) {
                $this->salesforceRepository->fetchToken(true);
                $this->fetchContacts($query);
            }
        } catch (\Exception $e) {
            Log::error('Error occurred when fetching Salesforce contacts data: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * @param $id
     * @param $dto
     * @return array
     * @throws ContactUpdateException
     */
    public function updateContact($id, $dto): array
    {
        $contactApiUri = $this->salesforceRepository->getApiUri() . config('salesforce.entityService') . "/contact/$id";
        $result = [];

        try {
            $response = Http::withToken($this->salesforceRepository->getAccessToken())->patch($contactApiUri, $dto);

            if ($response->failed() && Response::HTTP_UNAUTHORIZED === $response->status()) {
                $this->salesforceRepository->fetchToken(true);
                $this->updateContact($id, $dto);
            }

            $result = [
                'success' => $response->successful()
            ];
        } catch (\Exception $e) {
            Log::error("Error occurred when updating Salesforce contact id - $id: " . $e->getMessage());
            throw new ContactUpdateException($e->getMessage());
        }

        return $result;
    }
}
