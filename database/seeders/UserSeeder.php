<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Super admin ----
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@sharet.africa'],
            [
                'name' => 'Sharet Super Admin',
                'phone' => '+254700000001',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'intent' => 'undecided',
                'onboarding_status' => 'active',
                'onboarded_at' => now(),
                'country' => 'KE',
                'city' => 'Nairobi',
                'status' => true,
            ]
        );
        $superAdmin->assignRole('super_admin');
        $superAdmin->getOrCreateWallet('Sharet Super Admin Wallet');

        // ---- A sample waiting-list user (undecided) ----
        $waiting = User::firstOrCreate(
            ['email' => 'waiting@example.com'],
            [
                'name' => 'New Signup',
                'phone' => '+254700000002',
                'password' => Hash::make('password'),
                'intent' => 'undecided',
                'onboarding_status' => 'waiting_list',
                'country' => 'KE',
                'city' => 'Nairobi',
                'status' => true,
            ]
        );
        $waiting->assignRole('waiting_list');

        // ---- A sample individual ----
        $individual = User::firstOrCreate(
            ['email' => 'individual@example.com'],
            [
                'name' => 'Amina Wanjiru',
                'phone' => '+254700000003',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'intent' => 'individual',
                'onboarding_status' => 'active',
                'onboarded_at' => now(),
                'country' => 'KE',
                'city' => 'Nairobi',
                'status' => true,
            ]
        );
        $individual->assignRole('individual');
        $wallet = $individual->getOrCreateWallet('Amina Wanjiru Wallet');
        // Seed with KES 10,000 for testing
        $wallet->depositFloat(10000);

        // ---- A sample merchant (linked to a company) ----
        $company = Company::firstOrCreate(
            ['slug' => 'nairobi-events-co'],
            [
                'name' => 'Nairobi Events Co.',
                'legal_name' => 'Nairobi Events Company Limited',
                'email' => 'hello@nairobievents.co.ke',
                'phone' => '+254700000004',
                'type' => 'merchant',
                'country' => 'KE',
                'city' => 'Nairobi',
                'status' => true,
            ]
        );

        $merchant = User::firstOrCreate(
            ['email' => 'merchant@example.com'],
            [
                'name' => 'Grace Mwangi',
                'phone' => '+254700000005',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'intent' => 'merchant',
                'onboarding_status' => 'active',
                'onboarded_at' => now(),
                'company_id' => $company->id,
                'country' => 'KE',
                'city' => 'Nairobi',
                'status' => true,
            ]
        );
        $merchant->assignRole('merchant');
        $merchant->getOrCreateWallet('Nairobi Events Co. Wallet');

        // ---- A sample business / trader ----
        $traderCompany = Company::firstOrCreate(
            ['slug' => 'nairobi-electronics'],
            [
                'name' => 'Nairobi Electronics Ltd.',
                'email' => 'grace@nairobielectronics.co.ke',
                'type' => 'business',
                'country' => 'KE',
                'city' => 'Nairobi',
                'status' => true,
            ]
        );

        $trader = User::firstOrCreate(
            ['email' => 'trade@example.com'],
            [
                'name' => 'Grace Mwangi',
                'phone' => '+254700000006',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'intent' => 'business',
                'onboarding_status' => 'active',
                'onboarded_at' => now(),
                'company_id' => $traderCompany->id,
                'country' => 'KE',
                'city' => 'Nairobi',
                'status' => true,
            ]
        );
        $trader->assignRole('business');
        $trader->getOrCreateWallet('Nairobi Electronics Wallet');

        // ---- A sample techie / developer ----
        $techie = User::firstOrCreate(
            ['email' => 'dev@example.com'],
            [
                'name' => 'Brian Otieno',
                'phone' => '+254700000007',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'intent' => 'techie',
                'onboarding_status' => 'active',
                'onboarded_at' => now(),
                'country' => 'KE',
                'city' => 'Nairobi',
                'status' => true,
            ]
        );
        $techie->assignRole('techie');

        // ---- A sample support user ----
        $support = User::firstOrCreate(
            ['email' => 'support@sharet.africa'],
            [
                'name' => 'Sharet Support',
                'phone' => '+254700000008',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'intent' => 'undecided',
                'onboarding_status' => 'active',
                'onboarded_at' => now(),
                'country' => 'KE',
                'city' => 'Nairobi',
                'status' => true,
            ]
        );
        $support->assignRole('support');
    }
}