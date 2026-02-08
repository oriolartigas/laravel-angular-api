<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Create 1 admin user and 20 random users
     */
    public function run(): void
    {
        $adminRole = Role::where(column: 'name', operator: '=', value: 'Admin')->first();

        if (! $adminRole) {
            $this->command->info(string: 'The "admin" role were not found. Make sure the RoleSeeder has been executed.');

            return;
        }

        $adminUser = User::factory()->create(attributes: [
            'name' => 'Administrator',
        ]);

        $adminUser->roles()->sync(ids: $adminRole);

        $adminUser->addresses()->saveMany(
            Address::factory(rand(min: 1, max: 3))->make([
                'user_id' => null,
            ])
        );

        $allowedRoleIds = Role::where('name', '!=', 'Admin')
            ->pluck('id');

        User::factory(count: 20)->create()->each(callback: function (User $user) use ($allowedRoleIds): void {
            $randomRoleId = $allowedRoleIds->random(rand(min: 1, max: 2));
            $user->roles()->sync(ids: $randomRoleId->toArray());

            $user->addresses()->saveMany(
                Address::factory(rand(min: 1, max: 3))->make([
                    'user_id' => null,
                ])
            );
        });
    }
}
