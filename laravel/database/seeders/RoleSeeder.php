<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the Role seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(
            attributes: ['name' => 'Admin'],
            values: ['description' => 'Role with full access.']
        );

        Role::firstOrCreate(
            attributes: ['name' => 'Manager'],
            values: ['description' => 'Basic role for Managers.']
        );

        Role::firstOrCreate(
            attributes: ['name' => 'Designer'],
            values: ['description' => 'Basic role for designers.']
        );

        Role::firstOrCreate(
            attributes: ['name' => 'Purchaser'],
            values: ['description' => 'Basic role for purchasers.']
        );

        Role::firstOrCreate(
            attributes: ['name' => 'Editor'],
            values: ['description' => 'Basic role for editors.']
        );

        Role::firstOrCreate(
            attributes: ['name' => 'User'],
            values: ['description' => 'Basic role for users.']
        );
    }
}
