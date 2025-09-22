<?php

namespace Mbsoft\Graph\IO;

use Mbsoft\Graph\Contracts\ExporterInterface;
use Mbsoft\Graph\Contracts\GraphInterface;

/**
 * Exports a graph to Cytoscape.js JSON format.
 */
final class CytoscapeJsonExporter implements ExporterInterface
{
    /**
     * {@inheritDoc}
     *
     * @return array{elements: array{nodes: list<array>, edges: list<array>}}
     */
    public function export(GraphInterface $g): array
    {
        $nodes = [];
        foreach ($g->nodes() as $nodeId) {
            $nodes[] = [
                'data' => array_merge(
                    ['id' => $nodeId],
                    $g->nodeAttrs($nodeId)
                )
            ];
        }

        $edges = [];
        foreach ($g->edges() as $edge) {
            $edges[] = [
                'data' => array_merge(
                    [
                        'source' => $edge->from,
                        'target' => $edge->to,
                    ],
                    $edge->attributes
                )
            ];
        }

        return [
            'elements' => [
                'nodes' => $nodes,
                'edges' => $edges
            ]
        ];
    }
}