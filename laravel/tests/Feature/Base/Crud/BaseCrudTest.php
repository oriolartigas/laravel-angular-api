<?php

declare(strict_types=1);

namespace Tests\Feature\Base\Crud;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\TestCase;

abstract class BaseCrudTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * The configurator of the model
     */
    protected BaseTestConfigurator $configurator;

    /**
     * Get the configurator of the model
     *
     * This method must be implemented by concrete test classes to provide a specific
     * BaseTestConfigurator instance that contains all the necessary configuration
     * for testing a particular model's CRUD operations, including factory data,
     * endpoint URLs, and relationship definitions.
     */
    abstract protected function getConfigurator(): BaseTestConfigurator;

    /**
     * Set up the test
     *
     * Initializes the test environment by calling the parent setUp method
     * and configuring the test-specific configurator instance. This method
     * is called before each test method execution to ensure a clean and
     * consistent testing environment with all necessary dependencies properly
     * initialized and configured for the specific model being tested.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->configurator = $this->getConfigurator();
    }

    /**
     * Remove pivot fields from expected database data
     * since they are stored in a separate pivot table.
     *
     * This method filters out belongsToMany relationship fields from the data
     * that will be used for database assertions, as these relationships are
     * stored in separate pivot tables rather than as columns in the main model
     * table. This ensures accurate database state verification.
     *
     * @param  array  $data  The database data to filter
     * @return array The data without pivot fields, suitable for database assertions
     */
    protected function getExpectedDatabaseData(array $data): array
    {
        $belongsToKeys = array_keys($this->configurator->getBelongsToRelations() ?? []);
        $belongsToManyKeys = array_keys($this->configurator->getBelongsToManyRelations() ?? []);
        $hasManyKeys = array_keys($this->configurator->getHasManyRelations() ?? []);

        $keysToExclude = array_merge(
            $belongsToManyKeys,
            $belongsToKeys,
            $hasManyKeys
        );

        return collect($data)->except($keysToExclude)->toArray();
    }

    /**
     * Remove pivot fields from expected JSON response data.
     *
     * This method processes response data to exclude belongsToMany relationship
     * fields that should not appear in the JSON response structure. It ensures
     * that API response assertions only check for fields that are actually
     * returned by the controller, maintaining accurate response validation.
     *
     * @param  array  $data  The JSON response data to filter
     * @return array The data without pivot fields, suitable for response assertions
     */
    protected function getExpectedResponseKeys(array $data): array
    {
        return collect($data)->except(array_keys($this->configurator->getBelongsToManyRelations()))->toArray();
    }

    /**
     * Get the pivot table name based on Laravel's default convention.
     *
     * Laravel automatically generates pivot table names by combining the
     * singular forms of both model names in alphabetical order, separated
     * by an underscore. This method replicates that convention to generate
     * the correct table name for database assertions on pivot relationships.
     *
     * @param  string  $classA  The class name of the first model
     * @param  string  $classB  The class name of the second model
     * @return string The pivot table name following Laravel's naming convention
     */
    protected function getPivotTableName(string $classA, string $classB): string
    {
        $tableA = strtolower(class_basename($classA));
        $tableB = strtolower(class_basename($classB));

        return collect([$tableA, $tableB])->sort()->join('_');
    }

    /**
     * Get the foreign key name based on Laravel's default convention.
     * Use the name of the class in lowercase and append '_id'.
     *
     * Laravel automatically generates foreign key names by taking the model's
     * class name, converting it to lowercase, and appending '_id'. This method
     * replicates that convention to generate the correct foreign key names
     * for use in pivot table assertions and relationship testing.
     *
     * @param  string  $class  The fully qualified class name
     * @return string The foreign key name following Laravel's naming convention
     */
    protected function getForeignKey(string $class): string
    {
        return strtolower(class_basename($class)).'_id';
    }

    /**
     * Create a model instance and return it
     *
     * This helper method generates a complete model instance with all necessary
     * relationships properly configured for use in update tests. It creates
     * related models for belongsTo relationships and ensures all foreign key
     * constraints are satisfied, providing a valid model that can be used
     * as the target for update operations.
     *
     * @param  bool  $withBelongsTo  The belongsTo relations
     * @param  bool  $withBelongsToMany  The belongsToMany relations
     * @param  bool  $withHasMany  The hasMany relations
     * @return Model A fully configured model instance ready for testing
     */
    protected function createModel(bool $withBelongsTo = false, bool $withBelongsToMany = false, bool $withHasMany = false): Model
    {
        $data = $this->createAttributes(withBelongsTo: $withBelongsTo, withBelongsToMany: $withBelongsToMany, withHasMany: $withHasMany);

        $fillableData = $this->getExpectedDatabaseData($data);

        return $this->configurator->getModelClass()::factory()->create($fillableData);
    }

    /**
     * Generate raw attributes using the model factory,
     * including belongsTo, belongsToMany and hasMany relations.
     *
     * @param  bool  $withBelongsTo  The belongsTo relations
     * @param  bool  $withBelongsToMany  The belongsToMany relations
     * @param  bool  $withHasMany  The hasMany relations
     * @return array The complete set of attributes
     */
    protected function createAttributes(bool $withBelongsTo = false, bool $withBelongsToMany = false, bool $withHasMany = false): array
    {
        $data = $this->configurator->getModelClass()::factory()->raw();
        $relatedData = [];

        if ($withBelongsTo === true) {
            $relatedData = $this->addBelongsToRelations($relatedData);
        }

        if ($withBelongsToMany === true) {
            $relatedData = $this->addBelongsToManyRelations($relatedData);
        }

        if ($withHasMany === true) {
            $relatedData = $this->addHasManyRelations($relatedData);
        }

        return array_merge($data, $relatedData);
    }

    /**
     * Add belongsTo relations
     *
     * @param  array  $relatedData  Array to add relations
     * @return array The updated array with relations added
     */
    protected function addBelongsToRelations(array $relatedData): array
    {
        foreach ($this->configurator->getBelongsToRelations() as $foreignKey => $relatedClass) {
            $relatedData[$foreignKey] = $relatedClass::factory()->create()->id;
        }

        return $relatedData;
    }

    /**
     * Add belongsToMany relations
     *
     * @param  array  $relatedData  Array to add relations
     * @return array The updated array with relations added
     */
    protected function addBelongsToManyRelations(array $relatedData): array
    {
        foreach ($this->configurator->getBelongsToManyRelations() as $relationName => $relatedClass) {
            $relatedData[$relationName] = $relatedClass::factory()->count(2)->create()->pluck('id')->toArray();
        }

        return $relatedData;
    }

    /**
     * Add hasMany relations
     *
     * @param  array  $relatedData  Array to add relations
     * @return array The updated array with relations added
     */
    protected function addHasManyRelations(array $relatedData): array
    {
        foreach ($this->configurator->getHasManyRelations() as $relationName => $relatedClass) {
            $relatedData[$relationName] = $relatedClass::factory()->count(2)->create()->toArray();
        }

        return $relatedData;
    }

    /**
     * Remove keys from array
     *
     * @param  array  $data  The data to remove keys from
     * @param  array  $keys  Additonal keys to remove
     */
    protected function removeKeys(array $data, array $additionalKeys = []): array
    {
        $defaultKeys = [
            'password',
            'created_at',
            'updated_at',
            'remember_token',
            'email_verified_at',
        ];

        $excludedKeys = array_merge($defaultKeys, $additionalKeys);

        $cleanData = $data;

        foreach ($excludedKeys as $key) {
            if (isset($cleanData[$key])) {
                unset($cleanData[$key]);
            }
        }

        return $cleanData;
    }
}
