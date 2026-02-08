<?php

declare(strict_types=1);

namespace Tests\Unit\Base;

use Illuminate\Database\Eloquent\Model;
use Mockery;
use Mockery\MockInterface;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\TestCase;

abstract class BaseRepositoryTest extends TestCase
{
    /**
     * The configurator of the model
     */
    protected BaseTestConfigurator $configurator;

    /**
     * The Mocked Eloquent Model instance
     *
     * @var MockInterface|Model
     */
    protected MockInterface $mockedModel;

    /**
     * Get the configurator of the model
     */
    abstract protected function getConfigurator(): BaseTestConfigurator;

    /**
     * The Repository class to test
     */
    abstract protected function getRepositoryClass(): string;

    /**
     * Initializes the Mocked Eloquent Model.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->configurator = $this->getConfigurator();

        $this->mockedModel = Mockery::mock($this->configurator->getModelClass());
    }

    /**
     * Cleans up the test environment after each test method is executed
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Helper to instantiate the Repository and execute the given method.
     *
     * @param  string  $method  The repository method to call (e.g., 'find', 'create', 'update')
     * @param  array  $args  The arguments to pass to the method
     * @return mixed The result of the repository method call
     */
    protected function executeRepositoryAction(string $method, array $args = []): mixed
    {
        $repositoryClass = $this->getRepositoryClass();
        $repository = new $repositoryClass($this->mockedModel);

        return $repository->{$method}(...$args);
    }
}
