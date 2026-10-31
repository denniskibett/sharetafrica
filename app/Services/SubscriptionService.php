<?php
// app/Services/SubscriptionService.php - Updated billing logic

namespace App\Services;

use App\Models\Company;
use App\Models\SubscriptionPlan;
use App\Models\CompanySubscription;
use App\Models\SubscriptionInvoice;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    protected $paymentGateway;
    
    public function __construct(PaymentGatewayService $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }
    
    /**
     * Calculate the actual price for a company based on their plan
     * FIXED: Counts BOTH active and available units
     */
    public function calculateCompanyPrice(Company $company, SubscriptionPlan $plan, $cycle = 'monthly')
    {
        $features = $plan->features_json ?? [];
        $pricingType = $features['pricing_type'] ?? 'fixed';
        
        if ($pricingType === 'per_unit') {
            $pricePerUnit = $features['price_per_unit'] ?? 0;
            
            // Count BOTH active AND available units for maximum coverage
            $unitCount = \App\Models\Unit::where('company_id', $company->id)
                ->whereIn('status', ['occupied', 'available'])
                ->count();
            
            $monthlyPrice = $pricePerUnit * $unitCount;
            
            if ($cycle === 'monthly') {
                return $monthlyPrice;
            } else {
                // Yearly with 10% discount
                return ($monthlyPrice * 12) * 0.9;
            }
        }
        
        // Fixed pricing
        return $cycle === 'monthly' ? $plan->price_monthly : $plan->price_yearly;
    }
    
    /**
     * Subscribe company to a plan with proper pricing
     */
    public function subscribe(Company $company, $planId, $cycle = 'monthly', $paymentMethodId = null)
    {
        DB::beginTransaction();
        
        try {
            $plan = SubscriptionPlan::findOrFail($planId);
            $price = $this->calculateCompanyPrice($company, $plan, $cycle);
            
            // Check if price is 0 (may happen if no units and per-unit pricing)
            if ($price == 0 && $plan->pricing_type === 'per_unit') {
                // Allow subscription with zero price warning
                \Log::warning('Company subscribed to per-unit plan with 0 units', [
                    'company_id' => $company->id,
                    'plan_id' => $planId
                ]);
            }
            
            // Create subscription record
            $subscription = CompanySubscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'status' => 'pending',
                'starts_at' => now(),
                'ends_at' => $cycle === 'monthly' ? now()->addMonth() : now()->addYear(),
                'trial_ends_at' => $plan->trial_days ? now()->addDays($plan->trial_days) : null,
                'auto_renew' => true,
                'billing_cycle' => $cycle,
                'unit_count' => \App\Models\Unit::where('company_id', $company->id)->whereIn('status', ['occupied', 'available'])->count() // Store for reference
            ]);
            
            // Process payment if not trial
            if (!$plan->trial_days || $price == 0) {
                if ($price > 0) {
                    $payment = $this->paymentGateway->charge(
                        $company,
                        $price,
                        $paymentMethodId,
                        "{$plan->name} Subscription - {$cycle}"
                    );
                    
                    // Create invoice
                    $this->createInvoice($subscription, $payment);
                } else {
                    // Zero price subscription (no payment needed)
                    $subscription->status = 'active';
                    $subscription->save();
                }
            } else {
                $subscription->status = 'trial';
                $subscription->save();
            }
            
            // Update company subscription status
            $company->subscription_status = $subscription->status;
            $company->save();
            
            DB::commit();
            return $subscription;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Renew subscription with dynamic pricing
     */
    public function renewSubscription(CompanySubscription $subscription)
    {
        $company = $subscription->company;
        $plan = $subscription->plan;
        
        if (!$company || !$plan) {
            return false;
        }
        
        $price = $this->calculateCompanyPrice($company, $plan, $subscription->billing_cycle);
        
        // If price is 0 and per-unit, just extend the subscription
        if ($price == 0 && $plan->pricing_type === 'per_unit') {
            $subscription->starts_at = $subscription->ends_at;
            $subscription->ends_at = $subscription->billing_cycle === 'monthly' 
                ? $subscription->ends_at->addMonth()
                : $subscription->ends_at->addYear();
            $subscription->status = 'active';
            $subscription->save();
            
            // Update company status
            $company->subscription_status = 'active';
            $company->save();
            
            return true;
        }
        
        // Get default payment method
        $paymentMethod = $subscription->company->defaultPaymentMethod;
        
        if (!$paymentMethod) {
            $subscription->status = 'past_due';
            $subscription->save();
            
            // Update company status
            $company->subscription_status = 'past_due';
            $company->save();
            
            // Send notification to company
            event(new SubscriptionPaymentFailed($subscription));
            return false;
        }
        
        try {
            $payment = $this->paymentGateway->charge(
                $subscription->company,
                $price,
                $paymentMethod->id,
                "{$subscription->plan->name} Renewal"
            );
            
            // Create invoice
            $this->createInvoice($subscription, $payment);
            
            // Update subscription dates
            $subscription->starts_at = $subscription->ends_at;
            $subscription->ends_at = $subscription->billing_cycle === 'monthly' 
                ? $subscription->ends_at->addMonth()
                : $subscription->ends_at->addYear();
            $subscription->status = 'active';
            $subscription->unit_count = \App\Models\Unit::where('company_id', $company->id)->whereIn('status', ['occupied', 'available'])->count();
            $subscription->save();
            
            // Update company status
            $company->subscription_status = 'active';
            $company->save();
            
            event(new SubscriptionRenewed($subscription));
            return true;
            
        } catch (\Exception $e) {
            $subscription->status = 'past_due';
            $subscription->save();
            
            $company->subscription_status = 'past_due';
            $company->save();
            
            event(new SubscriptionPaymentFailed($subscription));
            return false;
        }
    }
    
    protected function createInvoice($subscription, $payment)
    {
        $invoice = SubscriptionInvoice::create([
            'company_subscription_id' => $subscription->id,
            'invoice_number' => 'SUB-' . strtoupper(uniqid()),
            'amount' => $payment['amount'] ?? 0,
            'currency' => $payment['currency'] ?? 'KES',
            'status' => 'paid',
            'payment_method' => $payment['method'] ?? 'manual',
            'payment_id' => $payment['id'] ?? null,
            'due_date' => now(),
            'paid_at' => now(),
            'invoice_json' => json_encode($payment)
        ]);
        
        return $invoice;
    }
    
    /**
     * Cancel subscription
     */
    public function cancelSubscription(CompanySubscription $subscription, $immediate = false)
    {
        if ($immediate) {
            $subscription->status = 'cancelled';
            $subscription->ends_at = now();
        } else {
            $subscription->auto_renew = false;
            $subscription->status = 'cancelled';
            // Keep active until end date
        }
        
        $subscription->save();
        
        // Update company status
        $company = $subscription->company;
        if ($company) {
            $company->subscription_status = 'cancelled';
            $company->save();
        }
        
        return $subscription;
    }
}