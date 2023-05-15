<?php

namespace App\Interfaces;

interface SalesforceRepositoryInterface
{
    public function getAccessToken(): string | null;

    public function getApiUri(): string | null;

    public function fetchToken($force = false): void;
}
