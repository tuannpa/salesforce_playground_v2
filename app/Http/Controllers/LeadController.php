<?php

namespace App\Http\Controllers;

use App\Interfaces\SalesforceServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    private SalesforceServiceInterface $salesforceService;

    /**
     * @param SalesforceServiceInterface $salesforceService
     */
    public function __construct(SalesforceServiceInterface $salesforceService)
    {
        $this->salesforceService = $salesforceService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getList(Request $request): JsonResponse
    {
        $entity = 'Lead';

        $totalLeads = $this->salesforceService->countAllRecords($entity);
        $leads = $this->salesforceService->fetchRecords($entity, $request->query());

        $response = array_merge($leads, $totalLeads);

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * @return JsonResponse
     */
    public function exportData(): JsonResponse
    {
        $response = $this->salesforceService->export('Lead');

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * @param $exportId
     * @return StreamedResponse
     */
    public function getExportResult($exportId): StreamedResponse
    {
        $response = $this->salesforceService->getExportResult($exportId);

        return response()->streamDownload(function () use ($response) {
            echo $response['content'];
        }, 'leads.csv');
    }
}
