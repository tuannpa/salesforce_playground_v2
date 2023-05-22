<?php

namespace App\Interfaces;

interface CampaignServiceInterface
{
    /**
     * @param array $queryParams
     * @return array
     */
    public function getCampaignFilters(array $queryParams): array;

    /**
     * @param array $queryParams
     * @param string $campaignId
     * @return array
     */
    public function getChartData(array $queryParams, string $campaignId): array;
}
