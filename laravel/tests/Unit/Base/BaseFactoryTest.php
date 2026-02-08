<?php

declare(strict_types=1);

namespace Tests\Unit\Base;

use Arr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\TestCase;

abstract class BaseFactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The configurator of the model
     */
    protected BaseTestConfigurator $configurator;

    /**
     * Get the configurator of the model
     */
    abstract protected function getConfigurator(): BaseTestConfigurator;

    /**
     * Get the allowed fields but not fillable
     */
    abstract protected function getAllowedFieldsButNotFillable(): array;

    /**
     * Initializes the Mocked Eloquent Model.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->configurator = $this->getConfigurator();
    }

    // --- TESTS ---

    /**
     * Test that the factory only creates fillable fields.
     * Merge the fillable fields with the allowed but not fillable fields
     * and compare them with the factory fields
     */
    public function test_factory_only_creates_fillable_fields(): void
    {
        $modelInstance = $this->configurator->getModelInstance();
        $tableName = $modelInstance->getTable();

        $factoryFields = array_keys($modelInstance::factory()->make()->toArray());
        $fillableFields = $modelInstance->getFillable();
        $allowedButNotFillable = $this->getAllowedFieldsButNotFillable();
        $allowedFields = array_merge($fillableFields, $allowedButNotFillable);

        // Get the non-existent fields
        $nonExistentColumns = array_diff($factoryFields, $allowedFields);

        $this->assertEmpty(
            $nonExistentColumns,
            "The factory is generating columns that do not exist in the '{$tableName}' table: ".implode(', ', $nonExistentColumns)
        );
    }

    /**
     * Test that the factory definition creates a valid record
     */
    public function test_factory_creates_a_valid_record(): void
    {
        $modelInstance = $this->configurator->getModelInstance();
        $model = $modelInstance::factory()->create();
        $tableName = $model->getTable();

        $expectedAttributes = Arr::except($model->toArray(), [
            'created_at',
            'updated_at',
        ]);

        $this->assertDatabaseHas($tableName, $expectedAttributes);
    }
}
