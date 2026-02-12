import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { of } from 'rxjs';
import { UserService } from '@shared/services/entities/user.service';
import { RoleFormDialogComponent } from './role-form-dialog.component';

describe('RoleFormDialogComponent', () => {
  let component: RoleFormDialogComponent;
  let fixture: ComponentFixture<RoleFormDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RoleFormDialogComponent],
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        {
          provide: MAT_DIALOG_DATA,
          useValue: {
            action: 'create',
            item: { id: 1, name: 'Test Role', description: 'Test Description' },
          },
        },
        { provide: MatDialogRef, useValue: { close: () => {}, afterClosed: () => ({}) } },
        {
          provide: UserService,
          useValue: {
            index: jasmine.createSpy('index').and.returnValue(of({ data: [] })),
          },
        },
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(RoleFormDialogComponent);
    component = fixture.componentInstance;
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
