<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ItemMeta;
use App\Models\ItemSaleData;
use App\Models\ItemStockData;
use App\Models\StockLocation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {

    }

    public function createItemMeta(Request $request) {

      $name = $request->input('name');
      $sku = $request->input('sku');
      $upc = $request->input('upc');
      $brand = $request->input('brand');
      $category = $request->input('category');
      $price = $request->input('price');

      DB::beginTransaction();

      try {
        $company_id = $request->requestFrom->company_id;

        $itemMeta = ItemMeta::create([
          'name' => $name,
          'company_id' => $company_id,
          'stock_keeping_unit' => $sku,
          'universal_product_code' => $upc,
          'brand' => $brand,
          'category' => $category,
        ]);

        if ($price) {
          ItemSaleData::create([
            'item_meta_id' => $itemMeta->id,
            'price' => $price,
            'started_at' => Carbon::now(),
          ]);
        }

        DB::commit();

        return ResponseHelper::success();
      }
      catch (\Exception $e) {
        DB::rollBack();
        return ResponseHelper::error([
          'error_message' => $e->getMessage(),
        ]);
      }
    }

    public function createBrand(Request $request) {
      $company_id = $request->requestFrom->company_id;
      $name = $request->input('name');

      if (Brand::where('company_id', $company_id)->where('name', 'name')->first()) {
        return ResponseHelper::rejected([
          'message' => 'FAILED_RECORD_EXISTED',
        ]);
      }

      $brand = Brand::create([
        'company_id' => $company_id,
        'name' => $name,
      ]);

      return ResponseHelper::success([
        'data' => $brand
      ]);
    }

    public function getBrand(Request $request) {
      $company_id = $request->requestFrom->company_id;

      return ResponseHelper::success([
        'data' => Brand::where('company_id', $company_id)->get(),
      ]);
    }

    public function createCategory(Request $request) {
      $company_id = $request->requestFrom->company_id;
      $name = $request->input('name');

      if (Category::where('company_id', $company_id)->where('name', 'name')->first()) {
        return ResponseHelper::rejected([
          'message' => 'FAILED_RECORD_EXISTED',
        ]);
      }

      $category = Category::create([
        'company_id' => $company_id,
        'name' => $name,
      ]);

      return ResponseHelper::success([
        'data' => $category,
      ]);
    }

    public function getCategory(Request $request) {
      $company_id = $request->requestFrom->company_id;

      return ResponseHelper::success([
        'data' => Category::where('company_id', $company_id)->get(),
      ]);
    }

    public function createItemLocation(Request $request) {
      $company_id = $request->requestFrom->company_id;

      $name = $request->input('name');
      $parent = $request->input('parent');

      DB::beginTransaction();

      try {
        if (StockLocation::where('company_id', $company_id)->where('name', $name)->first()) {
          return ResponseHelper::rejected([
            'message' => 'FAILED_RECORD_EXISTED',
          ]);
        }

        StockLocation::create([
          'company_id' => $company_id,
          'parent_id' => $parent,
          'name' => $name,
        ]);

        DB::commit();

        return ResponseHelper::success();
      }
      catch (\Exception $e) {
        DB::rollBack();
        return ResponseHelper::error([
          'error_message' => $e->getMessage(),
        ]);
      }
    }

    public function getItems(Request $request) {
      $company_id = $request->requestFrom->company_id;

      $records = ItemMeta::where('company_id', $company_id)->get();

      foreach ($records as $record) {
        $record['stock_count'] = $record->stocks->sum(function ($stock) {
          return $stock->quantity;
        });
      }

      return ResponseHelper::success([
        'data' => $records,
      ]);
    }

  protected function recursiveGetChildren($locations) {
    foreach ($locations as $location) {
      $children = $location->children;
      if ($children->count() > 0) {
        $location->children = $this->recursiveGetChildren($children);
      } else {
        $location->children = null;
      }
    }

    $location->load('stocks');
    $location->load('stocks.item_meta');
    return $locations;
  }

  public function getLocations(Request $request) {
    $company_id = $request->requestFrom->company_id;

    $records = StockLocation::with('parent')
      ->where('company_id', $company_id)
      ->get();

    return ResponseHelper::success([
      'data' => $records,
    ]);
  }

  public function getInventory(Request $request) {
    $company_id = $request->requestFrom->company_id;

    $records = StockLocation::with(['children', 'stocks', 'stocks.item_meta'])
      ->where('company_id', $company_id)
      ->whereNull('parent_id')
      ->get();

    $records = $this->recursiveGetChildren($records);

    return ResponseHelper::success([
      'data' => $records,
    ]);
  }

  public function stockIn(Request $request) {
    $company_id = $request->requestFrom->company_id;
    $item_meta_id = $request->input('product');
    $location_id = $request->input('location');
    $quantity = $request->input('quantity');

    try {
      DB::beginTransaction();

      $item_meta = ItemMeta::of($company_id)->where('id', $item_meta_id)->first();
      if (!$item_meta) {
        return ResponseHelper::rejected([
          'message' => 'FAILED_RECORD_NOT_FOUND',
        ]);
      }

      $location = StockLocation::of($company_id)->where('id', $location_id)->first();
      if (!$location) {
        return ResponseHelper::rejected([
          'message' => 'FAILED_RECORD_NOT_FOUND',
        ]);
      }

      $item_stock_data = ItemStockData::where('location_id', $location_id)
        ->where('item_meta_id', $item_meta_id)
        ->first();

      if ($item_stock_data) {
        $item_stock_data->update([
          'quantity' => $item_stock_data->quantity + $quantity,
        ]);
      }
      else {
        ItemStockData::create([
          'location_id' => $location_id,
          'item_meta_id' => $item_meta_id,
          'quantity' => $quantity,
        ]);
      }

      DB::commit();
      return ResponseHelper::success();
    }
    catch (\Exception $exception) {
      DB::rollBack();
      return ResponseHelper::error([
        'error_message' => $exception->getMessage(),
      ]);
    }
  }

  public function getStocksOnLocation(Request $request, $location_id) {
    $company_id = $request->requestFrom->company_id;

    $location = StockLocation::of($company_id)->where('id', $location_id)->first();
    if (!$location) {
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ]);
    }

    $data = $location->stocks;

    return ResponseHelper::success([
      'data' => $data,
    ]);
  }

  public function stockTransfer(Request $request) {
    $company_id = $request->requestFrom->company_id;
    $item_stock_data_id = $request->input('item_stock_data');
    $new_location_id = $request->input('new_location');
    $quantity = $request->input('quantity');

    try {
      DB::beginTransaction();

      $item_stock_data = ItemStockData::where('id', $item_stock_data_id)->first();
      if (!$item_stock_data) {
        return ResponseHelper::rejected([
          'message' => 'FAILED_RECORD_NOT_FOUND',
        ]);
      }

      if ($item_stock_data < $quantity) {
        return ResponseHelper::rejected([
          'message' => 'FAILED_INSUFFICIENT_STOCK',
        ]);
      }

      $new_location = StockLocation::of($company_id)->where('id', $new_location_id)->first();
      if (!$new_location) {
        return ResponseHelper::rejected([
          'message' => 'FAILED_RECORD_NOT_FOUND',
        ]);
      }

      $new_item_stock_data = ItemStockData::where('location_id', $new_location_id)
        ->where('item_meta_id', $item_stock_data->item_meta_id)
        ->first();

      if ($new_item_stock_data) {
        $new_item_stock_data->update([
          'quantity' => $new_item_stock_data->quantity + $quantity,
        ]);
      }
      else {
        ItemStockData::create([
          'location_id' => $new_location_id,
          'item_meta_id' => $item_stock_data->item_meta_id,
          'quantity' => $quantity,
        ]);
      }

      $item_stock_data->update([
        'quantity' => $item_stock_data->quantity - $quantity,
      ]);

      DB::commit();
      return ResponseHelper::success();
    }
    catch (\Exception $exception) {
      DB::rollBack();
      return ResponseHelper::error([
        'error_message' => $exception->getMessage(),
      ]);
    }
  }
}
