<?php

namespace App\Interfaces;

interface ContactRepositoryInterface
{
    public function countTotalContacts();

    public function fetchContacts(array $query);

    public function updateContact(string $id, $dto);
}
