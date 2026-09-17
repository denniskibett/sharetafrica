<?php
// app/Helpers/SubscriptionHelper.php

namespace App\Helpers;

use App\Models\Company;
use App\Models\Unit;

class SubscriptionHelper
{
    /**
     * Get total units for a company (both occupied and available)
     */
    public static function getCompanyUnitCount(Company $company)
    {
        return Unit::where('company_id', $company->id)
            ->whereIn('status', ['occupied', 'available'])
            ->count();
    }
    
    /**
     * Get unit breakdown for a company
     */
    public static function getCompanyUnitBreakdown(Company $company)
    {
        return [
            'total' => Unit::where('company_id', $company->id)->count(),
            'occupied' => Unit::where('company_id', $company->id)->where('status', 'occupied')->count(),
            'available' => Unit::where('company_id', $company->id)->where('status', 'available')->count(),
            'vacant' => Unit::where('company_id', $company->id)->where('status', 'vacant')->count(),
            'maintenance' => Unit::where('company_id', $company->id)->where('status', 'maintenance')->count(),
        ];
    }
}