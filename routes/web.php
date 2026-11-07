<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\EmailDesignController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WaitingListController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Marketing site (public)
|--------------------------------------------------------------------------
*/

Route::controller(HomeController::class)->group(function () {
    // Pages
    Route::get('/', 'index')->name('home');
    Route::get('/merchants', 'merchants')->name('merchants');
    Route::get('/personal', 'personal')->name('personal');
    Route::get('/trade', 'trade')->name('trade');
    Route::get('/developers', 'developers')->name('developers');
    Route::get('/company', 'company')->name('company');
    Route::get('/contact', 'contact')->name('contact');
});

// Dashboard › Settlements: second approval for large API batches. Names under "pay." so the ledger freeze stops approvals.
Route::middleware(['auth', 'permission:developer.api_keys'])->prefix('settlements')->controller(\App\Http\Controllers\SettlementApprovalsController::class)->group(function () {
    Route::get('/', 'index')->name('settlements.index');
    Route::post('/{settlement}/approve', 'approve')->middleware('password.confirm')->name('pay.settlements.approve');
    Route::post('/{settlement}/reject', 'reject')->middleware('password.confirm')->name('pay.settlements.reject');
});

// Connected accounts, the person's side (docs/v2/INTEGRATION.md §6): consent with the PIN, and disconnect any time
Route::prefix('connect/{token}')->where(['token' => '[A-Za-z0-9]{40}'])->controller(\App\Http\Controllers\ConnectConsentController::class)->group(function () {
    Route::get('/', 'show')->middleware('throttle:60,1')->name('connect.show');
    Route::post('/approve', 'approve')->middleware(['auth', 'throttle:10,1'])->name('connect.approve');
    Route::post('/decline', 'decline')->middleware(['auth', 'throttle:10,1'])->name('connect.decline');
});
Route::middleware('auth')->controller(\App\Http\Controllers\ConnectConsentController::class)->group(function () {
    Route::get('/connected-apps', 'index')->name('connections.index');
    Route::post('/connected-apps/{connection}/disconnect', 'revoke')->name('connections.revoke');
});

// Hosted pay page for API payment requests (docs/v2/INTEGRATION.md §3). Paying moves money: names under "pay." so the
// ledger freeze stops it. Sharet wallet needs sign-in and the PIN; M-PESA works for anyone with the link.
Route::prefix('r/{token}')->where(['token' => '[A-Za-z0-9]{40}'])->controller(\App\Http\Controllers\PayRequestController::class)->group(function () {
    Route::get('/', 'show')->middleware('throttle:60,1')->name('pay-request.show');
    Route::get('/status', 'status')->middleware('throttle:60,1')->name('pay-request.status');
    Route::post('/sharet', 'sharet')->middleware(['auth', 'throttle:10,1'])->name('pay.request.sharet');
    Route::post('/mpesa', 'mpesa')->middleware('throttle:5,1')->name('pay.request.mpesa');
    Route::post('/sandbox', 'sandbox')->middleware('throttle:20,1')->name('pay-request.sandbox');
});

// The Android app (APK uploaded by the app's scripts/release-android.sh; offered on the Developers page for testing only)
Route::get('/app/android/{abi?}', [\App\Http\Controllers\AppDownloadController::class, 'android'])->name('app.android');

Route::controller(HomeController::class)->group(function () {

    // Form submissions (public — do NOT require auth); thank-you emails are sent from HomeController
    Route::middleware('throttle:10,10')->group(function () {
        Route::post('/personal/signup', 'storePersonalSignup')->name('personal.signup');
        Route::post('/merchants/signup', 'storeMerchantSignup')->name('merchants.signup');
        Route::post('/trade/apply', 'storeTradeApplication')->name('trade.apply');
        Route::post('/developers/apply', 'storeDeveloperApplication')->name('developers.apply');
        Route::post('/contact/send', 'storeContact')->name('contact.send');
    });
});

/*
|--------------------------------------------------------------------------
| Website analytics beacon + SEO files (sitemap, llms.txt)
|--------------------------------------------------------------------------
*/

Route::post('/t', TrackController::class)->middleware('throttle:240,1')
    ->withoutMiddleware([\App\Http\Middleware\ApplyIntegrations::class])->name('track');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::view('/privacy', 'privacy')->name('privacy');
