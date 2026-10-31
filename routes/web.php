<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\EmailDesignController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WaitingListController;
use App\Http\Controllers\OnboardingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Marketing site (public)
|--------------------------------------------------------------------------
*/

Route::controller(HomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/merchants', 'merchants')->name('merchants');
    Route::get('/personal', 'personal')->name('personal');
    Route::get('/trade', 'trade')->name('trade');
    Route::get('/developers', 'developers')->name('developers');
    Route::get('/company', 'company')->name('company');
    Route::get('/contact', 'contact')->name('contact');
});

/*
|--------------------------------------------------------------------------
| Website analytics beacon + SEO files (sitemap, llms.txt)
|--------------------------------------------------------------------------
*/

Route::post('/t', TrackController::class)->middleware('throttle:240,1')
    ->withoutMiddleware([\App\Http\Middleware\ApplyIntegrations::class])->name('track');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,10')->name('contact.store');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/llms.txt', [SeoController::class, 'llms'])->name('llms');

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard router
    |--------------------------------------------------------------------------
    */


    

    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->hasRole(['super_admin', 'admin'])) {
            return redirect()->route('dashboard.admin');
        }

        if ($user->hasRole(['support', 'operations'])) {
            return redirect()->route('dashboard.ops');
        }

        if ($user->hasRole('individual')) {
            return redirect()->route('dashboard.individual');
        }

        if ($user->hasRole('merchant')) {
            return redirect()->route('dashboard.merchant');
        }

        if ($user->hasRole('business')) {
            return redirect()->route('dashboard.business');
        }

        if ($user->hasRole('techie')) {
            return redirect()->route('dashboard.developer');
        }

        return redirect()->route('waiting_list.show');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Waiting list
    |--------------------------------------------------------------------------
    */

    Route::get('/waiting-list', [WaitingListController::class, 'show'])->name('waiting_list.show');

    /*
    |--------------------------------------------------------------------------
    | Onboarding
    |--------------------------------------------------------------------------
    */

    // OnboardingController is not written yet; registering it would break route:cache on deploy
    if (class_exists(OnboardingController::class)) Route::prefix('onboarding')->name('onboarding.')->group(function () {

        Route::get('/', [OnboardingController::class, 'welcome'])->name('welcome');
        Route::get('/choose-lane', [OnboardingController::class, 'chooseLane'])->name('choose_lane');
        Route::post('/choose-lane', [OnboardingController::class, 'storeLane'])->name('store_lane');

        Route::get('/merchant', [OnboardingController::class, 'merchant'])->name('merchant');
        Route::post('/merchant', [OnboardingController::class, 'storeMerchant'])->name('merchant.store');

        Route::get('/business', [OnboardingController::class, 'business'])->name('business');
        Route::post('/business', [OnboardingController::class, 'storeBusiness'])->name('business.store');

        Route::get('/developer', [OnboardingController::class, 'developer'])->name('developer');
        Route::post('/developer', [OnboardingController::class, 'storeDeveloper'])->name('developer.store');

        Route::get('/individual', [OnboardingController::class, 'individual'])->name('individual');
        Route::post('/individual', [OnboardingController::class, 'storeIndividual'])->name('individual.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.delete-avatar');
    Route::put('/profile/address', [ProfileController::class, 'updateAddress'])->name('profile.address.update');
    Route::get('/profile/data', [ProfileController::class, 'getUserData'])->name('profile.data');
});

/*
|--------------------------------------------------------------------------
| Individual dashboard
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:individual'])->group(function () {
    Route::get('/wallet', function () {
        return view('dashboard.individual');
    })->name('dashboard.individual');
});

/*
|--------------------------------------------------------------------------
| Merchant dashboard
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:merchant'])->group(function () {
    Route::get('/merchant', function () {
        return view('dashboard.merchant');
    })->name('dashboard.merchant');
});

/*
|--------------------------------------------------------------------------
| Business dashboard
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:business'])->group(function () {
    Route::get('/business', function () {
        return view('dashboard.business');
    })->name('dashboard.business');
});

/*
|--------------------------------------------------------------------------
| Developer / Techie dashboard
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:techie'])->group(function () {
    Route::get('/developer', function () {
        return view('dashboard.developer');
    })->name('dashboard.developer');
});

/*
|--------------------------------------------------------------------------
| Admin dashboard
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin|super_admin'])->group(function () {

    Route::get('/admin', function () {
        return view('dashboard.admin');
    })->name('dashboard.admin');

    /*
    |--------------------------------------------------------------------------
    | Waiting list management
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/waiting-list', [WaitingListController::class, 'index'])->name('admin.waiting_list');
    Route::post('/admin/waiting-list/{user}/assign', [WaitingListController::class, 'assign'])->name('admin.waiting_list.assign');
});

/*
|--------------------------------------------------------------------------
| Operations / Support dashboard
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:support|operations|admin|super_admin',
])->group(function () {

    Route::get('/ops', function () {
        return view('dashboard.ops');
    })->name('dashboard.ops');
});

/*
|--------------------------------------------------------------------------
| Integrations (M-Pesa, SMS, email… per company, platform defaults for admins)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'permission:integrations.manage'])
    ->prefix('integrations')->name('integrations.')->controller(IntegrationController::class)
    ->group(function () {
        $provider = implode('|', array_keys(config('integrations')));
        Route::get('/', 'index')->name('index');
        Route::put('/{provider}', 'update')->name('update')->where('provider', $provider);
        Route::patch('/{provider}/toggle', 'toggle')->name('toggle')->where('provider', $provider);
        Route::post('/{provider}/test', 'test')->name('test')->where('provider', $provider);
        Route::post('/smtp/send-test', 'sendTestEmail')->name('smtp.send-test');
        Route::delete('/{provider}', 'destroy')->name('destroy')->where('provider', $provider);
    });

/*
|--------------------------------------------------------------------------
| API keys for the Sharet API
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'permission:api_keys.manage'])
    ->prefix('api-keys')->name('api-keys.')->controller(ApiKeyController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::patch('/{id}/toggle', 'toggle')->name('toggle')->whereNumber('id');
        Route::post('/{id}/rotate', 'rotate')->name('rotate')->whereNumber('id');
        Route::delete('/{id}', 'destroy')->name('destroy')->whereNumber('id');
    });

/*
|--------------------------------------------------------------------------
| Roles & permissions (Spatie)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'permission:roles.manage'])
    ->prefix('admin/roles')->name('admin.roles.')->controller(RoleController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{role}/data', 'getRole')->name('data');
        Route::put('/{role}', 'update')->name('update');
        Route::delete('/{role}', 'destroy')->name('destroy');
    });

/*
|--------------------------------------------------------------------------
| Website analytics (reports + click heatmap)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'permission:analytics.view'])
    ->prefix('admin/analytics')->name('admin.analytics.')->controller(AnalyticsController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/heatmap', 'heatmap')->name('heatmap');
        Route::get('/clicks', 'clicks')->name('clicks');
    });

/*
|--------------------------------------------------------------------------
| Email designs + contact form inbox
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'permission:system.settings'])->controller(EmailDesignController::class)->group(function () {
    Route::get('/admin/emails', 'index')->name('admin.emails.index');
    Route::get('/admin/emails/{key}', 'show')->name('admin.emails.show')->where('key', '[a-z-]+');
});
Route::middleware(['auth', 'permission:waiting_list.manage'])
    ->get('/admin/contact-messages', [EmailDesignController::class, 'messages'])->name('admin.contact.index');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';