<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OTPsController;

use App\Http\Controllers\BrandsController;

use App\Http\Controllers\DivisionsController;
use App\Http\Controllers\DistrictsController;
use App\Http\Controllers\ThanasController;

use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UsersController;

use App\Http\Controllers\FAQsController;

use App\Http\Controllers\ActivitiesController;
use App\Http\Controllers\AuditLogController;
use Arcanedev\LogViewer\Http\Controllers\LogViewerController;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::middleware('auth:web')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::get('/edit-profile', [ProfileController::class, 'editProfile'])->name('edit-profile');
    Route::put('/update-profile', [ProfileController::class, 'updateProfile'])->name('update-profile');
    Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('change-password');

    Route::group(['prefix' => 'permission', 'as' => 'permission.'], function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::get('create', [PermissionController::class, 'create'])->name('create');
        Route::post('/', [PermissionController::class, 'store'])->name('store');
        Route::get('/trash', [PermissionController::class, 'trash'])->name('trash');
        Route::group(['prefix' => '{permission}'], function () {
            Route::get('/', [PermissionController::class, 'show'])->name('show')->withTrashed();
            Route::get('edit', [PermissionController::class, 'edit'])->name('edit');
            Route::patch('/', [PermissionController::class, 'update'])->name('update');
            Route::delete('/', [PermissionController::class, 'destroy'])->name('destroy');
            Route::post('restore', [PermissionController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [PermissionController::class, 'delete'])->name('delete');
        });
    });

    Route::group(['prefix' => 'role', 'as' => 'role.'], function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/trash', [RoleController::class, 'trash'])->name('trash');
        Route::group(['prefix' => '{role}'], function () {
            Route::get('/', [RoleController::class, 'show'])->name('show')->withTrashed();
            Route::get('edit', [RoleController::class, 'edit'])->name('edit');
            Route::patch('/', [RoleController::class, 'update'])->name('update');
            Route::get('/permissions', [RoleController::class, 'editPermissions'])->name('edit-permissions');
            Route::post('/permissions', [RoleController::class, 'updatePermissions'])->name('update-permissions');
            Route::delete('/', [RoleController::class, 'destroy'])->name('destroy');
            Route::post('restore', [RoleController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [RoleController::class, 'delete'])->name('delete');
        });
    });

    Route::group(['prefix' => 'user', 'as' => 'user.'], function () {
        Route::get('/', [UsersController::class, 'index'])->name('index');
        Route::get('create', [UsersController::class, 'create'])->name('create');
        Route::post('/', [UsersController::class, 'store'])->name('store');
        Route::get('/trash', [UsersController::class, 'trash'])->name('trash');
        Route::group(['prefix' => '{user}'], function () {
            Route::get('/', [UsersController::class, 'show'])->name('show');
            Route::get('edit', [UsersController::class, 'edit'])->name('edit');
            Route::patch('/', [UsersController::class, 'update'])->name('update');
            Route::delete('/', [UsersController::class, 'destroy'])->name('destroy');
            Route::post('restore', [UsersController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [UsersController::class, 'delete'])->name('delete');

            Route::get('/assign-permissions', [UsersController::class, 'assignPermissions'])->name('assign-permissions');
            Route::post('assign-permissions', [UsersController::class, 'syncPermissions'])->name('sync-permissions');

            Route::get('/assign-roles', [UsersController::class, 'assignRoles'])->name('assign-roles');
            Route::post('assign-roles', [UsersController::class, 'syncRoles'])->name('sync-roles');

            Route::post('change-password', [UsersController::class, 'changePassword'])->name('change-password');

            Route::get('/activities', [UsersController::class, 'activities'])->name('activities');
            Route::get('/login-history', [UsersController::class, 'loginHistory'])->name('login-history');
        });
    });

    Route::group(['prefix' => 'brand', 'as' => 'brand.'], function () {
        Route::get('/', [BrandsController::class, 'index'])->name('index');
        Route::get('create', [BrandsController::class, 'create'])->name('create');
        Route::post('/', [BrandsController::class, 'store'])->name('store');
        Route::get('/trash', [BrandsController::class, 'trash'])->name('trash');
        Route::group(['prefix' => '{brand}'], function () {
            Route::get('/', [BrandsController::class, 'show'])->name('show')->withTrashed();
            Route::get('edit', [BrandsController::class, 'edit'])->name('edit');
            Route::patch('/', [BrandsController::class, 'update'])->name('update');
            Route::delete('/', [BrandsController::class, 'destroy'])->name('destroy');
            Route::post('restore', [BrandsController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [BrandsController::class, 'delete'])->name('delete');
        });
    });


    //Route::resource('division', DivisionsController::class);
    Route::group(['prefix' => 'division', 'as' => 'division.'], function () {
        Route::get('/', [DivisionsController::class, 'index'])->name('index');
        Route::get('create', [DivisionsController::class, 'create'])->name('create');
        Route::post('/', [DivisionsController::class, 'store'])->name('store');
        Route::get('/trash', [DivisionsController::class, 'trash'])->name('trash');
        Route::group(['prefix' => '{division}'], function () {
            Route::get('/', [DivisionsController::class, 'show'])->name('show')->withTrashed();
            Route::get('edit', [DivisionsController::class, 'edit'])->name('edit');
            Route::patch('/', [DivisionsController::class, 'update'])->name('update');
            Route::delete('/', [DivisionsController::class, 'destroy'])->name('destroy');
            Route::post('restore', [DivisionsController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [DivisionsController::class, 'delete'])->name('delete');
        });
    });

    //Route::resource('district', DistrictsController::class);
    Route::group(['prefix' => 'district', 'as' => 'district.'], function () {
        Route::get('/', [DistrictsController::class, 'index'])->name('index');
        Route::get('create', [DistrictsController::class, 'create'])->name('create');
        Route::post('/', [DistrictsController::class, 'store'])->name('store');
        Route::get('/trash', [DistrictsController::class, 'trash'])->name('trash');
        Route::group(['prefix' => '{district}'], function () {
            Route::get('/', [DistrictsController::class, 'show'])->name('show')->withTrashed();
            Route::get('edit', [DistrictsController::class, 'edit'])->name('edit');
            Route::patch('/', [DistrictsController::class, 'update'])->name('update');
            Route::delete('/', [DistrictsController::class, 'destroy'])->name('destroy');
            Route::post('restore', [DistrictsController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [DistrictsController::class, 'delete'])->name('delete');
        });
    });
    Route::get('/get-districts-by-division', [DistrictsController::class, 'getDistrictsByDivision'])->name('getDistrictsByDivision');

    //Route::resource('thana', ThanasController::class);
    Route::group(['prefix' => 'thana', 'as' => 'thana.'], function () {
        Route::get('/', [ThanasController::class, 'index'])->name('index');
        Route::get('create', [ThanasController::class, 'create'])->name('create');
        Route::post('/', [ThanasController::class, 'store'])->name('store');
        Route::get('/trash', [ThanasController::class, 'trash'])->name('trash');
        Route::group(['prefix' => '{thana}'], function () {
            Route::get('/', [ThanasController::class, 'show'])->name('show')->withTrashed();
            Route::get('edit', [ThanasController::class, 'edit'])->name('edit');
            Route::patch('/', [ThanasController::class, 'update'])->name('update');
            Route::delete('/', [ThanasController::class, 'destroy'])->name('destroy');
            Route::post('restore', [ThanasController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [ThanasController::class, 'delete'])->name('delete');
        });
    });
    Route::get('/get-thanas-by-district', [ThanasController::class, 'getThanasByDistrict'])->name('getThanasByDistrict');

    Route::group(['prefix' => 'faq', 'as' => 'faq.'], function () {
        Route::get('/', [FAQsController::class, 'index'])->name('index');
        Route::get('create', [FAQsController::class, 'create'])->name('create');
        Route::post('/', [FAQsController::class, 'store'])->name('store');
        Route::get('/trash', [FAQsController::class, 'trash'])->name('trash');
        Route::group(['prefix' => '{faq}'], function () {
            Route::get('/', [FAQsController::class, 'show'])->name('show')->withTrashed();
            Route::get('edit', [FAQsController::class, 'edit'])->name('edit');
            Route::patch('/', [FAQsController::class, 'update'])->name('update');
            Route::delete('/', [FAQsController::class, 'destroy'])->name('destroy');
            Route::post('restore', [FAQsController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [FAQsController::class, 'delete'])->name('delete');
        });
    });

    // Arcandev LogViewer Package Route Override
    Route::prefix('log-viewer')->name('log-viewer.')->group(function () {
        Route::get('/', [LogViewerController::class, 'index'])->name('dashboard'); // log-viewer.dashboard
        Route::prefix('/logs')->name('logs.')->group(function () {
            Route::get('/', [LogViewerController::class, 'listLogs'])->name('list'); // log-viewer.logs.list
            Route::delete('/delete', [LogViewerController::class, 'delete'])->name('delete'); // log-viewer.logs.delete
            Route::prefix('/{date}')->group(function () {
                Route::get('/', [LogViewerController::class, 'show'])->name('show'); // log-viewer.logs.show
                Route::put('/download', [LogViewerController::class, 'download'])->name('download'); // log-viewer.logs.download
                Route::get('/{level}', [LogViewerController::class, 'showByLevel'])->name('filter'); // log-viewer.logs.filter
                Route::get('/{level}/search', [LogViewerController::class, 'search'])->name('search'); // log-viewer.logs.search
            });
        });
    });

    // Start Users Login-Logout Histories Routes
    Route::get('/login-history/{type?}', [ActivitiesController::class, 'loginHistory'])->name('login-history')->where('type', 'all|active|expired|my');
    Route::delete('/login-history/{id}/delete', [ActivitiesController::class, 'deleteLoginHistory'])->name('delete-login-history');

    // Start Users Activities (Audit-Log) Routes
    Route::get('/audit-log/{type?}', [AuditLogController::class, 'auditLogs'])->name('audit-log')->where('type', 'all|created|updated|deleted');
    Route::delete('/audit-log/{id}/delete', [AuditLogController::class, 'deleteAuditLog'])->name('delete-audit-log');

});
