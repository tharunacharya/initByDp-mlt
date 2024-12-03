<?php

/*
@copyright

Fleet Manager v6.5

Copyright (C) 2017-2023 Hyvikk Solutions <https://hyvikk.com/> All rights reserved.
Design and developed by Hyvikk Solutions <https://hyvikk.com/>

*/

namespace App\Http\Controllers\FrontEnd;
use App\Http\Controllers\Controller;
use App\Mail\ForgotPassword;
use App\Mail\VehicleBooked;
use App\Model\Address;
use App\Model\Bookings;
use App\Model\CompanyServicesModel;
use App\Model\Hyvikk;
use App\Model\MessageModel;
use App\Model\PasswordResetModel;
use App\Model\TeamModel;
use App\Model\Testimonial;
use App\Model\User;
use App\Model\VehicleModel;
use App\Model\VehicleTypeModel;
// //SOS
// namespace App\Http\Controllers;
// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Notification;
// use App\Notifications\SOSNotification;
// //SOS

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Auth;
use Edujugon\PushNotification\PushNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as Login;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use session;

class HomeController extends Controller
{
    
   
    
    
// // SOS
//     public function triggerSOS(Request $request)
//     {
//         // Get all users with Admin or Super Admin roles
//         $admins = User::role(['Admin', 'Super Admin'])->get();

//         // Send notification to all Admins and Super Admins
//         Notification::send($admins, new SOSNotification());

//         return response()->json(['success' => true, 'message' => 'SOS sent successfully.']);
//     }

// //SOS   

    public function __construct()
    {
        if(file_exists(storage_path('installed'))){
            app()->setLocale(Hyvikk::frontend('language'));
        }
    }

    public function edit_profile()
    {
        $data['detail'] = User::find(Auth::user()->id);
        return view('frontend.edit_profile', $data);
    }

    // public function edit_profile_post(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'first_name' => 'required',
    //         'last_name' => 'required',
    //         'gender' => 'required|integer',
    //         'phone' => 'required|numeric',
    //         'email' => 'required|email|unique:users,email,' . $request->id,
    //     ]);

    //     if ($validator->fails()) {
    //         return back()->withErrors($validator)->withInput();
    //     }

    //     $user = User::find(Auth::user()->id);
    //     $user->name = $request->first_name . " " . $request->last_name;
    //     $user->user_type = "C";
    //     $user->api_token = str_random(60);
    //     $user->first_name = $request->first_name;
    //     $user->last_name = $request->last_name;
    //     $user->email = $request->email;
    //     $user->address = $request->address;
    //     $user->mobno = $request->phone;
    //     $user->gender = $request->gender;
    //     $user->save();

    //     return back()->with('success', 'You are Profile Update Successfully!');
    // }

public function edit_profile_post(Request $request)
{
    $validator = Validator::make($request->all(), [
        'first_name' => 'required',
        'last_name' => 'required',
        'gender' => 'required|integer',
        'phone' => 'required|numeric',
        'guardian_number' => 'required|numeric',  // Add this line
        'email' => 'required|email|unique:users,email,' . $request->id,
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $user = User::find(Auth::user()->id);
    $user->name = $request->first_name . " " . $request->last_name;
    $user->user_type = "C";
    $user->api_token = str_random(60);
    $user->first_name = $request->first_name;
    $user->last_name = $request->last_name;
    $user->email = $request->email;
    $user->address = $request->address;
    $user->mobno = $request->phone;
    $user->guardian_number = $request->guardian_number;  // Add this line
    $user->gender = $request->gender;
    $user->save();

    return back()->with('success', 'Your profile has been updated successfully!');
}



    public function redirect($method, $booking_id)
    {
        $booking = Bookings::find($booking_id);
        try {
            if ($method == "cash") {
                return redirect('cash/' . $booking_id);
            }
            if ($method == "stripe") {
                return redirect('stripe/' . $booking_id);
            }
            if ($method == "razorpay") {
                return redirect('razorpay/' . $booking_id);
            }
            if ($method == "paystack") {
                return redirect('paystack/' . $booking_id);
            }
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Payment redirection failed.']);
        }

    }

    public function redirect_payment(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'booking_id' => 'required',
            'method' => 'required',
        ]);
        $errors = $validation->errors();

        if (count($errors) > 0) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong, please try again later!']);
        } else {
            // dd($request->all());
            $booking = Bookings::find($request->booking_id);
            if ($booking->receipt) {
                if ($request->method == "cash") {
                    return redirect('cash/' . $request->booking_id);
                }
                if ($request->method == "stripe") {
                    return redirect('stripe/' . $request->booking_id);
                }
                if ($request->method == "razorpay") {
                    return redirect('razorpay/' . $request->booking_id);
                }
                if ($request->method == "paystack") {
                    return redirect('paystack/' . $request->booking_id);
                }
            } else {
                return redirect()->back()->withErrors(['error' => 'Booking receipt not generated, try after generation of booking receipt.']);

            }
        }
    }

    public function index()
    {
        $data['testimonial'] = Testimonial::get();
        $data['vehicle'] = VehicleModel::get();
        $data['company_services'] = CompanyServicesModel::get();
        $data['vehicle_type'] = VehicleTypeModel::get();
        return view('frontend.home', $data);
    }

