// Import third-party
import { CommonModule } from '@angular/common';
import { Component, EventEmitter, inject, Output } from '@angular/core';
import { FormGroup, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { take } from 'rxjs';

// Import types
import { HttpResponseData } from '../../../core/types/api.interface';
import { Address } from '../../../shared/entities/address.interface';
import { User } from '../../../shared/entities/user.interface';
import { FormAction } from '../../../shared/types/types';

// Import services
import { AddressFormFactory } from '../../../shared/forms/factories/address-form.factory';
import { UserService } from '../../../shared/services/user.service';

// Import components
import { FormDialogAbstract } from '../../../shared/components/dialogs/form-dialog/form-dialog.abstract';
import { FormDialogComponent } from '../../../shared/components/dialogs/form-dialog/form-dialog.component';
import { AddressFormComponent } from '../../../shared/forms/components/address-form/address-form.component';

@Component({
  selector: 'app-address-form-dialog',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatButtonModule,
    FormDialogComponent,
    AddressFormComponent,
  ],
  templateUrl: './address-form-dialog.component.html',
})
export class AddressFormDialogComponent extends FormDialogAbstract<Address> {
  /** The event emitted when the user saves the form */
  @Output() override saveForm = new EventEmitter<Address>();

  /** The form group used to create/update records */
  override formGroup!: FormGroup;

  /** The form factory used to create the form */
  override formFactory = inject(AddressFormFactory);

  /** The user service used to get the list of users */
  private userService: UserService = inject(UserService);

  /** The list of users */
  users: User[] = [];

  /**
   * Get the initial data to fill the form
   */
  protected override setInitialData() {
    this.setTitle();
    this.getUsers();
  }

  /**
   * Get the list of users to fill the select
   */
  private getUsers() {
    this.userService
      .index()
      .pipe(take(1))
      .subscribe({
        next: (response: HttpResponseData<User[]>) => {
          this.users = response.data;
        },
      });
  }

  /**
   * Set the title of the dialog
   */
  protected override setTitle(): void {
    if (this.action === FormAction.Create) {
      this.title = 'Create new address';
    } else {
      this.title = 'Update address';
    }
  }
}
