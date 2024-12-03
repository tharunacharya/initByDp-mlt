<?php

/*
@copyright

Fleet Manager v6.5

Copyright (C) 2017-2023 Hyvikk Solutions <https://hyvikk.com/> All rights reserved.
Design and developed by Hyvikk Solutions <https://hyvikk.com/>

 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customers as CustomerRequest;
use App\Http\Requests\ImportRequest;
use App\Imports\CustomerImport;
use App\Model\User;
use Auth;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Validator;

class CustomersController extends Controller {
	public function __construct() {
		// $this->middleware(['role:Admin']);
		$this->middleware('permission:Customer add', ['only' => ['create']]);
		$this->middleware('permission:Customer edit', ['only' => ['edit']]);
		$this->middleware('permission:Customer delete', ['only' => ['bulk_delete', 'destroy']]);
		$this->middleware('permission:Customer list');
		$this->middleware('permission:Customer import', ['only' => ['importCutomers']]);
	}

	public function importCutomers(ImportRequest $request) {

		$file = $request->excel;
		$destinationPath = './assets/samples/'; // upload path
		$extension = $file->getClientOriginalExtension();
		$fileName = Str::uuid() . '.' . $extension;
		$file->move($destinationPath, $fileName);
		// dd($fileName);
		Excel::import(new CustomerImport, 'assets/samples/' . $fileName);

		// $excel = Importer::make('Excel');
		// $excel->load('assets/samples/' . $fileName);
		// $collection = $excel->getCollection()->toArray();
		// array_shift($collection);
		// // dd($collection);
		// foreach ($collection as $customer) {
		//     if ($customer[3] != null) {
		//         $id = User::create([
		//             "name" => $customer[0] . " " . $customer[1],
		//             "email" => $customer[3],
		//             "password" => bcrypt($customer[6]),
		//             "user_type" => "C",
		//             "api_token" => str_random(60),
		//         ])->id;
		//         $user = User::find($id);
		//         $user->first_name = $customer[0];
		//         $user->last_name = $customer[1];
		//         $user->address = $customer[5];
		//         $user->mobno = $customer[2];
		//         if ($customer[4] == "female") {
		//             $user->gender = 0;
		//         } else {
		//             $user->gender = 1;
		//         }
		//         $user->save();
		//         $user->givePermissionTo(['Bookings add','Bookings edit','Bookings list','Bookings delete']);
		//     }
		// }
		return back();
	}

	public function index() {
		return view("customers.index");
	}
// orignal comment part
// 	public function fetch_data(Request $request) {
// 		if ($request->ajax()) {

// 			$users = User::select('users.*')->with(['user_data'])->whereUser_type("C")->orderBy('users.id', 'desc')->groupBy('users.id');

// 			return DataTables::eloquent($users)
// 				->addColumn('check', function ($user) {
// 					$tag = '<input type="checkbox" name="ids[]" value="' . $user->id . '" class="checkbox" id="chk' . $user->id . '" onclick=\'checkcheckbox();\'>';
// 					return $tag;
// 				})
// 				->addColumn('mobno', function ($user) {
// 					return $user->getMeta('mobno');
// 				})
// 				->editColumn('name', function ($user) {
// 					return "<a href=" . route('customers.show', $user->id) . ">$user->name</a>";
// 				})
// 				->addColumn('gender', function ($user) {
// 					return ($user->getMeta('gender')) ? "Male" : "Female";
// 				})
// 				->addColumn('address', function ($user) {
// 					return $user->getMeta('address');
// 				})
// 				->addColumn('action', function ($user) {
// 					return view('customers.list-actions', ['row' => $user]);
// 				})
// 				->rawColumns(['action', 'check', 'name'])
// 				->make(true);
// 			//return datatables(User::all())->toJson();

// 		}
// 	}





// //  dupliacte by dheeraj 
// public function fetch_data(Request $request) {
//     if ($request->ajax()) {

//         // Fetch customers with their meta data
//         $users = User::select('users.*')
//             ->with(['user_data'])
//             ->whereUser_type("C")
//             ->orderBy('users.id', 'desc')
//             ->groupBy('users.id');
        
//         // // Fetch all admins
//         $admins = User::where('user_type', 'O')->select('id', 'name')->get();
//         // Fetch all admins with user_type 'O' and 'M'
// // $admins = User::whereIn('user_type', ['O', 'M'])->select('id', 'name')->get();


//         return DataTables::eloquent($users)
//             ->addColumn('check', function ($user) {
//                 return '<input type="checkbox" name="ids[]" value="' . $user->id . '" class="checkbox" id="chk' . $user->id . '" onclick=\'checkcheckbox();\'>';
//             })
//             ->addColumn('mobno', function ($user) {
//                 return $user->getMeta('mobno');
//             })
//             ->editColumn('name', function ($user) {
//                 return "<a href=" . route('customers.show', $user->id) . ">$user->name</a>";
//             })
//             ->addColumn('gender', function ($user) {
//                 return ($user->getMeta('gender')) ? "Male" : "Female";
//             })
//             ->addColumn('address', function ($user) {
//                 return $user->getMeta('address');
//             })
//             ->addColumn('assign_admin', function ($user) use ($admins) {
//                 $dropdown = '<select class="form-control assign-admin-customer" data-customer-id="' . $user->id . '">';
//                 $dropdown .= '<option value="">Select Admin</option>';
                
//                 foreach ($admins as $admin) {
//                     $selected = $user->assigned_admin == $admin->id ? 'selected' : '';
//                     $dropdown .= '<option value="' . $admin->id . '" ' . $selected . '>' . $admin->name . '</option>';
//                 }
                
//                 $dropdown .= '</select>';
//                 return $dropdown;
//             })

//             ->addColumn('assigned_admin', function($user){
//                 return $user->assigned_admin ? User::find($user->assigned_admin)->name : 'No Admin Assigned';  
//             })

//             ->addColumn('action', function ($user) {
//                 return view('customers.list-actions', ['row' => $user]);
//             })
//             ->rawColumns(['action', 'check', 'name', 'assign_admin'])
//             ->make(true);
//     }
// }
public function fetch_data(Request $request) {
    if ($request->ajax()) {
        // Fetch customers with their meta data
        $users = User::select('users.*')
            ->with(['user_data']) // Ensure you load the user_data relationship
            ->whereUser_type("C")
            ->orderBy('users.id', 'desc')
            ->groupBy('users.id');

        // Fetch all admins
        $admins = User::where('user_type', 'O')->select('id', 'name')->get();

        return DataTables::eloquent($users)
            ->addColumn('check', function ($user) {
                return '<input type="checkbox" name="ids[]" value="' . $user->id . '" class="checkbox" id="chk' . $user->id . '" onclick=\'checkcheckbox();\'>';
            })
            ->addColumn('mobno', function ($user) {
                return $user->getMeta('mobno');
            })
            ->editColumn('name', function ($user) {
                return "<a href=" . route('customers.show', $user->id) . ">$user->name</a>";
            })
            ->addColumn('gender', function ($user) {
                return ($user->getMeta('gender')) ? "Male" : "Female";
            })
            ->addColumn('home_address', function ($user) {
                return $user->address; // Assuming this is the user's home address
            })
            ->addColumn('office_address', function ($user) {
                // Fetch the assigned admin address
                if ($user->assigned_admin) { // CHECK IF ADMIN IS ASSIGNED
                    $admin = User::find($user->assigned_admin); // FETCH ADMIN DETAILS
                    return $admin ? $admin->address : 'No Admin Assigned'; // RETURN ADMIN'S ADDRESS
                }
                return 'Company Not Assigned'; // RETURN IF NO ADMIN IS ASSIGNED
            })
            ->addColumn('assign_admin', function ($user) use ($admins) {
                $dropdown = '<select class="form-control assign-admin-customer" data-customer-id="' . $user->id . '">';
                $dropdown .= '<option value="">Select Admin</option>';

                foreach ($admins as $admin) {
                    $selected = $user->assigned_admin == $admin->id ? 'selected' : '';
                    $dropdown .= '<option value="' . $admin->id . '" ' . $selected . '>' . $admin->name . '</option>';
                }

                $dropdown .= '</select>';
                return $dropdown;
            })
            ->addColumn('assigned_admin', function($user){
                return $user->assigned_admin ? User::find($user->assigned_admin)->name : 'No Admin Assigned';  
            })
            ->addColumn('action', function ($user) {
                return view('customers.list-actions', ['row' => $user]);
            })
            ->rawColumns(['action', 'check', 'name', 'assign_admin'])
            ->make(true);
    }
}



public function assignAdmin(Request $request)
{
    $request->validate([
        'customer_id' => 'required|exists:users,id',
        'admin_id' => 'nullable|exists:users,id',
    ]);

    $customer = User::findOrFail($request->customer_id);
    $customer->assigned_admin = $request->admin_id;
    $customer->save();

    return response()->json(['success' => true]);
}

public function fetch_admin_data(Request $request) {
    if ($request->ajax()) {
        $adminId = $request->input('admin_id'); // Get the admin ID from the request

        $users = User::select('users.*')
            ->with(['user_data'])
            ->where('assigned_admin', $adminId) // Filter by the assigned admin ID
            ->where('user_type', 'C') // Ensure we're only fetching customers
            ->orderBy('users.id', 'desc')
            ->groupBy('users.id');

        return DataTables::eloquent($users)
            ->addColumn('check', function ($user) {
                $tag = '<input type="checkbox" name="ids[]" value="' . $user->id . '" class="checkbox" id="chk' . $user->id . '" onclick=\'checkcheckbox();\'>';
                return $tag;
            })
            ->addColumn('mobno', function ($user) {
                return $user->getMeta('mobno');
            })
            ->editColumn('name', function ($user) {
                return "<a href=" . route('customers.show', $user->id) . ">$user->name</a>";
            })
            ->addColumn('gender', function ($user) {
                return ($user->getMeta('gender')) ? "Male" : "Female";
            })
            // ->addColumn('address', function ($user) {
            //     return $user->getMeta('address');
            // })
             ->addColumn('home_address', function ($user) {
                return $user->address; // Assuming this is the user's home address
            })
            ->addColumn('office_address', function ($user) {
                // Fetch the assigned admin address
                if ($user->assigned_admin) { // CHECK IF ADMIN IS ASSIGNED
                    $admin = User::find($user->assigned_admin); // FETCH ADMIN DETAILS
                    return $admin ? $admin->address : 'No Admin Assigned'; // RETURN ADMIN'S ADDRESS
                }
                return 'Company Not Assigned'; // RETURN IF NO ADMIN IS ASSIGNED
            })
            ->addColumn('action', function ($user) {
                return view('customers.list-actions', ['row' => $user]);
            })
            ->rawColumns(['action', 'check', 'name'])
            ->make(true);
    }
}



	public function create() {
		return view("customers.create");
	}
	public function store(CustomerRequest $request) {

		$id = User::create([
			"name" => $request->get("first_name") . " " . $request->get("last_name"),
			"email" => $request->get("email"),
			"password" => bcrypt("password"),
			"user_type" => "C",
			"api_token" => str_random(60),
		])->id;
		$user = User::find($id);
		$user->user_id = Auth::user()->id;
		$user->first_name = $request->get("first_name");
		$user->last_name = $request->get("last_name");
		$user->address = $request->get("address");
		$user->mobno = $request->get("phone");
		$user->gender = $request->get('gender');
		$user->save();
		$user->givePermissionTo(['Bookings add', 'Bookings edit', 'Bookings list', 'Bookings delete']);

		return redirect()->route("customers.index");
	}
	public function ajax_store(Request $request) {
		$v = Validator::make($request->all(), [
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'unique:users,email',
			'phone' => 'required|numeric|digits_between:7,15',
			'gender' => 'required',
			'address' => 'required',
		]);

		if ($v->fails()) {
			$d = ['error'=>'true','messages' => $v->errors()];
		} else {
			$id = User::create([
				"name" => $request->get("first_name") . " " . $request->get("last_name"),
				"email" => $request->get("email"),
				"password" => bcrypt("password"),
				"user_type" => "C",
				"api_token" => str_random(60),
			])->id;
			$user = User::find($id);
			$user->first_name = $request->get("first_name");
			$user->last_name = $request->get("last_name");
			$user->address = $request->get("address");
			$user->mobno = $request->get("phone");
			$user->gender = $request->get('gender');
			$user->save();
			$user->givePermissionTo(['Bookings add', 'Bookings edit', 'Bookings list', 'Bookings delete']);
			$d = User::whereUser_type("C")->get(["id", "name as text"]);

		}

		return $d;

	}

	public function show($id) {
		$index['customer'] = User::find($id);

		return view('customers.show', $index);
	}

	public function destroy(Request $request) {
		// User::find($request->get('id'))->get_detail()->delete();
		User::find($request->get('id'))->user_data()->delete();
		//$user = User::find($request->get('id'))->delete();
		$user = User::find($request->get('id'));
		$user->update([
			'email' => time() . "_deleted" . $user->email,
		]);
		$user->delete();

		return redirect()->route('customers.index');
	}

	public function edit($id) {
		$index['data'] = User::whereId($id)->first();
		return view("customers.edit", $index);
	}
	public function update(CustomerRequest $request) {

		$user = User::find($request->id);
		$user->name = $request->get("first_name") . " " . $request->get("last_name");
		$user->email = $request->get('email');
		// $user->password = bcrypt($request->get("password"));
		// $user->save();
		$user->first_name = $request->get("first_name");
		$user->last_name = $request->get("last_name");
		$user->address = $request->get("address");
		$user->mobno = $request->get("phone");
		$user->guardian_number = $request->get("guardian_number");  // Add this line
		$user->gender = $request->get('gender');
		$user->save();

		return redirect()->route("customers.index");
	}

	public function bulk_delete(Request $request) {
		// dd($request->all());
		//User::whereIn('id', $request->ids)->delete();
		// return redirect('admin/customers');
		$users = User::whereIn('id', $request->ids)->get();
		foreach ($users as $user) {
			$user->update([
				'email' => time() . "_deleted" . $user->email,
			]);
			$user->delete();
		}

		return back();
	}
}