// public function index()
// {
//     // Fetch general data
//     $data['testimonial'] = Testimonial::get();
//     $data['vehicle'] = VehicleModel::get();
//     $data['company_services'] = CompanyServicesModel::get();
//     $data['vehicle_type'] = VehicleTypeModel::get();

//     // Check if the user is logged in
//     if (Auth::check()) {
//         // User is logged in
//         $userId = Auth::id();
        
//         // Fetch the latest booking for the logged-in user
//         $latestBooking = Booking::where('user_id', $userId)
//                                 ->latest('created_at') // Assuming you want the most recent booking
//                                 ->first();

//         if ($latestBooking) {
//             $data['pickup_addr'] = $latestBooking->pickup_addr;
//             $data['drop_addr'] = $latestBooking->dest_addr;
//         } else {
//             $data['pickup_addr'] = null;
//             $data['drop_addr'] = null;
//         }
//     } else {
//         // User is not logged in
//         $data['pickup_addr'] = null;
//         $data['drop_addr'] = null;
//     }

//     return view('frontend.home', $data);
// }


    public function contact()
    {
        return view('frontend.contact');
    }

    public function about()
    {
        $data['team'] = TeamModel::get();
        return view('frontend.about', $data);
    }

    public function booking_history($id)
{
    // if (Auth::user()->id == $id) {
    //     // Paginate with 10 items per page
    //     $data['bookings'] = Bookings::where('customer_id', $id)->latest()->paginate(5);
    // } else {
    //     $data['bookings'] = collect(); // Use an empty collection instead of an array for consistency
    // }
    if (Auth::user()->id == $id) {
    // Handle both JSON and non-JSON customer_id formats
    $data['bookings'] = Bookings::where(function ($query) use ($id) {
        $query->where('customer_id', $id)
              ->orWhereJsonContains('customer_id', $id);
    })->latest()->paginate(5);
} else {
    $data['bookings'] = collect(); // Use an empty collection instead of an array for consistency
}


    return view('frontend.booking_history', $data);
}


    public function user_logout(Request $request)
    {
        $user = Login::user();
        $user->login_status = 0;
        $user->save();
        Auth::logout();
        $request->session()->invalidate();
        return redirect('/');
    }

    public function user_login(Request $request)
    {
        if (Login::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Login::user();

            if ($user->user_type == "C") {
                $user->login_status = 1;
                $user->save();
                return redirect('/');
            } else {
                Auth::logout();
                $request->session()->invalidate();
                return back()->withErrors(["error" => "Invalid login credentials or customer not verified."], 'login')->withInput();
            }
        } else {
            return back()->withErrors(["error" => "Invalid login credentials"], 'login')->withInput();
        }
    }

    public function forgot()
    {
        return view('frontend.auth.forgot_password');
    }

    public function forgot_password(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $response = Password::sendResetLink(
            $request->only('email')
        );

        if ($response == Password::RESET_LINK_SENT) {
            return back()->with(['success' => 'Email Sent Successfully...']);
        } else {
            return back()->with(['error' => 'User Email Not Valid Please Enter Valid Email.'])->withInput();
        }
    }

    public function customer_register(Request $request)
    {
        //dd($request->all());
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm_password|min:8',
            'gender' => 'required|integer',
            'phone' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'register')->withInput();
        }

        $id = User::create([
            "name" => $request->first_name . " " . $request->last_name,
            "email" => $request->email,
            "password" => bcrypt($request->password),
            "user_type" => "C",
            "api_token" => str_random(60),
        ])->id;
        $user = User::find($id);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->address = $request->address;
        $user->mobno = $request->phone;
        $user->gender = $request->gender;
        $user->save();
        // Mail::to($user->email)->send(new WelcomeEmail($user));
        // Mail::to($user->email)->send(new AccountDetails($user, $request->password));
        return back()->with('success', 'You are registered Successfully! please login here.');
    }

    public function send_enquiry(Request $request)
    {
        // dd($request->all());
        $message = MessageModel::create([
            "name" => $request->name,
            "email" => $request->email,
            "message" => $request->message,
        ]);

        return back()->withErrors(["error" => "Your message has been sent successfully!"], 'contact');
    }
    
