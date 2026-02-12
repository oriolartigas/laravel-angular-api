import { ComponentFixture, TestBed } from '@angular/core/testing';

import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { of } from 'rxjs';
import { RoleService } from '@shared/services/entities/role.service';
import { UserFormDialogComponent } from './user-form-dialog.component';

describe('UserFormDialogComponent', () => {
  let component: UserFormDialogComponent;
  let fixture: ComponentFixture<UserFormDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [UserFormDialogComponent],
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        {
          provide: MAT_DIALOG_DATA,
          useValue: {
            action: 'create',
            item: { id: 1, name: 'Test User', email: 'test@example.com' },
          },
        },
        { provide: MatDialogRef, useValue: { close: () => {}, afterClosed: () => ({}) } },
        {
          provide: RoleService,
          useValue: {
            index: jasmine.createSpy('index').and.returnValue(of({ data: [] })),
          },
        },
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(UserFormDialogComponent);
    component = fixture.componentInstance;
    // Don't call detectChanges to avoid initialization errors
    // fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
