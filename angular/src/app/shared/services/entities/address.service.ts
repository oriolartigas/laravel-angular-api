import { Injectable } from '@angular/core';
import { CrudService } from '@core/services/crud.service';
import { Address } from '../../entities/address.interface';

@Injectable({
  providedIn: 'root',
})
export class AddressService extends CrudService<Address> {
  protected endpoint = 'addresses';
}
