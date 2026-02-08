import { ComponentFixture, TestBed } from '@angular/core/testing';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { UserFormComponent } from './user-form.component';

describe('UserFormComponent', () => {
  let component: UserFormComponent;
  let fixture: ComponentFixture<UserFormComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ReactiveFormsModule, UserFormComponent],
      providers: [
        { provide: MAT_DIALOG_DATA, useValue: {} },
        { provide: MatDialogRef, useValue: { close: () => {}, afterClosed: () => ({}) } },
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(UserFormComponent);
    component = fixture.componentInstance;

    const fb = new FormBuilder();
    component.formGroup = fb.group({
      role_ids: [''],
      name: [''],
      email: [''],
      password: [''],
      password_confirmation: [''],
      addresses: [''],
    });
  });

  it('should create', () => {
    expect(component).toBeTruthy();
    // Only call detectChanges in the test if needed
    // fixture.detectChanges();
  });
});
