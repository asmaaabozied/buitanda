<?php

namespace App\Models;

use App\Models\Product;
use App\Traits\PreventDemoModeChanges;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithMapping, WithHeadings
{
    use PreventDemoModeChanges;

    public function collection()
    {
//        dd(request()->user_id);


        if (request()->search != null) {

            $sort_search = request()->search;
            $products = Product::
            orWhere('name', 'like', '%' . $sort_search . '%')
                ->orWhere('cost', 'like', '%' . $sort_search . '%')
                ->orWhere('supplier_id', 'like', '%' . $sort_search . '%')
                ->orWhere('user_id', 'like', '%' . $sort_search . '%')
                ->orWhere('category_id', 'like', '%' . $sort_search . '%')
                ->orWhere('cost', 'like', '%' . $sort_search . '%')
                ->orWhere('fulffiled', 'like', '%' . $sort_search . '%')
                ->orWhere('current_stock', 'like', '%' . $sort_search . '%')
                ->orWhere('low_stock_quantity', 'like', '%' . $sort_search . '%')
                ->orWhereHas('stocks', function ($q) use ($sort_search) {
                    $q->where('sku', 'like', '%' . $sort_search . '%');
                });
        } elseif (request()->type != null) {
            $var = explode(",", request()->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = Product::orderBy($col_name, $query);
            $sort_type = request()->type;
        } elseif (!empty(request()->user_id) && !empty(request()->supplier_id) && !empty(request()->category_id) && !empty(request()->sku) && !empty(request()->published) && !empty(request()->fulffiled)) {

            $products = Product::orderBy('created_at', 'desc')
                ->where('supplier_id', request()->supplier_id)
                ->where('user_id', request()->user_id)
                ->wherein('category_id', request()->category_id)
                ->where('published', request()->published)
//                ->orWhereBetween('unit_price', [$request->price_from, $request->price_to])
                ->orWhere('cost', 'like', '%' . request()->cost . '%')
                ->where('fulffiled', 'like', '%' . request()->fulffiled . '%')
                ->orWhereBetween('current_stock', [request()->quantity_from, request()->quantity_to])
                ->orWhereHas('stocks', function ($q) {
                    $q->where('sku', 'like', '%' . request()->sku . '%');
                });
        } elseif (!empty(request()->user_id) && !empty(request() > supplier_id) && !empty(request()->category_id)) {

            $products = Product::orderBy('created_at', 'desc')
                ->where('supplier_id', request()->supplier_id)
                ->where('user_id', request()->user_id)
                ->wherein('category_id', request()->category_id);

        } elseif (!empty(request()->user_id) && !empty(request()->supplier_id)) {

            $products = Product::orderBy('created_at', 'desc')
                ->where('supplier_id', request()->supplier_id)
                ->where('user_id', request()->user_id);

        } elseif (request()->user_id != null) {
            $seller_id = request()->user_id;
            $products = Product::orderBy('created_at', 'desc')
                ->where('user_id', request()->user_id);
        } elseif (request()->fulffiled != null) {

            $products = Product::orderBy('created_at', 'desc')
                ->where('fulffiled', request()->fulffiled);
        } elseif (request()->published != null) {

            $products = Product::orderBy('created_at', 'desc')
                ->where('published', request()->published);
        } elseif (request()->cost_from != null) {


            $products = Product::orderBy('created_at', 'desc')
                ->whereBetween('cost', [request()->cost_from, request()->cost_to]);


        } elseif (!empty(request()->price_from)) {

            $products = Product::orderBy('created_at', 'desc')
                ->whereBetween('unit_price', [request()->price_from, request()->price_to]);

        } elseif (request()->quantitysold_from != null) {

            $totals = OrderDetail::select('product_id', DB::raw('count(*) as total'))
                ->groupBy('product_id')
                ->get();
            $data = $totals->whereBetween('total', [request()->quantitysold_from, request()->quantitysold_to]);
            $product_id = $data->pluck('product_id');

            $products = Product::orderBy('created_at', 'desc')->wherein('id', $product_id);


        } elseif (!empty(request()->quantity_from)) {

            $products = Product::orderBy('created_at', 'desc')
                ->whereBetween('current_stock', [request()->quantity_from, request()->quantity_to]);


        } elseif (!empty(request()->supplier_id)) {
            $supplier_id = request()->supplier_id;

            $products = Product::orderBy('created_at', 'desc')
                ->where('supplier_id', request()->supplier_id);
        } elseif (!empty(request()->category_id)) {
            $category_id = request()->category_id;

            $products = Product::orderBy('created_at', 'desc')
                ->wherein('category_id', request()->category_id);
        } elseif (request()->sku != null) {


            $products = Product::orderBy('created_at', 'desc')
                ->whereHas('stocks', function ($q) {
                    $q->where('sku', 'like', '%' . request()->sku . '%');
                });
        } else {

            $products = Product::orderBy('created_at', 'desc')
                ->when(!empty($request), function ($query) {

                    $query->orWhere('cost', 'like', '%' . request()->cost . '%')
//                ->orWhereBetween('cost', [$request->cost_from, $request->cost_to])
//                        ->orWhereBetween('unit_price', [$request->price_from, $request->price_to])
                        ->orWhereBetween('current_stock', [request()->quantity_from, request()->quantity_to])
                        ->orWhere('published', request()->published)
                        ->orWhere('supplier_id', request()->supplier_id)
                        ->orWhere('user_id', request()->user_id)
                        ->orWhere('category_id', request()->category_id)
                        ->orWhere('fulffiled', 'like', '%' . request()->fulffiled . '%')
                        ->orWhere('current_stock', 'like', '%' . request()->quantity_from . '%')
                        ->orWhereHas('stocks', function ($q) {
                            $q->where('sku', 'like', '%' . request()->sku . '%');
                        });
                });

        }


        if (request()->paginate) {
            $products = Product::orderBy('created_at', 'desc')->paginate(request()->paginate);

        } else {
            return $products->get();
        }
    }

    public function headings(): array
    {
        return [
            'name',
            'description',
            'added_by',
            'user_id',
            'category_id',
            'brand_id',
            'video_provider',
            'video_link',
            'unit_price',
            'unit',
            'current_stock',
            'est_shipping_days',
            'meta_title',
            'meta_description',
        ];
    }

    /**
     * @var Product $product
     */
    public function map($product): array
    {
        $qty = 0;
        foreach ($product->stocks as $key => $stock) {
            $qty += $stock->qty;
        }
        return [
            $product->name,
            $product->description,
            $product->added_by,
            $product->user_id,
            $product->category_id,
            $product->brand_id,
            $product->video_provider,
            $product->video_link,
            $product->unit_price,
            $product->unit,
            $qty,
            $product->est_shipping_days,
            $product->meta_title,
            $product->meta_description,
        ];
    }
}
