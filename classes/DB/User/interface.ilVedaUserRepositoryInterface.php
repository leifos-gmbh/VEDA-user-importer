<?php

interface ilVedaUserRepositoryInterface
{
    public function updateUser(ilVedaUserInterface $user_status): void;

    public function deleteUserByOID(string $oid): void;

    public function deleteUserByID(int $usr_id): void;

    public function lookupUserByOID(string $oid): ?ilVedaUserInterface;

    public function lookupUserByID(int $ref_id): ?ilVedaUserInterface;

    public function lookupAllUsers() : ilVedaUserCollectionInterface;
}