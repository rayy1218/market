<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\Checkout;
use App\Models\CheckoutItem;
use App\Models\ItemMeta;
use App\Models\ItemStockData;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShiftRecord;
use App\Models\StockLocation;
use App\Models\StockTransactionLog;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportingController extends Controller
{
  public function underStock(Request $request) {
    $company_id = $request->requestFrom->company_id;

    $items = ItemMeta::of($company_id)->with(['supply_data'])->get();
    foreach ($items as $item) {
      $item->totalStock = $item->stocks->map(function (ItemStockData $itemStockData) {
        return $itemStockData->quantity;
      })->sum();
      $item->setHidden(['stocks']);
    }

    $items = $items->filter(function (ItemMeta $item) {
      return $item->supply_data
        ? $item->totalStock < $item->supply_data->restock_point
        : $item->totalStock == 0;
    });

    return ResponseHelper::success([
      'data' => $items->values(),
    ]);
  }

  public function shiftToday(Request $request) {
    $user_id = $request->requestFrom->id;
    $timestamp = ShiftRecord::of($user_id)
      ->whereBetween('created_at', [
          Carbon::now('Asia/Kuala_Lumpur')->startOfDay(),
          Carbon::now('Asia/Kuala_Lumpur')->endOfDay()]
      )
      ->orderByDesc('created_at')
      ->first();

    return ResponseHelper::success([
      'data' => $timestamp == null ? 'not record' : ShiftRecord::currentStatus($user_id),
    ]);
  }

  public function deliveringOrder(Request $request) {
    $company_id = $request->requestFrom->company_id;

    $orders = Order::of($company_id)
      ->with('supplier')
      ->where('status', Order::STATUS_DELIVERING)
      ->get();

    return ResponseHelper::success([
      'data' => $orders,
    ]);
  }

  public function inventoryStockFlowSummary(Request $request) {
    $company_id = $request->requestFrom->company_id;
    $date = $request->input('date');

    if ($date) {
      $startDate = Carbon::parse($date)->startOfMonth();
      $endDate = Carbon::parse($date)->endOfMonth();
    }
    else {
      $startDate = Carbon::now()->startOfMonth();
      $endDate = Carbon::now()->endOfMonth();
    }

    $checkouts = Checkout::of($company_id)
      ->whereBetween('created_at', [$startDate, $endDate])
      ->get();

    $orders = Order::of($company_id)
      ->whereBetween('created_at', [$startDate, $endDate])
      ->get();

    $stock_in_count = $orders->map(function (Order $order) {
      return $order->order_items->map(function (OrderItem $item) {
        return $item->quantity;
      })->sum();
    })->sum();

    $stock_out_count = $checkouts->map(function (Checkout $checkout) {
      return $checkout->items->map(function (CheckoutItem $item) {
        return $item->quantity;
      })->sum();
    })->sum();

    return ResponseHelper::success([
      'data' => [
        'orderNum' => $orders->count(),
        'checkoutNum' => $checkouts->count(),
        'stockInNum' => $stock_in_count,
        'stockOutNum' => $stock_out_count,
      ],
    ]);
  }

  public function inventoryFinanceSummary(Request $request) {
    $company_id = $request->requestFrom->company_id;
    $date = $request->input('date');

    if ($date) {
      $startDate = Carbon::parse($date)->startOfMonth();
      $endDate = Carbon::parse($date)->endOfMonth();
    }
    else {
      $startDate = Carbon::now()->startOfMonth();
      $endDate = Carbon::now()->endOfMonth();
    }

    $checkouts = Checkout::of($company_id)
      ->whereBetween('created_at', [$startDate, $endDate])
      ->get();

    $orders = Order::of($company_id)
      ->whereBetween('created_at', [$startDate, $endDate])
      ->get();

    $income = $checkouts->map(function (Checkout $checkout) {
      return $checkout->items->reduce(function (CheckoutItem $item) {
        return ($item->sale_data ? $item->sale_data->price : 0) * $item->quantity;
      });
    })->sum();

    $cost = $orders->map(function (Order $order) {
      return $order->order_items->map(function (OrderItem $item) {
        return ($item->item_source ? $item->item_source->unit_price : 0) * $item->quantity;
      })->sum();
    })->sum();

    return ResponseHelper::success([
      'income' => $income,
      'cost' => $cost,
      'totalCheckout' => $checkouts->count(),
    ]);
  }

  public function mostProductiveItem(Request $request) {
    $company_id = $request->requestFrom->company_id;
    $date = $request->input('date');

    if ($date) {
      $startDate = Carbon::parse($date)->startOfMonth();
      $endDate = Carbon::parse($date)->endOfMonth();
    }
    else {
      $startDate = Carbon::now()->startOfMonth();
      $endDate = Carbon::now()->endOfMonth();
    }

    $items = CheckoutItem::with(['checkout', 'sale_data'])
      ->whereBetween('created_at', [$startDate, $endDate])
      ->get();

    $items = $items->groupBy('sale_data.item_meta_id');

    return ResponseHelper::success([
      'data' => $items,
    ]);
  }

  public function mostSalesItem(Request $request) {
    $company_id = $request->requestFrom->company_id;
    $date = $request->input('date');

    if ($date) {
      $startDate = Carbon::parse($date)->startOfMonth();
      $endDate = Carbon::parse($date)->endOfMonth();
    }
    else {
      $startDate = Carbon::now()->startOfMonth();
      $endDate = Carbon::now()->endOfMonth();
    }

    $top = CheckoutItem::with(['checkout'])
      ->where('checkout.company_id', $company_id)
      ->whereBetween('checkout.created_at', [$startDate, $endDate])
      ->select([
        'id', "SUM('quantity') AS total"
      ])
      ->groupBy('id')
      ->orderByDesc("SUM('quantity')")
      ->limit(10)
      ->get();

    $items = CheckoutItem::with(['sale_data', 'sale_data.item_meta'])
      ->whereIn('id', $top->pluck('id'))
      ->get();

    foreach ($items as $item) {
      $item->total = $top->where('id', $item->id)->first->total;
    }

    return ResponseHelper::success([
      'data' => $items,
    ]);
  }

  public function approachingUnderStock(Request $request) {
    $company_id = $request->requestFrom->company_id;
    $items = ItemMeta::of($company_id)
      ->whereHas('supply_data')
      ->get();

    $items->filter(function (ItemMeta $item) {
      $current_stock = $item->stocks->reduce(function (ItemStockData $stock) {
        return $stock->quantity;
      });

      $rate = $item->stockOutRate();

      return $current_stock / $rate['week'] < $item->supply_data->source->estimated_lead_time_day;
    });

    return ResponseHelper::success([
      'data' => $items,
    ]);
  }

  public function orderSummary(Request $request) {
    $company_id = $request->requestFrom->company_id;
    $date = $request->input('date');

    if ($date) {
      $startDate = Carbon::parse($date)->startOfMonth();
      $endDate = Carbon::parse($date)->endOfMonth();
    }
    else {
      $startDate = Carbon::now()->startOfMonth();
      $endDate = Carbon::now()->endOfMonth();
    }

    $orders = Order::of($company_id)
      ->with('supplier')
      ->whereBetween('created_at', [$startDate, $endDate])
      ->get();

    foreach ($orders as $order) {
      $order->cost = $order->order_items->map(function (OrderItem $item) {
        return ($item->item_source ? $item->item_source->unit_price : 0) * $item->quantity;
      })->sum();
    }

    $cost = $orders->map(function (Order $order) {
      return $order->cost;
    })->sum();

    $group_by_supplier = $orders->groupBy('supplier_id');
    $supplier_data = array();
    foreach ($group_by_supplier as $supplier => $supplier_orders) {
      $supplier_data[$supplier] = [
        'number' => $supplier_orders->count(),
        'capital' => $supplier_orders->map(function ($order) {
          return $order->cost;
        })->sum(),
      ];
    }

    $suppliers = Supplier::whereIn('id', array_keys($supplier_data))->get();
    foreach ($suppliers as $supplier) {
      $supplier->number = $supplier_data[$supplier->id]['number'];
      $supplier->capital = $supplier_data[$supplier->id]['capital'];
    }

    return ResponseHelper::success([
      'data' => [
        'cost' => $cost,
        'totalCreated' => $orders->count(),
        'totalCompleted' => $orders->where('status', Order::STATUS_RECEIVED)->count(),
        'supplier' => $suppliers,
      ]
    ]);
  }

  public function topSupplier(Request $request) {
    $company_id = $request->requestFrom->company_id;
    $date = $request->input('date');

    if ($date) {
      $startDate = Carbon::parse($date)->startOfMonth();
      $endDate = Carbon::parse($date)->endOfMonth();
    }
    else {
      $startDate = Carbon::now()->startOfMonth();
      $endDate = Carbon::now()->endOfMonth();
    }

    $suppliers = Supplier::of($company_id)->withCount((['orders' => function ($query) use ($startDate, $endDate) {
      $query->whereBetween('created_at', [$startDate, $endDate]);
    }]))->orderByDesc('orders_count')->limit(5)->get();

    return ResponseHelper::success([
      'data' => $suppliers,
    ]);
  }

  public function stockFlowLog(Request $request) {
    $company_id = $request->requestFrom->company_id;
    $date = $request->input('date');

    if ($date) {
      $startDate = Carbon::parse($date)->startOfMonth();
      $endDate = Carbon::parse($date)->endOfMonth();
    }
    else {
      $startDate = Carbon::now()->startOfMonth();
      $endDate = Carbon::now()->endOfMonth();
    }

    $logs = StockTransactionLog::of($company_id)
      ->with(['user', 'stockInItem', 'stockOutItem', 'stockInLocation', 'stockOutItem', 'stockOutLocation', 'checkoutItem', 'checkoutItem.sale_data.item_meta', 'checkoutItem.checkout'])
      ->whereBetween('created_at', [$startDate, $endDate])
      ->orderByDesc('created_at')
      ->get();

    return ResponseHelper::success([
      'data' => $logs
    ]);
  }

  public function itemStockOutRate() {

  }

  public function stockLocationSummary(Request $request) {
    $company_id = $request->requestFrom->company_id;
    $stockLocationIds = StockLocation::of($company_id)->get()->pluck('id');

    $invalid = ItemStockData::with(['stock_location', 'item_meta'])
      ->whereIn('location_id', $stockLocationIds)
      ->where('quantity', '<', 0)
      ->get();

    $await_restock = ItemStockData::with(['stock_location', 'item_meta'])
      ->whereIn('location_id', $stockLocationIds)
      ->where('quantity', 0)
      ->get();

    $stockLocations = StockLocation::of($company_id)
      ->whereIn('id', array_merge(array_unique($invalid->pluck('location_id')->toArray()), array_unique($await_restock->pluck('location_id')->toArray())))
      ->get();

    return ResponseHelper::success([
      'data' => [
        'invalid' => $invalid,
        'await_restock' => $await_restock,
        'locations' => $stockLocations,
      ]
    ]);
  }

  public function deliveringOrders(Request $request) {
    $company_id = $request->requestFrom->company_id;
    $orders = Order::of($company_id)
      ->with('supplier')
      ->where('status', Order::STATUS_DELIVERING)
      ->get();

    return ResponseHelper::success([
      'data' => $orders,
    ]);
  }
}
