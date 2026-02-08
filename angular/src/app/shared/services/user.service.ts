import { Injectable } from '@angular/core';
import { CrudService } from '../../core/services/crud.service';
import { User } from '../entities/user.interface';

@Injectable({
  providedIn: 'root',
})
export class UserService extends CrudService<User> {
  protected endpoint = 'users';
}
