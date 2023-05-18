<?php

namespace App\Interfaces;

interface ContactRepositoryInterface
{
    public function countTotalContacts();

    public function fetchContacts(string $query);

    public function updateContact(string $id, $dto);
}
