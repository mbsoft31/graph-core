<?php

namespace Tests\Unit\Support;

use Mbsoft\Graph\Support\IndexMap;
use OutOfBoundsException;

test('can map strings to indices and back', function () {
    $map = new IndexMap();

    expect($map->index('node1'))->toBe(0)// First ID gets index 0
        ->and($map->id(0))->toBe('node1')
        ->and($map->index('node2'))->toBe(1) // Second ID gets index 1
        ->and($map->id(1))->toBe('node2')
        ->and($map->index('node1'))->toBe(0)
        ->and($map->index('node2'))->toBe(1); // Repeated calls return same index
});

test('hasId and hasIndex work correctly', function () {
    $map = new IndexMap();

    $map->index('node1');

    expect($map->hasId('node1'))->toBeTrue()
        ->and($map->hasId('node2'))->toBeFalse()
        ->and($map->hasIndex(0))->toBeTrue()
        ->and($map->hasIndex(1))->toBeFalse();
});

test('allIds returns all registered IDs', function () {
    $map = new IndexMap();

    $map->index('node1');
    $map->index('node2');
    $map->index('node3');

    expect($map->allIds())->toMatchArray(['node1', 'node2', 'node3']);
});

test('throws exception for invalid index', function () {
    $map = new IndexMap();

    $map->index('node1');

    expect(fn () => $map->id(999))->toThrow(OutOfBoundsException::class);
});
