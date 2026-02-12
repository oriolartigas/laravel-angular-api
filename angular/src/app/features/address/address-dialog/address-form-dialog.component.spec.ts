import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { of } from 'rxjs';
import { UserService } from '@shared/services/entities/user.service';
import { AddressFormDialogComponent } from './address-form-dialog.component';

describe('AddressFormDialogComponent', () => {
  let component: AddressFormDialogComponent;
  let fixture: ComponentFixture<AddressFormDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AddressFormDialogComponent],
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        {
          provide: MAT_DIALOG_DATA,
          useValue: {
            action: 'create',
            item: { id: 1, name: 'Test Address', street: '123 Test St' },
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

    fixture = TestBed.createComponent(AddressFormDialogComponent);
    component = fixture.componentInstance;
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
