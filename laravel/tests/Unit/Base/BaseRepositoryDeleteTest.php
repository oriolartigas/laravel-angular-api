<?php

declare(strict_types=1);

namespace Tests\Unit\Base;

use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class BaseRepositoryDeleteTest extends BaseRepositoryTest
{
    /**
     * Tests the delete method
     */
    public function test_delete_returns_boolean(): void
    {
        $mockId = 1;

        // Simulate the delete call methods

        $this->mockedModel->shouldReceive(methodNames: 'findOrFail')
            ->with($mockId)
            ->once()
            ->andReturnSelf();

        $this->mockedModel->shouldReceive(methodNames: 'delete')
            ->once()
            ->andReturn(args: true);

        $result = $this->executeRepositoryAction(method: 'delete', args: [$mockId]);

        $this->assertTrue(condition: $result);
    }

    /**
     * Tests that the delete method throws ModelNotFoundException when the model does not exist
     */
    public function test_delete_throws_exception_if_not_found(): void
    {
        $mockId = 999;

        $this->mockedModel->shouldReceive(methodNames: 'findOrFail')
            ->with($mockId)
            ->once()
            ->andThrow(exception: new ModelNotFoundException);

        $this->expectException(exception: ModelNotFoundException::class);

        $this->executeRepositoryAction(method: 'delete', args: [$mockId]);
    }
}
