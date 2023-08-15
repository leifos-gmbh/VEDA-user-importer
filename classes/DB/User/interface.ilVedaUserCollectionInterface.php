<?php

interface ilVedaUserCollectionInterface extends Iterator, Countable
{
    public function getUsersWithPendingCreationStatus(): ilVedaUserCollectionInterface;

    public function count(): int;

    public function current(): ilVedaUserInterface;

    public function key(): int;

    public function next(): void;

    public function rewind(): void;

    public function valid(): bool;
}