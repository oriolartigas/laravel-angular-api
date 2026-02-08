import { Address } from './address.interface';
import { Entity } from './base/entity.interface';
import { Role } from './role.interface';

/** Type for a user */
export interface User extends Entity {
  id: number;
  name: string;
  email: string;
  password?: string;

  /** Roles relation */
  roles?: Role[];

  /** Addresses relation */
  addresses?: Address[];

  /** Count of roles relation */
  roles_count?: number;

  /** Count of addresses relation */
  addresses_count?: number;

  /** Array of role ids to assign in the update method */
  role_ids?: number[];
}

/** Type for creating a new user */
export type UserCreationData = Pick<User, 'name' | 'email' | 'password'> & {
  password_confirmation: string;
  role_ids?: number[];
};
