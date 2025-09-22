<?php

namespace Mbsoft\Graph\IO\Concerns;

use Mbsoft\Graph\Contracts\GraphInterface;

trait CollectsAttributes
{
    /**
     * Collects all unique attribute keys from nodes or edges in the graph.
     *
     * @param GraphInterface $g The graph instance.
     * @param string $type Either 'node' or 'edge' to specify which attributes to collect.
     * @return array An array of unique attribute keys.
     */
    private function collectAttributeKeys(GraphInterface $g, string $type): array
    {
        $keys = [];

        if ($type === 'node') {
            foreach ($g->nodes() as $nodeId) {
                foreach (array_keys($g->nodeAttrs($nodeId)) as $key) {
                    $keys[$key] = true;
                }
            }
        } else {
            foreach ($g->edges() as $edge) {
                foreach (array_keys($edge->attributes) as $key) {
                    $keys[$key] = true;
                }
            }
        }

        return array_keys($keys);
    }
}
