<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Demo Customer 1 ───────────────────────────────────────────────────
        $customer1 = Customer::create([
            'account_number' => '1911800',
            'name'           => 'Similoni Tu\'akalau',
            'email'          => 'similoni.tuakalau@example.com',
            'phone'          => '+676 12345',
            'block_number'   => 'BLK 19',
            'address'        => '1 Tonga Address Street',
            'status'         => 'active',
        ]);

        User::create([
            'name'        => $customer1->name,
            'email'       => $customer1->email,
            'password'    => Hash::make('password'),
            'role'        => 'customer',
            'customer_id' => $customer1->id,
            'is_active'   => true,
        ]);

        // ── Demo Customer 2 ───────────────────────────────────────────────────
        $customer2 = Customer::create([
            'account_number' => '1903400',
            'name'           => 'Vilisoni Ha\'unga',
            'email'          => 'vilisoni.haunga@example.com',
            'phone'          => '+676 67890',
            'block_number'   => 'BLK 19',
            'address'        => '1 Tonga Addrs Star',
            'status'         => 'active',
        ]);

        User::create([
            'name'        => $customer2->name,
            'email'       => $customer2->email,
            'password'    => Hash::make('password'),
            'role'        => 'customer',
            'customer_id' => $customer2->id,
            'is_active'   => true,
        ]);

        // ── Demo Customer 3 ───────────────────────────────────────────────────
        $customer3 = Customer::create([
            'account_number' => '1109406',
            'name'           => 'Semisi Motuliki',
            'email'          => 'semisi.motuliki@example.com',
            'phone'          => '+676 11111',
            'block_number'   => 'SOPU',
            'address'        => 'Kolomotu\'a',
            'status'         => 'active',
        ]);

        User::create([
            'name'        => $customer3->name,
            'email'       => $customer3->email,
            'password'    => Hash::make('password'),
            'role'        => 'customer',
            'customer_id' => $customer3->id,
            'is_active'   => true,
        ]);

        $this->command->info('Phase 1 seed complete.');
        $this->command->info('');
        $this->command->info('Demo logins (password: password):');
        $this->command->info('  ' . $customer1->email);
        $this->command->info('  ' . $customer2->email);
        $this->command->info('  ' . $customer3->email);
    }
}
