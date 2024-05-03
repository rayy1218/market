<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\ItemMeta;
use App\Models\ItemSaleData;
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
        $itemMeta = ItemMeta::create([
          'name' => $name,
          'stock_keeping_unit' => $sku,
          'universal_product_code' => $upc,
          'brand' => $brand,
          'category' => $category,
        ]);

        $itemSaleData = ItemSaleData::create([
          'item_meta_id' => $itemMeta->id,
          'price' => $price,
          'started_at' => Carbon::now(),
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
}
