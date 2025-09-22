<?php

namespace Tests\Unit\Domain;

use Mbsoft\Graph\Domain\Graph;
use Mbsoft\Graph\Domain\SubgraphView;
use InvalidArgumentException;

test('can create subgraph view', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'B');
    $graph->addEdge('B', 'C');
    $graph->addEdge('C', 'D');
    $graph->addEdge('D', 'A');

    $subgraph = new SubgraphView($graph, ['A', 'B', 'C']);

    expect($subgraph->isDirected())->toBe($graph->isDirected());
    expect($subgraph->nodes())->toMatchArray(['A', 'B', 'C']);
    expect($subgraph->hasNode('A'))->toBeTrue();
    expect($subgraph->hasNode('B'))->toBeTrue();
    expect($subgraph->hasNode('C'))->toBeTrue();
    expect($subgraph->hasNode('D'))->toBeFalse();
});

test('filters edges correctly', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'B', ['weight' => 1]);
    $graph->addEdge('B', 'C', ['weight' => 2]);
    $graph->addEdge('C', 'D', ['weight' => 3]);
    $graph->addEdge('D', 'A', ['weight' => 4]);

    $subgraph = new SubgraphView($graph, ['A', 'B', 'C']);

    // Only edges within the subgraph nodes
    $edges = $subgraph->edges();
    expect($edges)->toHaveCount(2);

    expect($subgraph->hasEdge('A', 'B'))->toBeTrue();
    expect($subgraph->hasEdge('B', 'C'))->toBeTrue();
    expect($subgraph->hasEdge('C', 'D'))->toBeFalse(); // D not in subgraph
    expect($subgraph->hasEdge('D', 'A'))->toBeFalse(); // D not in subgraph
});

test('filters successors and predecessors', function () {
    $graph = new Graph();
    $graph->addEdge('A', 'B');
    $graph->addEdge('A', 'C');
    $graph->addEdge('A', 'D');
    $graph->addEdge('B', 'C');
    $graph->addEdge('D', 'B');

    $subgraph = new SubgraphView($graph, ['A', 'B', 'C']);

    // A's successors: B and C are in subgraph, D is not
    expect($subgraph->successors('A'))->toMatchArray(['B', 'C']);

    // B's predecessors: A is in subgraph, D is not
    expect($subgraph->predecessors('B'))->toMatchArray(['A']);
});

test('preserves node and edge attributes', function () {
    $graph = new Graph();
    $graph->addNode('A', ['color' => 'red']);
    $graph->addNode('B', ['color' => 'blue']);
    $graph->addEdge('A', 'B', ['weight' => 5]);

    $subgraph = new SubgraphView($graph, ['A', 'B']);

    expect($subgraph->nodeAttrs('A'))->toEqual(['color' => 'red']);
    expect($subgraph->nodeAttrs('B'))->toEqual(['color' => 'blue']);
    expect($subgraph->edgeAttrs('A', 'B'))->toEqual(['weight' => 5]);
});

test('throws exception for nodes not in view', function () {
    $graph = new Graph();
    $graph->addNode('A');
    $graph->addNode('B');

    $subgraph = new SubgraphView($graph, ['A']);

    expect(fn() => $subgraph->nodeAttrs('B'))
        ->toThrow(InvalidArgumentException::class, "Node 'B' does not exist in the subgraph view");

    expect(fn() => $subgraph->successors('B'))
        ->toThrow(InvalidArgumentException::class, "Node 'B' does not exist in the subgraph view");
});