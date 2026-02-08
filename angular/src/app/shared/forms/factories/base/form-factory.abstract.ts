import { FormGroup } from '@angular/forms';

export abstract class BaseFormFactory<T> {
  /** Returns a base form */
  protected abstract getBaseForm(): FormGroup;

  /** Returns the form when creating */
  public abstract getCreateForm(entity?: Partial<T>): FormGroup;

  /** Returns the form when updating */
  public abstract getUpdateForm(entity: Partial<T>): FormGroup;
}
