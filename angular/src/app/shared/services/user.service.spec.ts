import { TestBed } from '@angular/core/testing';
import { UserEntityFactory } from '../../../testing/factories/user-entity.factory';
import { BaseCrudServiceSpec } from '../../../testing/services/base-crud.abstract.spec';
import { ApiService } from '../../core/services/api.service';
import { CrudService } from '../../core/services/crud.service';
import { User } from '../entities/user.interface';
import { UserService } from './user.service';

const mockApiService = jasmine.createSpyObj('ApiService', ['get', 'post', 'put', 'delete']);

class UserServiceSpec extends BaseCrudServiceSpec<User> {
  protected endpoint = 'users';
  protected factory = new UserEntityFactory();
}

const serviceSpec = new UserServiceSpec();

describe('UserService (via BaseCrudServiceSpec)', () => {
  let service: UserService;
  let crudService: CrudService<any>;
  let userFactory: UserEntityFactory;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [UserService, { provide: ApiService, useValue: mockApiService }],
    });

    serviceSpec.setService(TestBed.inject(UserService));

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
    expect(serviceSpec.getService().getEndpoint()).toEqual('users');
  });
});
