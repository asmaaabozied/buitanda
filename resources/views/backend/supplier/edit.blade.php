@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Edit Supplier Information')}}</h5>
</div>

<div class="col-lg-6 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Supplier Information')}}</h5>
        </div>

        <div class="card-body">
          <form action="{{ route('supplier.update', $supplier->id) }}" method="POST">
                <input name="_method" type="hidden" value="PATCH">
                @csrf
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="name">{{translate('Name')}} <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('Name')}}" id="name" name="name" class="form-control" value="{{$supplier->name}}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="email">{{translate('Email Address')}}</label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('Email Address')}}" id="email" name="email" class="form-control" value="{{$supplier->email}}" >
                    </div>
                </div>


              <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="email">{{translate('Phone')}}</label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('Phone')}}" id="phone" name="phone" class="form-control" value="{{$supplier->phone}}" >
                    </div>
                </div>
    <div class="form-group row">
                  <label class="col-sm-3 col-from-label" for="password">{{translate('address')}}</label>
                  <div class="col-sm-9">
                      <input type="text" placeholder="{{translate('address')}}" id="address" name="address" class="form-control"  value="{{$supplier->address}}" >
                  </div>
              </div>
                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
