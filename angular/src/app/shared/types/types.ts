/** The form actions available */
export enum FormAction {
  Create = 'create',
  Update = 'update',
}
/**
 * The object used to scroll to the item
 * and highlight it when it is added or updated
 */
export interface HighlightRowId {
  id: number | undefined;
  /** Use timestamp to force the @Input() to refresh when the value is the same */
  timestamp: number;
}
