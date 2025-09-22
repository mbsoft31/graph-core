# mbsoft/graph-core

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mbsoft/graph-core.svg?style=flat-square)](https://packagist.org/packages/mbsoft/graph-core)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/YOUR_GITHUB_USERNAME/graph-core/ci.yml?branch=main&style=flat-square)](https://github.com/YOUR_GITHUB_USERNAME/graph-core/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/mbsoft/graph-core.svg?style=flat-square)](https://packagist.org/packages/mbsoft/graph-core)

A lightweight, performant, and dependency-free graph data structure library for PHP 8.2+.

## Installation

You can install the package via Composer:

```bash
composer require mbsoft/graph-core
```

## Usage

Here's a simple example of how to use the library:

```php
use Mbsoft\Graph\Domain\Graph;
use Mbsoft\Graph\IO\CytoscapeJsonExporter;

// Create a new directed graph
$g = new Graph(directed: true);

// Add nodes with attributes
$g->addNode('A', ['label' => 'Node A']);
$g->addNode('B', ['label' => 'Node B']);

// Add an edge with attributes
$g->addEdge('A', 'B', ['type' => 'link', 'weight' => 1.5]);

// Get successors
$successors = $g->successors('A'); // Returns ['B']

// Export to Cytoscape.js format
$json = (new CytoscapeJsonExporter())->export($g);
echo $json;
```

## Features

- Directed and undirected graphs
- Weighted edges
- Node and edge attributes

## Testing
To run the test suite, use the following command:

```bash
composer test
```

## License
This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## Credits
- [Mouadh BEKHOUCHE](https://mouadh.tech) - Author

