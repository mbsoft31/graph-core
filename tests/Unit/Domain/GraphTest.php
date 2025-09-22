<?php

namespace Tests\Unit\Domain;

use Mbsoft\Graph\Domain\Graph;
use InvalidArgumentException;

test('can create empty graph', function () {
    $graph = new Graph();

    expect($graph->isDirected())->toBeTrue();
    expect($graph->nodes())->toBeEmpty();
    expect($graph->edges())->toBeEmpty();
});

test('can create undirected graph', function () {
    $graph = new Graph(directed: false);

    expect($graph->isDirected())->toBeFalse();
});

test('can add nodes', function () {
    $graph = new Graph();

    $graph->addNode('A');
    $graph->addNode('B', ['label' => 'Node B']);

    expect($graph->hasNode('A'))->toBeTrue();
    expect($graph->hasNode('B'))->toBeTrue();
    expect($graph->hasNode('C'))->toBeFalse();
    expect($graph->nodes())->toHaveCount(2);
    expect($graph->nodes())->toContain('A', 'B');
});

test('can add and update node attributes', function () {
    $graph = new Graph();

    $graph->addNode('A', ['color' => 'red', 'size' => 10]);
    expect($graph->nodeAttrs('A'))->toEqual(['color' => 'red', 'size' => 10]);

    // Update with merge
    $graph->addNode('A', ['color' => 'blue', 'weight' => 5]);
    expect($graph->nodeAttrs('A'))->toEqual(['color' => 'blue', 'weight' => 5, 'size' => 10]);

    // Set attributes (replace all)
    $graph->setNodeAttrs('A', ['new' => 'value']);
    expect($graph->nodeAttrs('A'))->toEqual(['new' => 'value']);
});

test('can add edges in directed graph', function () {
    $graph = new Graph(directed: true);

    $graph->addEdge('A', 'B', ['weight' => 1.5]);

    expect($graph->hasNode('A'))->toBeTrue();
    expect($graph->hasNode('B'))->toBeTrue();
    expect($graph->hasEdge('A', 'B'))->toBeTrue();
    expect($graph->hasEdge('B', 'A'))->toBeFalse(); // Directed

    expect($graph->edges())->toHaveCount(1);
    $edge = $graph->edges()[0];
    expect($edge->from)->toBe('A');
    expect($edge->to)->toBe('B');
    expect($edge->attributes)->toEqual(['weight' => 1.5]);
});

test('can add edges in undirected graph', function () {
    $graph = new Graph(directed: false);

    $graph->addEdge('A', 'B', ['weight' => 2.0]);

    expect($graph->hasEdge('A', 'B'))->toBeTrue();
    expect($graph->hasEdge('B', 'A'))->toBeTrue(); // Both directions

    // Only one edge returned (canonicalized)
    expect($graph->edges())->toHaveCount(1);
    $edge = $graph->edges()[0];
    expect($edge->from)->toBe('A');
    expect($edge->to)->toBe('B');
});

test('can get successors and predecessors in directed graph', function () {
    $graph = new Graph(directed: true);

    $graph->addEdge('A', 'B');
    $graph->addEdge('A', 'C');
    $graph->addEdge('B', 'C');
    $graph->addEdge('C', 'D');

    expect($graph->successors('A'))->toEqual(['B', 'C'])
        ->and($graph->successors('B'))->toEqual(['C'])
        ->and($graph->successors('C'))->toEqual(['D'])
        ->and($graph->successors('D'))->toEqual([])
        ->and($graph->predecessors('A'))->toEqual([])
        ->and($graph->predecessors('B'))->toEqual(['A'])
        ->and($graph->predecessors('C'))->toEqual(['A', 'B'])
        ->and($graph->predecessors('D'))->toEqual(['C']);

});

test('can get successors and predecessors in undirected graph', function () {
    $graph = new Graph(directed: false);

    $graph->addEdge('A', 'B');
    $graph->addEdge('B', 'C');

    // In undirected graphs, successors and predecessors are the same (neighbors)
    expect($graph->successors('A'))->toMatchArray(['B']);
    expect($graph->predecessors('A'))->toMatchArray(['B']);

    expect($graph->successors('B'))->toMatchArray(['A', 'C']);
    expect($graph->predecessors('B'))->toMatchArray(['A', 'C']);

    expect($graph->successors('C'))->toMatchArray(['B']);
    expect($graph->predecessors('C'))->toMatchArray(['B']);
});

test('prevents duplicate edges', function () {
    $graph = new Graph();

    $graph->addEdge('A', 'B', ['weight' => 1]);
    $graph->addEdge('A', 'B', ['weight' => 2]); // Updates attributes

    expect($graph->edges())->toHaveCount(1);
    expect($graph->edgeAttrs('A', 'B'))->toEqual(['weight' => 2]);
});

test('can handle self-loops', function () {
    $graph = new Graph();

    $graph->addEdge('A', 'A', ['type' => 'loop']);

    expect($graph->hasEdge('A', 'A'))->toBeTrue();
    expect($graph->successors('A'))->toMatchArray(['A']);
    expect($graph->predecessors('A'))->toMatchArray(['A']);
});

test('throws exception for non-existent nodes', function () {
    $graph = new Graph();

    expect(fn() => $graph->nodeAttrs('X'))
        ->toThrow(InvalidArgumentException::class, "Node 'X' does not exist");

    expect(fn() => $graph->setNodeAttrs('X', []))
        ->toThrow(InvalidArgumentException::class, "Node 'X' does not exist");

    expect(fn() => $graph->successors('X'))
        ->toThrow(InvalidArgumentException::class, "Node 'X' does not exist");

    expect(fn() => $graph->predecessors('X'))
        ->toThrow(InvalidArgumentException::class, "Node 'X' does not exist");
});

test('throws exception for non-existent edges', function () {
    $graph = new Graph();
    $graph->addNode('A');
    $graph->addNode('B');

    expect(fn() => $graph->edgeAttrs('A', 'B'))
        ->toThrow(InvalidArgumentException::class, "Edge from 'A' to 'B' does not exist");

    expect(fn() => $graph->setEdgeAttrs('A', 'B', []))
        ->toThrow(InvalidArgumentException::class, "Edge from 'A' to 'B' does not exist");
});

test('can create graph from edge list', function () {
    $edges = [
        ['A', 'B', ['weight' => 1]],
        ['B', 'C', ['weight' => 2]],
        ['C', 'D'],
    ];

    $graph = Graph::fromEdgeList($edges);

    expect($graph->nodes())->toHaveCount(4);
    expect($graph->edges())->toHaveCount(3);
    expect($graph->hasEdge('A', 'B'))->toBeTrue();
    expect($graph->hasEdge('B', 'C'))->toBeTrue();
    expect($graph->hasEdge('C', 'D'))->toBeTrue();
    expect($graph->edgeAttrs('A', 'B'))->toEqual(['weight' => 1]);
    expect($graph->edgeAttrs('C', 'D'))->toEqual([]);
});