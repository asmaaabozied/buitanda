@extends('backend.layouts.app')
{{--<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />--}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js" crossorigin="anonymous"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/css/bootstrap-multiselect.css" rel="stylesheet">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
@section('content')

    @php
        CoreComponentRepository::instantiateShopRepository();
        CoreComponentRepository::initializeCache();
    @endphp

    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="row align-items-center">
            <div class="col-auto">
                <h1 class="h3">{{translate('All products')}}</h1>
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
                <div class="col">
                    <h5 class="mb-md-0 h6">{{ translate('All Product') }}</h5>
                </div>
                <div class="col-md-2 ml-auto">
                    <div class="form-group mb-0">
                        <input type="text" class="form-control form-control-sm" onkeyup="sort_products()" id="search"
                               name="search" @isset($sort_search) value="{{ $sort_search }}"
                               @endisset placeholder="{{ translate('Search By Keyboard') }}">
                    </div>
                </div>


                @can('product_delete')
                    <div class="dropdown mb-2 mb-md-0">
                        <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                            {{translate('Bulk Action')}}
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item confirm-alert" href="javascript:void(0)"
                               data-target="#bulk-delete-modal"> {{translate('Delete selection')}}</a>
                        </div>
                    </div>
                @endcan
                <div class="col-md-2 ml-auto">
                    <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="type" id="type"
                            onchange="sort_products()">
                        <option value="">{{ translate('Sort By') }}</option>
                        <option value="rating,desc"
                                @isset($col_name , $query) @if($col_name == 'rating' && $query == 'desc') selected @endif @endisset>{{translate('Rating (High > Low)')}}</option>
                        <option value="rating,asc"
                                @isset($col_name , $query) @if($col_name == 'rating' && $query == 'asc') selected @endif @endisset>{{translate('Rating (Low > High)')}}</option>
                        <option value="num_of_sale,desc"
                                @isset($col_name , $query) @if($col_name == 'num_of_sale' && $query == 'desc') selected @endif @endisset>{{translate('Num of Sale (High > Low)')}}</option>
                        <option value="num_of_sale,asc"
                                @isset($col_name , $query) @if($col_name == 'num_of_sale' && $query == 'asc') selected @endif @endisset>{{translate('Num of Sale (Low > High)')}}</option>
                        <option value="unit_price,desc"
                                @isset($col_name , $query) @if($col_name == 'unit_price' && $query == 'desc') selected @endif @endisset>{{translate('Base Price (High > Low)')}}</option>
                        <option value="unit_price,asc"
                                @isset($col_name , $query) @if($col_name == 'unit_price' && $query == 'asc') selected @endif @endisset>{{translate('Base Price (Low > High)')}}</option>
                    </select>
                </div>


                <div class="col-md-2 ml-auto">

                    {{--                 //   <a class="btn btn-slide-primary" id="buttonfilter" onclick="myFunction()"> Filter</a>--}}
                    <a data-toggle="collapse" data-target="#demo" >

                        <i class='fas fa-filter' style='font-size:24px'></i>
                    </a>

                </div>

            </div>
            <div id="demo" class="collapse">
                <div class="card-header row gutters-5" id="showrow">

                    <div class="col-md-2 ml-auto">
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


                    <div class="col-md-2 ml-auto">
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
                    <div class="col-md-2 ml-auto">

                        {{--                        <select id="categoryMultiSelect" multiple="multiple" style="width: 300px;">--}}
                        {{--                            <option value="livros">Livros</option>--}}
                        {{--                            <option value="mobilias">Mobílias</option>--}}
                        {{--                            <option value="armarios">Armários</option>--}}
                        {{--                            <option value="cadeiras">Cadeiras</option>--}}
                        {{--                            <option value="cofre">Cofre</option>--}}
                        {{--                        </select>--}}
                        {{--                    </div>--}}

                        {{--                        <div class="col-md-2 ml-auto">--}}
                        {{--                        <select id="categorySelect" multiple="multiple" style="width: 300px;">--}}
                        {{--                            <option value="livros">Livros</option>--}}
                        {{--                            <option value="mobilias">Mobílias</option>--}}
                        {{--                            <option value="armarios">Armários</option>--}}
                        {{--                            <option value="cadeiras">Cadeiras</option>--}}
                        {{--                            <option value="cofre">Cofre</option>--}}
                        {{--                        </select>--}}


                        <select  class="form-control"
                                 name="category_id[]" multiple="multiple" id="categoryMultiSelect">

                            @foreach (App\Models\Category::where('parent_id',0)->get() as $key => $cat)
                                <option value="{{ $cat->id }}">
                                {{ $cat->name ?? '' }}
                                @foreach (App\Models\Category::where('parent_id',$cat->id)->get() as $key => $cats)

                                    {{--                                <optgroup>--}}

                                    <option value="{{ $cats->id }}">
                                        -> {{ $cats->name ?? '' }}</option>

                                    {{--                                </optgroup>--}}
                                    @endforeach
                                    </option>
                                @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 ml-auto">
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

                    <div class="col-md-2 ml-auto">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control form-control-sm"  id="search"
                                   name="sku"  placeholder="{{ translate('Search By SKU') }}">
                        </div>
                    </div>

                    <div class="col-md-2 ml-auto">
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
                        </div>  </div>
                </div>




                <div class="card-header row gutters-5">
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control form-control-sm"  id="search"
                                   name="quantity_from"  placeholder="{{ translate('Search By Quantity From') }}">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control form-control-sm"  id="search"
                                   name="quantity_to"  placeholder="{{ translate('Search By Quantity To') }}">
                        </div>
                    </div>


                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control form-control-sm"  id="search"
                                   name="quantitysold_from"  placeholder="{{ translate('Search By QuantitySold from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <input type="text" class="form-control form-control-sm"  id="search"
                                   name="quantitysold_to"  placeholder="{{ translate('Search By QuantitySold To') }}">
                        </div>
                    </div>

                    <div class="col-md-2  ml-auto">
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-success"> Search</button>
                        </div>
                    </div>


                </div>

            </div>

        </form>
    </div>




    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
            <tr>
                @if(auth()->user()->can('product_delete'))
                    <th>
                        <div class="form-group">
                            <div class="aiz-checkbox-inline">
                                <label class="aiz-checkbox">
                                    <input type="checkbox" class="check-all">
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
                <th>{{translate('Special Price')}}</th>
                {{--                <th>{{translate('Visibility')}}</th>--}}
                <th>{{translate('Quantity')}}</th>
                <th>{{translate('Qauntity Sold')}}</th>
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
        <div class="aiz-pagination">
            {{ $products->appends(request()->input())->links() }}
        </div>
    </div>
    {{--        </form>--}}
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/js/bootstrap-multiselect.min.js"></script>

    <!-- Include Select2 -->
    {{--    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />--}}
    {{--    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>--}}
    <script type="text/javascript">


        function myFunction() {
            var x = document.getElementById("showrow");
            if (x.style.display === "none") {
                x.style.display = "block";
            } else {
                x.style.display = "none";
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

        function bulk_delete() {
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

        // $(document).ready(function() {
        //     $("#categorySelect").select2({
        //         placeholder: "Select Categories",
        //         allowClear: true
        //     });
        // });

        $(document).ready(function() {
            $('#categoryMultiSelect').multiselect({
                includeSelectAllOption: true,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                buttonWidth: '300px',

                doneButtonHeight: '300px',
                tag:true,
                width: 'resolve',
                placeholder: " Please Select Category",
                allowClear: true,
                scroll:true

            });
        });
        // $('#categoryMultiSelect').select2({
        //         includeSelectAllOption: true,
        //         enableFiltering: true,
        //         enableCaseInsensitiveFiltering: true,
        //         buttonWidth: '300px',
        //         tag:true,
        //         width: 'resolve',
        //         placeholder: " Please Select Category",
        //     allowClear: true
        //
        // });
        // });
        // $(document).ready(function() {
        //     $('.js-example-basic-multiple').select2({
        //         placeholder: " Please Select Category",
        //       // tags: true,
        //       //  tokenSeparators: [',', ' '],
        //         width: 'resolve',
        //
        //
        //
        //
        //     });
        //
        // });
    </script>
@endsection
