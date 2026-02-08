import { Identifiable } from '../entities/base/entity.interface';

/**
 * Configuration of the tables
 */
export interface EntityTableConfig {
  can_edit: boolean;
  can_delete: boolean;
  can_select: boolean;
  caption?: string;
}

/**
 * Types of columns
 */
export enum ColumnType {
  Text = 'text',
  Email = 'email',
  Number = 'number',
  Button = 'button',
  Password = 'password',
}

/**
 * Configuration of the table columns
 */
export interface TableColumn<T extends Identifiable = Identifiable> {
  /** The property key to display from data objects */
  key: string;

  /** Display label for the column header */
  label: string;

  /** Input type for editable fields */
  type: ColumnType;

  /** The function to execute on click */
  onClick?: (item: T) => void;
}
