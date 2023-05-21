<?php

namespace App\Interfaces;

interface SalesforceServiceInterface
{
    /**
     * @param string $encryptedString
     * @return string
     */
    public function decryptLoginData(string $encryptedString): string;

    /**
     * @param string $username
     * @param string $password
     * @param string $securityToken
     * @return array
     */
    public function authenticate(string $username, string $password, string $securityToken): array;

    /**
     * @return array
     */
    public function accountLogout(): array;

    /**
     * @param string $entity
     * @return array
     */
    public function countAllRecords(string $entity): array;

    /**
     * @param string $entity
     * @param array $queryParams
     * @return array
     */
    public function fetchRecords(string $entity, array $queryParams): array;

    /**
     * @param string $entity
     * @param string $projection
     * @param array $filter
     * @param bool $queryAll
     * @return array
     */
    public function export(string $entity, string $projection, array $filter, bool $queryAll): array;

    /**
     * @param string $exportId
     * @return array
     */
    public function getExportResult(string $exportId): array;
}
