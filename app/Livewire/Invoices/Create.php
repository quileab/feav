<?php

namespace App\Livewire\Invoices;

use Livewire\Component;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Config;
use App\Models\Warehouse;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Services\BillingService;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Action;

class Create extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    public ?string $customerId = null;
    public int $voucherTypeId = 6;
    public int $pointOfSale = 2;
    public int $warehouseId = 1;
    public array $cart = [];
    public array $voucherTypes = [];
    public bool $isAssociated = false;
    public array $savedCarts = [];
    public bool $showSavedCarts = false;

    // Campos para Notas de Crédito/Débito
    public ?int $originalPtoVta = null;
    public ?int $originalCbteNro = null;

    // Búsqueda
    public string $searchProduct = '';
    public array $searchResults = [];
    public int $highlightIndex = 0;
    public ?string $errorMessage = null;

    public function mount(): void
    {
        $config = DB::table('configs')->pluck('value', 'id');
        $this->pointOfSale = (int) ($config['point_of_sale'] ?? 2);
        $this->warehouseId = (int) ($config['warehouse_id'] ?? 1);

        $fiscal = ($config['fiscal'] ?? '0') == '1';

        $this->voucherTypes = \App\Models\VoucherType::query()
            ->where('enabled', true)
            ->when(!$fiscal, function ($query) {
                return $query->where('id', '>', 5000);
            })
            ->when($fiscal, function ($query) {
                return $query->where('id', '<', 5000);
            })
            ->get()
            ->toArray();

        if (!empty($this->voucherTypes)) {
            $this->voucherTypeId = $this->voucherTypes[0]['id'];
        }

        // Si venimos de un cliente directo
        if (request()->has('asocCustomer')) {
            $reqCustomerId = request()->get('asocCustomer');
            $customer = Customer::find($reqCustomerId);
            if ($customer) {
                $this->customerId = (string)$customer->id;
                $this->voucherTypeId = ($customer->responsibility_type_id == 1) ? 1 : 6;
            }
        }

        // Si venimos de un comprobante asociado
        if (request()->has('asocPtoVta')) {
            $this->isAssociated = true;
            $this->originalPtoVta = request()->get('asocPtoVta');
            $this->originalCbteNro = request()->get('asocNro');
            $targetMode = request()->get('targetMode', 'NC');

            if (!$this->customerId) {
                $reqCustomerId = request()->get('asocCustomer');
                if ($reqCustomerId && Customer::where('id', $reqCustomerId)->exists()) {
                    $this->customerId = (string)$reqCustomerId;
                } else {
                    $docNro = request()->get('asocDocNro');
                    if ($docNro && $docNro != "0") {
                        $foundCustomer = Customer::where('CUIT', (string)$docNro)->first();
                        if ($foundCustomer) {
                            $this->customerId = (string)$foundCustomer->id;
                        } else {
                            $this->customerId = "0";
                        }
                    } else {
                        $this->customerId = "0";
                    }
                }
            }

            $asocTipo = (int)request()->get('asocTipo');
            $this->voucherTypeId = match ($asocTipo) {
                1 => ($targetMode === 'NC' ? 3 : 2),
                6 => ($targetMode === 'NC' ? 8 : 7),
                11 => ($targetMode === 'NC' ? 13 : 12),
                default => $this->voucherTypeId
            };
        }

        // Carga automática de carrito aparcado
        if (request()->has('asocCart')) {
            $cartFile = request()->get('asocCart');
            $path = storage_path('app/carts/' . $cartFile);
            if (file_exists($path)) {
                $data = json_decode(file_get_contents($path), true);
                $this->cart = $data['cart'] ?? [];
            }
        }
    }

    public function updatedSearchProduct($value)
    {
        if (strlen($value) < 2) {
            $this->searchResults = [];
            $this->highlightIndex = 0;
            return;
        }

        $query = Product::query()
            ->select('products.id', 'products.description', 'products.barcode', 'products.sale_price1', 'products.tax_condition_type_id')
            ->leftJoin('inventories', function ($join) {
                $join->on('products.id', '=', 'inventories.product_id')
                    ->where('inventories.warehouse_id', '=', $this->warehouseId);
            })
            ->addSelect(DB::raw('COALESCE(inventories.quantity, 0) as stock'));

        if (is_numeric($value) && strlen($value) >= 8) {
            $query->where('products.barcode', $value);
        } else {
            foreach (explode(' ', $value) as $term) {
                if (!empty($term)) $query->where('products.description', 'like', "%{$term}%");
            }
        }

        $this->searchResults = $query->limit(15)->get()->toArray();
        $this->highlightIndex = 0;
    }

    public function incrementHighlight() { if ($this->highlightIndex < count($this->searchResults) - 1) $this->highlightIndex++; else $this->highlightIndex = 0; }
    public function decrementHighlight() { if ($this->highlightIndex > 0) $this->highlightIndex--; else $this->highlightIndex = count($this->searchResults) - 1; }
    public function selectHighlightedProduct() { if (!empty($this->searchResults)) $this->addProduct($this->searchResults[$this->highlightIndex]['id']); }

    public function addProduct($productId)
    {
        $product = Product::with(['taxConditionType', 'unitType'])->find($productId);
        if (!$product) return;

        $id = $product->id;
        if (isset($this->cart[$id])) {
            $this->cart[$id]['qty']++;
        } else {
            $this->cart[$id] = [
                'id' => $id,
                'name' => $product->description,
                'qty' => 1,
                'price' => (float)$product->sale_price1,
                'price1' => (float)$product->sale_price1,
                'price2' => (float)$product->sale_price2,
                'price_type' => 1,
                'tax_rate' => (float) ($product->taxConditionType?->value ?? 21),
                'unit' => $product->unitType?->value ?? 'un',
                'discount' => 0,
                'discount_max' => (float)$product->discount_max,
            ];
        }
        $this->searchProduct = '';
        $this->searchResults = [];
    }

    public function togglePriceType($id)
    {
        if (!isset($this->cart[$id])) return;
        $this->cart[$id]['price_type'] = ($this->cart[$id]['price_type'] == 1) ? 2 : 1;
        $this->cart[$id]['price'] = ($this->cart[$id]['price_type'] == 1) ? $this->cart[$id]['price1'] : $this->cart[$id]['price2'];
    }

    public function updatedCart($value, $key)
    {
        if (str_contains($key, '.discount')) {
            $id = explode('.', $key)[0];
            $max = (float)$this->cart[$id]['discount_max'];
            if ((float)$value > $max) {
                $this->cart[$id]['discount'] = $max;
                $this->notify('Aviso', "El descuento máximo es de $".$max, 'warning');
            }
        }
    }

    public function removeProduct($id) { unset($this->cart[$id]); $this->notify('Info', 'Producto eliminado', 'warning'); }

    public function saveCart()
    {
        if (empty($this->cart) || is_null($this->customerId)) {
            $this->notify('Error', 'Seleccione cliente y productos.', 'danger');
            return;
        }
        $folder = storage_path('app/carts');
        if (!file_exists($folder)) mkdir($folder, 0777, true);
        $filename = "cliente_{$this->customerId}_".now()->format('Y-m-d_H-i-s').".json";
        file_put_contents($folder.DIRECTORY_SEPARATOR.$filename, json_encode(['customerId' => $this->customerId, 'cart' => $this->cart, 'date' => now()->toDateTimeString()]));
        $this->notify('Éxito', 'Carrito guardado.', 'success');
    }

    public function selectSavedCart($path) { if (file_exists($path)) { $data = json_decode(file_get_contents($path), true); $this->cart = $data['cart'] ?? []; $this->unmountAction(); $this->notify('Éxito', 'Carrito recuperado.', 'success'); } }
    public function deleteSavedCart($path) { if (file_exists($path)) { unlink($path); $this->notify('Info', 'Archivo eliminado.', 'warning'); } }

    public function openSavedCartsAction(): Action
    {
        return Action::make('openSavedCarts')->label('Cargar')->color('gray')->icon('heroicon-o-folder-open')->modalHeading('Carritos Aparcados')->modalWidth('lg')->modalSubmitAction(false)->modalCancelActionLabel('Cerrar')
            ->modalContent(fn() => view('livewire.invoices.saved-carts-modal', ['savedCarts' => $this->getFreshSavedCarts()]));
    }

    protected function getFreshSavedCarts(): array
    {
        if (is_null($this->customerId)) return [];
        $folder = storage_path('app/carts');
        if (!file_exists($folder)) return [];
        $files = glob(str_replace('\\', '/', $folder) . "/cliente_{$this->customerId}_*.json");
        $carts = array_map(fn($file) => ['name' => basename($file), 'path' => str_replace('\\', '/', $file), 'date' => date("d/m/Y H:i", filemtime($file))], $files);
        usort($carts, fn($a, $b) => filemtime($b['path']) <=> filemtime($a['path']));
        return $carts;
    }

    public function getTotalsProperty()
    {
        $net = 0; $tax = 0;
        foreach ($this->cart as $item) {
            $lineTotal = ((float)$item['price'] - (float)($item['discount'] ?? 0)) * (float)$item['qty'];
            $lineNet = $lineTotal / (1 + ($item['tax_rate'] / 100));
            $net += $lineNet; $tax += ($lineTotal - $lineNet);
        }
        return ['net' => $net, 'tax' => $tax, 'total' => $net + $tax];
    }

    public function save(BillingService $billingService)
    {
        $this->errorMessage = null;
        if (empty($this->cart) || is_null($this->customerId)) { $this->notify('Error', 'Complete los datos requeridos', 'danger'); return; }

        try {
            $customer = Customer::find($this->customerId);
            if (!$customer && $this->customerId == "0") {
                $customer = new Customer(); $customer->id = 0; $customer->name = 'Consumidor Final'; $customer->CUIT = '0'; $customer->responsibility_type_id = 5; $customer->customer_id_type_id = 99;
            }
            if (!$customer) throw new \Exception("Cliente no encontrado.");

            $config = DB::table('configs')->pluck('value', 'id');
            
            $voucher = $billingService->processVoucher([
                'customer' => $customer,
                'cart' => $this->cart,
                'totals' => $this->totals,
                'voucherTypeId' => $this->voucherTypeId,
                'pointOfSale' => $this->pointOfSale,
                'warehouseId' => $this->warehouseId,
                'isFiscal' => ($config['fiscal'] ?? '0') == '1' && $this->voucherTypeId <= 5000,
                'originalPtoVta' => $this->originalPtoVta,
                'originalCbteNro' => $this->originalCbteNro,
            ]);

            $this->notify('Éxito', 'Comprobante generado correctamente', 'success');
            return redirect()->to('/admin/vouchers/' . $voucher->id);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->notify('Error', $e->getMessage(), 'danger');
        }
    }

    protected function notify($title, $body, $type): void { Notification::make()->title($title)->body($body)->{$type}()->send(); }
    public function render() { return view('livewire.invoices.create'); }
}