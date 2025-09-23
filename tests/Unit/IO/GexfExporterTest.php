<?php

namespace Tests\Unit\IO;

use DOMDocument;
use Mbsoft\Graph\Domain\Graph;
use Mbsoft\Graph\IO\GexfExporter;

test('exports valid GEXF XML', function () {
    $graph = new Graph();
    $graph->addNode('A', ['type' => 'person']);
    $graph->addNode('B', ['type' => 'company']);
    $graph->addEdge('A', 'B', ['relationship' => 'works_for']);

    $exporter = new GexfExporter();
    $xml = $exporter->export($graph);

    // Validate it's well-formed XML
    $dom = new DOMDocument();
    expect($dom->loadXML($xml))->toBeTrue();

    // Check basic structure
    expect($xml)->toContain('<gexf');
    expect($xml)->toContain('version="1.3"');
    expect($xml)->toContain('xmlns="http://www.gexf.net/1.3"');
    expect($xml)->toContain('<graph');
    expect($xml)->toContain('defaultedgetype="directed"');
    expect($xml)->toContain('<node id="A"');
    expect($xml)->toContain('<node id="B"');
    expect($xml)->toContain('<edge');
    expect($xml)->toContain('source="A"');
    expect($xml)->toContain('target="B"');
});

test('includes attributes in GEXF export', function () {
    $graph = new Graph();
    $graph->addNode('A', ['color' => 'red', 'size' => '10']);

    $exporter = new GexfExporter();
    $xml = $exporter->export($graph);

    expect($xml)->toContain('<attributes class="node">');
    expect($xml)->toContain('title="color"');
    expect($xml)->toContain('title="size"');
    expect($xml)->toContain('<attvalues>');
    expect($xml)->toContain('value="red"');
    expect($xml)->toContain('value="10"');
});
