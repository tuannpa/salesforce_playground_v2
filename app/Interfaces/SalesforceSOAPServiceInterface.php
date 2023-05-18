<?php

namespace App\Interfaces;

interface SalesforceSOAPServiceInterface
{
    public function decryptLoginData(string $encryptedString): string;

    public function authenticate(string $username, string $password, string $securityToken): array;

    public function accountLogout(): array;
}
