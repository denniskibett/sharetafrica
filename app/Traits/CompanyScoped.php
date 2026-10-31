<?php
// app/Traits/CompanyScoped.php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait CompanyScoped
{
    protected static function bootCompanyScoped()
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (auth()->check() && auth()->user()->company_id) {
                $builder->where('company_id', auth()->user()->company_id);
            }
        });
    }

    public function scopeWithoutCompanyScope(Builder $builder)
    {
        return $builder->withoutGlobalScope('company');
    }
}