import { Injectable } from '@angular/core';
import { CrudService } from '@core/services/crud.service';
import { Role } from '../../entities/role.interface';

@Injectable({
  providedIn: 'root',
})
export class RoleService extends CrudService<Role> {
  protected endpoint = 'roles';
}
