<?php

namespace Mbsoft\Graph\Support;

use OutOfBoundsException;

/**
 * Manages a bidirectional mapping between string IDs and dense integer indices (0..n-1).
 */
final class IndexMap
{
    /** @var array<string, int> */
    private array $idToIdx = [];

    /** @var array<int, string> */
    private array $idxToId = [];

    private int $nextIdx = 0;

    /**
     * Gets the integer index for a given string ID.
     * If the ID does not exist, a new index is created and returned.
     */
    public function index(string $id): int
    {
        if (!isset($this->idToIdx[$id])) {
            $this->idToIdx[$id] = $this->nextIdx;
            $this->idxToId[$this->nextIdx] = $id;
            $this->nextIdx++;
        }

        return $this->idToIdx[$id];
    }

    /**
     * Gets the string ID for a given integer index.
     *
     * @throws OutOfBoundsException if the index does not exist.
     */
    public function id(int $index): string
    {
        if (!isset($this->idxToId[$index])) {
            throw new OutOfBoundsException("No ID found for index: $index");
        }

        return $this->idxToId[$index];
    }

    public function hasId(string $id): bool
    {
        return isset($this->idToIdx[$id]);
    }

    public function hasIndex(int $index): bool
    {
        return isset($this->idxToId[$index]);
    }

    /** @return list<string> */
    public function allIds(): array
    {
        return array_keys($this->idToIdx);
    }
}
