<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the Address seed
     */
    public function run(): void
    {
        Address::factory(count: 20)->create();
    }
}
