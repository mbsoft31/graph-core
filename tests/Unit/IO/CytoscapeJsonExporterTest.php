<?php

namespace Tests\Unit\IO;

use Mbsoft\Graph\Domain\Graph;
use Mbsoft\Graph\IO\CytoscapeJsonExporter;

test('exports empty graph', function () {
    $graph = new Graph();
    $exporter = new CytoscapeJsonExporter();

    $result = $exporter->export($graph);

    expect($result)->toEqual([
        'elements' => [
            'nodes' => [],
            'edges' => []
        ]
    ]);
});

test('exports graph with nodes and edges', function () {
    $graph = new Graph();
    $graph->addNode('A', ['label' => 'Node A', 'color' => 'red']);
    $graph->addNode('B', ['label' => 'Node B']);
    $graph->addEdge('A', 'B', ['weight' => 1.5, 'type' => 'connection']);

    $exporter = new CytoscapeJsonExporter();
    $result = $exporter->export($graph);

    expect($result)->toEqual([
        'elements' => [
            'nodes' => [
                ['data' => ['id' => 'A', 'label' => 'Node A', 'color' => 'red']],
                ['data' => ['id' => 'B', 'label' => 'Node B']]
            ],
            'edges' => [
                ['data' => ['source' => 'A', 'target' => 'B', 'weight' => 1.5, 'type' => 'connection']]
            ]
        ]
    ]);
});