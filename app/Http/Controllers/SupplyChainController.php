<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Mail\OrderMail;
use App\Models\Address;
use App\Models\ItemMeta;
use App\Models\ItemSource;
use App\Models\ItemSupplyData;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockLocation;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SupplyChainController extends Controller
{
  public function createSupplier(Request $request) {
    $company_id = $request->requestFrom->company_id;

    $name = $request->input('name');
    $phone = $request->input('phone_number');
    $email = $request->input('email');

    try {
      DB::beginTransaction();

      if (Supplier::of($company_id)->where('name', $name)->first())
        return ResponseHelper::rejected([
          'message' => 'FAILED_RECORD_EXISTED',
        ]);

      $address = Address::fromRequest($request);

      Supplier::create([
        'company_id' => $company_id,
        'address_id' => $address->id,
        'name' => $name,
        'phone_number' => $phone,
        'email' => $email,
      ]);

      DB::commit();
      return ResponseHelper::success();
    }
    catch (Exception $e) {
      DB::rollBack();
      return ResponseHelper::error([
        'error_message' => $e->getMessage(),
      ]);
    }
  }

  public function updateSupplier(Request $request, $id) {
    $company_id = $request->requestFrom->company_id;

    $name = $request->input('name');
    $phone = $request->input('phone_number');
    $email = $request->input('email');

    try {
      DB::beginTransaction();

      $supplier = Supplier::of($company_id)->where('id', $id)->first();
      if (!$supplier)
        return ResponseHelper::rejected([
          'message' => 'FAILED_RECORD_NOT_FOUND',
        ]);

      $supplier->update([
        'name' => $name,
        'phone_number' => $phone,
        'email' => $email,
      ]);

      $supplier->address->update([
        'line1' => $request->input('line1'),
        'line2' => $request->input('line2'),
        'city' => $request->input('city'),
        'state' => $request->input('state'),
        'zipcode' => $request->input('zipcode'),
        'country' => $request->input('country'),
      ]);

      DB::commit();
      return ResponseHelper::success();
    }
    catch (Exception $e) {
      DB::rollBack();
      return ResponseHelper::error([
        'error_message' => $e->getMessage(),
      ]);
    }
  }

  public function getSuppliers(Request $request) {
    $company_id = $request->requestFrom->company_id;

    $suppliers = Supplier::of($company_id)->with(['address', 'sources', 'sources.item_meta'])->get();

    return ResponseHelper::success([
      'data' => $suppliers,
    ]);
  }

  public function getSupplier(Request $request, $id) {
    $company_id = $request->requestFrom->company_id;

    $supplier = Supplier::of($company_id)->with(['address', 'orders', 'sources', 'sources.item_meta'])->where('id', $id)->first();
    if (!$supplier)
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ]);

    return ResponseHelper::success([
      'data' => $supplier,
    ]);
  }

  public function createItemSource(Request $request) {
    $company_id = $request->requestFrom->company_id;
    $supplier_id = $request->input('supplier');
    $item_meta_id = $request->input('item_meta');
    $unit_price = $request->input('unit_price');
    $min_order_quantity = $request->input('min_order_quantity');
    $estimated_lead_time = $request->input('estimated_lead_time');

    try {
      DB::beginTransaction();

      $supplier = Supplier::of($company_id)->where('id', $supplier_id)->first();
      $item_meta = ItemMeta::of($company_id)->where('id', $item_meta_id)->first();

      if (!$supplier)
        return ResponseHelper::rejected([
          'message' => 'FAILED_RECORD_NOT_FOUND',
        ]);

      if (!$item_meta)
        return ResponseHelper::rejected([
          'message' => 'FAILED_RECORD_NOT_FOUND',
        ]);

      if ($item_meta->sources->where('supplier_id', $supplier->id)->count() > 0)
        return ResponseHelper::rejected([
          'message' => 'FAILED_RECORD_EXISTED',
        ]);

      ItemSource::create([
        'supplier_id' => $supplier->id,
        'item_meta_id' => $item_meta->id,
        'unit_price' => $unit_price,
        'min_order_quantity' => $min_order_quantity,
        'estimated_lead_time_day' => $estimated_lead_time,
      ]);

      DB::commit();

      return ResponseHelper::success();
    }
    catch (Exception $e) {
      DB::rollBack();
      return ResponseHelper::error([
        'error_message' => $e->getMessage(),
      ]);
    }
  }

  public function updateItemSource(Request $request, $id) {
    $unit_price = $request->input('unit_price');
    $min_order_quantity = $request->input('min_order_quantity');
    $estimated_lead_time = $request->input('estimated_lead_time');

    try {
      DB::beginTransaction();
      $item_source = ItemSource::where('id', $id)->first();

      if (!$item_source)
        return ResponseHelper::rejected([
          'message' => 'FAILED_RECORD_NOT_FOUND',
        ]);

      $item_source->update([
        'unit_price' => $unit_price,
        'min_order_quantity' => $min_order_quantity,
        'estimated_lead_time_day' => $estimated_lead_time,
      ]);

      DB::commit();

      return ResponseHelper::success();
    }
    catch (Exception $e) {
      DB::rollBack();
      return ResponseHelper::error([
        'error_message' => $e->getMessage(),
      ]);
    }
  }

  public function createOrder(Request $request) {
    $company_id = $request->requestFrom->company_id;
    $supplier_id = $request->input('supplier');
    $remark = $request->input('remark', '');
    $send_mail = $request->input('send_mail', false);
    $order_items = $request->input('order_items', []);

    try {
      DB::beginTransaction();

      $supplier = Supplier::of($company_id)->where('id', $supplier_id)->first();
      if (!$supplier)
        return ResponseHelper::rejected([
          'message' => 'FAILED_RECORD_NOT_FOUND',
        ]);

      $order = Order::create([
        'supplier_id' => $supplier->id,
        'user_id' => $request->requestFrom->id,
        'status' => Order::STATUS_PENDING,
        'remark' => $remark ?? '',
        'reference_code' => 'P-' . Carbon::now()->format('Ymd') . '-' . Str::random(10)
      ]);

      foreach ($order_items as $order_item) {
        $item_source = ItemSource::where('id', $order_item['id'])->first();
        if (!$item_source)
          return ResponseHelper::rejected([
            'message' => 'FAILED_RECORD_NOT_FOUND',
          ]);

        OrderItem::create([
          'item_source_id' => $item_source->id,
          'order_id' => $order->id,
          'quantity' => $order_item['quantity'],
        ]);
      }

      if ($send_mail) {
        Mail::to($supplier->email)->send(new OrderMail($order));
      }

      DB::commit();
      return ResponseHelper::success();
    }
    catch (Exception $e) {
      DB::rollBack();
      return ResponseHelper::error([
        'error_message' => $e->getMessage(),
      ]);
    }
  }

  public function getOrders(Request $request) {
    $company_id = $request->requestFrom->company_id;

    $orders = Order::with('supplier')
      ->whereHas('supplier', function($query) use ($company_id) {
        $query->where('company_id', $company_id);
      })
      ->get();

    return ResponseHelper::success([
      'data' => $orders,
    ]);
  }

  public function getOrder(Request $request, $id) {
    $company_id = $request->requestFrom->company_id;
    $order = Order::of($company_id)
      ->with([
        'supplier', 'supplier.address', 'order_items', 'order_items.item_source', 'order_items.item_source.item_meta',
        'order_items.item_source.item_meta.brand', 'order_items.item_source.item_meta.category'
      ])
      ->where('id', $id)
      ->first();

    if (!$order)
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ]);

    return ResponseHelper::success([
      'data' => $order,
    ]);
  }

  public function updateOrder(Request $request, $id) {
    $company_id = $request->requestFrom->company_id;
    $order = Order::of($company_id)
      ->where('id', $id)
      ->first();

    if (!$order)
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ]);

    $order->update([
      'status' => $request->input('status'),
    ]);

    return ResponseHelper::success([
      'new_status' => $order->status,
      'timestamp' => $order->updated_at,
    ]);
  }

  public function createItemSupply(Request $request, $id) {
    $company_id = $request->requestFrom->company_id;

    $request_data = $request->validate([
      'item_source_id' => ['required', 'int'],
      'on_low_stock_action' => ['required', 'string', 'in:email,notify,show,hidden'],
      'default_restock_quantity' => ['required', 'int'],
      'restock_point' => ['required', 'int'],
    ]);

    $item = ItemMeta::of($company_id)->where('id', $id)->first();
    if (!$item)
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ]);

    $item_source = ItemSource::where('id', $request->input('item_source_id'));
    if (!$item_source)
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ]);


    try {
      DB::beginTransaction();

      $supply_data = $item->supply_data;
      if ($supply_data) {
        $supply_data->update([
          'item_source_id' => $request_data['item_source_id'],
          'on_low_stock_action' => $request_data['on_low_stock_action'],
          'default_restock_quantity' => $request_data['default_restock_quantity'],
          'restock_point' => $request_data['restock_point'],
        ]);
      }
      else {
        ItemSupplyData::create([
          'item_meta_id' => $item->id,
          'item_source_id' => $request_data['item_source_id'],
          'on_low_stock_action' => $request_data['on_low_stock_action'],
          'default_restock_quantity' => $request_data['default_restock_quantity'],
          'restock_point' => $request_data['restock_point'],
        ]);
      }

      DB::commit();
      return ResponseHelper::success();
    }
    catch (Exception $e) {
      DB::rollBack();
      return ResponseHelper::error([
        'error_message' => $e->getMessage(),
      ]);
    }
  }

  public function stockInByOrder(Request $request, $id) {
    $company_id = $request->requestFrom->company_id;
    $location_id = $request->input('location');

    $location = StockLocation::of($company_id)->find($location_id);
    if (!$location)
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ]);

    $order = Order::of($company_id)->find($id);
    if (!$order)
      return ResponseHelper::rejected([
        'message' => 'FAILED_RECORD_NOT_FOUND',
      ]);

    try {
      DB::beginTransaction();

      foreach ($order->order_items as $item) {
        $location->stockIn($request->requestFrom, $item->item_source->item_meta_id, $item->quantity, $item->id);
      }

      DB::commit();
      return ResponseHelper::success();
    }
    catch (Exception $e) {
      DB::rollBack();
      return ResponseHelper::error([
        'error_message' => $e->getMessage(),
      ]);
    }
  }
}
