@extends('backend.layouts.app')

@section('content')


    <div class="aiz-titlebar text-left mt-2 mb-3">
        <h5 class="mb-0 h6">{{translate('Add New Supplier')}}</h5>
    </div>

    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Supplier Information')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('supplier.store') }}" method="POST">
                    @csrf
                    <div class="form-group row">
                        <label class="col-sm-2 col-from-label" for="name">
                            {{translate('Name')}} <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control @if ($errors->has('name')) is-invalid @endif"
                                   name="name" value="{{ old('name') }}" placeholder="{{ translate('Name') }}" required>
                            @if ($errors->has('name'))
                                <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('name') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-from-label" for="email">
                            {{ translate('Email') }} 
                        </label>
                        <div class="col-sm-10">
                            <input type="email"
                                   class="form-control rounded-0 @if($errors->has('email')) is-invalid @endif"
                                   value="{{ old('email') }}" placeholder="{{  translate('Email') }}" name="email">
                            @if ($errors->has('email'))
                                <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
             
                    <div class="form-group row">
                        <label class="col-sm-2 col-from-label" for="email">
                            {{ translate('Phone') }} 
                        </label>
                        <div class="col-sm-10">
                            <input type="text"
                                   class="form-control rounded-0 @if($errors->has('phone')) is-invalid @endif"
                                   value="{{ old('phone') }}" placeholder="{{  translate('Phone') }}" name="phone">
                            @if ($errors->has('phone'))
                                <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('phone') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
       <div class="form-group row">
                        <label class="col-sm-2 col-from-label" for="email">
                            {{ translate('Address') }}
                        </label>
                        <div class="col-sm-10">
                            <input type="text"
                                   class="form-control rounded-0 @if($errors->has('address')) is-invalid @endif"
                                   value="{{ old('address') }}" placeholder="{{  translate('Address') }}" name="address">
                            @if ($errors->has('address'))
                                <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('address') }}</strong>
                            </span>
                            @endif
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
