<?php

namespace Tests\Unit\IO;

use DOMDocument;
use Mbsoft\Graph\Domain\Graph;
use Mbsoft\Graph\IO\GraphMLExporter;

test('exports valid GraphML XML', function () {
    $graph = new Graph();
    $graph->addNode('A', ['label' => 'Node A']);
    $graph->addNode('B', ['label' => 'Node B']);
    $graph->addEdge('A', 'B', ['weight' => '1.5']);

    $exporter = new GraphMLExporter();
    $xml = $exporter->export($graph);

    // Validate it's well-formed XML
    $dom = new DOMDocument();
    expect($dom->loadXML($xml))->toBeTrue();

    // Check basic structure
    expect($xml)->toContain('<graphml');
    expect($xml)->toContain('xmlns="http://graphml.graphdrawing.org/xmlns"');
    expect($xml)->toContain('<graph');
    expect($xml)->toContain('edgedefault="directed"');
    expect($xml)->toContain('<node id="A">');
    expect($xml)->toContain('<node id="B">');
    expect($xml)->toContain('<edge');
    expect($xml)->toContain('source="A"');
    expect($xml)->toContain('target="B"');
});

test('exports undirected graph correctly', function () {
    $graph = new Graph(directed: false);
    $graph->addEdge('A', 'B');

    $exporter = new GraphMLExporter();
    $xml = $exporter->export($graph);

    expect($xml)->toContain('edgedefault="undirected"');
});
