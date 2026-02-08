import { Entity } from './base/entity.interface';

/** Type for an address */
export interface Address extends Entity {
  id: number;
  user_id?: number;
  name: string;
  street: string;
  city: string;
  postal_code: string;
  state: string;
  country: string;
}

/** Type for creating a new address */
export interface AddressCreationData {
  user_id: number;
  name: string;
  street: string;
  city: string;
  postal_code: string;
  state: string;
  country: string;
}
