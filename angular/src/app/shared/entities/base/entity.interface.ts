/**
 * The base used for all database entities.
 * The ID is optional as created entities don't have it
 */
export interface Entity {
  id?: number;
  created_at?: string;
  updated_at?: string;
  [key: string]: unknown;
}

/**
 * Used to identify database entities in tables
 */
export interface Identifiable extends Entity {
  id: number;
}
