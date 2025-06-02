<?php

namespace App\Http\Controllers;

use AizPackages\CombinationGenerate\Services\CombinationService;
use App\Http\Requests\ProductRequest;
use App\Models\OrderDetail;
use App\Models\ProductsExport;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductTranslation;
use App\Models\Category;
use App\Models\AttributeValue;
use App\Models\Cart;
use App\Models\ProductCategory;
use App\Models\Review;
use App\Models\Wishlist;
use App\Models\User;
use App\Notifications\ShopProductNotification;
use Carbon\Carbon;
use CoreComponentRepository;
use Artisan;
use Cache;
use App\Services\ProductService;
use App\Services\ProductTaxService;
use App\Services\ProductFlashDealService;
use App\Services\ProductStockService;
use App\Services\FrequentlyBoughtProductService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use DB;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    protected $productService;
    protected $productTaxService;
    protected $productFlashDealService;
    protected $productStockService;
    protected $frequentlyBoughtProductService;

    public function __construct(
        ProductService $productService,
        ProductTaxService $productTaxService,
        ProductFlashDealService $productFlashDealService,
        ProductStockService $productStockService,
        FrequentlyBoughtProductService $frequentlyBoughtProductService
    )
    {
        $this->productService = $productService;
        $this->productTaxService = $productTaxService;
        $this->productFlashDealService = $productFlashDealService;
        $this->productStockService = $productStockService;
        $this->frequentlyBoughtProductService = $frequentlyBoughtProductService;

        // Staff Permission Check
        $this->middleware(['permission:add_new_product'])->only('create');
        $this->middleware(['permission:show_all_products'])->only('all_products');
        $this->middleware(['permission:show_in_house_products'])->only('admin_products');
        $this->middleware(['permission:show_seller_products'])->only('seller_products');
        $this->middleware(['permission:product_edit'])->only('admin_product_edit', 'seller_product_edit');
        $this->middleware(['permission:product_duplicate'])->only('duplicate');
        $this->middleware(['permission:product_delete'])->only('destroy');
        $this->middleware(['permission:set_category_wise_discount'])->only('categoriesWiseProductDiscount');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin_products(Request $request)
    {
        CoreComponentRepository::instantiateShopRepository();

        $type = 'In House';
        $col_name = null;
        $query = null;
        $sort_search = null;
        $seller_id = null;
        $supplier_id = null;
        $category_id = null;
        // $products = Product::where('added_by', 'admin')->where('auction_product', 0)->where('wholesale_product', 0);

        if ($request->type != null) {
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = Product::orderBy($col_name, $query);
            $sort_type = $request->type;
        }
        if ($request->search != null) {
            $sort_search = $request->search;
            $products = Product::
            where('name', 'like', '%' . $sort_search . '%')
                ->orWhereHas('stocks', function ($q) use ($sort_search) {
                    $q->where('sku', 'like', '%' . $sort_search . '%');
                });
        }
        $paginate = $request->paginate ?? 20;
        $count = $products->count();
        $products = Product::orderBy('created_at', 'desc')->paginate($paginate);

        return view('backend.product.products.index', compact('count', 'paginate', 'products', 'supplier_id', 'type', 'col_name', 'query', 'sort_search', 'seller_id', 'category_id'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function seller_products(Request $request, $product_type)
    {
        $col_name = null;
        $query = null;
        $sort_search = null;
        $seller_id = null;
        $supplier_id = null;
        $category_id = null;
        $products = Product::where('added_by', 'seller')->where('auction_product', 0)->where('wholesale_product', 0);
        if ($request->has('user_id') && $request->user_id != null) {
            $products = $products->where('user_id', $request->user_id);
            $seller_id = $request->user_id;
        }
        if ($request->search != null) {
            $products = $products
                ->where('name', 'like', '%' . $request->search . '%');
            $sort_search = $request->search;
        }
        if ($request->type != null) {
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = $products->orderBy($col_name, $query);
            $sort_type = $request->type;
        }
        $products = $product_type == 'physical' ? $products->where('digital', 0) : $products->where('digital', 1);

        $paginate = $request->paginate ?? 20;
        $count = $products->count();
        $products = $products->orderBy('created_at', 'desc')->paginate($paginate);
        $type = 'Seller';

        if ($product_type == 'digital') {
            return view('backend.product.digital_products.index', compact('count', 'paginate', 'supplier_id', 'products', 'sort_search', 'type', 'seller_id', 'category_id'));
        }
        return view('backend.product.products.index', compact('count', 'supplier_id', 'paginate', 'products', 'seller_id', 'category_id', 'type', 'col_name', 'query', 'seller_id', 'sort_search'));
    }

    public function all_products(Request $request)

    {

        $col_name = null;
        $query = null;
        $seller_id = null;
        $supplier_id = null;
        $category_id = null;
        $sort_search = null;

        if ($request->search != null) {

            $sort_search = $request->search;
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
        } elseif ($request->type != null) {
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = Product::orderBy($col_name, $query);
            $sort_type = $request->type;
        } elseif (!empty($request['user_id']) && !empty($request['supplier_id']) && !empty($request['category_id']) && !empty($request->sku) && !empty($request->published) && !empty($request->fulffiled)) {

            $products = Product::orderBy('created_at', 'desc')
                ->where('supplier_id', $request->supplier_id)
                ->where('user_id', $request->user_id)
                ->wherein('category_id', $request->category_id)
                ->where('published', $request->published)
//                ->orWhereBetween('unit_price', [$request->price_from, $request->price_to])
                ->orWhere('cost', 'like', '%' . $request->cost . '%')
                ->where('fulffiled', 'like', '%' . $request->fulffiled . '%')
                ->orWhereBetween('current_stock', [$request->quantity_from, $request->quantity_to])
                ->orWhereHas('stocks', function ($q) use ($request) {
                    $q->where('sku', 'like', '%' . $request->sku . '%');
                });
        } elseif (!empty($request['user_id']) && !empty($request['supplier_id']) && !empty($request['category_id'])) {

            $products = Product::orderBy('created_at', 'desc')
                ->where('supplier_id', $request->supplier_id)
                ->where('user_id', $request->user_id)
                ->wherein('category_id', $request->category_id);

        } elseif (!empty($request['user_id']) && !empty($request['supplier_id'])) {

            $products = Product::orderBy('created_at', 'desc')
                ->where('supplier_id', $request->supplier_id)
                ->where('user_id', $request->user_id);

        } elseif ($request->user_id != null) {
            $seller_id = $request->user_id;
            $products = Product::orderBy('created_at', 'desc')
                ->where('user_id', $request->user_id);
        } elseif ($request->fulffiled != null) {

            $products = Product::orderBy('created_at', 'desc')
                ->where('fulffiled', $request->fulffiled);
        } elseif ($request->published != null) {

            $products = Product::orderBy('created_at', 'desc')
                ->where('published', $request->published);
        } elseif ($request->cost_from != null) {


            $products = Product::orderBy('created_at', 'desc')
                ->whereBetween('cost', [$request->cost_from, $request->cost_to]);


        } elseif (!empty($request['price_from'])) {

            $products = Product::orderBy('created_at', 'desc')
                ->whereBetween('unit_price', [$request->price_from, $request->price_to]);

        } elseif ($request->quantitysold_from != null) {

            $totals = OrderDetail::select('product_id', DB::raw('count(*) as total'))
                ->groupBy('product_id')
                ->get();
            $data = $totals->whereBetween('total', [$request->quantitysold_from, $request->quantitysold_to]);
            $product_id = $data->pluck('product_id');

            $products = Product::orderBy('created_at', 'desc')->wherein('id', $product_id);


        } elseif (!empty($request['quantity_from'])) {

            $products = Product::orderBy('created_at', 'desc')
                ->whereBetween('current_stock', [$request->quantity_from, $request->quantity_to]);


        } elseif (!empty($request['supplier_id'])) {
            $supplier_id = $request->supplier_id;

            $products = Product::orderBy('created_at', 'desc')
                ->where('supplier_id', $request->supplier_id);
        } elseif (!empty($request['category_id'])) {
            $category_id = $request->category_id;

            $products = Product::orderBy('created_at', 'desc')
                ->wherein('category_id', $request->category_id);
        } elseif ($request->sku != null) {


            $products = Product::orderBy('created_at', 'desc')
                ->whereHas('stocks', function ($q) use ($request) {
                    $q->where('sku', 'like', '%' . $request->sku . '%');
                });
        } else {

            $products = Product::orderBy('created_at', 'desc')
                ->when(!empty($request), function ($query) use ($request) {

                    $query->orWhere('cost', 'like', '%' . $request->cost . '%')
                        ->orWhereBetween('current_stock', [$request->quantity_from, $request->quantity_to])
                        ->orWhere('published', $request->published)
                        ->orWhere('supplier_id', $request->supplier_id)
                        ->orWhere('user_id', $request->user_id)
                        ->orWhere('category_id', $request->category_id)
                        ->orWhere('fulffiled', 'like', '%' . $request->fulffiled . '%')
                        ->orWhere('current_stock', 'like', '%' . $request->quantity_from . '%')
                        ->orWhereHas('stocks', function ($q) use ($request) {
                            $q->where('sku', 'like', '%' . $request->sku . '%');
                        });
                });

        }
        $paginate = $request->paginate ?? 20;

        if ($request->paginate) {


            $products = $products->paginate($request->paginate);
            $count = $products->count();
        } else {
            $count = $products->count();
            $products = $products->paginate(20);

        }

        $type = 'All';

        if ($request->export != null) {
            return Excel::download(new ProductsExport, 'products.xlsx');

        }
        return view('backend.product.products.index', compact('products', 'count', 'paginate', 'supplier_id', 'category_id', 'type', 'col_name', 'query', 'seller_id', 'sort_search'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public
    function create()
    {
        CoreComponentRepository::initializeCache();

        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();

        return view('backend.product.products.create', compact('categories'));
    }

    public
    function add_more_choice_option(Request $request)
    {
        $all_attribute_values = AttributeValue::with('attribute')->where('attribute_id', $request->attribute_id)->get();

        $html = '';

        foreach ($all_attribute_values as $row) {
            $html .= '<option value="' . $row->value . '">' . $row->value . '</option>';
        }

        echo json_encode($html);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public
    function store(ProductRequest $request)
    {
        $product = $this->productService->store($request->except([
            '_token', 'choice', 'tax_id', 'tax', 'tax_type', 'flash_deal_id', 'flash_discount', 'flash_discount_type'
        ]));
        $request->merge(['product_id' => $product->id]);

        //Product categories
        $product->categories()->attach($request->category_ids);

        //VAT & Tax
        if ($request->tax_id) {
            $this->productTaxService->store($request->only([
                'tax_id', 'tax', 'tax_type', 'product_id'
            ]));
        }

        //Flash Deal
        $this->productFlashDealService->store($request->only([
            'flash_deal_id', 'flash_discount', 'flash_discount_type'
        ]), $product);

        //Product Stock
        $this->productStockService->store($request->only([
            'colors_active', 'colors', 'choice_no', 'unit_price', 'sku', 'current_stock', 'product_id'
        ]), $product);

        // Frequently Bought Products
        $this->frequentlyBoughtProductService->store($request->only([
            'product_id', 'frequently_bought_selection_type', 'fq_bought_product_ids', 'fq_bought_product_category_id'
        ]));

        // Product Translations
        $request->merge(['lang' => env('DEFAULT_LANGUAGE')]);
        ProductTranslation::create($request->only([
            'lang', 'name', 'unit', 'description', 'product_id'
        ]));

        flash(translate('Product has been inserted successfully'))->success();

        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        return redirect()->route('products.all');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public
    function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public
    function admin_product_edit(Request $request, $id)
    {
        CoreComponentRepository::initializeCache();

        $product = Product::findOrFail($id);
        if ($product->digital == 1) {
            return redirect('admin/digitalproducts/' . $id . '/edit');
        }

        $lang = $request->lang;
        $tags = json_decode($product->tags);
        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();
        $colors = ProductStock::where('product_id', $id)->get();

        return view('backend.product.products.edit', compact('product', 'colors', 'categories', 'tags', 'lang'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public
    function seller_product_edit(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        if ($product->digital == 1) {
            return redirect('digitalproducts/' . $id . '/edit');
        }
        $lang = $request->lang;
        $tags = json_decode($product->tags);
        // $categories = Category::all();
        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();

        return view('backend.product.products.edit', compact('product', 'categories', 'tags', 'lang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public
    function update(ProductRequest $request, Product $product)
    {


        //Product
        $product = $this->productService->update($request->except([
            '_token', 'sku', 'choice', 'tax_id', 'tax', 'tax_type', 'flash_deal_id', 'flash_discount', 'flash_discount_type'
        ]), $product);

        $request->merge(['product_id' => $product->id]);

        //Product categories
        $product->categories()->sync($request->category_ids);


        //Product Stock
        $product->stocks()->delete();
        $this->productStockService->store($request->only([
            'colors_active', 'colors', 'choice_no', 'unit_price', 'sku', 'current_stock', 'product_id'
        ]), $product);

        //Flash Deal
        $this->productFlashDealService->store($request->only([
            'flash_deal_id', 'flash_discount', 'flash_discount_type'
        ]), $product);

        //VAT & Tax
        if ($request->tax_id) {
            $product->taxes()->delete();
            $this->productTaxService->store($request->only([
                'tax_id', 'tax', 'tax_type', 'product_id'
            ]));
        }

        // Frequently Bought Products
        $product->frequently_bought_products()->delete();
        $this->frequentlyBoughtProductService->store($request->only([
            'product_id', 'frequently_bought_selection_type', 'fq_bought_product_ids', 'fq_bought_product_category_id'
        ]));

        // Product Translations
        ProductTranslation::updateOrCreate(
            $request->only([
                'lang', 'product_id'
            ]),
            $request->only([
                'name', 'unit', 'description'
            ])
        );

        flash(translate('Product has been updated successfully'))->success();

        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        if ($request->has('tab') && $request->tab != null) {
            return Redirect::to(URL::previous() . "#" . $request->tab);
        }
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public
    function destroy($id)
    {
        $product = Product::findOrFail($id);

        $product->product_translations()->delete();
        $product->categories()->detach();
        $product->stocks()->delete();
        $product->taxes()->delete();
        $product->frequently_bought_products()->delete();
        $product->last_viewed_products()->delete();
        $product->flash_deal_products()->delete();
        deleteProductReview($product);
        if (Product::destroy($id)) {
            Cart::where('product_id', $id)->delete();
            Wishlist::where('product_id', $id)->delete();

            flash(translate('Product has been deleted successfully'))->success();

            Artisan::call('view:clear');
            Artisan::call('cache:clear');

            return back();
        } else {
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    public
    function bulk_product_delete(Request $request)
    {
        if ($request->discount != null && $request->id != null) {
            foreach ($request->id as $product_id) {

                $product = Product::find($product_id);
                $product->update(['discount' => $request->discount]);
            }


        } elseif ($request->status != null && $request->id != null) {
            foreach ($request->id as $product_id) {

                $product = Product::find($product_id);
                $product->update(['published' => $request->status]);
            }
        }
            elseif ($request->seller_id != null && $request->id != null) {
                foreach ($request->id as $product_id) {

                    $product = Product::find($product_id);
                    $product->update(['user_id' => $request->seller_id]);
                }

            }
            elseif ($request->sup_id != null && $request->id != null) {
                foreach ($request->id as $product_id) {

                    $product = Product::find($product_id);
                    $product->update(['supplier_id' => $request->sup_id]);
                }

            }


        elseif ($request->cat_id != null && $request->id != null) {
                foreach ($request->id as $product_id) {

                    $product = Product::find($product_id);
                    $product->update(['category_id' => $request->cat_id]);
                }

            }

        elseif ($request->fulf != null && $request->id != null) {
                foreach ($request->id as $product_id) {

                    $product = Product::find($product_id);
                    $product->update(['fulffiled' => $request->fulf]);
                }

            }


        elseif (!empty($request->id)) {
            foreach ($request->id as $product_id) {
                $this->destroy($product_id);
            }
        }

        return 1;
    }

    /**
     * Duplicates the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public
    function duplicate(Request $request, $id)
    {
        $product = Product::find($id);

        //Product
        $product_new = $this->productService->product_duplicate_store($product);

        //Product Stock
        $this->productStockService->product_duplicate_store($product->stocks, $product_new);

        //VAT & Tax
        $this->productTaxService->product_duplicate_store($product->taxes, $product_new);

        // Product Categories
        foreach ($product->product_categories as $product_category) {
            ProductCategory::insert([
                'product_id' => $product_new->id,
                'category_id' => $product_category->category_id,
            ]);
        }

        // Frequently Bought Products
        $this->frequentlyBoughtProductService->product_duplicate_store($product->frequently_bought_products, $product_new);

        flash(translate('Product has been duplicated successfully'))->success();
        if ($request->type == 'In House')
            return redirect()->route('products.admin');
        elseif ($request->type == 'Seller')
            return redirect()->route('products.seller');
        elseif ($request->type == 'All')
            return redirect()->route('products.all');
    }

    public
    function get_products_by_brand(Request $request)
    {
        $products = Product::where('brand_id', $request->brand_id)->get();
        return view('partials.product_select', compact('products'));
    }

    public
    function updateTodaysDeal(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->todays_deal = $request->status;
        $product->save();
        Cache::forget('todays_deal_products');
        return 1;
    }

    public
    function updatePublished(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->published = $request->status;

        if ($product->added_by == 'seller' && addon_is_activated('seller_subscription') && $request->status == 1) {
            $shop = $product->user->shop;
            if (
                $shop->package_invalid_at == null
                || Carbon::now()->diffInDays(Carbon::parse($shop->package_invalid_at), false) < 0
                || $shop->product_upload_limit <= $shop->user->products()->where('published', 1)->count()
            ) {
                return 0;
            }
        }

        $product->save();

        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        return 1;
    }

    public
    function updateProductApproval(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->approved = $request->approved;

        if ($product->added_by == 'seller' && addon_is_activated('seller_subscription')) {
            $shop = $product->user->shop;
            if (
                $shop->package_invalid_at == null
                || Carbon::now()->diffInDays(Carbon::parse($shop->package_invalid_at), false) < 0
                || $shop->product_upload_limit <= $shop->user->products()->where('published', 1)->count()
            ) {
                return 0;
            }
        }

        $product->save();

        $users = User::findMany($product->user_id);
        $data = array();
        $data['product_type'] = $product->digital == 0 ? 'physical' : 'digital';
        $data['status'] = $request->approved == 1 ? 'approved' : 'rejected';
        $data['product'] = $product;
        $data['notification_type_id'] = get_notification_type('seller_product_approved', 'type')->id;
        Notification::send($users, new ShopProductNotification($data));

        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        return 1;
    }

    public
    function updateFeatured(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->featured = $request->status;
        if ($product->save()) {
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            return 1;
        }
        return 0;
    }

    public
    function sku_combination(Request $request)
    {
        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        } else {
            $colors_active = 0;
        }

        $unit_price = $request->unit_price;
        $product_name = $request->name;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                // foreach (json_decode($request[$name][0]) as $key => $item) {
                if (isset($request[$name])) {
                    $data = array();
                    foreach ($request[$name] as $key => $item) {
                        // array_push($data, $item->value);
                        array_push($data, $item);
                    }
                    array_push($options, $data);
                }
            }
        }

        $combinations = (new CombinationService())->generate_combination($options);
        return view('backend.product.products.sku_combinations', compact('combinations', 'unit_price', 'colors_active', 'product_name'));
    }

    public
    function sku_combination_edit(Request $request)
    {
        $product = Product::findOrFail($request->id);

        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        } else {
            $colors_active = 0;
        }

        $product_name = $request->name;
        $unit_price = $request->unit_price;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                // foreach (json_decode($request[$name][0]) as $key => $item) {
                if (isset($request[$name])) {
                    $data = array();
                    foreach ($request[$name] as $key => $item) {
                        // array_push($data, $item->value);
                        array_push($data, $item);
                    }
                    array_push($options, $data);
                }
            }
        }

        $combinations = (new CombinationService())->generate_combination($options);
        return view('backend.product.products.sku_combinations_edit', compact('combinations', 'unit_price', 'colors_active', 'product_name', 'product'));
    }

    public
    function product_search(Request $request)
    {
        $products = $this->productService->product_search($request->except(['_token']));
        return view('partials.product.product_search', compact('products'));
    }

    public
    function get_selected_products(Request $request)
    {
        $products = product::whereIn('id', $request->product_ids)->get();
        return view('partials.product.frequently_bought_selected_product', compact('products'));
    }

    public
    function setProductDiscount(Request $request)
    {
        return $this->productService->setCategoryWiseDiscount($request->except(['_token']));
    }
}
