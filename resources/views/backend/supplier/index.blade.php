@extends('backend.layouts.app')

@section('content')

@php
    $route = Route::currentRouteName() == 'supplier.index' ? 'supplier.index' : 'supplier.index';
@endphp

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3"> {{translate('All Supplier')}} </h1>
        </div>
{{--      //  @if(auth()->user()->can('add_seller') && ($route == 'all_seller_route'))--}}
            <div class="col text-right">
                <a href="{{ route('supplier.create') }}" class="btn btn-circle btn-info">
                    <span>{{ translate('Add New Supplier')}}</span>
                </a>
            </div>
{{--        @endif--}}
    </div>
</div>

<div class="card">
    <form class="" id="sort_sellers" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{translate('Supplier')}}</h5>
            </div>

            <div class="col-md-3">
                <div class="form-group mb-0">
                  <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type name or email & Enter') }}">
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                <tr>
                    <th>
{{--                      //  @if(auth()->user()->can('delete_seller') && ($route == 'all_seller_route'))--}}
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
{{--                            </div>--}}
{{--                        @else--}}
{{--                            #--}}
{{--                        @endif--}}
                    </th>
                    <th>{{translate('ID')}}</th>
                    <th>{{translate('Name')}}</th>
                    <th data-breakpoints="lg">{{translate('Email Address')}}</th>

                    <th data-breakpoints="lg">{{translate('Phone')}}</th>
                    <th data-breakpoints="lg"> {{translate('Address')}}</th>
                    <th width="10%">{{translate('Options')}}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($suppliers as $key => $supplier)
                    <tr>

                        <td>
{{--                          //  @if(auth()->user()->can('delete_seller') && ($route == 'all_seller_route'))--}}
                                <div class="form-group">
                                    <div class="aiz-checkbox-inline">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" class="check-one" name="id[]" >
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </div>
{{--                            @else--}}
{{--                                {{ ($key+1) + ($shops->currentPage() - 1)*$shops->perPage() }}--}}
{{--                            @endif--}}
                        </td>
                        <td>{{$supplier->id}}</td>
                        <td>
                            <div class="row gutters-5  mw-100 align-items-center">

                                <div class="col">
                                    <span class="text-truncate-2">{{ $supplier->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{$supplier->email}}</td>
                        <td>{{$supplier->phone}}</td>

                        <td>{{$supplier->address}}</td>
<td>

    <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('supplier.edit',$supplier->id)}}" title="{{ translate('Edit') }}">
        <i class="las la-edit"></i>
    </a>
    <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('supplier.delete',$supplier->id)}}" title="{{ translate('Delete') }}">
        <i class="las la-trash"></i>
    </a>
</td>

                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
              {{ $suppliers->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

@endsection

@section('modal')
	<!-- Delete Modal -->
	@include('modals.delete_modal')
    <!-- Bulk Delete modal -->
    @include('modals.bulk_delete_modal')

	<!-- Seller Profile Modal -->
	<div class="modal fade" id="profile_modal">
		<div class="modal-dialog">
			<div class="modal-content" id="profile-modal-content">

			</div>
		</div>
	</div>

	<!-- Seller Payment Modal -->
	<div class="modal fade" id="payment_modal">
	    <div class="modal-dialog">
	        <div class="modal-content" id="payment-modal-content">

	        </div>
	    </div>
	</div>



@endsection

@section('script')
    <script type="text/javascript">
        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });



        function update_approved(el){
            if('{{env('DEMO_MODE')}}' == 'On'){
                AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
                return;
            }

            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('sellers.approved') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Approved sellers updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

        function sort_sellers(el){
            $('#sort_sellers').submit();
        }

        function confirm_ban(url)
        {
            if('{{env('DEMO_MODE')}}' == 'On'){
                AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
                return;
            }

            $('#confirm-ban').modal('show', {backdrop: 'static'});
            document.getElementById('confirmation').setAttribute('href' , url);
        }

        function confirm_unban(url)
        {
            if('{{env('DEMO_MODE')}}' == 'On'){
                AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
                return;
            }

            $('#confirm-unban').modal('show', {backdrop: 'static'});
            document.getElementById('confirmationunban').setAttribute('href' , url);
        }

        function bulk_delete() {
            var data = new FormData($('#sort_sellers')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('bulk-seller-delete')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response == 1) {
                        location.reload();
                    }
                }
            });
        }

        // Set Commission
        function set_commission(shop_id){
            var sellerIds = [];
            sellerIds.push(shop_id);
            $('#seller_ids').val(sellerIds);
            $('#set_seller_commission').modal('show', {backdrop: 'static'});
        }

        // Set seller bulk commission
        function set_bulk_commission(){
            var sellerIds = [];
            $(".check-one[name='id[]']:checked").each(function() {
                sellerIds.push($(this).val());
            });
            if(sellerIds.length > 0){
                $('#seller_ids').val(sellerIds);
                $('#set_seller_commission').modal('show', {backdrop: 'static'});
            }
            else{
                AIZ.plugins.notify('danger', '{{ translate('Please Select Seller first.') }}');
            }
        }


        // Edit seller custom followers
        function editCustomFollowers(shop_id, custom_followers){
            $('#shop_id').val(shop_id);
            $('#custom_followers').val(custom_followers);
            $('#edit_seller_custom_followers').modal('show', {backdrop: 'static'});
        }

    </script>
@endsection
