<?php

namespace App\Services;

use App\Interfaces\CampaignServiceInterface;
use App\Interfaces\SalesforceServiceInterface;

class CampaignService implements CampaignServiceInterface
{
    private SalesforceServiceInterface $salesforceService;


    /**
     * @param SalesforceServiceInterface $salesforceService
     */
    public function __construct(
        SalesforceServiceInterface $salesforceService
    )
    {
        $this->salesforceService = $salesforceService;
    }

    /**
     * @param array $queryParams
     * @return array
     */
    public function getCampaignFilters(array $queryParams): array
    {
        $filterParams = array_filter($queryParams, function($field) {
            return $field === 'status' || $field === 'type';
        }, ARRAY_FILTER_USE_KEY);

        foreach ($filterParams as $f => $v) {
            $filterParams[$f] = [
                'operator' => 'in',
                'value' => $v
            ];
        }

        return [
            'filter' => $filterParams,
            'projection' => [
                'fields' => 'Id, Name, StartDate, EndDate, Status, Type'
            ]
        ];
    }

    /**
     * @param array $queryParams
     * @param string $campaignId
     * @return array[]
     */
    public function getChartData(array $queryParams, string $campaignId): array
    {
        $groupCampaignMembers = $this->salesforceService->fetchRecords('CampaignMember', [
            'fields' => 'Title, Type, count(Id) Total'
        ], [
            'where' => [
                'CampaignId' => [
                    'operator' => '=',
                    'value' => $campaignId
                ]
            ],
            'groupBy' => 'Title, Type'
        ]);

        $typesOfMemberLabels = [];
        $typesOfMemberDataSet = [];
        $numberOfVPs = 0;
        $numberOfCLevels = 0;
        $numberOfManagers = 0;

        if (empty($groupCampaignMembers['records'])) {
            return [];
        }

        foreach ($groupCampaignMembers['records'] as $item) {
            if (!in_array($item['Type'], $typesOfMemberLabels)) {
                $typesOfMemberLabels[] = $item['Type'];
                $idx = array_key_last($typesOfMemberLabels);
                $typesOfMemberDataSet[$idx] = $item['Total'];
            } else {
                $foundIdx = array_search($item['Type'], $typesOfMemberLabels);
                $typesOfMemberDataSet[$foundIdx] += $item['Total'];
            }

            $title = strtolower($item['Title']);

            if (str_contains($title, 'vp') || str_contains($title, 'svp')) {
                $numberOfVPs += $item['Total'];
            }

            if (str_contains($title, 'ceo') || str_contains($title, 'cfo') || str_contains($title, 'cmo')) {
                $numberOfCLevels += $item['Total'];
            }

            if (str_contains($title, 'manager')) {
                $numberOfManagers += $item['Total'];
            }
        }

        $isMembersTitleChartEmpty = $numberOfVPs == 0 && $numberOfCLevels == 0 && $numberOfManagers == 0;

        return [
            'typesOfMemberChart' => empty($typesOfMemberDataSet) ? [] : [
                'labels' => $typesOfMemberLabels,
                'dataSet' => $typesOfMemberDataSet
            ],
            'membersTitleChart' => $isMembersTitleChartEmpty ? [] : [
                'labels' => ['VPs', 'C-Levels', 'Managers'],
                'dataSet' => [
                    'VPs' => [$numberOfVPs, 0, 0],
                    'CLevels' => [0, $numberOfCLevels, 0],
                    'Managers' => [0, 0, $numberOfManagers]
                ]
            ]
        ];
    }
}
