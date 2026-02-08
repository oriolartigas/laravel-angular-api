import { Component, Input } from '@angular/core';
import { FormGroup, ReactiveFormsModule } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';

// Import types
import { User } from '../../../entities/user.interface';

@Component({
  selector: 'app-address-form',
  standalone: true,
  imports: [ReactiveFormsModule, MatFormFieldModule, MatInputModule, MatSelectModule],
  templateUrl: './address-form.component.html',
  styleUrl: './address-form.component.css',
})
export class AddressFormComponent {
  /** The form group */
  @Input() formGroup!: FormGroup;

  /** The list of users used in the select */
  @Input() users: User[] = [];

  /** Whether to show the users select or not */
  @Input() showUsers: boolean = false;
}
