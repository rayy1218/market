<?php

namespace App\Mail;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
      $this->order = $order;
    }

    public function build() {
      return $this->view('emails.order')
        ->with([
          'order_date' => Carbon::parse($this->order->created_at),
          'order_items' => $this->order->order_items,
          'company_name' => $this->order->user->company->company_name,
          'remark' => $this->order->remark,
        ])
        ->subject(subject: $this->order->user->company->company_name. ' sent an order');
    }
}