//     // start duplicate for testing  by dheeraj 
public function book(Request $request)
{
    // Check if user is authenticated and is a customer
    if (Auth::user() && Auth::user()->user_type == 'C') {

       

        // Handle "book_later" case
        if ($request->radio1 == "book_later") {
            $validation = Validator::make($request->all(), [
                'pickup_address' => 'required',
                'dropoff_address' => 'required|different:pickup_address',
                'pickup_date' => 'required|date_format:Y-m-d|after:today',
                'pickup_time' => 'required|date_format:H:i',
                'no_of_person' => 'required|integer',
                
                // 'vehicle_type' => 'required',
            ]);

            if ($validation->fails()) {
                return back()->withErrors($validation)->withInput();
            } else {
                $id = Bookings::create([
                    'customer_id' => Auth::user()->id,
                    'pickup_addr' => $request->pickup_address,
                    'dest_addr' => $request->dropoff_address,
                    'travellers' => $request->no_of_person,
                   
                    'note' => $request->note,
                    'pickup' => date('Y-m-d', strtotime($request->pickup_date)) . " " . date('H:i:s', strtotime($request->pickup_time)),
                ])->id;

                $booking = Bookings::find($id);
                $booking->journey_date = $request->pickup_date;
                $booking->journey_time = $request->pickup_time;
                $booking->booking_type = 1;
                $booking->booking_type = $request->radio1; // Store the selected booking type ('book_later' or 'book_week')
                $booking->accept_status = 0; // 0 = yet to accept, 1 = accept
                $booking->ride_status = null;
                
                // $booking->vehicle_typeid = $request->vehicle_type;
                $booking->save();

                Address::updateOrCreate(['customer_id' => Auth::user()->id, 'address' => $request->pickup_address]);
                Address::updateOrCreate(['customer_id' => Auth::user()->id, 'address' => $request->dropoff_address]);
                // $this->book_later_notification($booking->id, $booking->vehicle_typeid);
                return back()->with('success', 'Your Request has been Submitted Successfully.');
                if (Hyvikk::email_msg('email') == 1) {
                    Mail::to($booking->customer->email)->send(new VehicleBooked($booking));
                }
            }
        } 
        // Handle "book_week" case
        else if ($request->radio1 == "book_week") {
            // Debug the request to see the data
            // dd($request->all());

            // Validate the "book_week" data
            $validation = Validator::make($request->all(), [
                'radio1' => 'required|in:book_later,book_week',
                'pickup_address' => 'required|string|max:255',
                'dropoff_address' => 'required|string|max:255',
                'no_of_person' => 'required|integer|min:1',
                'increment_date' => 'required|integer|min:1',
                // 'pickup_date_day*' => 'required|date',
                // 'pickup_time_day*' => 'required|date_format:H:i',
                'pickup_datetime_day.*' => 'required|date_format:Y-m-d H:i', // Validate combined date and time
                'vehicle_type' => 'required|integer',
                'pickup_date' => 'nullable',
                'pickup_time' => 'nullable',
            ]);

                     // Prepare the pickup date and time data
                    $pickupDateTimes = [];
                    foreach ($request->input('pickup_datetime_day', []) as $datetime) {
                        $dateTimeParts = \DateTime::createFromFormat('Y-m-d H:i', $datetime);
                        $pickupDateTimes[] = [
                            'date' => $dateTimeParts->format('Y-m-d'),
                            'time' => $dateTimeParts->format('H:i'),
                        ];
                    }
                    
                    // Convert the pickupDateTimes array to JSON
                    $pickupDateTimesJson = json_encode($pickupDateTimes);
                    
                    // Log the JSON data for debugging
                    \Log::info("Pickup DateTimes JSON: " . $pickupDateTimesJson);
                    
                    // Create the booking record
                    $booking = new Bookings();
                    $booking->customer_id = Auth::user()->id;
                    $booking->pickup_addr = $request->pickup_address;
                    $booking->dest_addr = $request->dropoff_address;
                    $booking->travellers = $request->no_of_person;
                    $booking->booking_type = $request->radio1; // Store the selected booking type ('book_later' or 'book_week')
                    $booking->weekly_book = $request->increment_date;
                    $booking->note = $request->note;
                    $booking->pickup = $pickupDateTimesJson; // Store as JSON
                    $booking->accept_status = 0; // 0 = yet to accept, 1 = accept
                    $booking->ride_status = null;
                    
                    
            // Save the booking record
                if ($booking->save()) {
                    // Update addresses
                    Address::updateOrCreate(['customer_id' => Auth::user()->id, 'address' => $request->pickup_address]);
                    Address::updateOrCreate(['customer_id' => Auth::user()->id, 'address' => $request->dropoff_address]);
                
                    return back()->with('success', 'Your Request has been Submitted Successfully.');
                } else {
                    // Log error if booking fails to save
                    \Log::error("Failed to save booking: " . json_encode($booking->errors()));
                    return back()->withErrors(['error' => 'There was a problem submitting your request.'])->withInput();
                }
        }
    } else {
        return redirect('/login')->withErrors(['error' => 'You must be logged in to book.']);
    }
}

 // end duplicate for testing  by dheeraj 

