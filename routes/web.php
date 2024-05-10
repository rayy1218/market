<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ManagerialController;
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
    Route::get('/entries', 'getTodayEntries');
    Route::get('/all-entries', 'getAllEntries');
    Route::post('/entry', 'createEntry');
  });

  Route::prefix('/supply')->controller(SupplyChainController::class)->group(function () {
    Route::get('/orders');
    Route::get('/order/{id}');
    Route::post('/order');
    Route::put('/order/{id}');

    Route::get('/suppliers');
    Route::get('/supplier/{id}');
    Route::post('/supplier');
    Route::put('/supplier/{id}');
    Route::delete('/supplier/{id}');
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
    Route::get('/entries');
    Route::get('/entry/{id}');
    Route::post('/entry');
    Route::put('/entry/{id}');
    Route::delete('/entry/{id}');
  });

  Route::prefix('/checkout')->controller(CheckoutController::class)->group(function () {
    Route::post('/initial');
  });
});

