<?php

declare(strict_types=1);

namespace Tests\Unit\Base;

use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class BaseRepositoryFindTest extends BaseRepositoryTest
{
    /**
     * Tests that the find method returns the correct model
     */
    public function test_find_by_id_returns_model(): void
    {
        $mockId = 1;

        $this->mockedModel->shouldReceive(methodNames: 'findOrFail')
            ->with($mockId)
            ->once()
            ->andReturnSelf();

        $result = $this->executeRepositoryAction(method: 'find', args: [$mockId]);

        $this->assertInstanceOf(expected: $this->configurator->getModelClass(), actual: $result);
    }

    /**
     * Tests that the find method throws ModelNotFoundException when the model does not exist
     */
    public function test_find_by_id_throws_exception_if_not_found(): void
    {
        $mockId = 999;

        $this->mockedModel->shouldReceive(methodNames: 'findOrFail')
            ->with($mockId)
            ->once()
            ->andThrow(exception: new ModelNotFoundException);

        $this->expectException(exception: ModelNotFoundException::class);

        $this->executeRepositoryAction(method: 'find', args: [$mockId]);
    }
}
