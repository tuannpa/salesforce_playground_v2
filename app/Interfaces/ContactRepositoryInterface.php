<?php

namespace App\Interfaces;

interface ContactRepositoryInterface
{
    public function countTotalContacts();

    public function fetchContacts($query);

    public function updateContact($id, $dto);
}
