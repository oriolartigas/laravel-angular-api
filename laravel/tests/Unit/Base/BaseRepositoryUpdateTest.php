<?php

declare(strict_types=1);

namespace Tests\Unit\Base;

abstract class BaseRepositoryUpdateTest extends BaseRepositoryTest
{
    /**
     * Valid test data for update
     */
    abstract protected function getTestData(): array;

    /**
     * Tests the update method
     */
    public function test_update_returns_updated_model(): void
    {
        $mockId = 1;
        $data = $this->getTestData();

        // Simulate the update call methods

        $this->mockedModel->shouldReceive(methodNames: 'findOrFail')
            ->with($mockId)
            ->once()
            ->andReturnSelf();

        $this->mockedModel->shouldReceive(methodNames: 'fill')
            ->with($data)
            ->once()
            ->andReturnSelf();

        $this->mockedModel->shouldReceive(methodNames: 'isDirty')
            ->once()
            ->andReturn(args: true);

        $this->mockedModel->shouldReceive(methodNames: 'save')
            ->once()
            ->andReturn(args: true);

        $result = $this->executeRepositoryAction(method: 'update', args: [$mockId, $data]);

        $this->assertInstanceOf(expected: $this->configurator->getModelClass(), actual: $result);
    }
}
