<?php

namespace App\Http\Controllers;

use App\Interfaces\CampaignServiceInterface;
use App\Interfaces\SalesforceServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CampaignController extends Controller
{
    private SalesforceServiceInterface $salesforceService;

    private CampaignServiceInterface $campaignService;

    /**
     * @param SalesforceServiceInterface $salesforceService
     * @param CampaignServiceInterface $campaignService
     */
    public function __construct(
        SalesforceServiceInterface $salesforceService,
        CampaignServiceInterface $campaignService
    )
    {
        $this->salesforceService = $salesforceService;
        $this->campaignService = $campaignService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getList(Request $request): JsonResponse
    {
        $entity = 'Campaign';

        [
            'filter' => $filter,
            'projection' => $fields
        ] = $this->campaignService->getCampaignFilters($request->all());

        $campaigns = $this->salesforceService->fetchRecords($entity,
            array_merge($request->query(), $fields), [
                'where' => $filter
            ]);

        if (empty($filter)) {
            $totalCampaigns = $this->salesforceService->countAllRecords($entity);
            $response = array_merge($campaigns, $totalCampaigns);
        } else {
            $response = $campaigns;
        }

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param $campaignId
     * @return JsonResponse
     */
    public function getChartData(Request $request, $campaignId): JsonResponse
    {
        $response = $this->campaignService->getChartData($request->all(), $campaignId);

        return response()->json($response, Response::HTTP_OK);
    }
}
