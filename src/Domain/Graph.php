<?php

namespace Mbsoft\Graph\Domain;

use InvalidArgumentException;
use Mbsoft\Graph\Contracts\MutableGraphInterface;
use Mbsoft\Graph\Support\IndexMap;

/**
 * A concrete implementation of a mutable graph.
 *
 * Internally, this class uses integer indices for nodes to achieve high
 * performance for adjacency lookups, while exposing a public API based on strings.
 */
final class Graph implements MutableGraphInterface
{
    private const ERR_NODE_NOT_FOUND = "Node '%s' does not exist in the graph";
    private const ERR_EDGE_NOT_FOUND = "Edge from '%s' to '%s' does not exist";

    /** @var array<int, array<int, true>> Adjacency list for successors (out-edges) as sets. */
    private array $succ = [];

    /** @var array<int, array<int, true>> Adjacency list for predecessors (in-edges) as sets. */
    private array $pred = [];

    /** @var array<int, array<string, mixed>> Attribute map for nodes. */
    private array $nodeAttributes = [];

    /** @var array<int, array<int, array<string, mixed>>> Nested attribute map for edges. */
    private array $edgeAttributes = [];

    private readonly IndexMap $ids;

    /** @var list<Edge>|null Cached list of all edges. */
    private ?array $cachedEdges = null;

    private bool $edgesCacheDirty = true;

    public function __construct(private readonly bool $directed = true)
    {
        $this->ids = new IndexMap();
    }

    // --- Implementation of GraphInterface ---

    public function isDirected(): bool
    {
        return $this->directed;
    }

    public function nodes(): array
    {
        return $this->ids->allIds();
    }

    public function edges(): array
    {
        if ($this->edgesCacheDirty || $this->cachedEdges === null) {
            $edges = [];
            foreach ($this->succ as $fromIdx => $successors) {
                $fromId = $this->ids->id($fromIdx);
                foreach (array_keys($successors) as $toIdx) {
                    $toId = $this->ids->id($toIdx);

                    // For undirected graphs, only include each edge once
                    if (!$this->directed && $fromId > $toId) {
                        continue;
                    }

                    $attrs = $this->edgeAttributes[$fromIdx][$toIdx] ?? [];
                    $edges[] = new Edge($fromId, $toId, $attrs);
                }
            }
            $this->cachedEdges = $edges;
            $this->edgesCacheDirty = false;
        }

        return $this->cachedEdges;
    }

    public function successors(string $id): array
    {
        if (!$this->hasNode($id)) {
            throw new InvalidArgumentException(sprintf(self::ERR_NODE_NOT_FOUND, $id));
        }

        $idx = $this->ids->index($id);
        $result = [];

        if (isset($this->succ[$idx])) {
            foreach (array_keys($this->succ[$idx]) as $succIdx) {
                $result[] = $this->ids->id($succIdx);
            }
        }

        sort($result);

        return $result;
    }

    public function predecessors(string $id): array
    {
        if (!$this->hasNode($id)) {
            throw new InvalidArgumentException(sprintf(self::ERR_NODE_NOT_FOUND, $id));
        }

        $idx = $this->ids->index($id);
        $result = [];

        if (isset($this->pred[$idx])) {
            foreach (array_keys($this->pred[$idx]) as $predIdx) {
                $result[] = $this->ids->id($predIdx);
            }
        }

        sort($result);

        return $result;
    }

    public function hasNode(string $id): bool
    {
        return $this->ids->hasId($id);
    }

    public function hasEdge(string $u, string $v): bool
    {
        if (!$this->hasNode($u) || !$this->hasNode($v)) {
            return false;
        }

        $uIdx = $this->ids->index($u);
        $vIdx = $this->ids->index($v);

        return isset($this->succ[$uIdx][$vIdx]);
    }

    public function nodeAttrs(string $id): array
    {
        if (!$this->hasNode($id)) {
            throw new InvalidArgumentException(sprintf(self::ERR_NODE_NOT_FOUND, $id));
        }

        $idx = $this->ids->index($id);

        return $this->nodeAttributes[$idx] ?? [];
    }

    public function edgeAttrs(string $u, string $v): array
    {
        if (!$this->hasEdge($u, $v)) {
            throw new InvalidArgumentException(sprintf(self::ERR_EDGE_NOT_FOUND, $u, $v));
        }

        $uIdx = $this->ids->index($u);
        $vIdx = $this->ids->index($v);

        return $this->edgeAttributes[$uIdx][$vIdx] ?? [];
    }

    // --- Implementation of MutableGraphInterface ---

    public function addNode(string $id, array $attrs = []): void
    {
        $idx = $this->ids->index($id);

        // Initialize adjacency lists if needed
        $this->succ[$idx] ??= [];
        $this->pred[$idx] ??= [];

        // Merge attributes (new attributes override existing ones)
        $this->nodeAttributes[$idx] = $attrs + ($this->nodeAttributes[$idx] ?? []);

        // Mark edges cache as dirty since node addition may affect edge list
        $this->edgesCacheDirty = true;
    }

    public function addEdge(string $u, string $v, array $attrs = []): void
    {
        // Ensure both nodes exist
        $this->addNode($u);
        $this->addNode($v);

        $uIdx = $this->ids->index($u);
        $vIdx = $this->ids->index($v);

        // Add edge u -> v
        $this->succ[$uIdx][$vIdx] = true;
        $this->pred[$vIdx][$uIdx] = true;
        $this->edgeAttributes[$uIdx][$vIdx] = $attrs;

        // For undirected graphs, also add v -> u
        if (!$this->directed && $u !== $v) {
            $this->succ[$vIdx][$uIdx] = true;
            $this->pred[$uIdx][$vIdx] = true;
            $this->edgeAttributes[$vIdx][$uIdx] = $attrs;
        }

        // Mark edges cache as dirty since edge addition affects edge list
        $this->edgesCacheDirty = true;
    }

    public function setNodeAttrs(string $id, array $attrs): void
    {
        if (!$this->hasNode($id)) {
            throw new InvalidArgumentException(sprintf(self::ERR_NODE_NOT_FOUND, $id));
        }

        $idx = $this->ids->index($id);
        $this->nodeAttributes[$idx] = $attrs;

        $this->edgesCacheDirty = true;
    }

    public function setEdgeAttrs(string $u, string $v, array $attrs): void
    {
        if (!$this->hasEdge($u, $v)) {
            throw new InvalidArgumentException(sprintf(self::ERR_EDGE_NOT_FOUND, $u, $v));
        }

        $uIdx = $this->ids->index($u);
        $vIdx = $this->ids->index($v);

        $this->edgeAttributes[$uIdx][$vIdx] = $attrs;

        // For undirected graphs, update both directions
        if (!$this->directed && $u !== $v) {
            $this->edgeAttributes[$vIdx][$uIdx] = $attrs;
        }

        $this->edgesCacheDirty = true;
    }

    // --- Factory Methods ---

    /**
     * Creates a graph from an edge list.
     *
     * @param array<array{0: string, 1: string, 2?: array<string, mixed>}> $edges
     * @param bool                                                         $directed
     *
     * @return self
     */
    public static function fromEdgeList(array $edges, bool $directed = true): self
    {
        $graph = new self($directed);
        foreach ($edges as $edge) {
            $graph->addEdge($edge[0], $edge[1], $edge[2] ?? []);
        }

        return $graph;
    }
}
