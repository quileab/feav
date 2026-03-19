<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use App\Models\Customer;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

class ManageSavedCarts extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected string $view = 'filament.pages.manage-saved-carts';

    protected static ?string $title = 'Gestión de Carritos Aparcados';

    protected static ?string $navigationLabel = 'Carritos Aparcados';

    public array $carts = [];

    public function mount(): void
    {
        $this->loadCarts();
    }

    public function loadCarts(): void
    {
        $folder = storage_path('app/carts');
        if (!File::exists($folder)) {
            $this->carts = [];
            return;
        }

        $files = File::files($folder);
        
        $this->carts = array_map(function($file) {
            $data = json_decode(File::get($file->getPathname()), true);
            $customerId = $data['customerId'] ?? '0';
            $customer = Customer::find($customerId);
            
            $total = 0;
            if (isset($data['cart'])) {
                foreach ($data['cart'] as $item) {
                    $total += ((float)$item['price'] - (float)($item['discount'] ?? 0)) * (float)$item['qty'];
                }
            }

            return [
                'name' => $file->getFilename(),
                'path' => str_replace('\\', '/', $file->getPathname()),
                'customer' => $customer ? $customer->name : 'Consumidor Final',
                'customer_id' => $customerId,
                'items' => count($data['cart'] ?? []),
                'total' => $total,
                'date' => date("d/m/Y H:i", $file->getMTime()),
                'timestamp' => $file->getMTime()
            ];
        }, $files);

        // Ordenar por fecha descendente
        usort($this->carts, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
    }

    public function deleteCart(string $path): void
    {
        if (File::exists($path)) {
            File::delete($path);
            $this->loadCarts();
            Notification::make()
                ->title('Carrito eliminado')
                ->success()
                ->send();
        }
    }
}