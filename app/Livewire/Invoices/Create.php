<?php

namespace App\Livewire\Invoices;

use Livewire\Component;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Config;
use App\Models\Warehouse;
use App\Models\Voucher;
use App\Models\VoucherDetail;
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

        // Si venimos de un cliente directo (desde el listado de clientes o asociado)
        if (request()->has('asocCustomer')) {
            $reqCustomerId = request()->get('asocCustomer');
            $customer = Customer::find($reqCustomerId);

            if ($customer) {
                $this->customerId = (string)$customer->id;

                // Si es Responsable Inscripto (1), seleccionar Factura A (1)
                // de lo contrario Factura B (6)
                if ($customer->responsibility_type_id == 1) {
                    $this->voucherTypeId = 1;
                } else {
                    $this->voucherTypeId = 6;
                }
            }
        }

        // Si venimos de un comprobante asociado (desde el listado de comprobantes)
        if (request()->has('asocPtoVta')) {
            $this->isAssociated = true;
            $this->originalPtoVta = request()->get('asocPtoVta');
            $this->originalCbteNro = request()->get('asocNro');
            $targetMode = request()->get('targetMode', 'NC');

            // Re-evaluar cliente si no se asignó arriba
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

            // Determinar automáticamente el tipo de NC/ND basado en la Factura
            $asocTipo = (int)request()->get('asocTipo');
            $this->voucherTypeId = match ($asocTipo) {
                1 => ($targetMode === 'NC' ? 3 : 2),  // Factura A -> NC A (3) o ND A (2)
                6 => ($targetMode === 'NC' ? 8 : 7),  // Factura B -> NC B (8) o ND B (7)
                11 => ($targetMode === 'NC' ? 13 : 12), // Factura C -> NC C (13) o ND C (12)
                default => $this->voucherTypeId
            };
        }

        // Carga automática de carrito aparcado (desde gestión de carritos)
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

        // Si es puramente numérico y largo, buscamos primero por código de barras exacto
        if (is_numeric($value) && strlen($value) >= 8) {
            $query->where('products.barcode', $value);
        } else {
            // Búsqueda por múltiples términos (palabras)
            $terms = explode(' ', $value);
            foreach ($terms as $term) {
                if (empty($term)) continue;
                $query->where('products.description', 'like', "%{$term}%");
            }
        }

        $this->searchResults = $query->limit(15)->get()->toArray();
        $this->highlightIndex = 0;
    }

    public function incrementHighlight()
    {
        if ($this->highlightIndex === count($this->searchResults) - 1) {
            $this->highlightIndex = 0;
            return;
        }
        $this->highlightIndex++;
    }

    public function decrementHighlight()
    {
        if ($this->highlightIndex === 0) {
            $this->highlightIndex = count($this->searchResults) - 1;
            return;
        }
        $this->highlightIndex--;
    }

    public function selectHighlightedProduct()
    {
        if (!empty($this->searchResults)) {
            $product = $this->searchResults[$this->highlightIndex];
            $this->addProduct($product['id']);
        }
    }

    public function addProduct($productId)
    {
        $product = Product::with(['taxConditionType', 'unitType'])->find($productId);
        if (!$product) return;

        $id = $product->id;
        $taxRate = (float) ($product->taxConditionType?->value ?? 21);
        $unit = $product->unitType?->value ?? 'un';

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
                'tax_rate' => $taxRate,
                'unit' => $unit,
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

        if ($this->cart[$id]['price_type'] == 1) {
            $this->cart[$id]['price_type'] = 2;
            $this->cart[$id]['price'] = $this->cart[$id]['price2'];
        } else {
            $this->cart[$id]['price_type'] = 1;
            $this->cart[$id]['price'] = $this->cart[$id]['price1'];
        }
    }

    public function updatedCart($value, $key)
    {
        // Validar descuento máximo si se modifica el campo discount
        if (str_contains($key, '.discount')) {
            $id = explode('.', $key)[0];
            $discount = (float)$value;
            $max = (float)$this->cart[$id]['discount_max'];

            if ($discount > $max) {
                $this->cart[$id]['discount'] = $max;
                $this->notify('Aviso', "El descuento máximo para este producto es de $" . $max, 'warning');
            }
        }
    }

    public function removeProduct($id)
    {
        unset($this->cart[$id]);
        $this->notify('Info', 'Producto eliminado', 'warning');
    }

    public function saveCart()
    {
        if (empty($this->cart) || is_null($this->customerId)) {
            $this->notify('Error', 'Debe seleccionar un cliente y tener productos en el carrito.', 'danger');
            return;
        }

        $folder = storage_path('app/carts');
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $date = now()->format('Y-m-d_H-i-s');
        $filename = "cliente_{$this->customerId}_{$date}.json";
        $path = $folder . DIRECTORY_SEPARATOR . $filename;

        file_put_contents($path, json_encode([
            'customerId' => $this->customerId,
            'cart' => $this->cart,
            'date' => now()->toDateTimeString()
        ]));

        $this->notify('Éxito', 'Carrito guardado correctamente.', 'success');
    }

    public function loadSavedCarts()
    {
        if (is_null($this->customerId)) {
            $this->notify('Error', 'Seleccione un cliente para ver sus carritos guardados.', 'danger');
            return;
        }

        $folder = storage_path('app/carts');
        if (!file_exists($folder)) {
            $this->savedCarts = [];
        } else {
            $pattern = str_replace('\\', '/', $folder) . "/cliente_{$this->customerId}_*.json";
            $files = glob($pattern);

            $this->savedCarts = array_map(function ($file) {
                return [
                    'name' => basename($file),
                    'path' => str_replace('\\', '/', $file),
                    'date' => date("d/m/Y H:i", filemtime($file))
                ];
            }, $files);

            usort($this->savedCarts, fn($a, $b) => filemtime($b['path']) <=> filemtime($a['path']));
        }

        $this->showSavedCarts = true;
        // La apertura del modal ahora se maneja por la acción de Filament
    }

    public function selectSavedCart($path)
    {
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            $this->cart = $data['cart'] ?? [];
            
            // Cerrar el modal de Filament
            $this->unmountAction();
            
            $this->notify('Éxito', 'Carrito recuperado.', 'success');
        }
    }

    public function deleteSavedCart($path)
    {
        if (file_exists($path)) {
            unlink($path);
            
            // Forzar actualización del contenido del modal de Filament
            $this->replaceModalContent('openSavedCarts', view('livewire.invoices.saved-carts-modal', [
                'savedCarts' => $this->getFreshSavedCarts()
            ]));

            $this->notify('Info', 'Archivo eliminado.', 'warning');
        }
    }

    public function openSavedCartsAction(): Action
    {
        return Action::make('openSavedCarts')
            ->label('Cargar')
            ->color('gray')
            ->icon('heroicon-o-folder-open')
            ->modalHeading('Carritos Aparcados')
            ->modalWidth('lg')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Cerrar')
            ->modalContent(fn() => view('livewire.invoices.saved-carts-modal', [
                'savedCarts' => $this->getFreshSavedCarts()
            ]));
    }

    protected function getFreshSavedCarts(): array
    {
        if (is_null($this->customerId)) return [];

        $folder = storage_path('app/carts');
        if (!file_exists($folder)) return [];

        $pattern = str_replace('\\', '/', $folder) . "/cliente_{$this->customerId}_*.json";
        $files = glob($pattern);
        
        $carts = array_map(function($file) {
            return [
                'name' => basename($file),
                'path' => str_replace('\\', '/', $file),
                'date' => date("d/m/Y H:i", filemtime($file))
            ];
        }, $files);
        
        usort($carts, fn($a, $b) => filemtime($b['path']) <=> filemtime($a['path']));
        
        return $carts;
    }

    public function getTotalsProperty()
    {
        $net = 0;
        $tax = 0;
        foreach ($this->cart as $item) {
            $discountAmount = (float)($item['discount'] ?? 0);
            $finalUnitPrice = (float)$item['price'] - $discountAmount;
            $lineTotal = $finalUnitPrice * (float)$item['qty'];

            $lineNet = $lineTotal / (1 + ($item['tax_rate'] / 100));
            $net += $lineNet;
            $tax += ($lineTotal - $lineNet);
        }

        return [
            'net' => $net,
            'tax' => $tax,
            'total' => $net + $tax
        ];
    }

    public function save()
    {
        $this->errorMessage = null;

        if (empty($this->cart) || is_null($this->customerId)) {
            $this->notify('Error', 'Complete los datos requeridos', 'danger');
            return;
        }

        try {
            DB::beginTransaction();

            $totals = $this->totals;
            $customer = Customer::find($this->customerId);

            if (!$customer && $this->customerId == "0") {
                $customer = new Customer();
                $customer->id = 0;
                $customer->name = 'Consumidor Final';
                $customer->CUIT = '0';
                $customer->responsibility_type_id = 5;
                $customer->customer_id_type_id = 99;
            }

            if (!$customer) {
                throw new \Exception("Cliente no encontrado.");
            }

            $config = DB::table('configs')->pluck('value', 'id');
            $fiscal = ($config['fiscal'] ?? '0') == '1' && $this->voucherTypeId <= 5000;

            if ($fiscal) {
                $taFolder = storage_path('app/afip/');
                if (!file_exists($taFolder)) mkdir($taFolder, 0777, true);

                $certName = $config['afip_cert'];
                $keyName = $config['afip_key'];
                if (!$certName || !$keyName) throw new \Exception('Configuración AFIP incompleta.');

                $afip = new \Afip([
                    'CUIT' => (int) preg_replace('/[^0-9]/', '', $config['cuit'] ?? '0'),
                    'production' => ($config['production'] ?? '0') == '1',
                    'cert' => $certName,
                    'key' => $keyName,
                    'res_folder' => storage_path('app/'),
                    'ta_folder' => $taFolder,
                    'exceptions' => true
                ]);

                $taxes = [];
                foreach ($this->cart as $item) {
                    $taxId = $this->getAfipTaxId($item['tax_rate']);
                    if (!isset($taxes[$taxId])) $taxes[$taxId] = ['Id' => $taxId, 'BaseImp' => 0, 'Importe' => 0];
                    $lineTotal = ($item['price'] - ($item['discount'] ?? 0)) * $item['qty'];
                    $lineNet = round($lineTotal / (1 + ($item['tax_rate'] / 100)), 2);
                    $taxes[$taxId]['BaseImp'] += $lineNet;
                    $taxes[$taxId]['Importe'] += round($lineTotal - $lineNet, 2);
                }

                $last_voucher = $afip->ElectronicBilling->GetLastVoucher($this->pointOfSale, $this->voucherTypeId);
                $newNum = $last_voucher + 1;

                $data = [
                    'CantReg'   => 1,
                    'PtoVta'    => $this->pointOfSale,
                    'CbteTipo'  => $this->voucherTypeId,
                    'Concepto'  => 1,
                    'DocTipo'   => $customer->customer_id_type_id ?? 99,
                    'DocNro'    => (float) preg_replace('/[^0-9]/', '', $customer->CUIT ?? '0'),
                    'CondicionIVAReceptorId' => $customer->responsibility_type_id ?? 5,
                    'CbteDesde' => $newNum,
                    'CbteHasta' => $newNum,
                    'CbteFch'   => (int) date('Ymd'),
                    'ImpTotal'  => round($totals['total'], 2),
                    'ImpTotConc' => 0,
                    'ImpNeto'   => round($totals['net'], 2),
                    'ImpOpEx'   => 0,
                    'ImpIVA'    => round($totals['tax'], 2),
                    'ImpTrib'   => 0,
                    'MonId'     => 'PES',
                    'MonCotiz'  => 1,
                    'Iva'       => array_values($taxes),
                ];

                if (in_array($this->voucherTypeId, [2, 3, 7, 8, 12, 13])) {
                    if (!$this->originalPtoVta || !$this->originalCbteNro) throw new \Exception('Faltan datos de comprobante asociado.');
                    $asocType = match ($this->voucherTypeId) { 2, 3 => 1, 7, 8 => 6, 12, 13 => 11, default => 1 };
                    $data['CbtesAsoc'] = [['Tipo' => (int)$asocType, 'PtoVta' => (int)$this->originalPtoVta, 'Nro' => (int)$this->originalCbteNro]];
                }

                $res = $afip->ElectronicBilling->CreateVoucher($data);
                $cae = $res['CAE'];
                $caeFchVto = $res['CAEFchVto'];
            } else {
                $newNum = (int) (Config::where('id', "last_{$this->voucherTypeId}_{$this->pointOfSale}")->value('value') ?? 1000) + 1;
                $cae = 'INTERNAL-' . now()->timestamp;
                $caeFchVto = now()->format('Y-m-d');
                $data = ['CbteDesde' => $newNum, 'PtoVta' => $this->pointOfSale, 'CbteTipo' => $this->voucherTypeId, 'DocTipo' => $customer->customer_id_type_id ?? 99, 'DocNro' => (float) preg_replace('/[^0-9]/', '', $customer->CUIT ?? '0'), 'ImpTotal' => $totals['total'], 'ImpNeto' => $totals['net'], 'ImpIVA' => $totals['tax']];
                Config::updateOrCreate(['id' => "last_{$this->voucherTypeId}_{$this->pointOfSale}"], ['value' => $newNum]);
            }

            $voucher = Voucher::create([
                'id' => "{$this->voucherTypeId}-{$this->pointOfSale}-{$newNum}",
                'data' => array_merge($data, ['res' => ['CAE' => $cae, 'CAEFchVto' => $caeFchVto, 'CUIT' => $config['cuit'] ?? ''], 'items' => $this->cart, 'customerId' => $this->customerId])
            ]);

            foreach ($this->cart as $item) {
                VoucherDetail::create(['voucher_id' => $voucher->id, 'product_id' => $item['id'], 'quantity' => $item['qty'], 'price' => $item['price'], 'tax' => ($item['price'] * $item['qty']) - (($item['price'] * $item['qty']) / (1 + ($item['tax_rate'] / 100))), 'subtotal' => $item['price'] * $item['qty']]);
                DB::table('inventories')->where('product_id', $item['id'])->where('warehouse_id', $this->warehouseId)->decrement('quantity', $item['qty']);
            }

            DB::commit();
            $this->notify('Éxito', 'Comprobante generado correctamente', 'success');
            return redirect()->to('/admin/vouchers/' . $voucher->id);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = $e->getMessage();
            $this->notify('Error AFIP', $e->getMessage(), 'danger');
        }
    }

    protected function getAfipTaxId($rate): int { return match ((float)$rate) { 0.0 => 3, 10.5 => 4, 21.0 => 5, 27.0 => 6, default => 5 }; }

    protected function notify($title, $body, $type): void { Notification::make()->title($title)->body($body)->{$type}()->send(); }

    public function render() { return view('livewire.invoices.create'); }
}