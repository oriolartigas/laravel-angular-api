import { inject, Injectable } from '@angular/core';
import {
  AbstractControl,
  FormArray,
  FormBuilder,
  FormGroup,
  ValidationErrors,
  Validators,
} from '@angular/forms';

// Import services
import { AddressFormFactory } from './address-form.factory';
import { BaseFormFactory } from './base/form-factory.abstract';

// Import types
import { Address } from '../../entities/address.interface';
import { User } from '../../entities/user.interface';
import { AddressFormControls } from '../interfaces/address-form-interface';
import { UserFormControls } from '../interfaces/user-form-interface';

@Injectable({ providedIn: 'root' })
export class UserFormFactory extends BaseFormFactory<User> {
  /** The form builder used to create the form */
  private fb: FormBuilder = inject(FormBuilder);

  /** The address factory used to create the address form */
  private addressFactory: AddressFormFactory = inject(AddressFormFactory);

  /**
   * Get the create form
   * @param user The user used to fill the form
   * @returns FormGroup
   */
  public override getCreateForm(user?: User): FormGroup<UserFormControls> {
    const formGroup = this.getBaseForm(user);

    formGroup.addControl(
      'password',
      this.fb.control(null, [Validators.required, Validators.minLength(8)]),
    );
    formGroup.addControl('password_confirmation', this.fb.control(null, [Validators.required]));

    return formGroup as FormGroup<UserFormControls>;
  }

  /**
   * Get the update form
   *
   * Add the id field to the form
   * @param user The user to update
   * @returns FormGroup
   */
  public override getUpdateForm(user: User): FormGroup<UserFormControls> {
    const formGroup = this.getBaseForm(user);

    formGroup.addControl('id', this.fb.control({ value: user.id ?? null, disabled: true }));

    return formGroup as FormGroup<UserFormControls>;
  }

  /**
   * Get the base form
   * @param user The user used to fill the form
   * @returns FormGroup
   */
  protected override getBaseForm(user?: User): FormGroup<UserFormControls> {
    let role_ids: number[] = [];

    if (user?.roles) {
      role_ids = user.roles.map((role) => role.id!);
    }

    return this.fb.group(
      {
        role_ids: [role_ids ?? [], [Validators.required]],
        name: [user?.name ?? '', [Validators.required, Validators.minLength(2)]],
        email: [user?.email ?? '', [Validators.required, Validators.email]],
        addresses: this.fb.array<FormGroup<AddressFormControls>>([]),
      },
      { validators: this.passwordMatchValidator },
    ) as FormGroup<UserFormControls>;
  }

  /**
   * Get the addresses form array
   * @param form The form
   * @returns The addresses form array
   */
  getAddressesFormArray(form: FormGroup): FormArray {
    return form.get('addresses') as FormArray;
  }

  /**
   * Add an address to the addresses form array
   * @param form The form
   * @param address The address to add
   */
  addAddress(form: FormGroup, address?: Address) {
    const addresses = this.getAddressesFormArray(form);
    addresses.push(this.addressFactory.getCreateForm(address, false));
  }

  /**
   * Remove an address from the addresses form array
   * @param form The form
   * @param index The index of the address to remove
   */
  removeAddress(form: FormGroup, index: number) {
    const addresses = this.getAddressesFormArray(form);
    addresses.removeAt(index);
  }

  /**
   * Validate the password confirmation match the password
   * @param control The control to validate
   * @returns The validation errors
   */
  private passwordMatchValidator(control: AbstractControl): ValidationErrors | null {
    const password = control.get('password');
    const passwordConfirmation = control.get('password_confirmation');

    if (!password || !passwordConfirmation) {
      return null;
    }

    return password.value === passwordConfirmation.value ? null : { passwordMismatch: true };
  }
}
