import { TestBed } from '@angular/core/testing';
import { RoleEntityFactory } from '../../../../testing/factories/role-entity.factory';
import { BaseCrudServiceSpec } from '../../../../testing/services/base-crud.abstract.spec';
import { ApiService } from '@core/services/api.service';
import { CrudService } from '@core/services/crud.service';
import { Role } from '../../entities/role.interface';
import { RoleService } from './role.service';

const mockApiService = jasmine.createSpyObj('ApiService', ['get', 'post', 'put', 'delete']);

class RoleServiceSpec extends BaseCrudServiceSpec<Role> {
  protected endpoint = 'roles';
  protected factory = new RoleEntityFactory();
}

const serviceSpec = new RoleServiceSpec();

describe('RoleService (via BaseCrudServiceSpec)', () => {
  let service: RoleService;
  let crudService: CrudService<any>;
  let roleFactory: RoleEntityFactory;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [RoleService, { provide: ApiService, useValue: mockApiService }],
    });

    serviceSpec.setService(TestBed.inject(RoleService));

    // Set the mockApiService instance on the abstract class property
    serviceSpec.setApiService(mockApiService);

    // Reset all spy calls before each test
    mockApiService.get.calls.reset();
    mockApiService.post.calls.reset();
    mockApiService.put.calls.reset();
    mockApiService.delete.calls.reset();
  });

  serviceSpec.runTests();

  it('should have the correct endpoint property set', () => {
    expect(serviceSpec.getService().getEndpoint()).toEqual('roles');
  });
});
