import { ComponentFixture, TestBed } from '@angular/core/testing';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';
import { MatDialogRef } from '@angular/material/dialog';
import { Identifiable } from '../../../entities/base/entity.interface';
import { FormDialogComponent } from './form-dialog.component';

interface MockEntity extends Identifiable {
  id: number;
  name: string;
}

describe('FormDialogComponent', () => {
  let component: FormDialogComponent<MockEntity>;
  let fixture: ComponentFixture<FormDialogComponent<MockEntity>>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [FormDialogComponent, ReactiveFormsModule],
      providers: [{ provide: MatDialogRef, useValue: {} }],
    }).compileComponents();

    fixture = TestBed.createComponent(FormDialogComponent<MockEntity>);
    component = fixture.componentInstance;

    const fb = new FormBuilder();
    component.formGroup = fb.group({
      name: [''],
    });

    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
