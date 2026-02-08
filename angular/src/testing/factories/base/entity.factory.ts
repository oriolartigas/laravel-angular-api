import { Entity } from '../../../app/shared/entities/base/entity.interface';

export interface EntityFactoryInterface<T> {
  create(overrides?: Partial<T>): T;
  createArray(count?: number, overrides?: Partial<T>): T[];
}

/**
 * Abstract base class for creating mock data factories.
 * @template T - The entity type being mocked.
 */
export abstract class EntityFactory<T extends Entity> implements EntityFactoryInterface<T> {
  /**
   * Defines the creation logic for a single mock object of type T.
   * @param overrides - Optional partial data to override defaults.
   * @returns A mock object of type T.
   */
  public abstract create(overrides?: Partial<T>): T;

  /**
   * A static counter used for generating unique IDs for mock objects.
   */
  private static counter = 1;

  /**
   * Generates a unique ID for a mock object.
   * @returns A unique ID.
   */
  protected getNextId(): number {
    const ctor = this.constructor as typeof EntityFactory;
    return ctor.counter++;
  }

  /**
   * Creates an array of mock objects.
   * This logic is shared among all concrete factories.
   * @param count - The number of mocks to create (defaults to 2).
   * @param overrides - Optional partial data to apply to all mocks.
   * @returns An array of mock objects.
   */
  public createArray(count: number = 2, overrides?: Partial<T>): T[] {
    const mocks: T[] = [];

    for (let i = 1; i <= count; i++) {
      mocks.push(
        this.create({
          id: i,
          ...overrides,
        } as Partial<T>),
      );
    }
    return mocks;
  }
}
