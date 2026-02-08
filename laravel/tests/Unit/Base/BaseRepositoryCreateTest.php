<?php

declare(strict_types=1);

namespace Tests\Unit\Base;

use Mockery;

abstract class BaseRepositoryCreateTest extends BaseRepositoryTest
{
    /**
     * Valid test data for create
     */
    abstract protected function getTestData(): array;

    /**
     * Tests the create method
     */
    public function test_create_returns_new_model(): void
    {
        $data = $this->getTestData();

        $this->mockedModel->shouldReceive('create')
            ->with(Mockery::any())
            ->once()
            ->andReturnSelf();

        $result = $this->executeRepositoryAction('create', [$data]);

        $this->assertInstanceOf($this->configurator->getModelClass(), $result);
    }
}
