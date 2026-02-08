import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Input, Output } from '@angular/core';
import { FormGroup, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import {
  MatDialogActions,
  MatDialogClose,
  MatDialogContent,
  MatDialogTitle,
} from '@angular/material/dialog';
import { MatIconModule } from '@angular/material/icon';
import { Entity, Identifiable } from '../../../entities/base/entity.interface';

@Component({
  selector: 'app-form-dialog',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatButtonModule,
    MatDialogActions,
    MatDialogClose,
    MatDialogContent,
    MatDialogTitle,
    MatIconModule,
  ],
  templateUrl: './form-dialog.component.html',
  styleUrls: ['./form-dialog.component.css'],
})
export class FormDialogComponent<T extends Identifiable & Entity> {
  /** The title of the dialog */
  @Input() title!: string;

  /** The form group used to create new records */
  @Input({ required: true }) formGroup!: FormGroup;

  /** The event emitted when the user saves the form */
  @Output() saveClicked = new EventEmitter<T>();

  /**
   * Event executed when the user submits the form
   */
  save(): void {
    if (this.formGroup.invalid) {
      this.formGroup.markAllAsTouched();
      return;
    }

    this.saveClicked.emit(this.formGroup.getRawValue());
  }
}