//     public function book(Request $request) // comment by dheeraj 
//     {
//         // print_r($request);
//         //         exit;
// //         $max_seats = VehicleTypeModel::find($request->get('vehicle_type'))->seats;
// // 		if($request->get("no_of_person") > $max_seats){
// // 			return back()->withErrors(["error" => "Number of Travellers exceed seating capity of the vehicle | Seats Available : ".$max_seats.""])->withInput();
// // 		}

//         if (Auth::user() && Auth::user()->user_type == 'C') {
            
            
//             // if ($request->radio1 == "book_now")
//             // {
//             //     $validation = Validator::make($request->all(), [
//             //         'pickup_address' => 'required',
//             //         'dropoff_address' => 'required|different:pickup_address',
//             //         'no_of_person' => 'required|integer',
//             //         'vehicle_type' => 'required',
//             //     ]);

//             //     if ($validation->fails()) {
//             //         return back()->withErrors($validation)->withInput();
//             //     } else {
//             //         $booking_time = Hyvikk::frontend('booking_time');
//             //         $id = Bookings::create(['customer_id' => Auth::user()->id,
//             //             'pickup_addr' => $request->pickup_address,
//             //             'dest_addr' => $request->dropoff_address,
//             //             'travellers' => $request->no_of_person,
//             //             'note' => $request->note,
//             //             'pickup' => date('Y-m-d H:i:s', strtotime('+' . $booking_time . ' hours')),
//             //         ])->id;

//             //         $booking = Bookings::find($id);
//             //         $booking->journey_date = date('d-m-Y');
//             //         $booking->journey_time = date('H:i:s');
//             //         $booking->accept_status = 0; //0=yet to accept, 1= accept
//             //         $booking->ride_status = null;
//             //         $booking->booking_type = 0;
//             //         $booking->vehicle_typeid = $request->vehicle_type;
//             //         $booking->save();

//             //         Address::updateOrCreate(['customer_id' => Auth::user()->id, 'address' => $request->pickup_address]);
//             //         Address::updateOrCreate(['customer_id' => Auth::user()->id, 'address' => $request->dropoff_address]);
//             //         $this->book_now_notification($booking->id, $booking->vehicle_typeid);
//             //         return back()->with('success', 'Your Request has been Submitted Successfully.');
//             //         if (Hyvikk::email_msg('email') == 1) {
//                         // Mail::to($booking->customer->email)->send(new VehicleBooked($booking));
//             //         }

//             //     }
//             // } else
//              if ($request->radio1 == "book_later")
//             {
//                 // dd($request->all());
//                 $validation = Validator::make($request->all(), [
//                     'pickup_address' => 'required',
//                     'dropoff_address' => 'required|different:pickup_address',
//                     'pickup_date' => 'required|date_format:Y-m-d|after:today',
//                     'pickup_time' => 'required',
//                     'no_of_person' => 'required|integer',
//                     // 'vehicle_type' => 'required',
//                 ]);

//                 if ($validation->fails()) {
//                     return back()->withErrors($validation)->withInput();
//                 } else {
//                     $id = Bookings::create(['customer_id' => Auth::user()->id,
//                         'pickup_addr' => $request->pickup_address,
//                         'dest_addr' => $request->dropoff_address,
//                         'travellers' => $request->no_of_person,
//                         'note' => $request->note,
//                         'pickup' => date('Y-m-d', strtotime($request->pickup_date)) . " " . date('H:i:s', strtotime($request->pickup_time)),
//                     ])->id;

