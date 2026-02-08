<?php

declare(strict_types=1);

namespace Tests\Feature\Base\Crud;

abstract class BaseCrudUpdateTest extends BaseCrudTest
{
    /**
     * Verifies that a model is updated successfully
     *
     * This test validates the complete model update process.
     */
    public function test_update_updates_model_without_relations_successfully(): void
    {
        $model = $this->createModel(
            withBelongsTo: false,
            withBelongsToMany: false,
            withHasMany: false
        );

        $data = $this->createAttributes(
            withBelongsTo: false,
            withBelongsToMany: false,
            withHasMany: false
        );

        $response = $this->putJson(uri: "{$this->configurator->getEndpoint()}/{$model->id}", data: $data);
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            table: (new ($this->configurator->getModelClass()))->getTable(),
            data: array_merge($this->getExpectedDatabaseData($data), ['id' => $model->id])
        );
    }

    /**
     * Verifies that the model is successfully updated when ONLY belongsToMany
     * relationships are modified and the main attributes are unchanged.
     */
    public function test_update_updates_model_with_belongs_to_many_relations_successfully(): void
    {
        if (empty($this->configurator->getBelongsToManyRelations())) {
            self::markTestSkipped('Skipping test as the model has no belongsToMany relations configured.');
        }

        $model = $this->createModel(
            withBelongsTo: false,
            withBelongsToMany: true,
            withHasMany: false
        );
        $cleanOriginalAttributes = $this->removeKeys($model->toArray());
        $updateData = $cleanOriginalAttributes;

        foreach ($this->configurator->getBelongsToManyRelations() as $relationName => $relatedClass) {
            $newRelatedEntities = (new $relatedClass)->factory()->count(2)->create();

            $updateData[$relationName] = $newRelatedEntities->pluck('id')->toArray();
        }

        $response = $this->putJson("{$this->configurator->getEndpoint()}/{$model->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas(
            table: (new ($this->configurator->getModelClass()))->getTable(),
            data: array_merge($this->getExpectedDatabaseData($cleanOriginalAttributes), ['id' => $model->id])
        );

        foreach ($this->configurator->getBelongsToManyRelations() as $relationName => $relatedClass) {
            $pivotTable = $this->getPivotTableName($this->configurator->getModelClass(), $relatedClass);
            $newRelatedIds = $updateData[$relationName];

            foreach ($newRelatedIds as $id) {
                $this->assertDatabaseHas($pivotTable, [
                    $this->getForeignKey($this->configurator->getModelClass()) => $model->id,
                    $this->getForeignKey($relatedClass) => $id,
                ]);
            }
        }
    }

    /**
     * Verifies that the API returns the correct response after update
     *
     * This test validates that the update API endpoint returns the correct
     * HTTP status code (200 OK) and response structure after successful
     * model modification. It ensures that the response includes the updated
     * model's data with the correct ID and all modified attributes properly
     * formatted in the JSON response structure.
     */
    public function test_update_returns_correct_response_data(): void
    {
        $model = $this->createModel(
            withBelongsTo: false,
            withBelongsToMany: false,
            withHasMany: false
        );

        $data = $this->createAttributes(
            withBelongsTo: false,
            withBelongsToMany: false,
            withHasMany: false
        );

        foreach ($this->configurator->getBelongsToRelations() as $foreignKey => $relatedClass) {
            $data[$foreignKey] = $model->{$foreignKey};
        }

        $response = $this->putJson("{$this->configurator->getEndpoint()}/{$model->id}", $data);
        $response->assertStatus(200);

        $expectedResponseData = array_merge(
            [
                'id' => $model->id,
            ],
            $this->getExpectedResponseKeys($data)
        );

        $response->assertJson(['data' => $expectedResponseData]);
    }

    /**
     * Verifies that the API returns error 400 when model is not modified.
     */
    public function test_update_returns_400_when_model_is_not_modified(): void
    {
        $model = $this->createModel(
            withBelongsTo: false,
            withBelongsToMany: false,
            withHasMany: false
        );

        $data = $this->removeKeys($model->toArray());

        $response = $this->putJson(uri: "{$this->configurator->getEndpoint()}/{$model->id}", data: $data);

        $response->assertStatus(400);

        $this->assertDatabaseHas(
            table: (new ($this->configurator->getModelClass()))->getTable(),
            data: ['id' => $model->id, 'updated_at' => $model->updated_at]
        );
    }

    /**
     * Verifies that the API returns error 400 when model and the belongsToMany relations
     * are not modified.
     */
    public function test_update_returns_400_when_model_and_belongs_to_many_relations_are_not_modified(): void
    {
        $model = $this->createModel(
            withBelongsTo: false,
            withBelongsToMany: true,
            withHasMany: false
        );

        $data = $this->removeKeys($model->toArray());

        $response = $this->putJson(uri: "{$this->configurator->getEndpoint()}/{$model->id}", data: $data);

        $response->assertStatus(400);

        $this->assertDatabaseHas(
            table: (new ($this->configurator->getModelClass()))->getTable(),
            data: ['id' => $model->id, 'updated_at' => $model->updated_at]
        );
    }
}
