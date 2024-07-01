<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\Checkout;
use App\Models\CheckoutItem;
use App\Models\Customer;
use App\Models\ItemMeta;
use App\Models\StockLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {

    }

    public function checkout(Request $request) {
      $company_id = $request->requestFrom->company_id;

      $request_data = $request->validate([
        'customer' => ['nullable', 'int', 'exists:App\Models\Customer,id'],
        'items' => ['required', 'array', 'min:1'],
      ]);

      $customer_id = $request_data['customer'];
      $customer = $customer_id
        ? Customer::of($company_id)->where('id', $customer_id)->first()
        : null;

      $items_data = $request_data['items'];
      $items = array();

      foreach ($items_data as $item_data) {
        $item = ItemMeta::of($company_id)->where('id', $item_data['item_id'])->first();
        if (!$item)
          return ResponseHelper::rejected([
            'message' => 'FAILED_RECORD_NOT_FOUND',
          ]);

        $quantity = $item_data['quantity'];
        $price = $item->sale_data ? $item->sale_data->price : 0;

        $items[] = [
          'item' => $item,
          'location' => $item->sale_data->default_stock_out_location_id ?? StockLocation::of($company_id)->first()->id,
          'quantity' => $quantity,
          'price' => $price,
        ];
      }
      $items = collect($items);

      try {
        DB::beginTransaction();

        $checkout = Checkout::create([
          'company_id' => $company_id,
          'customer_id' => $customer_id,
          'user_id' => $request->requestFrom->id,
          'amount' => $items->map(function ($value) {
            return $value['quantity'] * $value['price'];
          })->sum(),
          'payment_method' => '',
          'reference_code' => 'C-' . Carbon::now()->format('Ymd') . '-' . Str::random(10),
        ]);

        foreach ($items as $item) {
          $checkout_item = CheckoutItem::create([
            'checkout_id' => $checkout->id,
            'item_sale_data_id' => $item['item']->sale_data->id,
            'quantity' => $item['quantity'],
          ]);

          $location = StockLocation::of($company_id)->where('id', $item['location'])->first();
          if (!$location)
            return ResponseHelper::rejected([
              'message' => 'FAILED_RECORD_NOT_FOUND',
            ]);
          $location->stockOut($request->requestFrom, $item['item']->id, $item['quantity'], $checkout_item->id);
        }

        $customer?->update([
          'points' => $customer->points + floor($checkout->amount),
        ]);


        DB::commit();
        return ResponseHelper::success();
      }
      catch (\Exception $e) {
        DB::rollBack();
        return ResponseHelper::error([
          'error_message' => $e->getMessage() . $e->getLine(),
        ]);
      }
    }
}
