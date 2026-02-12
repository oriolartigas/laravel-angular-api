import { OptionalQueryParams } from '@core/types/api.interface';
import { Entity, Identifiable } from '@shared/entities/base/entity.interface';
import { TableColumn } from '@shared/types/entity-table.interface';

/**
 * Interface defining the methods that a test harness exposes
 * for an entity page component.
 *
 * @template T - The entity type extending Identifiable & Entity.
 */
export interface EntityPageTestHarness<T extends Identifiable & Entity> {
  expose_getIndexParams(): OptionalQueryParams<T> | null;
  expose_items(): T[];
  set_items(value: T[]): void;
  expose_columns(): TableColumn<T>[];
  expose_getUpdatePayload(item: T): Partial<T>;
  expose_getCreateDialogData(): Partial<T>;
}

/**
 * Internal type describing the instance of a component
 * that the mixin can extend.
 */
type EntityPageTestInstance<T extends Identifiable & Entity> = {
  getIndexParams(): OptionalQueryParams<T> | null;
  items: T[];
  columns: TableColumn<T>[];
  getUpdatePayload(item: T): Partial<T>;
  getCreateDialogData(): Partial<T>;
};

/** Base constructor type for the mixin. */
type BaseConstructor<T extends Identifiable & Entity> = new (
  ...args: unknown[]
) => EntityPageTestInstance<T>;

/**
 * Mix-in function that creates a new class from a entity component,
 * exposing protected methods for easier testing.
 *
 * This allows test code to call methods and access properties
 * that are normally protected on the component.
 *
 * @template T - The entity type for the page.
 * @template C - The base component type to extend.
 * @param Base - The base component class.
 * @returns A new class extending the base, implementing `EntityPageTestHarness`.
 *
 * @example
 * const TestComponent = ExposeEntityPageMethods(MyEntityComponent);
 * const fixture = TestBed.createComponent(TestComponent);
 * const harness = fixture.componentInstance;
 * const items = harness.expose_items();
 */
export function ExposeEntityPageMethods<
  T extends Identifiable & Entity,
  C extends new (...args: unknown[]) => unknown,
>(Base: C) {
  const BaseWithProtectedAccess = Base as unknown as BaseConstructor<T>;

  return class extends BaseWithProtectedAccess implements EntityPageTestHarness<T> {
    public expose_getIndexParams(): OptionalQueryParams<T> | null {
      return this.getIndexParams();
    }
    public expose_items(): T[] {
      return this.items;
    }
    public set_items(value: T[]): void {
      this.items = value;
    }
    public expose_columns(): TableColumn<T>[] {
      return this.columns;
    }
    public expose_getUpdatePayload(item: T): Partial<T> {
      return this.getUpdatePayload(item);
    }
    public expose_getCreateDialogData(): Partial<T> {
      return this.getCreateDialogData();
    }
  };
}
