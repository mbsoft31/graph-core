<?php

namespace Mbsoft\Graph\IO;

use DOMDocument;
use DOMElement;
use DOMException;
use Mbsoft\Graph\Contracts\ExporterInterface;
use Mbsoft\Graph\Contracts\GraphInterface;

/**
 * Exports a graph to GraphML XML format.
 */
final class GraphMLExporter implements ExporterInterface
{
    /**
     * @param GraphInterface $g
     * @return string
     * @throws DOMException
     */
    public function export(GraphInterface $g): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        // Create root graphml element
        $graphml = $dom->createElementNS('http://graphml.graphdrawing.org/xmlns', 'graphml');
        $graphml->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $graphml->setAttribute('xsi:schemaLocation',
            'http://graphml.graphdrawing.org/xmlns http://graphml.graphdrawing.org/xmlns/1.0/graphml.xsd');
        $dom->appendChild($graphml);

        // Collect all attribute keys from nodes and edges
        $nodeAttrKeys = $this->collectAttributeKeys($g, 'node');
        $edgeAttrKeys = $this->collectAttributeKeys($g, 'edge');

        // Define attribute keys
        $keyIndex = 0;
        $nodeKeyMap = [];
        foreach ($nodeAttrKeys as $attrName) {
            $key = $dom->createElement('key');
            $key->setAttribute('id', 'd' . $keyIndex);
            $key->setAttribute('for', 'node');
            $key->setAttribute('attr.name', $attrName);
            $key->setAttribute('attr.type', 'string');
            $graphml->appendChild($key);
            $nodeKeyMap[$attrName] = 'd' . $keyIndex;
            $keyIndex++;
        }

        $edgeKeyMap = [];
        foreach ($edgeAttrKeys as $attrName) {
            $key = $dom->createElement('key');
            $key->setAttribute('id', 'd' . $keyIndex);
            $key->setAttribute('for', 'edge');
            $key->setAttribute('attr.name', $attrName);
            $key->setAttribute('attr.type', 'string');
            $graphml->appendChild($key);
            $edgeKeyMap[$attrName] = 'd' . $keyIndex;
            $keyIndex++;
        }

        // Create graph element
        $graph = $dom->createElement('graph');
        $graph->setAttribute('id', 'G');
        $graph->setAttribute('edgedefault', $g->isDirected() ? 'directed' : 'undirected');
        $graphml->appendChild($graph);

        // Add nodes
        foreach ($g->nodes() as $nodeId) {
            $node = $dom->createElement('node');
            $node->setAttribute('id', $nodeId);

            foreach ($g->nodeAttrs($nodeId) as $attrName => $attrValue) {
                if (isset($nodeKeyMap[$attrName])) {
                    $data = $dom->createElement('data', htmlspecialchars((string)$attrValue));
                    $data->setAttribute('key', $nodeKeyMap[$attrName]);
                    $node->appendChild($data);
                }
            }

            $graph->appendChild($node);
        }

        // Add edges
        $edgeId = 0;
        foreach ($g->edges() as $edge) {
            $edgeElement = $dom->createElement('edge');
            $edgeElement->setAttribute('id', 'e' . $edgeId++);
            $edgeElement->setAttribute('source', $edge->from);
            $edgeElement->setAttribute('target', $edge->to);

            foreach ($edge->attributes as $attrName => $attrValue) {
                if (isset($edgeKeyMap[$attrName])) {
                    $data = $dom->createElement('data', htmlspecialchars((string)$attrValue));
                    $data->setAttribute('key', $edgeKeyMap[$attrName]);
                    $edgeElement->appendChild($data);
                }
            }

            $graph->appendChild($edgeElement);
        }

        return $dom->saveXML();
    }

    /**
     * Collects all unique attribute keys from nodes or edges.
     *
     * @return list<string>
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
