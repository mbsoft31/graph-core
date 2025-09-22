<?php

namespace Mbsoft\Graph\Contracts;

use Mbsoft\Graph\Domain\Edge;

/**
 * Represents an immutable, read-only graph.
 * All node and edge identifiers are strings.
 */
interface GraphInterface
{
    /**
     * Checks if the graph is directed.
     */
    public function isDirected(): bool;

    /**
     * Gets a list of all node IDs in the graph.
     *
     * @return list<string>
     */
    public function nodes(): array;

    /**
     * Gets a list of all edges in the graph.
     *
     * @return list<Edge>
     */
    public function edges(): array;

    /**
     * Gets the IDs of the direct successors of a given node.
     *
     * @param string $id The ID of the node.
     * @return list<string> An array of successor node IDs.
     * @throws \InvalidArgumentException If the node does not exist.
     */
    public function successors(string $id): array;

    /**
     * Gets the IDs of the direct predecessors of a given node.
     *
     * @param string $id The ID of the node.
     * @return list<string> An array of predecessor node IDs.
     * @throws \InvalidArgumentException If the node does not exist.
     */
    public function predecessors(string $id): array;

    /**
     * Checks if a node exists in the graph.
     */
    public function hasNode(string $id): bool;

    /**
     * Checks if a direct edge exists from node 'u' to node 'v'.
     * If the graph is undirected, it checks for an edge in both directions.
     */
    public function hasEdge(string $u, string $v): bool;

    /**
     * Gets a copy of the attributes for a given node.
     *
     * @param string $id The ID of the node.
     * @return array<string, mixed> A copy of the node's attributes.
     * @throws \InvalidArgumentException If the node does not exist.
     */
    public function nodeAttrs(string $id): array;

    /**
     * Gets a copy of the attributes for a given edge.
     *
     * @param string $u The ID of the source node.
     * @param string $v The ID of the target node.
     * @return array<string, mixed> A copy of the edge's attributes.
     * @throws \InvalidArgumentException If the edge does not exist.
     */
    public function edgeAttrs(string $u, string $v): array;
}