import { Directive, EventEmitter, inject, OnInit, Output } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { MAT_DIALOG_DATA } from '@angular/material/dialog';
import { BaseFormFactory } from '../../../forms/factories/base/form-factory.abstract';
import { FormDialogData } from '../../../types/form-dialog.interface';
import { FormAction } from '../../../types/types';

@Directive()
export abstract class FormDialogAbstract<T> implements OnInit {
  /** The event to emit when the form is saved */
  @Output() saveForm = new EventEmitter<T>();

  /** The form group of the dialog */
  protected abstract formGroup: FormGroup;

  /** The form factory of the dialog */
  protected abstract formFactory: BaseFormFactory<T>;

  /** Set the initial data of the dialog */
  protected abstract setInitialData(): void;

  /** Set the title of the dialog */
  protected abstract setTitle(): void;

  /** The data passed to the dialog */
  protected data = inject<FormDialogData<T>>(MAT_DIALOG_DATA);

  /** The title of the dialog */
  protected title: string = '';

  /** The action of the dialog (create or update) */
  public action!: FormAction;

  ngOnInit() {
    this.action = this.data.action;
    this.setInitialData();
    this.formGroup = this.createForm(this.data.item);
  }

  /**
   * Get the create or update form
   * @param fb The form builder
   * @returns The form group
   */
  createForm(data: Partial<T> | undefined): FormGroup {
    if (this.action === FormAction.Create) {
      return this.formFactory.getCreateForm(data);
    } else {
      if (!data) {
        throw new Error('Update action requires data item to be present.');
      }

      return this.formFactory.getUpdateForm(data);
    }
  }

  /**
   * Emit an event to save the form
   * @param data The data to save
   */
  formSaved(data: T): void {
    this.saveForm.emit(data);
  }
}
