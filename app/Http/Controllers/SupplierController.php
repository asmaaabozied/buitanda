<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use App\Notifications\ShopVerificationNotification;
use App\Services\PreorderService;
use App\Utility\EmailUtility;
use Cache;
use Illuminate\Support\Facades\Notification;

class SupplierController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search = $request->search ?? null;
        $approved = $request->approved_status ?? null;
        $verification_status =  $request->verification_status ?? null;

        $supplier = Supplier::latest();

        if ($sort_search != null || $verification_status != null) {
            if($sort_search != null){
                $suppliers = $supplier
                        ->orWhere('name', 'like', '%' . $sort_search . '%')
                        ->orWhere('phone', 'like', '%' . $sort_search . '%')
                        ->orWhere('address', 'like', '%' . $sort_search . '%')
                        ->orWhere('email', 'like', '%' . $sort_search . '%');

            }

        }else{
            $supplier = Supplier::latest();

        }

        $suppliers = $supplier->paginate(15);
        return view('backend.supplier.index', compact('suppliers', 'sort_search', 'approved', 'verification_status'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.supplier.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        $request->validate([
            'name' => 'required|max:255',
            'phone' => 'nullable',
            'email' => 'nullable|email|unique:suppliers',

        ],
        [
            'name.required' => translate('Name is required'),
            'name.max' => translate('Max 255 Character'),
            'email.email' => translate('Email must be a valid email address'),
            'email.unique' => translate('An supplier exists with this email'),
        ]);



        $user           = new Supplier();
        $user->name     = $request->name;
        $user->email    = $request->email;
        $user->phone    = $request->phone;
        $user->address    = $request->address;
      if(!empty($request->password)){
          $user->password = Hash::make($request->password);

      }
        $user->save();

        flash(translate('Supplier has been added successfully'))->success();
        return redirect()->route('supplier.index');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('backend.supplier.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $supplier->name = $request->name;
        $supplier->email = $request->email;
        $supplier->phone = $request->phone;
        $supplier->address = $request->address;
        if (strlen($request->password) > 0) {
            $supplier->password = Hash::make($request->password);
        }
        if ($supplier->save()) {
            flash(translate('Supplier has been updated successfully'))->success();
            return redirect()->route('supplier.index');
        }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        flash(translate('Supplier has been deleted successfully'))->success();
        return redirect()->route('supplier.index');


    }

    public function bulk_seller_delete(Request $request)
    {
        if ($request->id) {
            foreach ($request->id as $shop_id) {
                $this->destroy($shop_id);
            }
        }

        return 1;
    }

}
