<?php
// app/Console/Commands/CreateTenantWallets.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Tenants\Models\Tenant;
use App\Modules\Payments\Models\Wallet;
use Illuminate\Support\Str;

class CreateTenantWallets extends Command
{
    protected $signature = 'tenants:create-wallets {--force : Force create wallets even if they exist}';
    protected $description = 'Create wallets for all existing tenants';

    public function handle()
    {
        $this->info('Creating wallets for tenants...');
        
        $tenants = Tenant::all();
        $created = 0;
        $skipped = 0;
        $failed = 0;
        
        $progressBar = $this->output->createProgressBar($tenants->count());
        $progressBar->start();
        
        foreach ($tenants as $tenant) {
            // Check if wallet already exists
            $existingWallet = Wallet::where('holder_type', Tenant::class)
                ->where('holder_id', $tenant->id)
                ->first();
            
            if ($existingWallet && !$this->option('force')) {
                $skipped++;
                $progressBar->advance();
                continue;
            }
            
            // Create wallet manually
            try {
                // If force is enabled and wallet exists, delete it first
                if ($existingWallet && $this->option('force')) {
                    $existingWallet->forceDelete();
                }
                
                $user = $tenant->user;
                $userName = $user ? $user->name : 'Unknown';
                $walletName = $user ? "Wallet for {$user->name}" : "Tenant Wallet #{$tenant->id}";
                $walletDescription = "Main wallet for tenant " . ($user ? $user->name : $tenant->id);
                
                $wallet = Wallet::create([
                    'holder_type' => Tenant::class,
                    'holder_id' => $tenant->id,
                    'name' => $walletName,
                    'slug' => 'default',
                    'uuid' => (string) Str::uuid(),
                    'description' => $walletDescription,
                    'meta' => [
                        'tenant_id' => $tenant->id,
                        'user_id' => $user ? $user->id : null,
                        'created_by_command' => 'tenants:create-wallets',
                    ],
                    'balance' => 0,
                    'decimal_places' => 2,
                ]);
                
                $this->line("  ✓ Wallet created for tenant: {$userName} (ID: {$tenant->id}) - UUID: {$wallet->uuid}");
                $created++;
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to create wallet for tenant ID: {$tenant->id} - " . $e->getMessage());
                $failed++;
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📊 SUMMARY");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("  Total tenants processed: " . $tenants->count());
        $this->info("  ✅ Wallets created: {$created}");
        $this->info("  ⏭️  Wallets skipped (already exist): {$skipped}");
        $this->info("  ❌ Failed: {$failed}");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        
        return Command::SUCCESS;
    }
}