<!DOCTYPE html>
<html>
<head>
  <title>New Order</title>
</head>
<body>
<h1>{{ $company_name }} has sent an Order</h1>
<p>Date: {{ $order_date }}</p>
@if(isset($remark))
  <p>Remark: {{ $remark }}</p>
@endif
<p>Order Items:</p>
@foreach($order_items as $order_item)
  <p>{{ $order_item->item_source->item_meta->name }} [{{ $order_item->item_source->item_meta->universal_product_code }}] x {{ $order_item->quantity }} </p>
@endforeach
</body>
</html>
