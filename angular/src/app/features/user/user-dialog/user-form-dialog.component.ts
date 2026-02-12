// Import third-party
import { CommonModule } from '@angular/common';
import { Component, EventEmitter, inject, Output } from '@angular/core';
import { FormArray, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatExpansionModule } from '@angular/material/expansion';
import { MatIconModule } from '@angular/material/icon';
import { take } from 'rxjs';

// Import types
import { HttpResponseData } from '@core/types/api.interface';
import { Role } from '@shared/entities/role.interface';
import { User } from '@shared/entities/user.interface';
import { FormAction } from '@shared/types/types';

// Import services
import { UserFormFactory } from '@shared/forms/factories/user-form.factory';
import { RoleService } from '@shared/services/entities/role.service';

// Import components
import { FormDialogAbstract } from '@shared/components/dialogs/form-dialog/form-dialog.abstract';
import { FormDialogComponent } from '@shared/components/dialogs/form-dialog/form-dialog.component';
import { AddressFormComponent } from '@shared/forms/components/address-form/address-form.component';
import { UserFormComponent } from '@shared/forms/components/user-form/user-form.component';

@Component({
  selector: 'app-user-form-dialog',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatButtonModule,
    MatIconModule,
    MatExpansionModule,
    FormDialogComponent,
    UserFormComponent,
    AddressFormComponent,
  ],
  templateUrl: './user-form-dialog.component.html',
  styleUrl: './user-form-dialog.component.css',
})
export class UserFormDialogComponent extends FormDialogAbstract<User> {
  /** The event emitted when the user saves the form */
  @Output() override saveForm = new EventEmitter<User>();

  /** The form group used to create new records */
  override formGroup!: FormGroup;

  /** The form factory used to create new records */
  override formFactory = inject(UserFormFactory);

  /** The role service used to get the list of roles */
  private roleService: RoleService = inject(RoleService);

  /** The list of roles */
  public roles: Role[] = [];

  /** Whether to show the password field or not */
  public showPassword: boolean = false;

  /** The list of form actions used in the HTML */
  public FormAction = FormAction;

  /**
   * Get the initial data to fill the form
   */
  protected override setInitialData(): void {
    this.showPassword = this.action === FormAction.Create;
    this.setTitle();
    this.getRoles();
  }

  /**
   * Get the list of roles to fill the select
   */
  private getRoles(): void {
    this.roleService
      .index()
      .pipe(take(1))
      .subscribe({
        next: (response: HttpResponseData<Role[]>) => {
          this.roles = response.data;
        },
      });
  }

  /**
   * Get the addresses form array
   * @returns The addresses form array
   */
  get addresses(): FormArray<FormGroup> {
    return this.formFactory.getAddressesFormArray(this.formGroup);
  }

  /**
   * Add an address to the addresses form array
   */
  addAddress() {
    this.formFactory.addAddress(this.formGroup, undefined);
  }

  /**
   * Remove an address from the addresses form array
   * @param index The index of the address to remove
   */
  removeAddress(index: number) {
    this.formFactory.removeAddress(this.formGroup, index);
  }

  /**
   * Set the title of the dialog
   */
  protected override setTitle(): void {
    if (this.action === FormAction.Create) {
      this.title = 'Create new user';
    } else {
      this.title = 'Update user ' + this.data.item?.name;
    }
  }
}
