<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ManagerialController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SupplyChainController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('', function () {
  return 'OK';
});

Route::prefix('/api')->middleware('authentication')->group(function () {
  Route::get('/healthcheck', function () {
    return 'OK';
  });

  Route::get('/healthcheck-db', function () {
    return env('DB_HOST');
  });

  Route::prefix('/auth')->controller(AuthenticationController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::get('/get-access-right', 'getAccessRight');
  });

  Route::prefix('/managerial')->controller(ManagerialController::class)->group(function () {
    Route::get('/groups/all', 'getAllGroups');
    Route::get('/groups', 'getGroupsPagination');
    Route::get('/group/{id}', 'getGroupDetail');
    Route::post('/group', 'createGroup');
    Route::put('/group/{id}', 'updateGroup');
    Route::delete('group/{id}', 'deleteGroup');

    Route::get('/employees', 'getEmployeesPagination');
    Route::get('/employee/{id}', 'getEmployeeDetail');
    Route::post('/employee', 'createEmployee');
    Route::delete('employee/{id}', 'deleteEmployee');

    Route::get('/access-rights-dropdown', 'getAllAccessRight');
    Route::get('/group-dropdown', 'getGroupDropdown');
    Route::get('/member-list', 'getChangeMemberList');
  });

  Route::prefix('/schedule')->controller(ScheduleController::class)->group(function () {
    Route::get('/entries', 'getTodayTimestamps');
    Route::get('/all-entries', 'getAllEntries');
    Route::post('/entry', 'addTimestamp');
  });

  Route::prefix('/supply')->controller(SupplyChainController::class)->group(function () {
    Route::get('/orders', 'getOrders');
    Route::get('/order/{id}', 'getOrder');
    Route::post('/order', 'createOrder');
    Route::put('/order/{id}', 'updateOrder');

    Route::get('/suppliers', 'getSuppliers');
    Route::get('/supplier/{id}', 'getSupplier');
    Route::post('/supplier', 'createSupplier');
    Route::put('/supplier/{id}', 'updateSupplier');
    Route::delete('/supplier/{id}');

    Route::post('/source', 'createItemSource');
    Route::put('/source/{id}', 'updateItemSource');

    Route::post('/item/{id}', 'createItemSupply');
    Route::post('/order/stock-in/{id}', 'stockInByOrder');
  });

  Route::prefix('/inventory')->controller(InventoryController::class)->group(function () {
    Route::get('', 'getInventory');

    Route::get('/items', 'getItems');
    Route::get('/item/{id}', 'getItem');
    Route::post('/item', 'createItemMeta');
    Route::put('/item/{id}', 'editItem');
    Route::delete('/item/{id}');

    Route::get('/brands', 'getBrand');
    Route::get('/categories', 'getCategory');
    Route::post('/brand', 'createBrand');
    Route::post('category', 'createCategory');

    Route::get('/locations', 'getLocations');
    Route::get('/location/{id}');
    Route::get('location/{location_id}/items', 'getStocksOnLocation');
    Route::post('/location', 'createItemLocation');
    Route::put('/location/{id}');
    Route::delete('/location/{id}');

    Route::post('/stock-in', 'stockIn');
    Route::post('/stock-split', 'stockSplit');
    Route::post('/stock-transfer', 'stockTransfer');
  });

  Route::prefix('/customer')->controller(CustomerController::class)->group(function () {
    Route::get('/entries', 'getCustomers');
    Route::get('/entry/{id}', 'getCustomer');
    Route::post('/entry', 'createCustomer');
    Route::put('/entry/{id}', 'updateCustomer');
    Route::delete('/entry/{id}', 'deleteCustomer');
  });

  Route::prefix('/checkout')->controller(CheckoutController::class)->group(function () {
    Route::post('/create', 'checkout');
  });

  Route::prefix('/report')->controller(ReportingController::class)->group(function () {
    Route::post('/under-stock', 'underStock');
    Route::post('/shift-today', 'shiftToday');
    Route::post('/delivering-order', 'deliveringOrder');
    Route::post('/inventory-stock-flow-summary', 'inventoryStockFlowSummary');
    Route::post('/inventory-finance-summary', 'inventoryFinanceSummary');
    Route::post('/most-sale-items', 'mostSalesItem');
    Route::post('/most-productive-items', 'mostProductiveItem');
    Route::post('/approaching-under-stock', 'approachingUnderStock');
    Route::post('/top-supplier', 'topSupplier');
    Route::post('/order-summary', 'orderSummary');
    Route::post('/stock-flow-log', 'stockFlowLog');
    Route::post('/stock-location-summary', 'stockLocationSummary');
    Route::post('/delivering-orders', 'deliveringOrders');
    Route::post('/shift-summary', 'shiftSummary');
  });
});

