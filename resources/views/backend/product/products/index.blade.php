@extends('backend.layouts.app')

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js" crossorigin="anonymous"></script>

<!-- CSS -->
{{--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">--}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css">
<!-- JS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/css/bootstrap-multiselect.css"
      rel="stylesheet">
<style>


    .multiselect-container {

        overflow: scroll;
        max-height: 300px;
    }

    .btn-group {
        width: 100% !important;

    }

    .clickable-icon {
        cursor: pointer; /* يجعل المؤشر يظهر كيد عند التحويم */
        font-size: 2rem; /* تحديد حجم الأيقونة */
        color: rgba(35, 31, 31, 0.98); /* لون الأيقونة (اختياري) */
        transition: transform 0.3s ease; /* إضافة تأثير انسيابي عند النقر */
    }

    /* تأثير عند التحويم */
    .clickable-icon:hover {
        transform: scale(1.5); /* تكبير الأيقونة عند التحويم */
        color: #2475e9; /* تغيير اللون عند التحويم (اختياري) */
    }

</style>
@section('content')

    @php
        CoreComponentRepository::instantiateShopRepository();
        CoreComponentRepository::initializeCache();
    @endphp

    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="row align-items-center">
            <div class="col-auto">
                <h1 class="h3">{{ translate('All Product') }}({{$count}})</h1>
            </div>

            <div class="col-md-1">

                <a href="?export=1" class="form-control btn btn-success">

                    Export
                </a>

            </div>
            @if($type != 'Seller' && auth()->user()->can('add_new_product'))
                <div class="col text-right">
                    <a href="{{ route('products.create') }}" class="btn btn-circle btn-info">
                        <span>{{translate('Add New Product')}}</span>
                    </a>
                </div>
            @endif


        </div>
    </div>
    <br>

    <div class="card">
        <form class="" id="sort_products" action="" method="GET">
            <div class="card-header row gutters-5">
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control form-control-sm" onkeyup="sort_products()" id="search"
                               name="search" @isset($sort_search) value="{{ $sort_search }}"
                               @endisset placeholder="{{ translate('Search By Keyboard') }}">
                    </div>
                </div>

                <div class="col-md-1">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="status"
                            name="status" onchange="bulk_Action()">
                        <option value="">{{ translate('Published') }}</option>
                        <option value="1">
                            Enable
                        </option>
                        <option value="0">
                            Disable
                        </option>

                    </select>

                </div>

                <div class="col-md-1">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="user_id"
                            name="seller_id" onchange="bulk_Action()">
                        <option value="">{{ translate('Sellers') }}</option>
                        @foreach (App\Models\User::where('user_type', '=', 'seller')->get() as $key => $seller)
                            <option value="{{ $seller->id }}">
                                {{ $seller->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="sup_id"
                            name="sup_id" onchange="bulk_Action()">
                        <option value="">{{ translate('Supplier') }}</option>
                        @foreach ( \App\Models\Supplier::get() as $key => $supplier)
                            <option value="{{ $supplier->id }}">
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0"
                            name="cat_id" onchange="bulk_Action()" style="overflow: scroll;">
                        <option value="">{{ translate('Category') }}</option>
                        @foreach ( \App\Models\Category::get() as $key => $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="fulffiled"
                            name="fulf" onchange="bulk_Action()">
                        <option value="">{{ translate('Fulffiled By') }}</option>
                        <option value="Express">
                            Express
                        </option>
                        <option value="Mercado">
                            Mercado
                        </option>

                    </select>
                </div>

                @can('product_delete')
                    <div class="dropdown mb-2 mb-md-0">
                        <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                            {{translate('Bulk Action')}}
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item confirm-alert" href="javascript:void(0)"
                               data-target="#bulk-delete-modal"> {{translate('Delete selection')}}</a>

                            <a class="dropdown-item confirm-alert" href="javascript:void(0)"
                            >
                                <input onchange="bulk_Action()" type="text" name="discount" class="form-control"
                                       placeholder="{{translate('Add Discount')}}">


                            </a>


                        </div>

                @endcan
                </div>
                <div class="col-md-2">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="type" id="type"
                            onchange="sort_products()">
                        <option value="">{{ translate('Sort By') }}</option>

                        <option value="unit_price,desc"
                                @isset($col_name , $query) @if($col_name == 'unit_price' && $query == 'desc') selected @endif @endisset>{{translate('Price (High > Low)')}}</option>
                        <option value="unit_price,asc"
                                @isset($col_name , $query) @if($col_name == 'unit_price' && $query == 'asc') selected @endif @endisset>{{translate('Price (Low > High)')}}</option>


                        <option value="cost,desc"
                                @isset($col_name , $query) @if($col_name == 'cost' && $query == 'desc') selected @endif @endisset>{{translate('Cost (High > Low)')}}</option>
                        <option value="cost,asc"
                                @isset($col_name , $query) @if($col_name == 'cost' && $query == 'asc') selected @endif @endisset>{{translate('Cost (Low > High)')}}</option>


                        <option value="current_stock,desc"
                                @isset($col_name , $query) @if($col_name == 'current_stock' && $query == 'desc') selected @endif @endisset>{{translate('Quantity (High > Low)')}}</option>
                        <option value="current_stock,asc"
                                @isset($col_name , $query) @if($col_name == 'current_stock' && $query == 'asc') selected @endif @endisset>{{translate('Quantity (Low > High)')}}</option>


                        <option value="name,desc"
                                @isset($col_name , $query) @if($col_name == 'name' && $query == 'desc') selected @endif @endisset>{{translate('Name (Last > First)')}}</option>
                        <option value="name,asc"
                                @isset($col_name , $query) @if($col_name == 'name' && $query == 'asc') selected @endif @endisset>{{translate('Name (Last > First)')}}</option>

                        <option value="id,desc"
                                @isset($col_name , $query) @if($col_name == 'id' && $query == 'desc') selected @endif @endisset>{{translate(' ID (High > Low)')}}</option>
                        <option value="id,asc"
                                @isset($col_name , $query) @if($col_name == 'id' && $query == 'asc') selected @endif @endisset>{{translate(' ID (Low > High)')}}</option>


                        <option value="fulffiled,desc"
                                @isset($col_name , $query) @if($col_name == 'fulffiled' && $query == 'desc') selected @endif @endisset>{{translate('Fulffiled (Last > First)')}}</option>
                        <option value="fulffiled,asc"
                                @isset($col_name , $query) @if($col_name == 'fulffiled' && $query == 'asc') selected @endif @endisset>{{translate('Fulffiled (Last > First)')}}</option>


                        <option value="published,desc"
                                @isset($col_name , $query) @if($col_name == 'published' && $query == 'desc') selected @endif @endisset>{{translate('Published (Enable)')}}</option>


                        <option value="user_id,desc"
                                @isset($col_name , $query) @if($col_name == 'user_id' && $query == 'desc') selected @endif @endisset>{{translate('Seller (Last > First)')}}</option>
                        <option value="user_id,asc"
                                @isset($col_name , $query) @if($col_name == 'user_id' && $query == 'asc') selected @endif @endisset>{{translate('Seller (Last > First)')}}</option>

                        <option value="supplier_id,desc"
                                @isset($col_name , $query) @if($col_name == 'supplier_id' && $query == 'desc') selected @endif @endisset>{{translate('Supplier (Last > First)')}}</option>
                        <option value="supplier_id,asc"
                                @isset($col_name , $query) @if($col_name == 'supplier_id' && $query == 'asc') selected @endif @endisset>{{translate('Supplier (Last > First)')}}</option>

                        <option value="category_id,desc"
                                @isset($col_name , $query) @if($col_name == 'category_id' && $query == 'desc') selected @endif @endisset>{{translate('Category (Last > First)')}}</option>
                        <option value="category_id,asc"
                                @isset($col_name , $query) @if($col_name == 'category_id' && $query == 'asc') selected @endif @endisset>{{translate('Category (Last > First)')}}</option>


                        <option value="rating,desc"
                                @isset($col_name , $query) @if($col_name == 'rating' && $query == 'desc') selected @endif @endisset>{{translate('Rating (High > Low)')}}</option>
                        <option value="rating,asc"
                                @isset($col_name , $query) @if($col_name == 'rating' && $query == 'asc') selected @endif @endisset>{{translate('Rating (Low > High)')}}</option>
                        <option value="num_of_sale,desc"
                                @isset($col_name , $query) @if($col_name == 'num_of_sale' && $query == 'desc') selected @endif @endisset>{{translate('Num of Sale (High > Low)')}}</option>
                        <option value="num_of_sale,asc"
                                @isset($col_name , $query) @if($col_name == 'num_of_sale' && $query == 'asc') selected @endif @endisset>{{translate('Num of Sale (Low > High)')}}</option>


                    </select>
                </div>


                <div class="col-md-1">

                    <a data-toggle="collapse" data-target="#demo">

                        <i class='fas fa-filter clickable-icon' style='font-size:24px'></i>
                    </a>

                </div>


            </div>
            <div id="demo" class="collapse">
                <div class="card-header row gutters-5" id="showrow">

                    <div class="col-md-2">
                        <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="user_id"
                                name="user_id">
                            <option value="">{{ translate('All Sellers') }}</option>
                            @foreach (App\Models\User::where('user_type', '=', 'seller')->get() as $key => $seller)
                                <option value="{{ $seller->id }}" @if ($seller->id == $seller_id) selected @endif>
                                    {{ $seller->shop?->name }} ({{ $seller->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-md-2">
                        <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="supplier_id"
                                name="supplier_id">
                            <option value="">{{ translate('All Supplier') }}</option>
                            @foreach ( \App\Models\Supplier::get() as $key => $supplier)
                                <option value="{{ $supplier->id }}" @if ($supplier->id == $supplier_id) selected @endif>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="status"
                                name="published">
                            <option value="">{{ translate('All Status') }}</option>
                            <option value="1">
                                Enable
                            </option>
                            <option value="0">
                                Disable
                            </option>

                        </select>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control form-control-sm" id="search"
                                   name="sku" placeholder="{{ translate('Search By SKU') }}">
                        </div>
                    </div>
                    <div class="col-md-3" style="position: relative; border: 1px solid #dfdfe6;
    border-radius: 8px;">
                        <select class="form-control"
                                name="category_id[]" multiple="multiple" id="categoryMultiSelect">
                            <option selected value="">please Select Category</option>
                            @foreach (App\Models\Category::where('parent_id',0)->get() as $key => $cat)
                                <option
                                    value="{{ $cat->id }}"> {{ $cat->name ?? '' }} @foreach (App\Models\Category::where('parent_id',$cat->id)->get() as $key => $cats)
                                    <option value="{{ $cats->id }}"><p>&nbsp;</p>-> {{ $cats->name ?? '' }}
                                    @foreach (App\Models\Category::where('parent_id',$cats->id)->get() as $key => $category)
                                        <option value="{{$category->id}}">
                                            <p></p>
                                            <p>&nbsp;</p>
                                            <p>&nbsp;</p>->-> {{ $category->name ?? '' }}
                                        </option>
                                        @endforeach
                                        </option>
                                        @endforeach
                                        </option>
                                    @endforeach
                        </select>
                    </div>


                </div>


                <div class="card-header row gutters-5">


                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="fulffiled"
                                    name="fulffiled">
                                <option value="">{{ translate('Fulffiled By') }}</option>
                                <option value="Express">
                                    Express
                                </option>
                                <option value="Mercado">
                                    Mercado
                                </option>

                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control form-control-sm" id="search"
                                   name="quantity_from" placeholder="{{ translate('Search By Quantity From') }}">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control form-control-sm" id="search"
                                   name="quantity_to" placeholder="{{ translate('Search By Quantity To') }}">
                        </div>
                    </div>


                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control form-control-sm" id="search"
                                   name="quantitysold_from"
                                   placeholder="{{ translate('Search By QuantitySold from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control form-control-sm" id="search"
                                   name="quantitysold_to" placeholder="{{ translate('Search By QuantitySold To') }}">
                        </div>
                    </div>

                    <div class="col-md-2  ml-auto">
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-success"> Search</button>
                        </div>
                    </div>


                </div>

            </div>


            <div class="card-body table-responsive" style="position: relative;">
                <div class="row">
                    <div class="col-md-3">
                        <div><label>Show
                                <select onchange="AllPaginate(this)"

                                        class="form-control input-sm">
                                    <option value="{{ url('admin/products/all?paginate=10') }}"
                                            @if($paginate==10) selected @endif>
                                        10
                                    </option>
                                    <option value="{{ url('admin/products/all?paginate=25') }}"
                                            @if($paginate==25) selected @endif>25
                                    </option>
                                    <option value="{{ url('admin/products/all?paginate=50') }}"
                                            @if($paginate==50) selected @endif>50
                                    </option>
                                    <option value="{{ url('admin/products/all?paginate=100') }}"
                                            @if($paginate==100) selected @endif>100
                                    </option>
                                </select> entries</label></div>
                    </div>
                    <div class="col-md-3">
                    </div>
                    <div class="col-md-3">
                    </div>


                    <div class="col-md-3">
                        <div class="aiz-pagination" style="
    display: flex;
    align-items: center;
    border: 1px solid;
    width: fit-content;
    position: relative;
    right: 20px;
    border-radius: 20px;
    z-index: 5;
    top: 0;
">

                            {{ $products->appends(request()->input())->links() }}

                        </div>
                    </div>
                </div>
                {{--        <div style="overflow:auto;">--}}

                <table id="ManageUsers"
                       class="table table-bordered table-striped display responsive table aiz-table mb-0"
                       style="width:100%">

                    <thead>
                    <tr>
                        @if(auth()->user()->can('product_delete'))
                            <th>
                                <div class="form-group">
                                    <div class="aiz-checkbox-inline">
                                        <label class="form-control">
                                            <input type="checkbox" class="check-all" name="id[]">
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </th>
                        @else
                            <th data-breakpoints="lg">#</th>
                        @endif
                        <th>{{translate('ID')}}</th>

                        <th>{{translate('Picture')}}</th>
                        <th>{{translate('Name')}}</th>
                        <th>{{translate('SKU')}}</th>
                        <th>{{translate('COST')}}</th>
                        <th>{{translate('Price')}}</th>
                        <th>{{translate('SP')}}</th>
                        {{--                <th>{{translate('Visibility')}}</th>--}}
                        <th>{{translate('Quantity')}}</th>
                        <th>{{translate('QS')}}</th>
                        <th>{{translate('Supplier')}}</th>
                        <th>{{translate('Seller')}}</th>
                        <th>{{translate('Fulffiled')}}</th>
                        <th data-breakpoints="lg">{{translate('Published')}}</th>

                        <th data-breakpoints="sm" class="text-right">{{translate('Options')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $key => $product)
                        <tr>
                            @if(auth()->user()->can('product_delete'))
                                <td>
                                    <div class="form-group d-inline-block">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" class="check-one" name="id[]"
                                                   value="{{$product->id}}">
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </td>
                            @else
                                <td>{{ ($key+1) + ($products->currentPage() - 1)*$products->perPage() }}</td>
                            @endif
                            <td>{{ $product->id }}</td>


                            <td>


                                <img src="{{ uploaded_asset($product->thumbnail_img)}}" alt="Image"
                                     class="size-50px img-fit">


                            </td>
                            <td>


                                <span class="text-muted text-truncate-2">{{ $product->getTranslation('name') }}</span>


                            </td>

                            <td>{{ optional($product->stocks->first())->sku }}</td>
                            <td>{{ $product->cost }}</td>
                            <td>{{ $product->unit_price }}</td>
                            <td>{{ $product->unit_price-$product->discount }}</td>
                            {{--                    <td>{{ $product->Visibility}}</td>--}}
                            <td>{{ $product->current_stock}}</td>
                            <td>
                                {{\App\Models\OrderDetail::where('product_id',$product->id)->count() ?? 0}}

                                {{--        //  {{ $product->low_stock_quantity}}--}}


                            </td>
                            <td>{{ $product->supplier->name ?? ''}}</td>
                            <td>{{ $product->user->name ?? ''}}</td>
                            <td>{{ $product->fulffiled ?? ''}}</td>


                            <td>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input onchange="update_published(this)" value="{{ $product->id }}"
                                           type="checkbox" <?php if ($product->published == 1) echo "checked"; ?> >
                                    <span class="slider round"></span>
                                </label>
                            </td>


                            <td class="text-right">
                                <a class="btn btn-soft-success btn-icon btn-circle btn-sm"
                                   href="{{ route('product',$product->slug) }}" target="_blank"
                                   title="{{ translate('View') }}">
                                    <i class="las la-eye"></i>
                                </a>
                                @can('product_edit')
                                    @if ($type == 'Seller')
                                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                           href="{{route('products.seller.edit', ['id'=>$product->id, 'lang'=>env('DEFAULT_LANGUAGE')] )}}"
                                           title="{{ translate('Edit') }}">
                                            <i class="las la-edit"></i>
                                        </a>
                                    @else
                                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                           href="{{route('products.admin.edit', ['id'=>$product->id, 'lang'=>env('DEFAULT_LANGUAGE')] )}}"
                                           title="{{ translate('Edit') }}">
                                            <i class="las la-edit"></i>
                                        </a>
                                    @endif
                                @endcan
                                @can('product_duplicate')
                                    <a class="btn btn-soft-warning btn-icon btn-circle btn-sm"
                                       href="{{route('products.duplicate', ['id'=>$product->id, 'type'=>$type]  )}}"
                                       title="{{ translate('Duplicate') }}">
                                        <i class="las la-copy"></i>
                                    </a>
                                @endcan
                                @can('product_delete')
                                    <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                       data-href="{{route('products.destroy', $product->id)}}"
                                       title="{{ translate('Delete') }}">
                                        <i class="las la-trash"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{--        <div class="aiz-pagination">--}}
                {{--            {{ $products->appends(request()->input())->links() }}--}}
                {{--        </div>--}}

            </div>

        </form>

    </div>
@endsection

@section('modal')
    <!-- Delete modal -->
    @include('modals.delete_modal')
    <!-- Bulk Delete modal -->
    @include('modals.bulk_delete_modal')
@endsection


@section('script')
    <!-- Include jQuery -->
    {{--    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>--}}
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/js/bootstrap-multiselect.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.8/js/dataTables.bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.1.0/js/dataTables.responsive.js"></script>


    <!-- Include Select2 -->
    {{--    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />--}}
    {{--    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>--}}
    <script type="text/javascript">
        // var deleteclassform = document.querySelector(".dataTables_wrapper");
        //
        // deleteclassform.classList.remove("form-inline");

        $(function () {
            $('#ManageUsers').DataTable({
                paging: false,
                lengthChange: false,
                searching: false,
                ordering: false,
                info: false,
                autoWidth: true,
                responsive: true,
                scroll: true,
                // dom: 'ilfrtp'
            });
        });

        function AllPaginate(selectElement) {

            console.log("datata", selectElement);
            const selectedUrl = selectElement.value; // Get the selected URL
            if (selectedUrl) {
                window.location.href = selectedUrl; // Redirect to the selected URL
            }
        }


        $(document).on("change", ".check-all", function () {
            if (this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function () {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function () {
                    this.checked = false;
                });
            }

        });

        $(document).ready(function () {
            //$('#container').removeClass('mainnav-lg').addClass('mainnav-sm');
        });

        function update_todays_deal(el) {

            if ('{{env('DEMO_MODE')}}' == 'On') {
                AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
                return;
            }

            if (el.checked) {
                var status = 1;
            } else {
                var status = 0;
            }
            $.post('{{ route('products.todays_deal') }}', {
                _token: '{{ csrf_token() }}',
                id: el.value,
                status: status
            }, function (data) {
                if (data == 1) {
                    AIZ.plugins.notify('success', '{{ translate('Todays Deal updated successfully') }}');
                } else {
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

        function update_published(el) {

            if ('{{env('DEMO_MODE')}}' == 'On') {
                AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
                return;
            }

            if (el.checked) {
                var status = 1;
            } else {
                var status = 0;
            }
            $.post('{{ route('products.published') }}', {
                _token: '{{ csrf_token() }}',
                id: el.value,
                status: status
            }, function (data) {
                if (data == 1) {
                    AIZ.plugins.notify('success', '{{ translate('Published products updated successfully') }}');
                } else {
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

        function update_approved(el) {

            if ('{{env('DEMO_MODE')}}' == 'On') {
                AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
                return;
            }

            if (el.checked) {
                var approved = 1;
            } else {
                var approved = 0;
            }
            $.post('{{ route('products.approved') }}', {
                _token: '{{ csrf_token() }}',
                id: el.value,
                approved: approved
            }, function (data) {
                if (data == 1) {
                    AIZ.plugins.notify('success', '{{ translate('Product approval update successfully') }}');
                } else {
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

        function update_featured(el) {
            if ('{{env('DEMO_MODE')}}' == 'On') {
                AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
                return;
            }

            if (el.checked) {
                var status = 1;
            } else {
                var status = 0;
            }
            $.post('{{ route('products.featured') }}', {
                _token: '{{ csrf_token() }}',
                id: el.value,
                status: status
            }, function (data) {
                if (data == 1) {
                    AIZ.plugins.notify('success', '{{ translate('Featured products updated successfully') }}');
                } else {
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

        function sort_products(el) {
            $('#sort_products').submit();
        }

        function bulk_Action() {
            var data = new FormData($('#sort_products')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('bulk-product-delete')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response == 1) {
                        location.reload();
                    }
                }
            });
        }

        $(document).ready(function () {
            $('#categoryMultiSelect').multiselect({
                includeSelectAllOption: true,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                buttonWidth: '300px',
                buttonHeight: '200px',
                buttonTagInScope: true,
                tag: true,
                width: 'resolve',
                placeholder: " Please Select Category",
                allowClear: true,
                scroll: true

            });


        });

    </script>
@endsection
