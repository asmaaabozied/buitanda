<?php

namespace App\Models;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use App\Traits\PreventDemoModeChanges;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Str;
use Auth;
use Carbon\Carbon;
use Storage;

//class ProductsImport implements ToModel, WithHeadingRow, WithValidation
class ProductsImport implements ToCollection, WithHeadingRow, WithValidation, ToModel
{
    use PreventDemoModeChanges;

    private $rows = 0;

    public function collection(Collection $rows)
    {

        $canImport = true;
        $user = Auth::user();
        if ($user->user_type == 'seller' && addon_is_activated('seller_subscription')) {
            if ((count($rows) + $user->products()->count()) > $user->shop->product_upload_limit
                || $user->shop->package_invalid_at == null
                || Carbon::now()->diffInDays(Carbon::parse($user->shop->package_invalid_at), false) < 0
            ) {
                $canImport = false;
                flash(translate('Please upgrade your package.'))->warning();
            }
        }

        foreach ($rows->chunk(500) as $row) {
            $approved = 1;
            if ($user->user_type == 'seller' && get_setting('product_approve_by_admin') == 1) {
                $approved = 0;
            }

            $productId = Product::create([
                'name' => $row['name'],
                'description' => $row['description'],
                'added_by' => $user->user_type == 'seller' ? 'seller' : 'admin',
                'approved' => $approved,
                'user_id' => auth()->user()->id,
                'tags' => $row['tags'] ?? null,
                'purchase_price' => $row['purchase_price'] ?? null,
                'attributes' => $row['attributes'] ?? null,
                'stock_visibility_state' => $row['stock_visibility_state'] ?? null,
                'min_qty' => $row['min_qty'] ?? null,
                'unit_price' => $row['unit_price'] ?? null,
                'weight' => $row['weight'] ?? null,
                'slug' => $row['slug'] ?? null,
                'meta_title' => $row['meta_title'] ?? null,
                'meta_description' => $row['meta_description'] ?? null,
                'tax_type' => $row['tax_type'] ?? null,
            ]);
          
          
          dd($productId);
            ProductStock::create([
                'product_id' => $productId->id,
                'qty' => $row['min_qty'] ?? 0,
                'price' => $row['unit_price'] ?? 0,
                'sku' => $row['slug'] ?? null,
                'variant' => '',
            ]);

        }

        flash(translate('Products imported successfully'))->success();

    }

    public function model(array $row)
    {
        $this->rows;
    }
//
//    public function getRowCount(): int
//    {
//        return $this->rows;
//    }

    public function rules(): array
    {
        return [
            // Can also use callback validation rules
            'unit_price' => function ($attribute, $value, $onFailure) {
                if (!is_numeric($value)) {
                    $onFailure('Unit price is not numeric');
                }
            }
        ];
    }


}
