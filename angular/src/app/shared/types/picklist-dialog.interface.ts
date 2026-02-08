import { Subject } from 'rxjs';
import { Identifiable } from '../entities/base/entity.interface';
import { TableColumn } from './entity-table.interface';

/**
 * Configuration interface for the picklist dialog
 * @template T - The entity being updated
 * @template U - The entity used as relation
 */
export interface PicklistConfig<T extends Identifiable, U extends Identifiable> {
  /** The width of the dialog */
  width?: string;

  /** The height of the dialog */
  height?: string;

  /** The key used in the payload to update the relation (e.g. 'role_ids') */
  assignedIdKey: string;

  /** The name of the relation used to count (e.g. 'roles') */
  countRelation: string;

  /** The name of the entity used in the dialog as title (e.g. 'roles') */
  dialogEntityName: string;

  /** The function to parse the available items to PicklistItem objects */
  mapToPicklistItem: (item: U) => PicklistItem;

  /** Parse the assigned relation objects to an array of ids */
  getCurrentAssignedIds: (entity: T) => number[];
}

/**
 * The object that represents the picklist item
 * in the tables
 */
export interface PicklistItem extends Identifiable {
  id: number;
  name: string;
}

/**
 * The data used to configure the picklist dialog
 */
export interface PicklistDialogData<T extends Identifiable> {
  /** The name of the entity as title */
  entity: string;

  /** The configuration of the columns used in the tables */
  columns: TableColumn<T>[];

  /** Array of items that can be assigned */
  availableItems: PicklistItem[];

  /** Array of assigned ids */
  assignedIds: number[];

  /** Subject to return the assigned ids */
  returnSubject: Subject<number[]>;
}

/**
 * Return data interface
 */
export type PickListDialogReturnData = number[] | undefined;