//                     $booking = Bookings::find($id);
//                     $booking->journey_date = $request->pickup_date;
//                     $booking->journey_time = $request->pickup_time;
//                     $booking->booking_type = 1;
//                     $booking->accept_status = 0; //0=yet to accept, 1= accept
//                     $booking->ride_status = null;
//                     // $booking->vehicle_typeid = $request->vehicle_type;
//                     $booking->save();
//                     Address::updateOrCreate(['customer_id' => Auth::user()->id, 'address' => $request->pickup_address]);
//                     Address::updateOrCreate(['customer_id' => Auth::user()->id, 'address' => $request->dropoff_address]);
//                     // $this->book_later_notification($booking->id, $booking->vehicle_typeid);
//                     return back()->with('success', 'Your Request has been Submitted Successfully.');
//                     if (Hyvikk::email_msg('email') == 1) {
//                         Mail::to($booking->customer->email)->send(new VehicleBooked($booking));
//                     }

//                 }
//             }
//             else {
//                 dd($request->all());
//                 // dd($request->pickup_date, $request->pickup_time);
                
//               $validation = Validator::make($request->all(), [
//                   'radio1' => 'required|in:book_later,book_week',
//         'pickup_address' => 'required|string|max:255',
//         'dropoff_address' => 'required|string|max:255',
//         'no_of_person' => 'required|integer|min:1',
//         'increment_date' => 'required|integer|min:1',
//         'pickup_date_day*' => 'required_if:radio1,book_week|date',
//         'pickup_time_day*' => 'required_if:radio1,book_week|date_format:H:i',
//         'vehicle_type' => 'required|integer',
//          'pickup_date' => 'null',
//                     'pickup_time' => 'null',
        
                  
                  
//             //  'pickup_address' => 'required',
//             //     'dropoff_address' => 'required|different:pickup_address',
//             //     'pickup_date' => 'required|array|min:1',
//             //     'pickup_date.*' => 'required|date_format:Y-m-d|after:today',
//             //     'pickup_time' => 'required|array|min:1',
//             //     'pickup_time.*' => 'required|date_format:H:i',
//             //     'no_of_person' => 'required|integer',
//             //     'increment_date' => 'required|integer|min:1|max:5',
// ]);




       

            
//             if ($validation->fails()) {
//                 return back()->withErrors($validation)->withInput();
//             }
            
            
//              // Concatenate all dates and times into a single string
//         $pickupDateTimeString = '';
//         foreach ($pickupDates as $key => $date) {
//             $pickupDateTimeString .= date('Y-m-d', strtotime($date)) . " " . date('H:i:s', strtotime($pickupTimes[$key])) . " | ";
//         }

//         // Remove trailing separator
//         $pickupDateTime = rtrim($pickupDateTimeString, " | ");
        
        
//         foreach($request->pickup_date as $key => $date) {
//     $pickupDateTime = date('Y-m-d', strtotime($date)) . " " . date('H:i:s', strtotime($request->pickup_time[$key]));

//     $id = Bookings::create([
//         'customer_id' => Auth::user()->id,
//         'pickup_addr' => $request->pickup_address,
//         'dest_addr' => $request->dropoff_address,
//         'travellers' => $request->no_of_person,
//         'weekly_book' => $request->increment_date,
//         'note' => $request->note,
//         'pickup' => $pickupDateTime,
//     ])->id;

//     $booking = Bookings::find($id);
//     $booking->journey_date = $date;
//     $booking->journey_time = $request->pickup_time[$key];
//     $booking->booking_type = 1;
//     $booking->accept_status = 0; //0=yet to accept, 1= accept
//     $booking->ride_status = null;
//     $booking->save();
// }

// Address::updateOrCreate(['customer_id' => Auth::user()->id, 'address' => $request->pickup_address]);
// Address::updateOrCreate(['customer_id' => Auth::user()->id, 'address' => $request->dropoff_address]);

// return back()->with('success', 'Your Request has been Submitted Successfully.');

// }
            
            
            
//             // else{
                
//             //   $validation = Validator::make($request->all(), [
//             //             'pickup_address' => 'required',
//             //             'dropoff_address' => 'required|different:pickup_address',
//             //             'pickup_date.*' => 'required|date_format:Y-m-d|after:today',
//             //             'pickup_time.*' => 'required',
//             //             'no_of_person' => 'required|integer',
//             //             'increment_date' => 'required|integer'
//             //         ]);
                    
