<?php

namespace Mbsoft\Graph\Contracts;

/**
 * Defines a contract for exporting a graph to a specific format.
 */
interface ExporterInterface
{
    /**
     * Exports the given graph into a specific format.
     *
     * @param GraphInterface $g The graph to export.
     * @return array|string The exported representation (array for JSON, string for XML/text).
     */
    public function export(GraphInterface $g): array|string;
}