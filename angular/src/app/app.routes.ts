import { Routes } from '@angular/router';

export const routes: Routes = [
  {
    path: 'users',
    loadComponent: () => import('./features/user/user.component').then((m) => m.UserComponent),
  },

  {
    path: 'roles',
    loadComponent: () => import('./features/role/role.component').then((m) => m.RoleComponent),
  },

  {
    path: 'addresses',
    loadComponent: () =>
      import('./features/address/address.component').then((m) => m.AddressComponent),
  },

  {
    path: '',
    redirectTo: 'users',
    pathMatch: 'full',
  },
];
