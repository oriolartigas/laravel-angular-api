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
import { RoleComponentTest } from './role.component.test';

/**
 * Test suite for RoleComponent dialogs.
 *
 * This suite tests:
 *  - Opening a create dialog and ensuring the created role is emitted.
 *  - Opening an edit dialog and ensuring the updated role is emitted.
 *  - Opening a User Picklist dialog and ensuring users are correctly updated on save.
 *
 * Dependencies:
 *  - RoleService and UserService are mocked using `mockServiceClass`.
 *  - Dialog interactions are mocked using Jasmine spies.
 */
describe('RoleComponent Dialogs', () => {
  let component: RoleComponentTest;
  let fixture: ComponentFixture<RoleComponentTest>;
  let dialogSpy: jasmine.SpyObj<MatDialog>;
  let roleFactory: RoleEntityFactory;
  let userFactory: UserEntityFactory;
  let userService: UserService;
  let roleService: RoleService;

  beforeEach(async () => {
    dialogSpy = jasmine.createSpyObj('MatDialog', ['open']);
    userFactory = new UserEntityFactory();
    roleFactory = new RoleEntityFactory();

    await TestBed.configureTestingModule({
      imports: [RoleComponentTest, MatDialogModule],
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

    fixture = TestBed.createComponent(RoleComponentTest);
    component = fixture.componentInstance;

    userService = TestBed.inject(UserService);
    roleService = TestBed.inject(RoleService);

    (component as any).dialog = dialogSpy;
    fixture.detectChanges();
  });

  it('should open create dialog and emit saved role', (done) => {
    mockFormDialog(
      () => component,
      () => dialogSpy,
      roleFactory,
      true,
      done,
    );
  });

  it('should open edit dialog and emit updated role', (done) => {
    const mockRole = roleFactory.create();
    mockFormDialog(
      () => component,
      () => dialogSpy,
      mockRole,
      false,
      done,
    );
  });

  it('should open User Picklist Dialog and update users on save', fakeAsync(() => {
    const mockRole = roleFactory.create({ users_count: 1, users: [] });
    const users = userFactory.createArray(2);
    const emittedIds = users.map((r) => r.id);

    (userService.index as jasmine.Spy).and.returnValue(of({ data: users }));
    (roleService.update as jasmine.Spy).and.returnValue(
      of({ data: { ...mockRole, users_count: 2, users } }),
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

    component.set_items([mockRole]);
    component.expose_openUserDialog(mockRole);

    componentIdsSubject!.next(emittedIds);
    tick();

    expect(dialogSpy.open).toHaveBeenCalled();
    expect(roleService.update).toHaveBeenCalledWith(
      mockRole.id,
      { user_ids: emittedIds },
      { withCount: 'users' },
    );

    const updatedRole = component.expose_items()[0];
    expect(updatedRole.users!.length).toBe(2);
    expect(updatedRole.users![0].id).toEqual(users[0].id);
    expect(updatedRole.users![1].id).toEqual(users[1].id);
  }));
});