//             //         if ($validation->fails()) {
//             //             return back()->withErrors($validation)->withInput();
//             //         }
//             //         else {
//             //         $booking = Bookings::create([
//             //             'customer_id' => Auth::user()->id,
//             //             'pickup_addr' => $request->pickup_address,
//             //             'dest_addr' => $request->dropoff_address,
//             //             'travellers' => $request->no_of_person,
//             //             'weekly_book' => $request->increment_date,
//             //             'note' => $request->note,
//             //         ]);
                    
//             //         foreach ($request->pickup_date as $key => $date) {
//             //             $pickup = date('Y-m-d', strtotime($date)) . " " . date('H:i:s', strtotime($request->pickup_time[$key]));
//             //             // Save each pickup datetime to the database as needed
//             //             // You might want to save each datetime as a separate record in another table
//             //         }
                    
                 

//             //         // $id = Bookings::create(['customer_id' => Auth::user()->id,
//             //         //     'pickup_addr' => $request->pickup_address,
//             //         //     'dest_addr' => $request->dropoff_address,
//             //         //     'travellers' => $request->no_of_person,
//             //         //     'weekly_book' =>$request->increment_date,
//             //         //     'note' => $request->note,
//             //         //     'pickup' => date('Y-m-d', strtotime($request->pickup_date)) . " " . date('H:i:s', strtotime($request->pickup_time)),
//             //         // ])->id;

//             //         $booking = Bookings::find($id);
//             //         $booking->journey_date = $request->pickup_date;
//             //         $booking->journey_time = $request->pickup_time;
//             //         $booking->booking_type = 1;
//             //         $booking->accept_status = 0; //0=yet to accept, 1= accept
//             //         $booking->ride_status = null;
//             //         // $booking->vehicle_typeid = $request->vehicle_type;
//             //         $booking->save();
//             //         Address::updateOrCreate(['customer_id' => Auth::user()->id, 'address' => $request->pickup_address]);
//             //         Address::updateOrCreate(['customer_id' => Auth::user()->id, 'address' => $request->dropoff_address]);
//             //         // $this->book_later_notification($booking->id, $booking->vehicle_typeid);
//             //         return back()->with('success', 'Your Request has been Submitted Successfully.');
//             //         if (Hyvikk::email_msg('email') == 1) {
//             //             // Mail::to($booking->customer->email)->send(new VehicleBooked($booking));
//             //         }

//             //     }
                
//             // }
// //           elseif ($request->radio1 == "book_week") {
// //               Log::info('Form Data:', $request->all());
// // $validation = Validator::make($request->all(), [
// //     'pickup_address' => 'required',
// //     'dropoff_address' => 'required|different:pickup_address',
// //     'pickup_date' => 'required|array|min:1',
// //     'pickup_date.*' => 'required|date_format:Y-m-d|after:today',
// //     'pickup_time' => 'required|array|min:1',
// //     'pickup_time.*' => 'required',
// //     'no_of_person' => 'required|integer',
// //     'increment_date' => 'required|integer|min:1|max:5',
// // ]);

// // if ($validation->fails()) {
// //     return back()->withErrors($validation)->withInput();
// // }
// // else {
// //         $dateTimeArray = [];
// //         foreach ($request->pickup_date as $key => $date) {
// //             // Ensure that there's a matching time for each date
// //             if (isset($request->pickup_time[$key])) {
// //                 $dateTimeArray[] = [
// //                     'date' => $date,
// //                     'time' => $request->pickup_time[$key]
// //                 ];
// //             }
// //         }

// //         // Save each booking entry
// //         foreach ($dateTimeArray as $dateTime) {
// //             $booking = new Bookings();
// //             $booking->customer_id = Auth::user()->id;
// //             $booking->pickup_addr = $request->pickup_address;
// //             $booking->dest_addr = $request->dropoff_address;
// //             $booking->travellers = $request->no_of_person;
// //             $booking->note = $request->note;
// //             $booking->pickup = date('Y-m-d', strtotime($dateTime['date'])) . " " . date('H:i:s', strtotime($dateTime['time']));
// //             $booking->journey_date = $dateTime['date'];
// //             $booking->journey_time = $dateTime['time'];
// //             $booking->booking_type = 1;
// //             $booking->accept_status = 0; //0=yet to accept, 1=accept
// //             $booking->ride_status = null;
// //             $booking->weekly_book = $request->increment_date;
// //             $booking->save();
// //         }

// //         Address::updateOrCreate(['customer_id' => Auth::user()->id, 'address' => $request->pickup_address]);
// //         Address::updateOrCreate(['customer_id' => Auth::user()->id, 'address' => $request->dropoff_address]);

// //         // Optionally send notification
// //         // $this->book_week_notification($booking->id);

