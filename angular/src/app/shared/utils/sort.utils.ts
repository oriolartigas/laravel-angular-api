import { Entity, Identifiable } from '../entities/base/entity.interface';

type CompareFunction<T extends Entity> = (a: T, b: T) => number;

/**
 * Splits a path into nested properties and returns the value of the last property
 * @param obj  The object to get the value from
 * @param path The path to get the value from (e.g. 'user.name')
 * @returns
 */
function getNestedValue<T extends Entity, R = unknown>(obj: T, path: string): R | undefined {
  return path.split('.').reduce((currentObj: unknown, key: string) => {
    if (currentObj && typeof currentObj === 'object' && key in currentObj) {
      return (currentObj as Record<string, unknown>)[key];
    }

    return undefined;
  }, obj) as R | undefined;
}

/**
 * Returns a function that compares two objects based on a specific property.
 *
 * @param key The property to compare.
 * @param ascending Whether to sort in ascending or descending order.
 * @returns The compare function.
 */
export function sortByProperty<T extends Identifiable>(
  key: string,
  ascending: boolean = true,
): CompareFunction<T> {
  return (a: T, b: T) => {
    const valueA = getNestedValue<T, string | number | undefined>(a, key);
    const valueB = getNestedValue<T, string | number | undefined>(b, key);

    const strA = valueA !== undefined ? String(valueA).toUpperCase() : '';
    const strB = valueB !== undefined ? String(valueB).toUpperCase() : '';

    let comparison = 0;

    if (strA > strB) {
      comparison = 1;
    } else if (strA < strB) {
      comparison = -1;
    }

    return ascending ? comparison : comparison * -1;
  };
}
