<?php

declare(strict_types=1);

namespace Tests\Feature\Base\Crud;

abstract class BaseCrudStoreTest extends BaseCrudTest
{
    /**
     * Verifies that a model is created successfully without any relationships.
     */
    public function test_store_creates_model_without_relations_successfully(): void
    {
        $data = $this->createAttributes(withBelongsTo: false, withBelongsToMany: false, withHasMany: false);

        $response = $this->postJson($this->configurator->getEndpoint(), $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas(
            (new ($this->configurator->getModelClass()))->getTable(),
            $this->getExpectedDatabaseData($data)
        );
    }

    /**
     * Verifies that a model is created successfully with belongsToMany
     * relationships.
     */
    public function test_store_creates_model_with_belong_to_many_relations_successfully(): void
    {
        $data = $this->createAttributes(withBelongsTo: false, withBelongsToMany: true, withHasMany: false);
        $response = $this->postJson($this->configurator->getEndpoint(), $data);

        $response->assertStatus(201);

        $createdModelId = $response->json('data.id');

        $this->assertDatabaseHas(
            (new ($this->configurator->getModelClass()))->getTable(),
            $this->getExpectedDatabaseData($data)
        );

        foreach ($this->configurator->getBelongsToManyRelations() as $relationName => $relatedClass) {
            $pivotTable = $this->getPivotTableName($this->configurator->getModelClass(), $relatedClass);
            $relatedIds = $data[$relationName];

            foreach ($relatedIds as $id) {
                $this->assertDatabaseHas($pivotTable, [
                    $this->getForeignKey($this->configurator->getModelClass()) => $createdModelId,
                    $this->getForeignKey($relatedClass) => $id,
                ]);
            }
        }
    }

    /**
     * Verifies that a model is created successfully with hasMany
     * relationships.
     */
    public function test_store_creates_model_with_has_many_relations_successfully(): void
    {
        $data = $this->createAttributes(withBelongsTo: false, withBelongsToMany: false, withHasMany: true);

        $response = $this->postJson($this->configurator->getEndpoint(), $data);

        $response->assertStatus(201);
        $createdModelId = $response->json('data.id');

        $this->assertDatabaseHas(
            (new ($this->configurator->getModelClass()))->getTable(),
            $this->getExpectedDatabaseData($data)
        );

        foreach ($this->configurator->getHasManyRelations() as $relationName => $relatedClass) {
            $relatedTable = (new $relatedClass)->getTable();
            $foreignKey = $this->getForeignKey($this->configurator->getModelClass());
            $newPayload = $data[$relationName];

            foreach ($newPayload as $relatedItemData) {
                $cleanData = $this->removeKeys(data: $relatedItemData, additionalKeys: ['id']);

                $this->assertDatabaseHas($relatedTable, array_merge(
                    $cleanData,
                    [$foreignKey => $createdModelId]
                ));
            }
        }
    }

    /**
     * Verifies that the API returns the expected response.
     *
     * This test validates that the API endpoint returns the correct HTTP status
     * code (201 Created) and response structure after successful model creation.
     * It ensures that the response includes the newly created model's ID and
     * all expected attributes in the proper JSON format, excluding any fields
     * that should not be exposed in the API response.
     */
    public function test_store_returns_correct_response_data(): void
    {
        $data = $this->createAttributes(withBelongsTo: false, withBelongsToMany: false, withHasMany: false);

        $response = $this->postJson($this->configurator->getEndpoint(), $data);
        $response->assertStatus(201);

        $createdId = $response->json('data.id');
        $expectedResponseData = array_merge(
            [
                'id' => $createdId,
            ],
            $this->getExpectedResponseKeys($data)
        );

        $response->assertJson(['data' => $expectedResponseData]);
    }
}