// //         return back()->with('success', 'Your Weekly Request has been Submitted Successfully.');
// //         if (Hyvikk::email_msg('email') == 1) {
// //             // Mail::to($booking->customer->email)->send(new VehicleBooked($booking));
// //         }
// //     }
// // }

//             try {
//                 if (isset($request->method) && Hyvikk::frontend('admin_approval') == 0) {
//                     // fare calc
//                     $key = Hyvikk::api('api_key');

//                     $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . str_replace(" ", "", $booking->pickup_addr) . "&destination=" . str_replace(" ", "", $booking->dest_addr) . "&mode=driving&units=metric&sensor=false&key=" . $key;
//                     // dd($url);
//                     $ch = curl_init();
//                     curl_setopt($ch, CURLOPT_URL, $url);
//                     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//                     $data = curl_exec($ch);
//                     // curl_close($ch);
//                     $response = json_decode($data, true);
//                     // dd($response);
//                     // dd($response['routes'][0]['legs'][0]['duration']['text']);
//                     // dd($response['routes'][0]['legs'][0]['distance']['text']);
//                     if ($response['status'] == "OK") {
//                         // $v_type = VehicleTypeModel::find($request->vehicle_type);

//                         // $type = strtolower(str_replace(" ", "", $v_type->vehicletype));
//                         $fare_details = array();

//                         $total_kms = explode(" ", str_replace(",", "", $response['routes'][0]['legs'][0]['distance']['text']))[0];

//                         $km_base = Hyvikk::fare($type . '_base_km');
//                         $base_fare = Hyvikk::fare($type . '_base_fare');
//                         $std_fare = Hyvikk::fare($type . '_std_fare');
//                         $base_km = Hyvikk::fare($type . '_base_km');

//                         if ($total_kms <= $km_base) {
//                             $total_fare = $base_fare;

//                         } else {
//                             $total_fare = $base_fare + (($total_kms - $km_base) * $std_fare);
//                         }
//                         // calculate tax charges
//                         $count = 0;
//                         if (Hyvikk::get('tax_charge') != "null") {
//                             $taxes = json_decode(Hyvikk::get('tax_charge'), true);
//                             foreach ($taxes as $key => $val) {
//                                 $count = $count + $val;
//                             }
//                         }
//                         $total_fare = round($total_fare, 2);
//                         $tax_total = round((($total_fare * $count) / 100) + $total_fare, 2);
//                         $total_tax_percent = $count;
//                         $total_tax_charge_rs = round(($total_fare * $count) / 100, 2);

