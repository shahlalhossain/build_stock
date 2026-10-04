<?php

use App\Http\Controllers\ActivitiesController;
use App\Http\Controllers\AttributesController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BrandsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistrictsController;
use App\Http\Controllers\DivisionsController;
use App\Http\Controllers\FAQsController;
use App\Http\Controllers\OTPsController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductDeliveriesController;
use App\Http\Controllers\ProductPurchasesController;
use App\Http\Controllers\ProductReceivesController;
use App\Http\Controllers\ProductRequisitionsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProductTransfersController;
use App\Http\Controllers\ProductUnitsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockTransactionsController;
use App\Http\Controllers\StoresController;
use App\Http\Controllers\SubCategoriesController;
use App\Http\Controllers\SuppliersController;
use App\Http\Controllers\ThanasController;
use App\Http\Controllers\UnitConversionsController;
use App\Http\Controllers\UsersController;
use Arcanedev\LogViewer\Http\Controllers\LogViewerController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

    Route::group(['prefix' => 'attribute', 'as' => 'attribute.'], function () {
        Route::get('/', [AttributesController::class, 'index'])->name('index');
        Route::get('create', [AttributesController::class, 'create'])->name('create');
        Route::post('/', [AttributesController::class, 'store'])->name('store');
        Route::get('/trash', [AttributesController::class, 'trash'])->name('trash');
        Route::group(['prefix' => '{attribute}'], function () {
            Route::get('/', [AttributesController::class, 'show'])->name('show')->withTrashed();
            Route::get('edit', [AttributesController::class, 'edit'])->name('edit');
            Route::patch('/', [AttributesController::class, 'update'])->name('update');
            Route::delete('/', [AttributesController::class, 'destroy'])->name('destroy');
            Route::post('restore', [AttributesController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [AttributesController::class, 'delete'])->name('delete');
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
            Route::post('update-status', [BrandsController::class, 'updateStatus'])->name('update-status');
            Route::delete('/', [BrandsController::class, 'destroy'])->name('destroy');
            Route::post('restore', [BrandsController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [BrandsController::class, 'delete'])->name('delete');
        });
    });

    Route::group(['prefix' => 'category', 'as' => 'category.'], function () {
        Route::get('/', [CategoriesController::class, 'index'])->name('index');
        Route::get('create', [CategoriesController::class, 'create'])->name('create');
        Route::post('/', [CategoriesController::class, 'store'])->name('store');
        Route::get('/trash', [CategoriesController::class, 'trash'])->name('trash');
        Route::group(['prefix' => '{category}'], function () {
            Route::get('/', [CategoriesController::class, 'show'])->name('show')->withTrashed();
            Route::get('edit', [CategoriesController::class, 'edit'])->name('edit');
            Route::patch('/', [CategoriesController::class, 'update'])->name('update');
            Route::delete('/', [CategoriesController::class, 'destroy'])->name('destroy');
            Route::post('restore', [CategoriesController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [CategoriesController::class, 'delete'])->name('delete');
        });
    });

    Route::group(['prefix' => 'sub-category', 'as' => 'sub-category.'], function () {
        Route::get('/', [SubCategoriesController::class, 'index'])->name('index');
        Route::get('create', [SubCategoriesController::class, 'create'])->name('create');
        Route::post('/', [SubCategoriesController::class, 'store'])->name('store');
        Route::get('/trash', [SubCategoriesController::class, 'trash'])->name('trash');
        Route::group(['prefix' => '{sub_category}'], function () {
            Route::get('/', [SubCategoriesController::class, 'show'])->name('show')->withTrashed();
            Route::get('edit', [SubCategoriesController::class, 'edit'])->name('edit');
            Route::patch('/', [SubCategoriesController::class, 'update'])->name('update');
            Route::delete('/', [SubCategoriesController::class, 'destroy'])->name('destroy');
            Route::post('restore', [SubCategoriesController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [SubCategoriesController::class, 'delete'])->name('delete');
        });
    });

    Route::group(['prefix' => 'product-unit', 'as' => 'product-unit.'], function () {
        Route::get('/', [ProductUnitsController::class, 'index'])->name('index');
        Route::get('create', [ProductUnitsController::class, 'create'])->name('create');
        Route::post('/', [ProductUnitsController::class, 'store'])->name('store');
        Route::get('/trash', [ProductUnitsController::class, 'trash'])->name('trash');
        Route::group(['prefix' => '{product_unit}'], function () {
            Route::get('/', [ProductUnitsController::class, 'show'])->name('show')->withTrashed();
            Route::get('edit', [ProductUnitsController::class, 'edit'])->name('edit');
            Route::patch('/', [ProductUnitsController::class, 'update'])->name('update');
            Route::delete('/', [ProductUnitsController::class, 'destroy'])->name('destroy');
            Route::post('restore', [ProductUnitsController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [ProductUnitsController::class, 'delete'])->name('delete');
        });
    });

    Route::group(['prefix' => 'unit-conversion', 'as' => 'unit-conversion.'], function () {
        Route::get('/', [UnitConversionsController::class, 'index'])->name('index');
        Route::get('create', [UnitConversionsController::class, 'create'])->name('create');
        Route::post('/', [UnitConversionsController::class, 'store'])->name('store');
        Route::get('/trash', [UnitConversionsController::class, 'trash'])->name('trash');
        Route::group(['prefix' => '{unit_conversion}'], function () {
            Route::get('/', [UnitConversionsController::class, 'show'])->name('show')->withTrashed();
            Route::get('edit', [UnitConversionsController::class, 'edit'])->name('edit');
            Route::patch('/', [UnitConversionsController::class, 'update'])->name('update');
            Route::delete('/', [UnitConversionsController::class, 'destroy'])->name('destroy');
            Route::post('restore', [UnitConversionsController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [UnitConversionsController::class, 'delete'])->name('delete');
        });
    });

    Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
        Route::get('/', [ProductsController::class, 'index'])->name('index');
        Route::get('create', [ProductsController::class, 'create'])->name('create');
        Route::post('/', [ProductsController::class, 'store'])->name('store');
        Route::get('/trash', [ProductsController::class, 'trash'])->name('trash');
        Route::group(['prefix' => '{product}'], function () {
            Route::get('/', [ProductsController::class, 'show'])->name('show')->withTrashed();
            Route::get('edit', [ProductsController::class, 'edit'])->name('edit');
            Route::patch('/', [ProductsController::class, 'update'])->name('update');
            Route::post('update-status', [ProductsController::class, 'updateStatus'])->name('update-status');
            Route::delete('/', [ProductsController::class, 'destroy'])->name('destroy');
            Route::post('restore', [ProductsController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [ProductsController::class, 'delete'])->name('delete');
        });
    });

    // TODO: Have to Review the Process and also the Code
    Route::group(['prefix' => 'supplier', 'as' => 'supplier.'], function () {
        Route::get('/', [SuppliersController::class, 'index'])->name('index');
        Route::get('create', [SuppliersController::class, 'create'])->name('create');
        Route::post('/', [SuppliersController::class, 'store'])->name('store');
        Route::get('/trash', [SuppliersController::class, 'trash'])->name('trash');
        Route::group(['prefix' => '{supplier}'], function () {
            Route::get('/', [SuppliersController::class, 'show'])->name('show')->withTrashed();
            Route::get('edit', [SuppliersController::class, 'edit'])->name('edit');
            Route::patch('/', [SuppliersController::class, 'update'])->name('update');
            Route::post('update-status', [SuppliersController::class, 'updateStatus'])->name('update-status');
            Route::delete('/', [SuppliersController::class, 'destroy'])->name('destroy');
            Route::post('restore', [SuppliersController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [SuppliersController::class, 'delete'])->name('delete');
        });
    });
    Route::get('/get-branches-by-bank', [SuppliersController::class, 'getBranchesByBank'])->name('getBranchesByBank');

    Route::group(['prefix' => 'otp', 'as' => 'otp.'], function () {
        Route::post('send-mobile', [OTPsController::class, 'sendOTPToMobile'])->name('send.mobile');
        Route::post('verify-mobile', [OTPsController::class, 'verifyMobileOTP'])->name('verify.mobile');
        Route::post('send-email', [OTPsController::class, 'sendOTPToEmail'])->name('send.email');
        Route::post('verify-email', [OTPsController::class, 'verifyEmailOTP'])->name('verify.email');
    });

    Route::group(['prefix' => 'project', 'as' => 'project.'], function () {
        Route::get('/', [ProjectsController::class, 'index'])->name('index');
        Route::get('create', [ProjectsController::class, 'create'])->name('create');
        Route::post('/', [ProjectsController::class, 'store'])->name('store');
        Route::get('/trash', [ProjectsController::class, 'trash'])->name('trash');
        Route::group(['prefix' => '{project}'], function () {
            Route::get('/', [ProjectsController::class, 'show'])->name('show')->withTrashed();
            Route::get('edit', [ProjectsController::class, 'edit'])->name('edit');
            Route::patch('/', [ProjectsController::class, 'update'])->name('update');
            Route::post('update-status', [ProjectsController::class, 'updateStatus'])->name('update-status');
            Route::delete('/', [ProjectsController::class, 'destroy'])->name('destroy');
            Route::post('restore', [ProjectsController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [ProjectsController::class, 'delete'])->name('delete');
        });
    });

    Route::group(['prefix' => 'store', 'as' => 'store.'], function () {
        Route::get('/', [StoresController::class, 'index'])->name('index');
        Route::get('create', [StoresController::class, 'create'])->name('create');
        Route::post('/', [StoresController::class, 'store'])->name('store');
        Route::get('/trash', [StoresController::class, 'trash'])->name('trash');
        Route::group(['prefix' => '{store}'], function () {
            Route::get('/', [StoresController::class, 'show'])->name('show')->withTrashed();
            Route::get('edit', [StoresController::class, 'edit'])->name('edit');
            Route::patch('/', [StoresController::class, 'update'])->name('update');
            Route::post('update-status', [StoresController::class, 'updateStatus'])->name('update-status');
            Route::delete('/', [StoresController::class, 'destroy'])->name('destroy');
            Route::post('restore', [StoresController::class, 'restore'])->name('restore');
            Route::delete('force-delete', [StoresController::class, 'delete'])->name('delete');
        });
    });

    Route::group(['prefix' => 'product-requisition', 'as' => 'product-requisition.'], function () {
        Route::get('/', [ProductRequisitionsController::class, 'index'])->name('index')->middleware('permission:product-requisition.index');
        Route::get('create', [ProductRequisitionsController::class, 'create'])->name('create')->middleware('permission:product-requisition.create');
        Route::post('/', [ProductRequisitionsController::class, 'store'])->name('store')->middleware('permission:product-requisition.create');
        Route::get('/trash', [ProductRequisitionsController::class, 'trash'])->name('trash')->middleware('permission:product-requisition.trash');
        Route::group(['prefix' => '{product_requisition}'], function () {
            Route::get('/', [ProductRequisitionsController::class, 'show'])->name('show')->withTrashed()->middleware('permission:product-requisition.show');
            Route::get('edit', [ProductRequisitionsController::class, 'edit'])->name('edit')->middleware('permission:product-requisition.edit');
            Route::patch('/', [ProductRequisitionsController::class, 'update'])->name('update')->middleware('permission:product-requisition.edit');
            Route::post('update-status', [ProductRequisitionsController::class, 'updateStatus'])->name('update-status')->middleware('permission:product-requisition.update-status');
            Route::delete('/', [ProductRequisitionsController::class, 'destroy'])->name('destroy')->middleware('permission:product-requisition.destroy');
            Route::post('restore', [ProductRequisitionsController::class, 'restore'])->name('restore')->middleware('permission:product-requisition.restore');
            Route::delete('force-delete', [ProductRequisitionsController::class, 'delete'])->name('delete')->middleware('permission:product-requisition.delete');
        });
    });

    Route::group(['prefix' => 'product-purchase', 'as' => 'product-purchase.'], function () {
        Route::get('/', [ProductPurchasesController::class, 'index'])->name('index')->middleware('permission:product-purchase.index');
        Route::get('create', [ProductPurchasesController::class, 'create'])->name('create')->middleware('permission:product-purchase.create');
        Route::post('/', [ProductPurchasesController::class, 'store'])->name('store')->middleware('permission:product-purchase.create');
        Route::get('/trash', [ProductPurchasesController::class, 'trash'])->name('trash')->middleware('permission:product-purchase.trash');
        Route::get('requisition-purchase', [ProductPurchasesController::class, 'requisitionList'])->name('requisition-list')->middleware('permission:product-purchase.create');
        Route::get('requisition-purchase/{product_requisition}', [ProductPurchasesController::class, 'createFromRequisition'])->name('create-from-requisition')->middleware('permission:product-purchase.create');
        Route::post('requisition-purchase', [ProductPurchasesController::class, 'storeFromRequisition'])->name('store-from-requisition')->middleware('permission:product-purchase.create');
        Route::group(['prefix' => '{product_purchase}'], function () {
            Route::get('/', [ProductPurchasesController::class, 'show'])->name('show')->withTrashed()->middleware('permission:product-purchase.show');
            Route::get('edit', [ProductPurchasesController::class, 'edit'])->name('edit')->middleware('permission:product-purchase.edit');
            Route::patch('/', [ProductPurchasesController::class, 'update'])->name('update')->middleware('permission:product-purchase.edit');
            Route::post('update-status', [ProductPurchasesController::class, 'updateStatus'])->name('update-status')->middleware('permission:product-purchase.update-status');
            Route::delete('/', [ProductPurchasesController::class, 'destroy'])->name('destroy')->middleware('permission:product-purchase.destroy');
            Route::post('restore', [ProductPurchasesController::class, 'restore'])->name('restore')->middleware('permission:product-purchase.restore');
            Route::delete('force-delete', [ProductPurchasesController::class, 'delete'])->name('delete')->middleware('permission:product-purchase.delete');
        });
    });

    Route::group(['prefix' => 'product-transfer', 'as' => 'product-transfer.'], function () {
        Route::get('/', [ProductTransfersController::class, 'index'])->name('index')->middleware('permission:product-transfer.index');
        Route::get('create', [ProductTransfersController::class, 'create'])->name('create')->middleware('permission:product-transfer.create');
        Route::post('/', [ProductTransfersController::class, 'store'])->name('store')->middleware('permission:product-transfer.create');
        Route::get('/trash', [ProductTransfersController::class, 'trash'])->name('trash')->middleware('permission:product-transfer.trash');
        Route::group(['prefix' => '{product_transfer}'], function () {
            Route::get('/', [ProductTransfersController::class, 'show'])->name('show')->withTrashed()->middleware('permission:product-transfer.show');
            Route::get('edit', [ProductTransfersController::class, 'edit'])->name('edit')->middleware('permission:product-transfer.edit');
            Route::patch('/', [ProductTransfersController::class, 'update'])->name('update')->middleware('permission:product-transfer.edit');
            Route::post('update-status', [ProductTransfersController::class, 'updateStatus'])->name('update-status')->middleware('permission:product-transfer.update-status');
            Route::delete('/', [ProductTransfersController::class, 'destroy'])->name('destroy')->middleware('permission:product-transfer.destroy');
            Route::post('restore', [ProductTransfersController::class, 'restore'])->name('restore')->middleware('permission:product-transfer.restore');
            Route::delete('force-delete', [ProductTransfersController::class, 'delete'])->name('delete')->middleware('permission:product-transfer.delete');
        });
    });

    Route::group(['prefix' => 'product-receive', 'as' => 'product-receive.'], function () {
        Route::get('/', [ProductReceivesController::class, 'index'])->name('index')->middleware('permission:product-receive.index');
        Route::get('create', [ProductReceivesController::class, 'create'])->name('create')->middleware('permission:product-receive.create');
        Route::post('/', [ProductReceivesController::class, 'store'])->name('store')->middleware('permission:product-receive.create');
        Route::get('/trash', [ProductReceivesController::class, 'trash'])->name('trash')->middleware('permission:product-receive.trash');
        Route::group(['prefix' => '{product_receive}'], function () {
            Route::get('/', [ProductReceivesController::class, 'show'])->name('show')->withTrashed()->middleware('permission:product-receive.show');
            Route::get('edit', [ProductReceivesController::class, 'edit'])->name('edit')->middleware('permission:product-receive.edit');
            Route::patch('/', [ProductReceivesController::class, 'update'])->name('update')->middleware('permission:product-receive.edit');
            Route::post('update-status', [ProductReceivesController::class, 'updateStatus'])->name('update-status')->middleware('permission:product-receive.update-status');
            Route::delete('/', [ProductReceivesController::class, 'destroy'])->name('destroy')->middleware('permission:product-receive.destroy');
            Route::post('restore', [ProductReceivesController::class, 'restore'])->name('restore')->middleware('permission:product-receive.restore');
            Route::delete('force-delete', [ProductReceivesController::class, 'delete'])->name('delete')->middleware('permission:product-receive.delete');
        });
    });

    Route::group(['prefix' => 'product-delivery', 'as' => 'product-delivery.'], function () {
        Route::get('/', [ProductDeliveriesController::class, 'index'])->name('index')->middleware('permission:product-delivery.index');
        Route::get('create', [ProductDeliveriesController::class, 'create'])->name('create')->middleware('permission:product-delivery.create');
        Route::post('/', [ProductDeliveriesController::class, 'store'])->name('store')->middleware('permission:product-delivery.create');
        Route::get('/trash', [ProductDeliveriesController::class, 'trash'])->name('trash')->middleware('permission:product-delivery.trash');
        Route::group(['prefix' => '{product_delivery}'], function () {
            Route::get('/', [ProductDeliveriesController::class, 'show'])->name('show')->withTrashed()->middleware('permission:product-delivery.show');
            Route::get('edit', [ProductDeliveriesController::class, 'edit'])->name('edit')->middleware('permission:product-delivery.edit');
            Route::patch('/', [ProductDeliveriesController::class, 'update'])->name('update')->middleware('permission:product-delivery.edit');
            Route::post('update-status', [ProductDeliveriesController::class, 'updateStatus'])->name('update-status')->middleware('permission:product-delivery.update-status');
            Route::delete('/', [ProductDeliveriesController::class, 'destroy'])->name('destroy')->middleware('permission:product-delivery.destroy');
            Route::post('restore', [ProductDeliveriesController::class, 'restore'])->name('restore')->middleware('permission:product-delivery.restore');
            Route::delete('force-delete', [ProductDeliveriesController::class, 'delete'])->name('delete')->middleware('permission:product-delivery.delete');
        });
    });

    Route::group(['prefix' => 'stock-transaction', 'as' => 'stock-transaction.'], function () {
        Route::get('/', [StockTransactionsController::class, 'index'])->name('index')->middleware('permission:stock-transaction.index');
        Route::get('create', [StockTransactionsController::class, 'create'])->name('create')->middleware('permission:stock-transaction.create');
        Route::post('/', [StockTransactionsController::class, 'store'])->name('store')->middleware('permission:stock-transaction.create');
        Route::get('/trash', [StockTransactionsController::class, 'trash'])->name('trash')->middleware('permission:stock-transaction.trash');
        Route::group(['prefix' => '{stock_transaction}'], function () {
            Route::get('/', [StockTransactionsController::class, 'show'])->name('show')->withTrashed()->middleware('permission:stock-transaction.show');
            Route::get('edit', [StockTransactionsController::class, 'edit'])->name('edit')->middleware('permission:stock-transaction.edit');
            Route::patch('/', [StockTransactionsController::class, 'update'])->name('update')->middleware('permission:stock-transaction.edit');
            Route::post('update-status', [StockTransactionsController::class, 'updateStatus'])->name('update-status')->middleware('permission:stock-transaction.update-status');
            Route::delete('/', [StockTransactionsController::class, 'destroy'])->name('destroy')->middleware('permission:stock-transaction.destroy');
            Route::post('restore', [StockTransactionsController::class, 'restore'])->name('restore')->middleware('permission:stock-transaction.restore');
            Route::delete('force-delete', [StockTransactionsController::class, 'delete'])->name('delete')->middleware('permission:stock-transaction.delete');
        });
    });

    // Route::resource('division', DivisionsController::class);
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

    // Route::resource('district', DistrictsController::class);
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

    // Route::resource('thana', ThanasController::class);
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
