import { TestBed } from '@angular/core/testing';
import { AddressEntityFactory } from '../../../../testing/factories/address-entity.factory';
import { BaseCrudServiceSpec } from '../../../../testing/services/base-crud.abstract.spec';
import { ApiService } from '@core/services/api.service';
import { CrudService } from '@core/services/crud.service';
import { Address } from '../../entities/address.interface';
import { AddressService } from './address.service';

const mockApiService = jasmine.createSpyObj('ApiService', ['get', 'post', 'put', 'delete']);

class AddressServiceSpec extends BaseCrudServiceSpec<Address> {
  protected endpoint = 'addresses';
  protected factory = new AddressEntityFactory();
}

const serviceSpec = new AddressServiceSpec();

describe('AddressService (via BaseCrudServiceSpec)', () => {
  let service: AddressService;
  let crudService: CrudService<any>;
  let addressFactory: AddressEntityFactory;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [AddressService, { provide: ApiService, useValue: mockApiService }],
    });

    serviceSpec.setService(TestBed.inject(AddressService));

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
    expect(serviceSpec.getService().getEndpoint()).toEqual('addresses');
  });
});