Route::get('/llms.txt', [SeoController::class, 'llms'])->name('llms');

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Dashboard shell + its JSON (stat cards, charts, recent activity)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    Route::get('/dashboard/chart/{name}', [DashboardController::class, 'chart'])->name('dashboard.chart')->where('name', '[a-z]+');
    Route::get('/dashboard/activity', [DashboardController::class, 'activity'])->name('dashboard.activity');
    Route::get('/dashboard/menu', [DashboardController::class, 'menu'])->name('dashboard.menu');

    // Waiting list status page (user side)
    Route::get('/waiting-list', [WaitingListController::class, 'show'])->name('waiting_list.show');

    // Onboarding: invited people choose their lane and fill in their details
    Route::prefix('onboarding')->name('onboarding.')->controller(OnboardingController::class)->group(function () {
        Route::get('/', 'welcome')->name('welcome');
        Route::get('/choose-lane', 'chooseLane')->name('choose_lane');
        Route::post('/choose-lane', 'storeLane')->name('store_lane');

        Route::get('/merchant', 'merchant')->name('merchant');
        Route::post('/merchant', 'storeMerchant')->name('merchant.store');

        Route::get('/business', 'business')->name('business');
        Route::post('/business', 'storeBusiness')->name('business.store');

        Route::get('/developer', 'developer')->name('developer');
        Route::post('/developer', 'storeDeveloper')->name('developer.store');

        Route::get('/individual', 'individual')->name('individual');
        Route::post('/individual', 'storeIndividual')->name('individual.store');
    });

    // Profile (overview) and account settings: personal details, verification (KYC tiers), security
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/verification', [ProfileController::class, 'updateVerification'])->name('profile.verification');
    Route::post('/profile/documents/{type}', [ProfileController::class, 'uploadDocument'])->name('profile.document.upload')->where('type', '[a-z_]+');
    Route::get('/kyc-documents/{user}/{type}', [ProfileController::class, 'document'])->name('profile.document')->where('type', '[a-z_]+');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.delete-avatar');
    Route::get('/profile/data', [ProfileController::class, 'getUserData'])->name('profile.data');

    Route::get('/wallet', [\App\Http\Controllers\WalletController::class, 'index'])->name('wallet.index');

    // Business settings for each tenant (merchant / business company): bank-grade business profile
    Route::get('/settings/business', [CompanySettingsController::class, 'edit'])->name('settings.company');
    Route::put('/settings/business', [CompanySettingsController::class, 'update'])->name('settings.company.update');
    Route::post('/settings/business/logo', [CompanySettingsController::class, 'logo'])->name('settings.company.logo');

    // People lists and reports (admins and support)
    Route::middleware('permission:admin.users')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/activity', [UserController::class, 'activity'])->name('users.activity');
        Route::view('/reports', 'reports.index')->name('reports.index');
    });

    // Admin: people and the waiting list
    Route::middleware('role:admin|super_admin')->group(function () {
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::put('/users/{user}/roles', [UserController::class, 'roles'])->name('users.roles');

        Route::get('/admin/waiting-list', [WaitingListController::class, 'index'])->name('admin.waiting_list');
        Route::post('/admin/waiting-list/{user}/invite', [WaitingListController::class, 'invite'])->name('admin.waiting_list.invite');
    });
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

Route::middleware(['auth', 'permission:developer.api_keys'])
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

Route::middleware(['auth', 'permission:admin.roles'])
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
        Route::get('/series', 'series')->name('series');
        Route::get('/heatmap', 'heatmap')->name('heatmap');
        Route::get('/clicks', 'clicks')->name('clicks');
    });

/*
|--------------------------------------------------------------------------
| Email designs + enquiries inbox
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'permission:admin.system'])->controller(EmailDesignController::class)->group(function () {
    Route::get('/admin/emails', 'index')->name('admin.emails.index');
    Route::get('/admin/emails/{key}', 'show')->name('admin.emails.show')->where('key', '[a-z-]+');
});
Route::middleware(['auth', 'permission:waiting_list.invite'])
    ->get('/admin/enquiries', [EmailDesignController::class, 'messages'])->name('admin.contact.index');

/*
|--------------------------------------------------------------------------
| System (platform admins) and the public website's team and blog
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'permission:admin.system'])->group(function () {
    Route::get('/admin/system', [SystemController::class, 'index'])->name('admin.system.index');
    Route::put('/admin/system', [SystemController::class, 'update'])->name('system.update');
    Route::post('/admin/system/clear-cache', [SystemController::class, 'clearCache'])->name('system.clear-cache');
    Route::post('/admin/system/maintenance', [SystemController::class, 'toggleMaintenance'])->name('system.toggle-maintenance');
    Route::post('/admin/system/lockdown', [SystemController::class, 'lockdown'])->middleware('role:super_admin')->name('system.lockdown');

    Route::prefix('admin/team')->name('admin.team.')->controller(TeamController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::post('/{member}', 'update')->name('update')->whereNumber('member'); // POST: carries a photo upload
        Route::delete('/{member}', 'destroy')->name('destroy')->whereNumber('member');
    });

    Route::prefix('admin/companies')->name('admin.group-companies.')->controller(\App\Http\Controllers\Admin\GroupCompanyController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::post('/{company}', 'update')->name('update')->whereNumber('company');
        Route::delete('/{company}', 'destroy')->name('destroy')->whereNumber('company');
    });

    Route::prefix('admin/blog')->name('admin.posts.')->controller(PostController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/new', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{post}/edit', 'edit')->name('edit')->whereNumber('post');
        Route::get('/{post}/stats', 'stats')->name('stats')->whereNumber('post');
        Route::post('/{post}', 'update')->name('update')->whereNumber('post');
        Route::delete('/{post}', 'destroy')->name('destroy')->whereNumber('post');
    });
});

Route::get('/blog', [PostController::class, 'publicIndex'])->name('blog.index');
Route::get('/companies/{slug}', [HomeController::class, 'groupCompany'])->name('companies.show')->where('slug', '[a-z0-9-]+');
Route::get('/blog/{slug}', [PostController::class, 'publicShow'])->name('blog.show')->where('slug', '[a-z0-9-]+');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';
