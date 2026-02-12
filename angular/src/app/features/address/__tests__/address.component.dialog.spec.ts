import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';

import { AddressEntityFactory } from '../../../../testing/factories/address-entity.factory';
import { UserEntityFactory } from '../../../../testing/factories/user-entity.factory';
import { mockServiceClass } from '../../../../testing/mocks/mock-crud-service.spec';
import { mockFormDialog } from '../../../../testing/mocks/mocks.spec';
import { UserService } from '@shared/services/entities/user.service';
import { AddressComponentTest } from './address.component.test';

describe('UserComponent Dialogs', () => {
  let component: AddressComponentTest;
  let fixture: ComponentFixture<AddressComponentTest>;
  let dialogSpy: jasmine.SpyObj<MatDialog>;
  let addressFactory: AddressEntityFactory;
  let userService: UserService;

  beforeEach(async () => {
    dialogSpy = jasmine.createSpyObj('MatDialog', ['open']);
    addressFactory = new AddressEntityFactory();

    await TestBed.configureTestingModule({
      imports: [AddressComponentTest, MatDialogModule],
      providers: [
        {
          provide: UserService,
          useClass: mockServiceClass(UserService, new UserEntityFactory(), {}),
        },
        { provide: MatDialog, useValue: dialogSpy },
        { provide: MatSnackBar, useValue: { open: () => {} } },
        provideHttpClient(),
        provideHttpClientTesting(),
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(AddressComponentTest);
    component = fixture.componentInstance;

    userService = TestBed.inject(UserService);

    (component as any).dialog = dialogSpy;
    fixture.detectChanges();
  });

  it('should open create dialog and emit saved address', (done) => {
    mockFormDialog(
      () => component,
      () => dialogSpy,
      addressFactory,
      true,
      done,
    );
  });

  it('should open edit dialog and emit updated address', (done) => {
    const mockUser = addressFactory.create();
    mockFormDialog(
      () => component,
      () => dialogSpy,
      mockUser,
      false,
      done,
    );
  });
});
