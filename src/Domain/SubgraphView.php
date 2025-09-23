<?php

namespace Mbsoft\Graph\Domain;

use InvalidArgumentException;
use Mbsoft\Graph\Contracts\GraphInterface;

/**
 * A read-only, immutable view of a subset of another graph.
 * It does not copy graph data, but filters it on the fly.
 */
final readonly class SubgraphView implements GraphInterface
{
    /** @var array<string, int> A hash set of node IDs included in this view for quick lookups. */
    private array $nodeSet;

    /**
     * @param GraphInterface $originalGraph The graph to create a view from.
     * @param list<string>   $nodeIds       The list of node IDs to include in the view.
     */
    public function __construct(
        private GraphInterface $originalGraph,
        array $nodeIds,
    ) {
        $this->nodeSet = array_flip($nodeIds);
    }

    public function isDirected(): bool
    {
        return $this->originalGraph->isDirected();
    }

    public function nodes(): array
    {
        return array_keys($this->nodeSet);
    }

    public function edges(): array
    {
        $edges = [];

        foreach ($this->originalGraph->edges() as $edge) {
            // Only include edges where both nodes are in the subgraph
            if (isset($this->nodeSet[$edge->from]) && isset($this->nodeSet[$edge->to])) {
                $edges[] = $edge;
            }
        }

        return $edges;
    }

    public function successors(string $id): array
    {
        if (!$this->hasNode($id)) {
            throw new InvalidArgumentException("Node '$id' does not exist in the subgraph view");
        }

        $successors = $this->originalGraph->successors($id);

        // Filter to only include successors that are in the subgraph
        return array_values(array_filter(
            $successors,
            fn ($succId) => isset($this->nodeSet[$succId]),
        ));
    }

    public function predecessors(string $id): array
    {
        if (!$this->hasNode($id)) {
            throw new InvalidArgumentException("Node '$id' does not exist in the subgraph view");
        }

        $predecessors = $this->originalGraph->predecessors($id);

        // Filter to only include predecessors that are in the subgraph
        return array_values(array_filter(
            $predecessors,
            fn ($predId) => isset($this->nodeSet[$predId]),
        ));
    }

    public function hasNode(string $id): bool
    {
        return isset($this->nodeSet[$id]);
    }

    public function hasEdge(string $u, string $v): bool
    {
        // Both nodes must be in the subgraph
        if (!isset($this->nodeSet[$u]) || !isset($this->nodeSet[$v])) {
            return false;
        }

        return $this->originalGraph->hasEdge($u, $v);
    }

    public function nodeAttrs(string $id): array
    {
        if (!$this->hasNode($id)) {
            throw new InvalidArgumentException("Node '$id' does not exist in the subgraph view");
        }

        return $this->originalGraph->nodeAttrs($id);
    }

    public function edgeAttrs(string $u, string $v): array
    {
        if (!$this->hasEdge($u, $v)) {
            throw new InvalidArgumentException("Edge from '$u' to '$v' does not exist in the subgraph view");
        }

        return $this->originalGraph->edgeAttrs($u, $v);
    }
}
