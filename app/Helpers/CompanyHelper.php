<?php
// app/Helpers/CompanyHelper.php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class CompanyHelper
{
    public static function getCurrentCompanyId()
    {
        if (Auth::check() && Auth::user()->company_id) {
            return Auth::user()->company_id;
        }
        
        return session('current_company_id', null);
    }
    
    public static function getCurrentCompany()
    {
        $companyId = self::getCurrentCompanyId();
        if ($companyId) {
            return \App\Models\Company::find($companyId);
        }
        
        return null;
    }
    
    public static function isMultiTenant()
    {
        return (bool) self::getCurrentCompanyId();
    }
}