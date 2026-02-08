import { inject, Injectable } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

// Import types
import { Role } from '../../entities/role.interface';
import { RoleFormControls } from '../interfaces/role-form.interface';

// Import services
import { BaseFormFactory } from './base/form-factory.abstract';

@Injectable({ providedIn: 'root' })
export class RoleFormFactory extends BaseFormFactory<Role> {
  /** The form builder used to create the form */
  private fb: FormBuilder = inject(FormBuilder);

  /**
   * Get the create form
   * @param role The role used to fill the form
   * @returns FormGroup
   */
  public override getCreateForm(role?: Role | undefined): FormGroup<RoleFormControls> {
    return this.getBaseForm(role);
  }

  /**
   * Get the update form
   *
   * Add the id field to the form
   * @param role The role to update
   * @returns FormGroup
   */
  public override getUpdateForm(role: Role): FormGroup<RoleFormControls> {
    const formGroup = this.getBaseForm(role);

    formGroup.addControl('id', this.fb.control({ value: role.id ?? null, disabled: true }));

    return formGroup as FormGroup<RoleFormControls>;
  }

  /**
   * Get the base form
   * @param role The role used to fill the form
   * @returns FormGroup
   */
  protected override getBaseForm(role?: Role): FormGroup<RoleFormControls> {
    let user_ids: number[] = [];

    if (role?.users) {
      user_ids = role.users.map((user) => user.id!);
    }

    return this.fb.group({
      user_ids: [user_ids],
      name: [role?.name ?? '', [Validators.required, Validators.minLength(2)]],
      description: [role?.description ?? ''],
    }) as FormGroup<RoleFormControls>;
  }
}
