import { FormAction } from './types';

/** The data passed to the form dialog */
export interface FormDialogData<T> {
  item: Partial<T> | undefined;
  action: FormAction;
}