//                         // $fare_details = array(
//                         //     'total_amount' => $tax_total,
//                         //     'total_tax_percent' => $total_tax_percent,
//                         //     'total_tax_charge_rs' => $total_tax_charge_rs,
//                         //     'ride_amount' => $total_fare,
//                         //     'base_fare' => $base_fare,
//                         //     'base_km' => $base_km,
//                         // );
//                         // dd($fare_details);
//                         $booking->setMeta([
//                             'customerId' => Auth::id(),
//                             // 'vehicleId' => $request->get('vehicleId'),
//                             'day' => 1,
//                             'mileage' => $total_kms,
//                             'waiting_time' => 0,
//                             'date' => date('Y-m-d'),
//                             'total' => round($total_fare, 2),
//                             'total_kms' => $total_kms,
//                             // 'ride_status' => 'Completed',
//                             'tax_total' => round($tax_total, 2),
//                             'total_tax_percent' => round($total_tax_percent, 2),
//                             'total_tax_charge_rs' => round($total_tax_charge_rs, 2),
//                         ]);
//                         $booking->save();
//                         return redirect('redirect-payment/' . $request->method . '/' . $booking->id);
//                     } else {
//                         return back()->withErrors(['error' => 'Your Booking Request has been Submitted Successfully, but payment has failed.']);
//                     }
//                 }
//             } catch (Exception $e) {
//                 return back()->withErrors(['error' => 'Your Booking Request has been Submitted Successfully, but payment has failed.']);
//             }
//             return back()->with('success', 'Your Request has been Submitted Successfully.');
//         }
//         else {
//             return redirect("/#login")->withErrors(["error" => "Please Login Fleet Manager"], 'login');
//         }
//     }

    public function send_reset_link(Request $request)
    {

        $user = User::where('email', $request->email)->get()->toArray();
        if (!empty($user) && $user[0]['user_type'] == "C") {
            $this->validateEmail($request);

            $email = $request->email;
            $token = Str::random(60);
            PasswordResetModel::where('email', $email)->delete();
            PasswordResetModel::create(['email' => $email, 'token' => Hash::make($token), 'created_at' => date('Y-m-d H:i:s')]);
            Mail::to($email)->send(new ForgotPassword($email, $token));

            return back()->with('success', "We have e-mailed your password reset link!");
        } else {
            return back()->with('success', "Please Enter Valid Email Address...");
        }

    }

    protected function validateEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);
    }

    public function reset($token)
    {
        $data['token'] = $token;
        $data['email'] = $_GET['email'];
        return view('frontend.auth.reset', $data);
    }

    public function reset_password(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);
        $errors = $validation->errors();

        if (count($errors) > 0) {
            return back()->with('error', implode(", ", $errors->all()));
        } else {
            $response = $this->broker()->reset(
                $this->credentials($request), function ($user, $password) {
                    $this->resetPassword($user, $password);
                }
            );

            if ($response == Password::PASSWORD_RESET) {
                return redirect('/#login')->with('success', __($response));
            } else {
                return back()->with('error', __($response));
            }

        }
    }

    public function broker()
    {
        return Password::broker();
    }

    protected function credentials(Request $request)
    {
        return $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );
    }

    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);
        $user->setRememberToken(Str::random(60));
        $user->save();
    }

    // book now notification
    public function book_now_notification($id, $type_id)
    {

        $booking = Bookings::find($id);
        $data['success'] = 1;
        $data['key'] = "book_now_notification";
        $data['message'] = 'Data Received.';
        $data['title'] = "New Ride Request (Book Now)";
        $data['description'] = "Do you want to Accept it ?";
        $data['timestamp'] = date('Y-m-d H:i:s');
        $data['data'] = array('riderequest_info' => array(
            'user_id' => $booking->customer_id,
            'booking_id' => $booking->id,
            'source_address' => $booking->pickup_addr,
            'dest_address' => $booking->dest_addr,
            'book_date' => date('Y-m-d'),
            'book_time' => date('H:i:s'),
            'journey_date' => date('d-m-Y'),
            'journey_time' => date('H:i:s'),
            'accept_status' => $booking->accept_status));
        if ($type_id == null) {
            $vehicles = VehicleModel::get()->pluck('id')->toArray();
        } else {
            $vehicles = VehicleModel::where('type_id', $type_id)->get()->pluck('id')->toArray();
        }
        $drivers = User::where('user_type', 'D')->get();

        foreach ($drivers as $d) {
            if (in_array($d->vehicle_id, $vehicles)) {

                if ($d->fcm_id != null && $d->is_available == 1 && $d->is_on != 1) {

                    // PushNotification::app('appNameAndroid')
                    //     ->to($d->fcm_id)
                    //     ->send($data);

                    $push = new PushNotification('fcm');
                    $push->setMessage($data)
                        ->setApiKey(env('server_key'))
                        ->setDevicesToken([$d->fcm_id])
                        ->send();
                }
            }

        }

    }

    // book later notification
    public function book_later_notification($id, $type_id)
    {
        $booking = Bookings::find($id);
        $data['success'] = 1;
        $data['key'] = "book_later_notification";
        $data['message'] = 'Data Received.';
        $data['title'] = "New Ride Request (Book Later)";
        $data['description'] = "Do you want to Accept it ?";
        $data['timestamp'] = date('Y-m-d H:i:s');
        $data['data'] = array('riderequest_info' => array('user_id' => $booking->customer_id,
            'booking_id' => $booking->id,
            'source_address' => $booking->pickup_addr,
            'dest_address' => $booking->dest_addr,
            'book_date' => date('Y-m-d'),
            'book_time' => date('H:i:s'),
            'journey_date' => $booking->journey_date,
            'journey_time' => $booking->journey_time,
            'accept_status' => $booking->accept_status));
        if ($type_id == null) {
            $vehicles = VehicleModel::get()->pluck('id')->toArray();
        } else {
            $vehicles = VehicleModel::where('type_id', $type_id)->get()->pluck('id')->toArray();
        }
        $drivers = User::where('user_type', 'D')->get();
        foreach ($drivers as $d) {
            if (in_array($d->vehicle_id, $vehicles)) {
                // echo $d->vehicle_id . " " . $d->id . "<br>";
                if ($d->fcm_id != null) {
                    // PushNotification::app('appNameAndroid')
                    //     ->to($d->fcm_id)
                    //     ->send($data);

                    $push = new PushNotification('fcm');
                    $push->setMessage($data)
                        ->setApiKey(env('server_key'))
                        ->setDevicesToken([$d->fcm_id])
                        ->send();
                }
            }
        }

    }
}
