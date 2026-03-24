<?php

namespace Tests\Feature;

use App\Models\Config;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Voucher;
use App\Models\VoucherType;
use App\Services\AfipService;
use App\Services\BillingService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

it('increments non-fiscal voucher numbering correctly in configs table', function () {
    // 1. Setup minimal data
    // We create the necessary related records for foreign key constraints
    DB::table('customer_id_types')->insert(['id' => 99, 'value' => 'Doc Otro']);
    DB::table('province_id_types')->insert(['id' => 1, 'value' => 'Capital Federal']);
    DB::table('responsibility_types')->insert(['id' => 5, 'value' => 'Consumidor Final']);
    DB::table('categories')->insert(['id' => 1, 'name' => 'Test Category']);
    DB::table('unit_types')->insert(['id' => 1, 'value' => 'un', 'description' => 'Unidades']);
    DB::table('tax_condition_types')->insert(['id' => 5, 'value' => 21.0, 'description' => 'IVA 21%']);

    $product = new Product;
    $product->id = 1;
    $product->category_id = 1;
    $product->tax_condition_type_id = 5;
    $product->unit_type_id = 1;
    $product->description = 'Test Product';
    $product->save();

    $voucherType = new VoucherType;
    $voucherType->id = 5990;
    $voucherType->value = 'Budget';
    $voucherType->letter = 'X';
    $voucherType->type = 'Non Fiscal';
    $voucherType->enabled = true;
    $voucherType->save();

    $customer = new Customer;
    $customer->id = 1;
    $customer->customer_id_type_id = 99;
    $customer->business_name = 'Test Customer';
    $customer->name = 'Test Customer';
    $customer->province_id_type_id = 1;
    $customer->responsibility_type_id = 5;
    $customer->CUIT = '0';
    $customer->save();

    // 2. Mock dependencies
    $afipService = mock(AfipService::class);
    $stockService = mock(StockService::class);
    $stockService->shouldReceive('decrementStock')->andReturn(true);

    $billingService = new BillingService($afipService, $stockService);

    $input = [
        'customer' => $customer,
        'cart' => [
            ['id' => 1, 'qty' => 1, 'price' => 100, 'tax_rate' => 21],
        ],
        'totals' => ['total' => 121, 'net' => 100, 'tax' => 21],
        'voucherTypeId' => 5990,
        'pointOfSale' => 2,
        'warehouseId' => 1,
        'isFiscal' => false,
    ];

    // 3. Process first voucher
    $voucher1 = $billingService->processVoucher($input);

    // Check voucher ID
    expect($voucher1->id)->toBe('5990-2-1');

    // Check config entry
    $config = Config::find('5990-2');
    expect($config)->not->toBeNull()
        ->and($config->value)->toBe('1')
        ->and($config->description)->toBe('z9) Non Fiscal');

    // 4. Process second voucher
    $voucher2 = $billingService->processVoucher($input);

    // Check voucher ID
    expect($voucher2->id)->toBe('5990-2-2');

    // Check config updated
    $config->refresh();
    expect($config->value)->toBe('2');
});
