<?php

/*
@copyright

Fleet Manager v6.5

Copyright (C) 2017-2023 Hyvikk Solutions <https://hyvikk.com/> All rights reserved.
Design and developed by Hyvikk Solutions <https://hyvikk.com/>

 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\BookingIncome;
use App\Model\BookingPaymentsModel;
use App\Model\Bookings;
use App\Model\FareSettings;
use App\Model\Hyvikk;
use App\Model\IncomeModel;
use App\Model\ReasonsModel;
use App\Model\ReviewModel;
use App\Model\User;
use App\Model\VehicleTypeModel;
use Edujugon\PushNotification\PushNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;




class DriversApi extends Controller {
// 	public function change_availability(Request $request) {
// 		$driver = User::find($request->get('user_id'));
// 		if ($driver != null) {
// 			$driver->is_available = $request->get('availability');
// 			$driver->save();
// 			if ($request->get('availability') == '0') {
// 				$status = 'Offline';
// 			}if ($request->get('availability') == '1') {
// 				$status = 'Online';
// 			}
// 			$data['success'] = 1;
// 			$data['message'] = "You are now " . $status;
// 			$data['data'] = "";
// 		} else {
// 			$data['success'] = 0;
// 			$data['message'] = "Unable to Change Availability. Please, Try again Later!";
// 			$data['data'] = "";
// 		}

// 		return $data;
// 	}
	public function change_availability(Request $request) {
		$driver = User::find($request->get('user_id'));
		if ($driver != null) {
			$driver->is_active = $request->get('availability');
			$driver->save();
			if ($request->get('availability') == '0') {
				$status = 'Offline';
			}if ($request->get('availability') == '1') {
				$status = 'Online';
			}
			$data['success'] = 1;
			$data['message'] = "You are now " . $status;
			$data['data'] = "";
		} else {
			$data['success'] = 0;
			$data['message'] = "Unable to Change Availability. Please, Try again Later!";
			$data['data'] = "";
		}

		return $data;
	}

	public function ride_requests() {
		$bookings = Bookings::meta()->where('bookings_meta.key', '=', 'ride_status')->where('bookings_meta.value', '=', null)->get();
// 		dd($bookings);
// 		$bookings = Bookings::get();

		if ($bookings->toArray() == null) {
			$data['success'] = 0;
			$data['message'] = "Unable to Receive Ride Requests. Please, Try again Later!";
			$data['data'] = "";
		} else {
			$details1 = array();
			$details2 = array();
			foreach ($bookings as $book) {
				if ($book->booking_type == 0) {

					if (strtotime($book->journey_date . " " . $book->journey_time) >= strtotime("-5 minutes")) {

						$details1[] = array('booking_id' => $book['id'],
							'book_date' => date('Y-m-d', strtotime($book['created_at'])),
							'book_time' => date('H:i:s', strtotime($book['created_at'])),
							'source_address' => $book['pickup_addr'],
							'dest_address' => $book['dest_addr'],
							'journey_date' => $book->getMeta('journey_date'),
							'journey_time' => $book->getMeta('journey_time'));
					}

				}if ($book->booking_type == 1) {
					if (strtotime($book->journey_date . " " . $book->journey_time) >= strtotime("now")) {
						$details2[] = array('booking_id' => $book['id'],
							'book_date' => date('Y-m-d', strtotime($book['created_at'])),
							'book_time' => date('H:i:s', strtotime($book['created_at'])),
							'source_address' => $book['pickup_addr'],
							'dest_address' => $book['dest_addr'],
							'journey_date' => $book->getMeta('journey_date'),
							'journey_time' => $book->getMeta('journey_time'));
					}

				}

			}

			$details = array_merge($details1, $details2);
			$data['success'] = 1;
			$data['message'] = "Data Received.";
			$data['data'] = array('ride_requests' => $details);
		}
		return $data;
	}
// BookingController.php

// public function driver_assign(Request $request) {
//     // Validate the input data
//     $validation = Validator::make($request->all(), [
//         'booking_id' => 'required|exists:bookings,id',
//         'driver_id' => 'required|exists:users,id'
//     ]);

//     if ($validation->fails()) {
//         return response()->json([
//             'success' => 0,
//             'message' => "Invalid Booking ID or Driver ID.",
//             'errors' => $validation->errors()
//         ], 400);
//     }

//     // Fetch booking and check if it is available
//     $booking = Bookings::where('id', $request->get('booking_id'))
//         ->whereNull('driver_id')
//         ->first();

//     if (!$booking) {
//         return response()->json([
//             'success' => 0,
//             'message' => "Booking not found or already assigned.",
//             'data' => ""
//         ], 404);
//     }

//     // Fetch the driver
//     $driver = User::where('id', $request->get('driver_id'))
//         ->where('user_type', 'D')
//         ->first();

//     if (!$driver) {
//         return response()->json([
//             'success' => 0,
//             'message' => "Driver not found.",
//             'data' => ""
//         ], 404);
//     }

//     // Assign the driver to the booking
//     $booking->driver_id = $driver->id;
//     $booking->status = 1; // Assuming '1' means 'assigned'
//     $booking->save();

//     // Prepare data to send to the driver
//     $rideDetails = [
//         'booking_id' => $booking->id,
//         'customer_id' => $booking->customer_id,
//         'pickup_address' => $booking->pickup_addr,
//         'dropoff_address' => $booking->dest_addr,
//         'journey_date' => $booking->journey_date,
//         'journey_time' => $booking->journey_time
//     ];

//     // Optional: Trigger a notification to the driver (using WebSockets, email, etc.)

//     return response()->json([
//         'success' => 1,
//         'message' => "Driver assigned successfully.",
//         'data' => [
//             'ride_details' => $rideDetails,
//             'driver_info' => [
//                 'driver_id' => $driver->id,
//                 'name' => $driver->name,
//                 'contact' => $driver->getMeta('contact')
//             ]
//         ]
//     ], 200);
// }


	//for driver
	public function single_ride_request(Request $request) {
		$booking = Bookings::find($request->get('booking_id'));

		if ($booking == null) {
			$data['success'] = 0;
			$data['message'] = "Unable to Receive Ride Request Info. Please, Try again Later !";
			$data['data'] = "";
		} else {
			if ($booking->getMeta('accept_status') == '1') {
				$user_details = array('user_id' => $booking->customer_id, 'user_name' => $booking->customer->name, 'mobno' => $booking->customer->getMeta('mobno'), 'profile_pic' => $booking->customer->getMeta('profile_pic'));
			} else {
				$user_details = array();
			}
			$data['success'] = 1;
			$data['message'] = "Data Received.";
			$data['data'] = array('riderequest_info' => array('booking_id' => $booking->id,
				'source_address' => $booking->pickup_addr,
				'dest_address' => $booking->dest_addr,
				'book_date' => date('Y-m-d', strtotime($booking->created_at)),
				'book_time' => date('H:i:s', strtotime($booking->created_at)),
				'journey_date' => $booking->getMeta('journey_date'),
				'journey_time' => $booking->getMeta('journey_time'),
				'accept_status' => $booking->getMeta('accept_status'),
				'approx_timetoreach' => $booking->getMeta('approx_timetoreach')),
				'user_details' => $user_details);
		}
		return $data;
	}

	public function reject_ride_request(Request $request) {
		$booking = Bookings::find($request->get('booking_id'));

		//for book later
		if ($request->get('book_type') == 1) {
			$count = User::meta()
				->where(function ($query) {
					$query->where('users_meta.key', '=', 'is_available')
						->where('users_meta.value', '=', 1)
						->where('users_meta.deleted_at', '=', null);
				})->count();
			if ($count == 0) {
				$this->reject_ride_notification($booking->customer_id);
			}

		}

		//for book now
		if ($request->get('book_type') == 0) {
			$drivers = User::meta()
				->where(function ($query) {
					$query->where('users_meta.key', '=', 'is_available')
						->where('users_meta.value', '=', 1)
						->where('users_meta.deleted_at', '=', null);
				})->get();
			$count = 0;
			foreach ($drivers as $driver) {

				if ($driver->is_on == null || $driver->is_on == 0) {

					$count++;
				}

			}
			if ($count == 0) {
				$this->reject_ride_notification($booking->customer_id);
			}

		}
		if ($booking == null) {
			$data['success'] = 0;
			$data['message'] = "Unable to Reject Ride Request. Please, Try again Later !";
			$data['data'] = "";
		} else {
			$data['success'] = 1;
			$data['message'] = "You have Rejected a Ride Request.";
			$data['data'] = array('booking_id' => $booking->id);
		}
		return $data;
	}

	public function reject_ride_notification($id) {
		$customer = User::find($id);

		$data['success'] = 1;
		$data['key'] = "driver_unavailable_notification";
		$data['message'] = 'Data Received.';
		$data['title'] = "No Drivers Available. Please, Try Again Later!";
		$data['description'] = "";
		$data['timestamp'] = date('Y-m-d H:i:s');
		if ($customer->getMeta('fcm_id') != null) {
			// PushNotification::app('appNameAndroid')
			//     ->to($customer->getMeta('fcm_id'))
			//     ->send($data);

			$push = new PushNotification('fcm');
			$push->setMessage($data)
				->setApiKey(env('server_key'))
				->setDevicesToken([$customer->getMeta('fcm_id')])
				->send();
		}

	}

	public function accept_ride_request(Request $request) {
		$booking = Bookings::find($request->get('booking_id'));

		if ($booking == null) {
			$data['success'] = 0;
			$data['message'] = "Unable to Receive Ride Request Info. Please, Try again Later !";
			$data['data'] = "";
		} else {
			$u = User::find($request->get('user_id'));
			$booking->accept_status = 1;
			$booking->driver_id = $request->get('user_id');
			if ($u->getMeta('vehicle_id') != null) {
				$booking->vehicle_id = $u->getMeta('vehicle_id');

				$booking->ride_status = "Upcoming";
				$booking->approx_timetoreach = $request->get('approx_timetoreach');
				$booking->save();
				$user_details = array('user_id' => $booking->customer_id, 'user_name' => $booking->customer->name, 'mobno' => $booking->customer->getMeta('mobno'), 'profile_pic' => $booking->customer->getMeta('profile_pic'));
				$this->accept_ride_notification($booking->id, $request->lat, $request->long);
				$data['success'] = 1;
				$data['message'] = "You have Accepted the Ride  Request. Pick up the Customer on Time !";
				$data['data'] = array('riderequest_info' => array('booking_id' => $booking->id,
					'source_address' => $booking->pickup_addr,
					'dest_address' => $booking->dest_addr,
					'book_date' => date('Y-m-d', strtotime($booking->created_at)),
					'book_time' => date('H:i:s', strtotime($booking->created_at)),
					'journey_date' => $booking->getMeta('journey_date'),
					'journey_time' => $booking->getMeta('journey_time'),
					'accept_status' => $booking->getMeta('accept_status'),
					'approx_timetoreach' => $booking->getMeta('approx_timetoreach')),
					'user_details' => $user_details);
			} else {
				$data['success'] = 0;
				$data['message'] = "You can not Accept Ride Requests. Please, Contact App Admin !";
				$data['data'] = "";

			}
		}
		return $data;
	}

	public function accept_ride_notification($id, $lat, $long) {
		$booking = Bookings::find($id);
		$rating = ReviewModel::where('booking_id', $id)->first();
		$avg = ReviewModel::where('driver_id', $booking->driver_id)->avg('ratings');

		if ($rating != null) {
			$r = $rating->ratings;
		} else {
			$r = "";
		}
		if ($booking->vehicle_id == null) {
			$vehicle_number = "";
			$vehicle_name = "";
		} else {
			$vehicle_number = $booking->vehicle->license_plate;
			$vehicle_name = $booking->vehicle->make_name . $booking->vehicle->model_name;
		}
		$data['success'] = 1;
		$data['key'] = "accept_ride_notification";
		$data['message'] = 'Data Received.';
		$data['title'] = "Your Ride Request has been Accepted.";
		$data['description'] = $booking->pickup_addr . "-" . $booking->dest_addr . ": Driver Name " . $booking->driver->name;
		$data['timestamp'] = date('Y-m-d H:i:s');
		$data['data'] = array('riderequest_info' => array('user_id' => $booking->customer_id,
			'booking_id' => $id,
			'source_address' => $booking->pickup_addr,
			'dest_address' => $booking->dest_addr,
			'book_date' => date('d-m-Y', strtotime($booking->created_at)),
			'book_time' => date('H:i:s', strtotime($booking->created_at)),
			'journey_date' => $booking->getMeta('journey_date'),
			'journey_time' => $booking->getMeta('journey_time'),
			'accept_status' => $booking->getMeta('accept_status'),

		),
			'driver_details' => array('driver_id' => $booking->driver_id,
				'driver_name' => $booking->driver->name,
				'profile_pic' => $booking->driver->getMeta('driver_image'),
				'vehicle_number' => $vehicle_number,
				'vehicle_name' => $vehicle_name,
				'ratings' => round($avg, 2),
				'mobile_number' => $booking->driver->getMeta('phone'),
				'lat' => $lat,
				'long' => $long,
			),
		);
		if ($booking->customer->getMeta('fcm_id') != null) {
			// PushNotification::app('appNameAndroid')
			//     ->to($booking->customer->getMeta('fcm_id'))
			// // ->to('fCsWgScV2qU:APA91bGeT1OKws4zk-1u09v83XFrnmEaIidPRl4-sTTOBbPvHXrq6lkRBLCfQFMml5v3gB1zbS0PDttKwEhvWC1fUQVhWhutVxKeVaxvPofD6XgMQn9UPJCKFnrB8h3amL0bhfFh4s98')
			//     ->send($data);

			$push = new PushNotification('fcm');
			$push->setMessage($data)
				->setApiKey(env('server_key'))
				->setDevicesToken([$booking->customer->getMeta('fcm_id')])
				->send();
		}

	}

	public function cancel_ride_request(Request $request) {
		$booking = Bookings::find($request->get('booking_id'));
		$reason = $request->get('reason');
		if ($booking == null || $reason == null) {
			$data['success'] = 0;
			$data['message'] = "Unable to Cancel Ride. Please, Try again Later !";
			$data['data'] = "";
		} else {

			$booking->ride_status = "Cancelled";
			$booking->reason = $reason;
			$booking->save();
			$this->cancel_ride_notification($booking->id);
			$data['success'] = 1;
			$data['message'] = "Your Ride has been Cancelled Successfully.";
			$data['data'] = array('booking_id' => $booking->id);
		}
		return $data;
	}

	public function cancel_ride_notification($id) {
		$booking = Bookings::find($id);

		$data['success'] = 1;
		$data['key'] = "cancel_ride_notification";
		$data['message'] = 'Oops, Your Ride has been Cancelled by the Driver.';
		$data['title'] = "Ride Cancelled - " . $id;
		$data['description'] = $booking->pickup_addr . " - " . $booking->dest_addr . ". Reason is " . $booking->reason;
		$data['timestamp'] = date('Y-m-d H:i:s');
		$data['data'] = array('booking _id' => $id,
			'source_address' => $booking->pickup_addr,
			'dest_address' => $booking->dest_addr,
			'book_date' => date('d-m-Y', strtotime($booking->created_at)),
			'book_time' => date('H:i:s', strtotime($booking->created_at)),
			'journey_date' => $booking->getMeta('journey_date'),
			'journey_time' => $booking->getMeta('journey_time'),
			'ride_status' => $booking->ride_status,
			'reason' => $booking->reason,
		);
		if ($booking->customer->getMeta('fcm_id') != null) {
			// PushNotification::app('appNameAndroid')
			//     ->to($booking->customer->getMeta('fcm_id'))
			//     ->send($data);

			$push = new PushNotification('fcm');
			$push->setMessage($data)
				->setApiKey(env('server_key'))
				->setDevicesToken([$booking->customer->getMeta('fcm_id')])
				->send();
		}

	}

// 	public function driver_rides(Request $request) {
// 		$bookings = Bookings::where('driver_id', $request->get('driver_id'))->get();

// 		if ($bookings == null) {
// 			$data['success'] = 0;
// 			$data['message'] = "Unable to Receive Rides. Please, Try again Later !";
// 			$data['data'] = "";
// 		} else {
// 			$u_rides = array();
// 			$c_rides = array();
// 			$cancel = array();
// 			if (Hyvikk::get('dis_format') == 'meter') {
// 				$unit = 'm';
// 			}if (Hyvikk::get('dis_format') == 'km') {
// 				$unit = 'km';
// 			}
// 			foreach ($bookings as $u) {
// 				if ($u->getMeta('ride_status') == "Upcoming") {
// 					$u_rides[] = array('booking_id' => $u->id,
// 						'book_date' => date('Y-m-d', strtotime($u->created_at)),
// 						'book_time' => date('H:i:s', strtotime($u->created_at)),
// 						'source_address' => $u->pickup_addr,
// 						'dest_address' => $u->dest_addr,
// 						'journey_date' => $u->getMeta('journey_date'),
// 						'journey_time' => $u->getMeta('journey_time'),
// 						'ride_status' => $u->getMeta('ride_status'),
// 					);
// 				}
// 				if ($u->getMeta('ride_status') == "Completed") {
// 					$c_rides[] = array('booking_id' => $u->id,
// 						'book_date' => date('Y-m-d', strtotime($u->created_at)),
// 						'book_time' => date('H:i:s', strtotime($u->created_at)),
// 						'source_address' => $u->pickup_addr,
// 						'source_time' => date('Y-m-d H:i:s', strtotime($u->getMeta('ridestart_timestamp'))),
// 						'dest_address' => $u->dest_addr,
// 						'dest_time' => date('Y-m-d H:i:s', strtotime($u->getMeta('rideend_timestamp'))),
// 						'driving_time' => $u->getMeta('driving_time'),
// 						'total_kms' => $u->getMeta('total_kms') . " " . $unit,
// 						'amount' => $u->getMeta('tax_total'),
// 						'journey_date' => $u->getMeta('journey_date'),
// 						'journey_time' => $u->getMeta('journey_time'),
// 						'ride_status' => $u->getMeta('ride_status'),
// 					);
// 				}
// 				if ($u->getMeta('ride_status') == "Cancelled") {
// 					$cancel[] = array('booking_id' => $u->id,
// 						'book_date' => date('Y-m-d', strtotime($u->created_at)),
// 						'book_time' => date('H:i:s', strtotime($u->created_at)),
// 						'source_address' => $u->pickup_addr,
// 						'dest_address' => $u->dest_addr,
// 						'journey_date' => $u->getMeta('journey_date'),
// 						'journey_time' => $u->getMeta('journey_time'),
// 						'ride_status' => $u->getMeta('ride_status'),
// 					);
// 				}
// 			}

// 			$data['success'] = 1;
// 			$data['message'] = "Data Received.";
// 			$data['data'] = array('upcoming_rides' => $u_rides,
// 				'completed_rides' => $c_rides,
// 				'cancelled_rides' => $cancel,
// 			);
// 		}
// 		return $data;
// 	}

// public function driver_rides(Request $request) {
//     $driver_id = $request->get('driver_id');

//     // Fetch only bookings with the specified driver_id and ensure driver_id is not null
//     $bookings = Bookings::where('driver_id', $driver_id)
//                         ->whereNotNull('driver_id')
//                         ->get();

//     // Check if any bookings were found
//     if ($bookings->isEmpty()) {
//         return [
//             'success' => 0,
//             'message' => "No rides found for this driver.",
//             'data' => [],
//         ];
//     } 
//   Log::info("Fetched bookings: ", $bookings->toArray());
//     // Initialize arrays for each category
//     $upcoming_rides = $completed_rides = $cancelled_rides = [];

//     // Process each booking and categorize by ride status
//     foreach ($bookings as $booking) {
//         $ride_status = $booking->getMeta('ride_status');
//         $rideData = [
//             'booking_id' => $booking->id,
//             'book_date' => date('Y-m-d', strtotime($booking->created_at)),
//             'book_time' => date('H:i:s', strtotime($booking->created_at)),
//             'source_address' => $booking->pickup_addr,
//             'dest_address' => $booking->dest_addr,
//             'journey_date' => $booking->getMeta('journey_date'),
//             'journey_time' => $booking->getMeta('journey_time'),
//             'ride_status' => $ride_status,
//         ];

//         // Categorize the ride based on ride status
//         if ($ride_status == 'Upcoming') {
//             $upcoming_rides[] = $rideData;
//         } elseif ($ride_status == 'Completed') {
//             $completed_rides[] = $rideData;
//         } elseif ($ride_status == 'Cancelled') {
//             $cancelled_rides[] = $rideData;
//         }
//     }

//     // Prepare the response
//     return [
//         'success' => 1,
//         'message' => "Rides data fetched successfully.",
//         'data' => [
//             'upcoming_rides' => $upcoming_rides,
//             'completed_rides' => $completed_rides,
//             'cancelled_rides' => $cancelled_rides,
//         ],
//     ];
// }


// wroking
// public function driver_rides(Request $request) 
// {
//     $driver_id = $request->get('driver_id');
    
//     // Fetch all bookings, but include those with a null driver_id if the ride is upcoming
//     $bookings = Bookings::where(function($query) use ($driver_id) {
//         $query->where('driver_id', $driver_id)
//               ->orWhereNull('driver_id'); // Include rides with no driver assigned
//     })->get();

//     \Log::info('Bookings:', $bookings->toArray());

//     if ($bookings->isEmpty()) {
//         $data['success'] = 0;
//         $data['message'] = "Unable to Receive Rides. Please, Try again Later!";
//         $data['data'] = [];
//     } else {
//         $u_rides = $c_rides = $cancel = [];
//         $unit = (Hyvikk::get('dis_format') == 'meter') ? 'm' : 'km';

//         foreach ($bookings as $u) {
//             if ($u->driver_id == null) {
//                 \Log::info('Skipping ride with null driver_id', ['booking_id' => $u->id]);
//                 continue; // Skip rides with null driver_id only for completed or cancelled ones
//             }

//             $rideData = [
//                 'booking_id' => $u->id,
//                 'book_date' => date('Y-m-d', strtotime($u->created_at)),
//                 'book_time' => date('H:i:s', strtotime($u->created_at)),
//                 'source_address' => $u->pickup_addr,
//                 'dest_address' => $u->dest_addr,
//                 'journey_date' => $u->getMeta('journey_date'),
//                 'journey_time' => $u->getMeta('journey_time'),
//                 'ride_status' => $u->getMeta('ride_status'),
//             ];

//             \Log::info('Ride Data:', $rideData);

//             switch ($u->getMeta('ride_status')) {
//                 case 'Upcoming':
//                     $u_rides[] = $rideData;
//                     break;
//                 case 'Completed':
//                     $rideData['source_time'] = date('Y-m-d H:i:s', strtotime($u->getMeta('ridestart_timestamp')));
//                     $rideData['dest_time'] = date('Y-m-d H:i:s', strtotime($u->getMeta('rideend_timestamp')));
//                     $rideData['driving_time'] = $u->getMeta('driving_time');
//                     $rideData['total_kms'] = $u->getMeta('total_kms') . " " . $unit;
//                     $rideData['amount'] = $u->getMeta('tax_total');
//                     $c_rides[] = $rideData;
//                     break;
//                 case 'Cancelled':
//                     $cancel[] = $rideData;
//                     break;
//             }
//         }

//         $data['success'] = 1;
//         $data['message'] = "Data Received.";
//         $data['data'] = [
//             'upcoming_rides' => $u_rides,
//             'completed_rides' => $c_rides,
//             'cancelled_rides' => $cancel,
//         ];
//     }
//     return $data;
// }

//upcoming 
public function upcomingRides(Request $request)
{
    $driver_id = $request->get('driver_id');
    
    // Fetch all bookings, but include those with a null driver_id if the ride is upcoming
    $bookings = Bookings::where(function($query) use ($driver_id) {
        $query->where('driver_id', $driver_id)
              ->orWhereNull('driver_id'); // Include rides with no driver assigned
    })->get();

    \Log::info('Bookings:', $bookings->toArray());

    if ($bookings->isEmpty()) {
        $data['success'] = 0;
        $data['message'] = "Unable to Receive Rides. Please, Try again Later!";
        $data['data'] = [];
    } else {
        $u_rides = $c_rides = $cancel = [];
        $unit = (Hyvikk::get('dis_format') == 'meter') ? 'm' : 'km';

        foreach ($bookings as $u) {
            if ($u->driver_id == null) {
                \Log::info('Skipping ride with null driver_id', ['booking_id' => $u->id]);
                continue; // Skip rides with null driver_id only for completed or cancelled ones
            }

            $rideData = [
                'booking_id' => $u->id,
                'book_date' => date('Y-m-d', strtotime($u->created_at)),
                'book_time' => date('H:i:s', strtotime($u->created_at)),
                'source_address' => $u->pickup_addr,
                'dest_address' => $u->dest_addr,
                'journey_date' => $u->getMeta('journey_date'),
                'journey_time' => $u->getMeta('journey_time'),
                'ride_status' => $u->getMeta('ride_status'),
            ];

            \Log::info('Ride Data:', $rideData);

            switch ($u->getMeta('ride_status')) {
                case 'Upcoming':
                    $u_rides[] = $rideData;
                    break;
                // case 'Completed':
                //     $rideData['source_time'] = date('Y-m-d H:i:s', strtotime($u->getMeta('ridestart_timestamp')));
                //     $rideData['dest_time'] = date('Y-m-d H:i:s', strtotime($u->getMeta('rideend_timestamp')));
                //     $rideData['driving_time'] = $u->getMeta('driving_time');
                //     $rideData['total_kms'] = $u->getMeta('total_kms') . " " . $unit;
                //     $rideData['amount'] = $u->getMeta('tax_total');
                //     $c_rides[] = $rideData;
                //     break;
                // case 'Cancelled':
                //     $cancel[] = $rideData;
                //     break;
            }
        }

        $data['success'] = 1;
        $data['message'] = "Data Received.";
        $data['data'] = [
            'upcoming_rides' => $u_rides,
            // 'completed_rides' => $c_rides,
            // 'cancelled_rides' => $cancel,
        ];
    }
    return $data;
}
public function completedRides(Request $request)
{
    $driver_id = $request->get('driver_id');
    
    // Fetch all bookings, but include those with a null driver_id if the ride is upcoming
    $bookings = Bookings::where(function($query) use ($driver_id) {
        $query->where('driver_id', $driver_id)
              ->orWhereNull('driver_id'); // Include rides with no driver assigned
    })->get();

    \Log::info('Bookings:', $bookings->toArray());

    if ($bookings->isEmpty()) {
        $data['success'] = 0;
        $data['message'] = "Unable to Receive Rides. Please, Try again Later!";
        $data['data'] = [];
    } else {
        $u_rides = $c_rides = $cancel = [];
        $unit = (Hyvikk::get('dis_format') == 'meter') ? 'm' : 'km';

        foreach ($bookings as $u) {
            if ($u->driver_id == null) {
                \Log::info('Skipping ride with null driver_id', ['booking_id' => $u->id]);
                continue; // Skip rides with null driver_id only for completed or cancelled ones
            }

            $rideData = [
                'booking_id' => $u->id,
                'book_date' => date('Y-m-d', strtotime($u->created_at)),
                'book_time' => date('H:i:s', strtotime($u->created_at)),
                'source_address' => $u->pickup_addr,
                'dest_address' => $u->dest_addr,
                'journey_date' => $u->getMeta('journey_date'),
                'journey_time' => $u->getMeta('journey_time'),
                'ride_status' => $u->getMeta('ride_status'),
            ];

            \Log::info('Ride Data:', $rideData);

            switch ($u->getMeta('ride_status')) {
                // case 'Upcoming':
                //     $u_rides[] = $rideData;
                //     break;
                case 'Completed':
                    $rideData['source_time'] = date('Y-m-d H:i:s', strtotime($u->getMeta('ridestart_timestamp')));
                    $rideData['dest_time'] = date('Y-m-d H:i:s', strtotime($u->getMeta('rideend_timestamp')));
                    $rideData['driving_time'] = $u->getMeta('driving_time');
                    $rideData['total_kms'] = $u->getMeta('total_kms') . " " . $unit;
                    $rideData['amount'] = $u->getMeta('tax_total');
                    $c_rides[] = $rideData;
                    break;
                // case 'Cancelled':
                //     $cancel[] = $rideData;
                //     break;
            }
        }

        $data['success'] = 1;
        $data['message'] = "Data Received.";
        $data['data'] = [
            // 'upcoming_rides' => $u_rides,
            'completed_rides' => $c_rides,
            // 'cancelled_rides' => $cancel,
        ];
    }
    return $data;
}
public function cancelledRides(Request $request)
{
    $driver_id = $request->get('driver_id');
    
    // Fetch all bookings, but include those with a null driver_id if the ride is upcoming
    $bookings = Bookings::where(function($query) use ($driver_id) {
        $query->where('driver_id', $driver_id)
              ->orWhereNull('driver_id'); // Include rides with no driver assigned
    })->get();

    \Log::info('Bookings:', $bookings->toArray());

    if ($bookings->isEmpty()) {
        $data['success'] = 0;
        $data['message'] = "Unable to Receive Rides. Please, Try again Later!";
        $data['data'] = [];
    } else {
        $u_rides = $c_rides = $cancel = [];
        $unit = (Hyvikk::get('dis_format') == 'meter') ? 'm' : 'km';

        foreach ($bookings as $u) {
            if ($u->driver_id == null) {
                \Log::info('Skipping ride with null driver_id', ['booking_id' => $u->id]);
                continue; // Skip rides with null driver_id only for completed or cancelled ones
            }

            $rideData = [
                'booking_id' => $u->id,
                'book_date' => date('Y-m-d', strtotime($u->created_at)),
                'book_time' => date('H:i:s', strtotime($u->created_at)),
                'source_address' => $u->pickup_addr,
                'dest_address' => $u->dest_addr,
                'journey_date' => $u->getMeta('journey_date'),
                'journey_time' => $u->getMeta('journey_time'),
                'ride_status' => $u->getMeta('ride_status'),
            ];

            \Log::info('Ride Data:', $rideData);

            switch ($u->getMeta('ride_status')) {
                // case 'Upcoming':
                //     $u_rides[] = $rideData;
                //     break;
                // case 'Completed':
                //     $rideData['source_time'] = date('Y-m-d H:i:s', strtotime($u->getMeta('ridestart_timestamp')));
                //     $rideData['dest_time'] = date('Y-m-d H:i:s', strtotime($u->getMeta('rideend_timestamp')));
                //     $rideData['driving_time'] = $u->getMeta('driving_time');
                //     $rideData['total_kms'] = $u->getMeta('total_kms') . " " . $unit;
                //     $rideData['amount'] = $u->getMeta('tax_total');
                //     $c_rides[] = $rideData;
                //     break;
                case 'Cancelled':
                    $cancel[] = $rideData;
                    break;
            }
        }

        $data['success'] = 1;
        $data['message'] = "Data Received.";
        $data['data'] = [
            // 'upcoming_rides' => $u_rides,
            // 'completed_rides' => $c_rides,
            'cancelled_rides' => $cancel,
        ];
    }
    return $data;
}




	public function single_ride_info(Request $request) {
		$booking = Bookings::find($request->get('booking_id'));
		if ($booking == null) {
			$data['success'] = 0;
			$data['message'] = "Unable to Receive Ride Info. Please, Try again Later !";
			$data['data'] = "";
		} else {
			if (Hyvikk::get('dis_format') == 'meter') {
				$unit = 'm';
			}if (Hyvikk::get('dis_format') == 'km') {
				$unit = 'km';
			}
			$ride_reviews = array('user_id' => '', 'ratings' => '', 'review_text' => '', 'date' => '');
			$user_details = array('user_id' => $booking->customer_id, 'user_name' => $booking->customer->name, 'mobno' => $booking->customer->getMeta('mobno'), 'profile_pic' => $booking->customer->getMeta('profile_pic'));
			$ride_info = array();
			if ($booking->getMeta('ride_status') == "Upcoming") {
				$ride_info = array('booking_id' => $booking->id,
					'source_address' => $booking->pickup_addr,
					'dest_address' => $booking->dest_addr,
					'book_timestamp' => date('Y-m-d H:i:s', strtotime($booking->created_at)),
					'ridestart_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('ridestart_timestamp'))),
					'ride_status' => $booking->getMeta('ride_status'),
					'journey_date' => $booking->getMeta('journey_date'),
					'journey_time' => $booking->getMeta('journey_time'),
				);

			}

			if ($booking->getMeta('ride_status') == "Completed") {
				$ride_info = array('booking_id' => $booking->id,
					'source_address' => $booking->pickup_addr,
					'dest_address' => $booking->dest_addr,
					'source_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('ridestart_timestamp'))),
					'dest_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('rideend_timestamp'))),
					'book_timestamp' => date('Y-m-d H:i:s', strtotime($booking->created_at)),
					'ridestart_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('ridestart_timestamp'))),
					'driving_time' => $booking->getMeta('driving_time'),
					'total_kms' => $booking->getMeta('total_kms') . " " . $unit,
					'amount' => $booking->getMeta('tax_total'),
					'ride_status' => $booking->getMeta('ride_status'),
					'journey_date' => $booking->getMeta('journey_date'),
					'journey_time' => $booking->getMeta('journey_time'),
				);

				$r1 = ReviewModel::where('booking_id', $request->get('booking_id'))->first();
				if ($r1 != null) {
					$ride_reviews = array('user_id' => $r1->user_id, 'ratings' => $r1->ratings, 'review_text' => $r1->review_text, 'date' => date('d-m-Y', strtotime($r1->created_at)));
				}

			}

			if ($booking->getMeta('ride_status') == "Cancelled") {
				$ride_info = array('booking_id' => $booking->id,
					'source_address' => $booking->pickup_addr,
					'dest_address' => $booking->dest_addr,
					'book_timestamp' => date('Y-m-d H:i:s', strtotime($booking->created_at)),
					'ridestart_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('ridestart_timestamp'))),
					'reason' => $booking->getMeta('reason'),
					'ride_status' => $booking->getMeta('ride_status'),
					'journey_date' => $booking->getMeta('journey_date'),
					'journey_time' => $booking->getMeta('journey_time'),
				);
			}

			$vehicle_type = VehicleTypeModel::find($booking->getMeta('vehicle_typeid'));

			$data['success'] = 1;
			$data['message'] = "Data Received.";
			$data['data'] = array('rideinfo' => $ride_info, 'user_details' => $user_details, 'ride_review' => $ride_reviews,
				'fare_breakdown' => array('base_fare' => Hyvikk::fare(strtolower(str_replace(' ', '', ($booking->vehicle_id != null ? $booking->vehicle->types->vehicletype : ($vehicle_type->vehicletype ?? "")))) . '_base_fare'), 'ride_amount' => $booking->getMeta('tax_total'), 'extra_charges' => 0, 'payment_mode' => 'CASH')); //done

		}

		return $data;

	}

	public function to_pickup(Request $request)
	{
		// Validate the input
		$request->validate([
			'booking_id' => 'required|exists:bookings,id', // Ensure booking_id exists in the bookings table
		]);
	log::info($request->all());
		// Retrieve the booking details using the booking_id from the request
		$booking = Bookings::find($request->get('booking_id'));
	
		// If the booking doesn't exist, return an error message
		if (!$booking) {
			return response()->json([
				'success' => 0,
				'message' => 'Booking not found!',
				'data' => []
			], 404);
		}
	
		// Get the pickup address and other details
	   $pickup_address = $booking->pickup_addr;
	   $pickup_lat = $booking->pickup_lat;
	   $pickup_long = $booking->pickup_long;
	
		// If pickup address is not found, return an error message
		if (!$pickup_address) {
			return response()->json([
				'success' => 0,
				'message' => 'Pickup address not available!',
				'data' => []
			], 404);
		}
	
		// Return the pickup details as a response
		return response()->json([
			'success' => 1,
			'message' => 'Pickup address retrieved successfully.',
			'data' => [
				'booking_id' => $booking->id,
				'pickup_address' => $pickup_address,
				'pickup_lat' => $booking->pickup_lat,
				'pickup_long' => $booking->pickup_long,
			]
		], 200);
	}
	



// 	public function start_ride(Request $request) {
// 		$booking = Bookings::find($request->get('booking_id'));
// 		if ($booking == null) {
// 			$data['success'] = 0;
// 			$data['message'] = "Unable to Start Ride. Please, Try again Later !";
// 			$data['data'] = "";
// 		} else {

// 			$booking->start_address = $request->get('start_address');
// 			$booking->start_lat = $request->get('start_lat');
// 			$booking->start_long = $request->get('start_long');
// 			$booking->pickup = date('Y-m-d H:i:s');
// 			$booking->ridestart_timestamp = date('Y-m-d H:i:s');
// 			$booking->save();
// // 			$driver = User::find($booking->driver_id);
// // 			$driver->is_on = 1;
// // 			$driver->save();

// // 			$this->ride_started_notification($booking->id);
// // 			$this->ride_ongoing_notification($booking->id);
// 			$data['success'] = 1;
// 			$data['message'] = "Ride Started";
// 			$data['data'] = array('booking_id' => $booking->id, 'ridestart_timestamp' => $booking->getMeta('ridestart_timestamp'));
// 		}
// 		return $data;

// 	}
public function start_ride(Request $request)
{
    $booking = Bookings::find($request->get('booking_id'));
    if ($booking == null) {
        $data['success'] = 0;
        $data['message'] = "Unable to Start Ride. Please, Try again Later!";
        $data['data'] = "";
    } else {
        // Check if the ride has already started
         if ($booking->ridestart_timestamp != null && $booking->ride_status === 'Ongoing')  {
            $data['success'] = 0;
            $data['message'] = "Ride is already ongoing!";
            $data['data'] = array(
                'booking_id' => $booking->id,
                'ridestart_timestamp' => $booking->ridestart_timestamp,
            );
        } else {
            // Start the ride
            $booking->start_address = $request->get('start_address');
            $booking->start_lat = $request->get('start_lat');
            $booking->start_long = $request->get('start_long');
            $booking->pickup = date('Y-m-d H:i:s');
            $booking->ridestart_timestamp = date('Y-m-d H:i:s');
             $booking->ride_status = 'Ongoing'; // Update ride status to Ongoing
            $booking->save();

            $data['success'] = 1;
            $data['message'] = "Ride Started";
            $data['data'] = array(
                'booking_id' => $booking->id,
                'ridestart_timestamp' => $booking->ridestart_timestamp,
            );
        }
    }
    return $data;
}


	public function ride_started_notification($id) {
		$booking = Bookings::find($id);
		$data['success'] = 1;
		$data['key'] = "ride_started_notification";
		$data['message'] = 'Data Received.';
		$data['title'] = "Ride Started";
		$data['description'] = $booking->pickup_addr . "-" . $booking->dest_addr . ": Driver Name " . $booking->driver->name;
		$data['timestamp'] = date('Y-m-d H:i:s');
		$data['data'] = array('ride_info' => array('user_id' => $booking->customer_id,
			'booking_id' => $id,
			'source_address' => $booking->pickup_addr,
			'dest_address' => $booking->dest_addr,
			'start_lat' => $booking->getMeta('start_lat'),
			'start_long' => $booking->getMeta('start_long'),
			'ridestart_timestamp' => $booking->getMeta('ridestart_timestamp'),
		));

		if ($booking->customer->getMeta('fcm_id') != null) {
			// PushNotification::app('appNameAndroid')
			//     ->to($booking->customer->getMeta('fcm_id'))
			//     ->send($data);

			$push = new PushNotification('fcm');
			$push->setMessage($data)
				->setApiKey(env('server_key'))
				->setDevicesToken([$booking->customer->getMeta('fcm_id')])
				->send();
		}

	}

	public function ride_ongoing_notification($id) {
		$booking = Bookings::find($id);
		$data['success'] = 1;
		$data['key'] = "ride_ongoing_notification";
		$data['message'] = 'Data Received.';
		$data['title'] = "Heading Towards [ " . $booking->dest_addr . " ]";
		$data['description'] = "Ongoing Ride From [ " . $booking->pickup_addr . " ]";
		$data['timestamp'] = date('Y-m-d H:i:s');
		$data['data'] = array(
			'user_id' => $booking->customer_id,
			'booking_id' => $id,
			'source_address' => $booking->pickup_addr,
			'dest_address' => $booking->dest_addr,
			'start_lat' => $booking->getMeta('start_lat'),
			'start_long' => $booking->getMeta('start_long'),
			'approx_timetoreach' => $booking->getMeta('approx_timetoreach'),
			'user_name' => $booking->customer->name,
			'user_profile' => $booking->customer->getMeta('profile_pic'),
			'ridestart_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('ridestart_timestamp'))),
		);

		// PushNotification::app('appNameAndroid')
		//     ->to($booking->customer->getMeta('fcm_id'))
		//     ->send($data);
		//not send to cutomer
		if ($booking->driver->getMeta('fcm_id') != null) {
			// PushNotification::app('appNameAndroid')
			//     ->to($booking->driver->getMeta('fcm_id'))
			//     ->send($data);

			$push = new PushNotification('fcm');
			$push->setMessage($data)
				->setApiKey(env('server_key'))
				->setDevicesToken([$booking->driver->getMeta('fcm_id')])
				->send();
		}

	}

// 	public function destination_reached(Request $request) {
// 		$booking = Bookings::find($request->get('booking_id'));

// 		if ($booking == null) {
// 			$data['success'] = 0;
// 			$data['message'] = "Unable to Fetch Ride Info. Please, Try again Later !";
// 			$data['data'] = "";
// 		} else {

// 			$driver = User::find($booking->driver_id);
// 			$driver->is_on = 0;
// 			$driver->save();

// 			if (Hyvikk::get('dis_format') == 'meter') {
// 				$unit = 'm';
// 			}if (Hyvikk::get('dis_format') == 'km') {
// 				$unit = 'km';
// 			}
// 			$booking->end_address = $request->get('end_address');
// 			$booking->end_lat = $request->get('end_lat');
// 			$booking->end_long = $request->get('end_long');
// 			$booking->dropoff = date('Y-m-d H:i:s');
// 			$booking->rideend_timestamp = date('Y-m-d H:i:s');
// 			$booking->ride_status = "Completed";
// 			$booking->driving_time = $request->get('driving_time');
// 			$booking->total_kms = $request->get('total_kms');
// 			$vehicle_type = VehicleTypeModel::find($booking->getMeta('vehicle_typeid'));

// 			$km_base = Hyvikk::fare(strtolower(str_replace(' ', '', ($booking->vehicle_id != null ? $booking->vehicle->types->vehicletype : ($vehicle_type->vehicletype ?? "")))) . '_base_km');
// 			if ($request->get('total_kms') <= $km_base) {
// 				$total_fare = Hyvikk::fare(strtolower(str_replace(' ', '', ($booking->vehicle_id != null ? $booking->vehicle->types->vehicletype : ($vehicle_type->vehicletype ?? "")))) . '_base_fare');

// 			} else {
// 				$total_fare = Hyvikk::fare(strtolower(str_replace(' ', '', ($booking->vehicle_id != null ? $booking->vehicle->types->vehicletype : ($vehicle_type->vehicletype ?? "")))) . '_base_fare') + (($request->get('total_kms') - $km_base) * Hyvikk::fare(strtolower(str_replace(' ', '', ($booking->vehicle_id != null ? $booking->vehicle->types->vehicletype : ($vehicle_type->vehicletype ?? "")))) . '_std_fare'));
// 			}
// 			// calculate tax charges
// 			$count = 0;
// 			if (Hyvikk::get('tax_charge') != "null") {
// 				$taxes = json_decode(Hyvikk::get('tax_charge'), true);
// 				foreach ($taxes as $key => $val) {
// 					$count = $count + $val;
// 				}
// 			}
// 			$booking->tax_total = (($total_fare * $count) / 100) + $total_fare;
// 			$booking->total_tax_percent = $count;
// 			$booking->total_tax_charge_rs = ($total_fare * $count) / 100;
// 			$booking->total = $total_fare;
// 			$booking->date = date('Y-m-d');
// 			$booking->waiting_time = 0;
// 			$booking->mileage = $request->get('total_kms');
// 			$booking->save();
// 			$ride_review = ReviewModel::where('booking_id', $booking->id)->first();

// 			if ($ride_review == null) {
// 				$reviews = array('user_id' => '', 'ratings' => '', 'review_text' => '', 'date' => '');
// 			} else {

// 				$reviews = array('user_id' => $ride_review->user_id, 'ratings' => $ride_review->ratings, 'review_text' => $ride_review->review_text, 'date' => date('Y-m-d', strtotime($ride_review->created_at)));

// 			}

// 			$rideinfo = array('booking_id' => $booking->id,
// 				'source_address' => $booking->getMeta('start_address'),
// 				'dest_address' => $booking->dest_addr,
// 				'source_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('ridestart_timestamp'))),
// 				'dest_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('rideend_timestamp'))),
// 				'book_timestamp' => date('Y-m-d H:i:s', strtotime($booking->created_at)),
// 				'ridestart_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('ridestart_timestamp'))),
// 				'driving_time' => $booking->getMeta('driving_time'),
// 				'total_kms' => $booking->getMeta('total_kms') . " " . $unit,
// 				'amount' => $booking->getMeta('tax_total'),
// 				'ride_status' => $booking->getMeta('ride_status'),
// 			);

// 			$user = User::find($booking->customer_id);

// 			$user_details = array('user_id' => $user->id, 'user_name' => $user->name, 'profile_pic' => $user->getMeta('profile_pic'));

// 			$this->dest_reach_or_ride_complete($booking->id);
// 			$data['success'] = 1;
// 			$data['message'] = "Ride Completed";
// 			$data['data'] = array('rideinfo' => $rideinfo, 'user_details' => $user_details, 'ride_review' => $reviews, 'fare_breakdown' => array('base_fare' => Hyvikk::fare(strtolower(str_replace(' ', '', ($booking->vehicle_id != null ? $booking->vehicle->types->vehicletype : ($vehicle_type->vehicletype ?? "")))) . '_base_fare'), 'ride_amount' => $booking->getMeta('tax_total'), 'extra_charges' => 0)); //done
// 		}
// 		return $data;
// 	}



public function destination_reached(Request $request) {
    $booking = Bookings::find($request->get('booking_id'));

    if ($booking == null) {
        $data['success'] = 0;
        $data['message'] = "Unable to Fetch Ride Info. Please, Try again Later !";
        $data['data'] = "";
    } else {
        $driver = User::find($booking->driver_id);
        $driver->is_on = 0;
        $driver->save();

        if (Hyvikk::get('dis_format') == 'meter') {
            $unit = 'm';
        } if (Hyvikk::get('dis_format') == 'km') {
            $unit = 'km';
        }

        $booking->end_address = $request->get('end_address');
        $booking->end_lat = $request->get('end_lat');
        $booking->end_long = $request->get('end_long');
        $booking->dropoff = date('Y-m-d H:i:s');
        $booking->rideend_timestamp = date('Y-m-d H:i:s');
        $booking->ride_status = "Completed";
        $booking->driving_time = $request->get('driving_time');
        $booking->total_kms = $request->get('total_kms');
        $vehicle_type = VehicleTypeModel::find($booking->getMeta('vehicle_typeid'));

        // Payment calculations (commented out)
        /*
        $km_base = Hyvikk::fare(strtolower(str_replace(' ', '', ($booking->vehicle_id != null ? $booking->vehicle->types->vehicletype : ($vehicle_type->vehicletype ?? "")))) . '_base_km');
        if ($request->get('total_kms') <= $km_base) {
            $total_fare = Hyvikk::fare(strtolower(str_replace(' ', '', ($booking->vehicle_id != null ? $booking->vehicle->types->vehicletype : ($vehicle_type->vehicletype ?? "")))) . '_base_fare');
        } else {
            $total_fare = Hyvikk::fare(strtolower(str_replace(' ', '', ($booking->vehicle_id != null ? $booking->vehicle->types->vehicletype : ($vehicle_type->vehicletype ?? "")))) . '_base_fare') + (($request->get('total_kms') - $km_base) * Hyvikk::fare(strtolower(str_replace(' ', '', ($booking->vehicle_id != null ? $booking->vehicle->types->vehicletype : ($vehicle_type->vehicletype ?? "")))) . '_std_fare'));
        }
        $count = 0;
        if (Hyvikk::get('tax_charge') != "null") {
            $taxes = json_decode(Hyvikk::get('tax_charge'), true);
            foreach ($taxes as $key => $val) {
                $count = $count + $val;
            }
        }
        $booking->tax_total = (($total_fare * $count) / 100) + $total_fare;
        $booking->total_tax_percent = $count;
        $booking->total_tax_charge_rs = ($total_fare * $count) / 100;
        $booking->total = $total_fare;
        */

        $booking->date = date('Y-m-d');
        $booking->waiting_time = 0;
        $booking->mileage = $request->get('total_kms');
        $booking->save();

        $ride_review = ReviewModel::where('booking_id', $booking->id)->first();

        if ($ride_review == null) {
            $reviews = array('user_id' => '', 'ratings' => '', 'review_text' => '', 'date' => '');
        } else {
            $reviews = array(
                'user_id' => $ride_review->user_id,
                'ratings' => $ride_review->ratings,
                'review_text' => $ride_review->review_text,
                'date' => date('Y-m-d', strtotime($ride_review->created_at))
            );
        }

        $rideinfo = array(
            'booking_id' => $booking->id,
            'source_address' => $booking->getMeta('start_address'),
            'dest_address' => $booking->dest_addr,
            'source_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('ridestart_timestamp'))),
            'dest_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('rideend_timestamp'))),
            'book_timestamp' => date('Y-m-d H:i:s', strtotime($booking->created_at)),
            'ridestart_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('ridestart_timestamp'))),
            'driving_time' => $booking->getMeta('driving_time'),
            'total_kms' => $booking->getMeta('total_kms') . " " . $unit,
            'amount' => null, // Payment logic removed
            'ride_status' => $booking->getMeta('ride_status'),
        );

        // $user = User::find($booking->customer_id);

        // $user_details = array(
        //     'user_id' => $user->id,
        //     'user_name' => $user->name,
        //     'profile_pic' => $user->getMeta('profile_pic')
        // );

        // $this->dest_reach_or_ride_complete($booking->id);

        $data['success'] = 1;
        $data['message'] = "Ride Completed";
        $data['data'] = array(
            'rideinfo' => $rideinfo,
            // 'user_details' => $user_details,
            // 'ride_review' => $reviews,
            // 'fare_breakdown' => array(
            //     'base_fare' => null, // Payment logic removed
            //     'ride_amount' => null, // Payment logic removed
            //     'extra_charges' => 0
            // )
        );
    }
    return $data;
}


	public function dest_reach_or_ride_complete($id) {
		$booking = Bookings::find($id);
		$rating = ReviewModel::where('booking_id', $id)->first();
		if ($rating != null) {
			$r = $rating->ratings;
		} else {
			$r = null;
		}
		if (Hyvikk::get('dis_format') == 'meter') {
			$unit = 'm';
		}if (Hyvikk::get('dis_format') == 'km') {
			$unit = 'km';
		}

		$vehicle_type = VehicleTypeModel::find($booking->getMeta('vehicle_typeid'));

		$data['success'] = 1;
		$data['key'] = "ride_completed_notification";
		$data['message'] = 'Data Received.';
		$data['title'] = "Ride Completed ";
		$data['description'] = "You have Reached your Destination, Thank you !";
		$data['timestamp'] = date('Y-m-d H:i:s');
		$data['data'] = array('rideinfo' => array('booking_id' => $booking->id,
			'source_address' => $booking->pickup_addr,
			'dest_address' => $booking->dest_addr,
			'source_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('ridestart_timestamp'))),
			'dest_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('rideend_timestamp'))),
			'book_timestamp' => date('Y-m-d', strtotime($booking->created_at)),
			'ridestart_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('ridestart_timestamp'))),
			'driving_time' => $booking->getMeta('driving_time'),
			'total_kms' => $booking->getMeta('total_kms') . " " . $unit,
			'amount' => $booking->getMeta('tax_total'),
			'ride_status' => $booking->getMeta('ride_status')),
			'user_details' => array('user_id' => $booking->customer_id,
				'user_name' => $booking->customer->name,
				'profile_pic' => $booking->customer->getMeta('profile_pic')),
			'fare_breakdown' => array('base_fare' => Hyvikk::fare(strtolower(str_replace(' ', '', ($booking->vehicle_id != null ? $booking->vehicle->types->vehicletype : ($vehicle_type->vehicletype ?? "")))) . '_base_fare'), //done
				'ride_amount' => $booking->getMeta('tax_total'),
				'extra_charges' => 0),
			'driver_details' => array('driver_id' => $booking->driver_id,
				'driver_name' => $booking->driver->name,
				'profile_pic' => $booking->driver->getMeta('driver_image'),
				'ratings' => $r));

		if ($booking->customer->getMeta('fcm_id') != null) {
			// PushNotification::app('appNameAndroid')
			//     ->to($booking->customer->getMeta('fcm_id'))
			//     ->send($data);

			$push = new PushNotification('fcm');
			$push->setMessage($data)
				->setApiKey(env('server_key'))
				->setDevicesToken([$booking->customer->getMeta('fcm_id')])
				->send();
		}

	}

	public function confirm_payment(Request $request) {
		$booking = Bookings::find($request->get('booking_id'));
		$booking->status = 1;
		$booking->payment = 1;
		$booking->receipt = 1;
		$booking->payment_method = "cash";
		$booking->save();
		BookingPaymentsModel::create(['method' => 'cash', 'booking_id' => $booking->id, 'amount' => $booking->tax_total, 'payment_details' => null, 'transaction_id' => null, 'payment_status' => "succeeded"]);
		$tax_percent = 0;
		if (Hyvikk::get('tax_charge') != "null") {
			$taxes = json_decode(Hyvikk::get('tax_charge'), true);
			foreach ($taxes as $key => $val) {
				$tax_percent = $tax_percent + $val;
			}
		}

		$tax_charge_rs = ($booking->total * $tax_percent) / 100;

		if ($booking != null && $booking->status == 1) {

			$id = IncomeModel::create([
				"vehicle_id" => $booking->vehicle_id,
				"amount" => $booking->getMeta('tax_total'),
				"user_id" => $booking->customer_id,
				"date" => date('Y-m-d'),
				"mileage" => $booking->mileage,
				"income_cat" => 1,
				"income_id" => $booking->id,
				"tax_percent" => $tax_percent,
				"tax_charge_rs" => $tax_charge_rs,
			])->id;
			$income = BookingIncome::create(['booking_id' => $request->get('booking_id'), 'income_id' => $id]);
			$this->payment_notification($booking->id);
			$data['success'] = 1;
			$data['message'] = "Payment Received.";
			$data['data'] = array('booking_id' => $request->get('booking_id'), 'payment_status' => $booking->status, 'payment_mode' => 'CASH');
		} else {
			$data['success'] = 0;
			$data['message'] = "Unable to Process your Request. Please, Try again Later !";
			$data['data'] = "";
		}

		return $data;
	}

	public function payment_notification($id) {

		$booking = Bookings::find($id);
		$data['success'] = 1;
		$data['key'] = "confirm_payment_notification";
		$data['message'] = 'Payment Received.';
		$data['title'] = "Payment Received CASH, id: " . $id;
		$data['description'] = $booking->pickup_addr . "-" . $booking->dest_addr;
		$data['timestamp'] = date('Y-m-d H:i:s');
		$review = ReviewModel::where('booking_id', $id)->first();
		if ($review != null) {
			$r = array('user_id' => $review->user_id, 'booking_id' => $review->booking_id, 'ratings' => $review->ratings, 'review_text' => $review->review_text, 'date' => date('Y-m-d', strtotime($review->created_at)));
		} else {
			$r = new \stdClass;
		}
		if (Hyvikk::get('dis_format') == 'meter') {
			$unit = 'm';
		}if (Hyvikk::get('dis_format') == 'km') {
			$unit = 'km';
		}
		$vehicle_type = VehicleTypeModel::find($booking->getMeta('vehicle_typeid'));

		$data['data'] = array('rideinfo' => array('user_id' => $booking->customer_id,
			'booking_id' => $id, 'source_address' => $booking->pickup_addr,
			'dest_address' => $booking->dest_addr,
			'source_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('ridestart_timestamp'))),
			'dest_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('rideend_timestamp'))),
			'book_timestamp' => date('d-m-Y', strtotime($booking->created_at)),
			'ridestart_timestamp' => date('Y-m-d H:i:s', strtotime($booking->getMeta('ridestart_timestamp'))),
			'driving_time' => $booking->getMeta('driving_time'),
			'total_kms' => $booking->getMeta('total_kms') . " " . $unit,
			'amount' => $booking->getMeta('tax_total'),
			'ride_status' => $booking->getMeta('ride_status'),
			'payment_status' => $booking->status,
			'payment_mode' => 'CASH',
		),
			'driver_details' => array('driver_id' => $booking->driver_id,
				'driver_name' => $booking->driver->name,
				'profile_pic' => $booking->driver->getMeta('driver_image'),
			),
			'fare_breakdown' => array('base_fare' => Hyvikk::fare(strtolower(str_replace(' ', '', ($booking->vehicle_id != null ? $booking->vehicle->types->vehicletype : ($vehicle_type->vehicletype ?? "")))) . '_base_fare'), //done
				'ride_amount' => $booking->getMeta('tax_total'),
				'extra_charges' => '0',
			),
			'review' => $r,
		);
		if ($booking->customer->getMeta('fcm_id') != null) {
			// PushNotification::app('appNameAndroid')
			//     ->to($booking->customer->getMeta('fcm_id'))
			//     ->send($data);

			$push = new PushNotification('fcm');
			$push->setMessage($data)
				->setApiKey(env('server_key'))
				->setDevicesToken([$booking->customer->getMeta('fcm_id')])
				->send();
		}

	}

	public function active_drivers() {

		$drivers = User::meta()->where('users_meta.key', '=', 'is_available')->where('users_meta.value', '=', 1)->get();
		if ($drivers->toArray() == null) {

			$data['data'] = array('driver_details' => array());
		} else {
			foreach ($drivers as $d) {
				$id[] = $d->id;
			}

			$data['data'] = array('driver_details' => $id);
		}
		$data['success'] = 1;
		$data['message'] = "Data Received.";
		return $data;
	}

	public function get_settings() {
		$data['success'] = 1;
		$data['message'] = "Data Received.";
		$reasons = ReasonsModel::get();
		$vehicle_types = VehicleTypeModel::select('id', 'vehicletype', 'displayname', 'icon', 'seats')->where('isenable', 1)->get();

		$vehicle_type_data = array();
		$setings = FareSettings::all();
		foreach ($vehicle_types as $vehicle_type) {
			if ($vehicle_type->icon != null) {
				$url = $vehicle_type->icon;
			} else {
				$url = null;
			}
			$type = strtolower(str_replace(" ", "", $vehicle_type->vehicletype));
			$vehicle_type_data[] = array('id' => $vehicle_type->id,
				'vehicletype' => $vehicle_type->vehicletype,
				'displayname' => $vehicle_type->displayname,
				'icon' => $url,
				'no_seats' => $vehicle_type->seats,
				'base_fare' => Hyvikk::fare($type . '_base_fare'), //done
				'base_km' => Hyvikk::fare($type . '_base_km'),
				'std_fare' => Hyvikk::fare($type . '_std_fare'),
				'base_waiting_time' => Hyvikk::fare($type . '_base_time'),
				'weekend_base_fare' => Hyvikk::fare($type . '_weekend_base_fare'),
				'weekend_base_km' => Hyvikk::fare($type . '_weekend_base_km'),
				'weekend_wait_time' => Hyvikk::fare($type . '_weekend_wait_time'),
				'weekend_std_fare' => Hyvikk::fare($type . '_weekend_std_fare'),
				'night_base_fare' => Hyvikk::fare($type . '_night_base_fare'),
				'night_base_km' => Hyvikk::fare($type . '_night_base_km'),
				'night_wait_time' => Hyvikk::fare($type . '_night_wait_time'),
				'night_std_fare' => Hyvikk::fare($type . '_night_std_fare'),
			);

		}
		$no_seats = VehicleTypeModel::pluck('seats')->toArray();
		$max_capacity = max($no_seats);
		$reason = array();
		foreach ($reasons as $r) {
			$reason[] = $r->reason;
		}
		array_unshift($reason, "What's The Reason ?");
		$data['data'] = array('base_fare' => 500,
			'base_km' => 10,
			'std_fare' => 20,
			'base_waiting_time' => 2,
			'weekend_base_fare' => 500,
			'weekend_base_km' => 10,
			'weekend_wait_time' => 2,
			'weekend_std_fare' => 20,
			'night_base_fare' => 500,
			'night_base_km' => 10,
			'night_wait_time' => 2,
			'night_std_fare' => 20,
			'reasons' => $reason,
			'distance_format' => Hyvikk::get('dis_format'),
			'max_trip_time' => Hyvikk::api('max_trip'),
			'currency_symbol' => Hyvikk::get('currency'),
			'vehicle_types' => $vehicle_type_data,
			'max_capacity_vehicle' => $max_capacity,
		);
		return $data;

	}

	public function get_code() {
		return array(
		
			array(
				"name" => "India",
				"dial_code" => "+91",
				"code" => "IN",
			),
		);
	}

}
