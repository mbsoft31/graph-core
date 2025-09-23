<?php

namespace Mbsoft\Graph\Contracts;

/**
 * Represents a mutable graph that can be modified.
 */
interface MutableGraphInterface extends GraphInterface
{
    /**
     * Adds a node to the graph. If the node already exists,
     * this method can be used to update its attributes.
     *
     * @param string               $id    The unique identifier for the node.
     * @param array<string, mixed> $attrs Attributes to associate with the node.
     */
    public function addNode(string $id, array $attrs = []): void;

    /**
     * Adds a directed edge from node 'u' to node 'v'.
     * If the graph is undirected, a symmetric edge is also implied.
     * If nodes 'u' or 'v' do not exist, they should be created.
     *
     * @param string               $u     The ID of the source node.
     * @param string               $v     The ID of the target node.
     * @param array<string, mixed> $attrs Attributes for the edge (e.g., 'weight').
     */
    public function addEdge(string $u, string $v, array $attrs = []): void;

    /**
     * Sets or overwrites all attributes for a given node.
     *
     * @param string               $id    The ID of the node.
     * @param array<string, mixed> $attrs The complete set of attributes to apply.
     *
     * @throws \InvalidArgumentException If the node does not exist.
     */
    public function setNodeAttrs(string $id, array $attrs): void;

    /**
     * Sets or overwrites all attributes for a given edge.
     *
     * @param string               $u     The ID of the source node.
     * @param string               $v     The ID of the target node.
     * @param array<string, mixed> $attrs The complete set of attributes to apply.
     *
     * @throws \InvalidArgumentException If the edge does not exist.
     */
    public function setEdgeAttrs(string $u, string $v, array $attrs): void;
}
