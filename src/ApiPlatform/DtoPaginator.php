<?php

namespace App\ApiPlatform;

use ApiPlatform\State\Pagination\PaginatorInterface;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

class DtoPaginator implements PaginatorInterface, IteratorAggregate
{
    private array $items;
    private int $totalItems;
    private int $currentPage;
    private int $itemsPerPage;

    public function __construct(array $data, int $currentPage, int $itemsPerPage)
    {
        $this->totalItems = count($data);
        $this->currentPage = $currentPage;
        $this->itemsPerPage = $itemsPerPage;
        $offset = ($currentPage - 1) * $itemsPerPage;
        $this->items = array_slice($data, $offset, $itemsPerPage);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getCurrentPage(): float
    {
        return $this->currentPage;
    }

    public function getLastPage(): float
    {
        return (int) ceil($this->totalItems / $this->itemsPerPage);
    }

    public function getTotalItems(): float
    {
        return $this->totalItems;
    }

    public function getItemsPerPage(): float
    {
        return $this->itemsPerPage;
    }
}
