import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, fakeAsync, TestBed, tick } from '@angular/core/testing';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { of, Subject } from 'rxjs';

import { RoleEntityFactory } from '../../../../testing/factories/role-entity.factory';
import { UserEntityFactory } from '../../../../testing/factories/user-entity.factory';
import { mockServiceClass } from '../../../../testing/mocks/mock-crud-service.spec';
import { mockFormDialog } from '../../../../testing/mocks/mocks.spec';
import { RoleService } from '@shared/services/entities/role.service';
import { UserService } from '@shared/services/entities/user.service';
import { UserComponentTest } from './user.component.test';

/**
 * Test suite for UserComponent dialogs.
 *
 * This suite tests:
 *  - Opening a create dialog and ensuring the created user is emitted.
 *  - Opening an edit dialog and ensuring the updated user is emitted.
 *  - Opening a Role Picklist dialog and ensuring roles are correctly updated on save.
 *
 * Dependencies:
 *  - UserService and RoleService are mocked using `mockServiceClass`.
 *  - Dialog interactions are mocked using Jasmine spies.
 */
describe('UserComponent Dialogs', () => {
  let component: UserComponentTest;
  let fixture: ComponentFixture<UserComponentTest>;
  let dialogSpy: jasmine.SpyObj<MatDialog>;
  let userFactory: UserEntityFactory;
  let roleFactory: RoleEntityFactory;
  let userService: UserService;
  let roleService: RoleService;

  beforeEach(async () => {
    dialogSpy = jasmine.createSpyObj('MatDialog', ['open']);
    userFactory = new UserEntityFactory();
    roleFactory = new RoleEntityFactory();

    await TestBed.configureTestingModule({
      imports: [UserComponentTest, MatDialogModule],
      providers: [
        {
          provide: UserService,
          useClass: mockServiceClass(UserService, new UserEntityFactory(), {}),
        },
        {
          provide: RoleService,
          useClass: mockServiceClass(RoleService, new RoleEntityFactory(), {}),
        },
        { provide: MatDialog, useValue: dialogSpy },
        { provide: MatSnackBar, useValue: { open: () => {} } },
        provideHttpClient(),
        provideHttpClientTesting(),
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(UserComponentTest);
    component = fixture.componentInstance;

    userService = TestBed.inject(UserService);
    roleService = TestBed.inject(RoleService);

    (component as any).dialog = dialogSpy;
    fixture.detectChanges();
  });

  it('should open create dialog and emit saved user', (done) => {
    mockFormDialog(
      () => component,
      () => dialogSpy,
      userFactory,
      true,
      done,
    );
  });

  it('should open edit dialog and emit updated user', (done) => {
    const mockUser = userFactory.create();
    mockFormDialog(
      () => component,
      () => dialogSpy,
      mockUser,
      false,
      done,
    );
  });

  it('should open Role Picklist Dialog and update roles on save', fakeAsync(() => {
    const mockUser = userFactory.create({ roles_count: 1, roles: [] });
    const roles = roleFactory.createArray(2);
    const emittedIds = roles.map((r) => r.id);

    (roleService.index as jasmine.Spy).and.returnValue(of({ data: roles }));
    (userService.update as jasmine.Spy).and.returnValue(
      of({ data: { ...mockUser, roles_count: 2, roles } }),
    );

    let componentIdsSubject: Subject<number[]>;

    const dialogRef = {
      afterClosed: () => of(true),
      close: jasmine.createSpy('close'),
    } as any;

    dialogSpy.open.and.callFake((component: any, config: any) => {
      componentIdsSubject = config.data.returnSubject;
      return dialogRef;
    });

    component.set_items([mockUser]);
    component.expose_openRoleDialog(mockUser);

    componentIdsSubject!.next(emittedIds);
    tick();

    expect(dialogSpy.open).toHaveBeenCalled();
    expect(userService.update).toHaveBeenCalledWith(
      mockUser.id,
      { role_ids: emittedIds },
      { withCount: 'roles' },
    );

    const updatedUser = component.expose_items()[0];
    expect(updatedUser.roles!.length).toBe(2);
    expect(updatedUser.roles![0].id).toEqual(roles[0].id);
    expect(updatedUser.roles![1].id).toEqual(roles[1].id);
  }));
});
