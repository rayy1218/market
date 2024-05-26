<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\Checkout;
use App\Models\CheckoutItem;
use App\Models\Customer;
use App\Models\ItemMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {

    }

    public function checkout(Request $request) {
      $company_id = $request->requestFrom->company_id;

      $request_data = $request->validate([
        'customer_id' => ['nullable', 'int', 'exists:App\Models\Customer,id'],
        'items' => ['required', 'array', 'min:1'],
        'method' => ['required', 'string']
      ]);

      $customer_id = $request_data['customer_id'];
      $customer = !$customer_id
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
          'amount' => $items->reduce(function ($value) {
            return $value['quantity'] * $value['price'];
          }),
          'payment_method' => $request_data['method'],
        ]);

        foreach ($items as $item) {
          CheckoutItem::create([
            'checkout_id' => $checkout->id,
            'item_sale_data_id' => $item['item']->sale_data(),
            'quantity' => $item['quantity'],
          ]);
        }

        $customer->update([
          'points' => $customer->points + floor($checkout->amount),
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
