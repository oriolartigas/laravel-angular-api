// Import third-party
import { CommonModule } from '@angular/common';
import { Component, EventEmitter, inject, OnInit, Output } from '@angular/core';
import { FormGroup, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { take } from 'rxjs';

// Import types
import { HttpResponseData } from '../../../core/types/api.interface';
import { Role } from '../../../shared/entities/role.interface';
import { User } from '../../../shared/entities/user.interface';
import { FormAction } from '../../../shared/types/types';

// Import services
import { RoleFormFactory } from '../../../shared/forms/factories/role-form.factory';
import { UserService } from '../../../shared/services/user.service';

// Import components
import { FormDialogAbstract } from '../../../shared/components/dialogs/form-dialog/form-dialog.abstract';
import { FormDialogComponent } from '../../../shared/components/dialogs/form-dialog/form-dialog.component';
import { RoleFormComponent } from '../../../shared/forms/components/role-form/role-form.component';

@Component({
  selector: 'app-role-form-dialog',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatButtonModule,
    FormDialogComponent,
    RoleFormComponent,
  ],
  templateUrl: './role-form-dialog.component.html',
})
export class RoleFormDialogComponent extends FormDialogAbstract<Role> implements OnInit {
  /** The event emitted when the user saves the form */
  @Output() override saveForm = new EventEmitter<Role>();

  /** The form group used to create new records */
  override formGroup!: FormGroup;

  /** The form factory used to create new records */
  override formFactory = inject(RoleFormFactory);

  /** The user service used to get the list of users */
  private userService: UserService = inject(UserService);

  /** The list of users */
  users: User[] = [];

  /**
   * Get the initial data to fill the form
   */
  protected override setInitialData(): void {
    this.setTitle();
    this.getUsers();
  }

  /**
   * Get the list of users to fill the select
   */
  private getUsers(): void {
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
      this.title = 'Create new role';
    } else {
      this.title = 'Update role ' + this.data.item?.name;
    }
  }
}
