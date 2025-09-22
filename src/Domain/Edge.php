<?php

namespace Mbsoft\Graph\Domain;

/**
 * A simple, immutable representation of a graph edge.
 */
final readonly class Edge
{
    /**
     * @param string $from The ID of the source node.
     * @param string $to The ID of the target node.
     * @param array<string, mixed> $attributes The edge's attributes.
     */
    public function __construct(
        public string $from,
        public string $to,
        public array $attributes = [],
    ) {}
}