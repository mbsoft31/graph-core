<?php

namespace Tests\Feature;

use Mbsoft\Graph\Domain\Graph;

test('handles large graphs efficiently', function () {
    $graph = new Graph();

    // Create a graph with 1000 nodes
    $nodeCount = 1000;
    for ($i = 0; $i < $nodeCount; $i++) {
        $graph->addNode("node_$i", ['index' => $i]);
    }

    // Add edges to create a connected graph
    for ($i = 0; $i < $nodeCount - 1; $i++) {
        $graph->addEdge("node_$i", "node_" . ($i + 1));
        // Add some random edges
        if ($i % 10 === 0) {
            $target = rand(0, $nodeCount - 1);
            $graph->addEdge("node_$i", "node_$target");
        }
    }

    expect($graph->nodes())->toHaveCount($nodeCount)
        ->and(count($graph->edges()))->toBeGreaterThan($nodeCount);

    // Test that operations remain fast
    $startTime = microtime(true);
    $graph->successors('node_500');
    $graph->predecessors('node_500');
    $graph->hasEdge('node_0', 'node_999');
    $duration = microtime(true) - $startTime;

    // These operations should complete in milliseconds
    expect($duration)->toBeLessThan(0.01); // Less than 10ms
});
