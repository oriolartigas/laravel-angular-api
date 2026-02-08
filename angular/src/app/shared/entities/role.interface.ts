import { Entity } from './base/entity.interface';
import { User } from './user.interface';

/** Type for a role */
export interface Role extends Entity {
  id: number;
  name: string;
  description?: string;

  /** Users relation */
  users?: User[];

  /** Count of users relation */
  users_count?: number;

  /** Array of user ids to assign in the update method */
  user_ids?: number[];
}

/** Type for creating a new role */
export interface RoleCreationData {
  name: string;
  description?: string;
  user_ids?: number[];
}
