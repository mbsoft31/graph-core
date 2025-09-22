<?php

namespace Mbsoft\Graph\Domain;

/**
 * A simple, immutable representation of a graph node.
 */
final readonly class Node
{
    /**
     * @param string $id The unique ID of the node.
     * @param array<string, mixed> $attributes The node's attributes.
     */
    public function __construct(
        public string $id,
        public array $attributes = [],
    ) {}
}