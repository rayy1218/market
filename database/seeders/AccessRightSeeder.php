<?php

namespace Database\Seeders;

use App\Models\AccessRight;
use Illuminate\Database\Seeder;

class AccessRightSeeder extends Seeder
{
    public function run(): void
    {
      $access_rights = [
        ['name' => 'managerial setting', 'label' => 'Access Company Setting'],
        ['name' => 'managerial employee view', 'label' => 'View Employee'],
        ['name' => 'managerial employee edit', 'label' => 'Edit Employee'],
        ['name' => 'managerial group view', 'label' => 'View Group'],
        ['name' => 'managerial group edit', 'label' => 'Edit Group'],
        ['name' => 'managerial group managerial', 'label' => 'Edit Managerial Access Right'],
        ['name' => 'shift view', 'label' => 'View Employee Shift Record'],
        ['name' => 'shift edit', 'label' => 'Record Shift'],
        ['name' => 'inventory view', 'label' => 'View Item Database & Inventory'],
        ['name' => 'inventory item edit', 'label' => 'Edit Item Database'],
        ['name' => 'inventory in', 'label' => 'Stock In'],
        ['name' => 'inventory split', 'label' => 'Stock Splitting'],
        ['name' => 'inventory transfer', 'label' => 'Stock Transfer'],
        ['name' => 'supply supplier view', 'label' => 'View Supplier List'],
        ['name' => 'supply supplier edit', 'label' => 'Edit Supplier List'],
        ['name' => 'supply source edit', 'label' => 'Edit Item Source List'],
        ['name' => 'supply source assign', 'label' => 'Assign Item Supply Source'],
        ['name' => 'supply restock edit', 'label' => 'Edit Auto Restock'],
        ['name' => 'supply order view', 'label' => 'View Order List'],
        ['name' => 'supply order create', 'label' => 'Create Order'],
        ['name' => 'supply order update', 'label' => 'Update Order Status'],
        ['name' => 'supply order cancel', 'label' => 'Cancel Order'],
        ['name' => 'sales price view', 'label' => 'View Item Price List'],
        ['name' => 'sales price edit', 'label' => 'Edit Item Price List'],
        ['name' => 'sales modifier edit', 'label' => 'Edit Item Price Modifier'],
        ['name' => 'sales customer view', 'label' => 'View Customer List'],
        ['name' => 'sales customer edit', 'label' => 'Edit Customer List'],
        ['name' => 'sales checkout', 'label' => 'Checkout'],
        ['name' => 'report glance', 'label' => 'View Task at a Glance'],
        ['name' => 'report sales', 'label' => 'Sales Report'],
        ['name' => 'report inventory', 'label' => 'Inventory Report'],
        ['name' => 'report shift', 'label' => 'Shift Record Report'],
        ['name' => 'report supply', 'label' => 'Supply Chain Report'],
      ];

      foreach ($access_rights as $access_right) {
        AccessRight::upsert($access_right, 'name');
      }
    }
}
