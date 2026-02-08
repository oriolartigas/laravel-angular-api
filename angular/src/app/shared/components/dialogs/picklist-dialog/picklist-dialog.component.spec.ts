import { ComponentFixture, TestBed } from '@angular/core/testing';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';

import { PicklistDialogComponent } from './picklist-dialog.component';

describe('PicklistDialogComponent', () => {
  let component: PicklistDialogComponent;
  let fixture: ComponentFixture<PicklistDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PicklistDialogComponent],
      providers: [
        {
          provide: MAT_DIALOG_DATA,
          useValue: {
            columns: [],
            entity: 'Test',
            availableItems: [],
            assignedIds: [],
            returnSubject: { next: jasmine.createSpy('next') },
          },
        },
        { provide: MatDialogRef, useValue: {} },
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(PicklistDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
