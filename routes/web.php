<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
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

    Route::get('/waiting-list', [UserController::class, 'show'])->name('waiting_list.show');

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

    Route::get('/admin/waiting-list', [UserController::class, 'index'])->name('admin.waiting_list');
    Route::post('/admin/waiting-list/{user}/invite', [UserController::class, 'invite'])->name('admin.waiting_list.invite');
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
| Authentication
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';