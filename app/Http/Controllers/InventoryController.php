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
  public function createItemMeta(Request $request) {
    $company_id = $request->requestFrom->company_id;

    $name = $request->input('name');
    $sku = $request->input('sku');
    $upc = $request->input('upc');
    $brand = $request->input('brand');
    $category = $request->input('category');
    $price = $request->input('price');

    DB::beginTransaction();

    try {
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

    $data = $location->stocks->load('item_meta');

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

      if ($item_stock_data->quantity < $quantity) {
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
        'error_message' => $exception->getMessage() . $exception->getLine(),
      ]);
    }
  }

  public function getItem(Request $request, $id) {
    $company_id = $request->requestFrom->company_id;

    $item = ItemMeta::of($company_id)->with([
      'stocks', 'stocks.stock_location', 'brand', 'category', 'sources', 'sources.supplier'
    ])->where('id', $id)->first();
    if (!$item) {
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ]);
    }

    return ResponseHelper::success([
      'data' => $item,
    ]);
  }

  public function editItem(Request $request, $id) {
    $company_id = $request->requestFrom->company_id;
    $name = $request->input('name');
    $upc = $request->input('upc');
    $sku = $request->input('sku');
    $brand_id = $request->input('brand');
    $category_id = $request->input('category');

    $item = ItemMeta::of($company_id)->where('id', $id)->first();
    if (!$item) {
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ]);
    }

    $unique_field = [
      'name' => $name,
      'stock_keeping_unit' => $sku,
      'universal_product_code' => $upc,
    ];
    foreach ($unique_field as $field => $value) {
      if (ItemMeta::of($company_id)->where($field, $value)->first() && $item[$field] != $value) {
        return ResponseHelper::rejected([
          'message' => 'FAILED_RECORD_EXISTED',
        ]);
      }
    }

    try {
      DB::beginTransaction();

      $item->update([
        'name' => $name,
        'stock_keeping_unit' => $sku,
        'universal_product_code' => $upc,
        'brand' => $brand_id,
        'category' => $category_id,
      ]);

      DB::commit();
      return ResponseHelper::success();
    }
    catch (\Exception $exception) {
      DB::rollBack();
      return ResponseHelper::error([
        'error_message' => $exception->getMessage() . $exception->getLine(),
      ]);
    }
  }

  public function stockSplit(Request $request) {
    $company_id = $request->requestFrom->company_id;
    $item_stock_data_id = $request->input('item_stock_data');
    $quantity = $request->input('quantity');
    $location_id = $request->input('output_location');
    $output_item_id = $request->input('output_item');
    $output_quantity = $request->input('output_quantity');

    $item_stock = ItemStockData::where('id', $item_stock_data_id)->first();
    if (!$item_stock) {
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

    $output_item = ItemMeta::of($company_id)->where('id', $output_item_id)->first();
    if (!$output_item) {
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ]);
    }

    if ($item_stock->quantity < $quantity) {
      return ResponseHelper::rejected([
        'message' => 'FAILED_INSUFFICIENT_STOCK',
      ]);
    }

    try {
      DB::beginTransaction();

      $item_stock->stock_location->stockOut($item_stock->item_meta_id, $quantity);
      $location->stockIn($output_item->id, $quantity * $output_quantity);

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
}
