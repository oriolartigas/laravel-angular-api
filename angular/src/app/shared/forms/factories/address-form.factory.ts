import { inject, Injectable } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

// Import types
import { Address } from '../../entities/address.interface';
import { AddressFormControls } from '../interfaces/address-form-interface';

// Import services
import { BaseFormFactory } from './base/form-factory.abstract';

@Injectable({ providedIn: 'root' })
export class AddressFormFactory extends BaseFormFactory<Address> {
  /** The form builder used to create the form */
  private fb: FormBuilder = inject(FormBuilder);

  /**
   * Get the create form
   *
   * If showUser is true, the user_id field is added to the form.
   * @param address The address used to fill the form
   * @param showUser Whether to show the user field
   * @returns The Address form
   */
  public override getCreateForm(
    address?: Address,
    showUser: boolean = true,
  ): FormGroup<AddressFormControls> {
    const formGroup = this.getBaseForm(address);
    const userDisabled = address?.user_id ? true : false;

    if (showUser) {
      this.addUserControl(formGroup, address?.user_id ?? null, userDisabled);
    }

    return formGroup as FormGroup<AddressFormControls>;
  }

  /**
   * The update form
   *
   * Add the id field to the form.
   * If showUser is true, the user_id field is added to the form,
   * but it is disabled.
   * @param address The address to update
   * @param showUser Whether to show the user field
   * @returns The Address form
   */
  public override getUpdateForm(
    address: Address,
    showUser: boolean = true,
  ): FormGroup<AddressFormControls> {
    const formGroup = this.getBaseForm(address);

    formGroup.addControl('id', this.fb.control({ value: address.id ?? null, disabled: true }));

    if (showUser) {
      this.addUserControl(formGroup, address.user_id ?? null, true);
    }

    return formGroup as FormGroup<AddressFormControls>;
  }

  /**
   * Create the base form.
   *
   * @param address The address used to fill the form
   * @returns The base form
   */
  protected override getBaseForm(address?: Address): FormGroup<AddressFormControls> {
    return this.fb.group({
      name: [address?.name ?? '', [Validators.required, Validators.minLength(3)]],
      street: [address?.street ?? '', [Validators.required, Validators.minLength(3)]],
      city: [address?.city ?? '', [Validators.required, Validators.minLength(2)]],
      postal_code: [address?.postal_code ?? '', [Validators.required]],
      country: [address?.country ?? '', [Validators.required, Validators.minLength(2)]],
      state: [address?.state ?? '', [Validators.required, Validators.minLength(2)]],
    }) as FormGroup<AddressFormControls>;
  }

  /**
   * Add the user_id field to the form
   *
   * @param formGroup The form
   * @param userId  The user id
   * @param isUpdate Whether the form is for updating
   */
  private addUserControl(formGroup: FormGroup, userId: number | null, isUpdate: boolean): void {
    if (isUpdate) {
      formGroup.addControl('user_id', this.fb.control({ value: userId, disabled: true }));
    } else {
      formGroup.addControl('user_id', this.fb.control(userId ?? '', Validators.required));
    }
  }
}
