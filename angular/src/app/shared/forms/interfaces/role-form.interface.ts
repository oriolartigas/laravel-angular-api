import { FormControl } from '@angular/forms';

/**
 * Controls of the role form
 */
export interface RoleFormControls {
  id?: FormControl<number | null>;
  user_ids?: FormControl<number[] | null>;
  name: FormControl<string | null>;
  description: FormControl<string | null>;
}
