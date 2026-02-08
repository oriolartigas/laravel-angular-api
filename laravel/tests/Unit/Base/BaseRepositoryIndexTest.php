<?php

declare(strict_types=1);

namespace Tests\Unit\Base;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;

abstract class BaseRepositoryIndexTest extends BaseRepositoryTest
{
    /**
     * Tests that the index method returns a collection of models
     * and calls the correct methods
     */
    public function test_index_returns_collection_of_models(): void
    {
        // Create a Mock collection object
        $collection = new EloquentCollection(items: [new ($this->configurator->getModelClass())]);

        // Simulate the index call methods

        $this->mockedModel->shouldReceive(methodNames: 'with')
            ->atLeast()
            ->once()
            ->andReturnSelf();

        $this->mockedModel->shouldReceive(methodNames: 'withCount')
            ->atLeast()
            ->once()
            ->andReturnSelf();

        $this->mockedModel->shouldReceive(methodNames: 'where')
            ->once()
            ->andReturnSelf();

        $this->mockedModel->shouldReceive(methodNames: 'sort')
            ->once()
            ->andReturnSelf();

        $this->mockedModel->shouldReceive(methodNames: 'get')
            ->once()
            ->andReturn(args: $collection);

        $result = $this->executeRepositoryAction(method: 'index', args: [[]]);

        $this->assertInstanceOf(expected: EloquentCollection::class, actual: $result);
        $this->assertCount(expectedCount: 1, haystack: $result);
    }
}
