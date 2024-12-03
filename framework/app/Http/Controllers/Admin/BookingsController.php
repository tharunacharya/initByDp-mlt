<?php

/*
@copyright

Fleet Manager v6.5

Copyright (C) 2017-2023 Hyvikk Solutions <https://hyvikk.com/> All rights reserved.
Design and developed by Hyvikk Solutions <https://hyvikk.com/>

 */

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Http;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Mail\CustomerInvoice;
use App\Mail\DriverBooked;
use App\Mail\VehicleBooked;
use App\Mail\BookingCancelled;
use App\Model\Address;
use App\Model\BookingIncome;
use App\Model\BookingPaymentsModel;
use App\Model\Bookings;
use App\Model\Hyvikk;
use App\Model\IncCats;
use App\Model\IncomeModel;
use App\Model\ServiceReminderModel;
use App\Model\User;
use App\Model\VehicleModel;
use App\Model\VehicleTypeModel;
use App\Model\ReasonsModel;
use Auth;
use Carbon\Carbon;
use DataTables;
use DB;
use Edujugon\PushNotification\PushNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use App\Notifications\BookingStatusUpdated;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

// use App\Services\FirebaseService;
class BookingsController extends Controller
{


	public function updateBookingStatus(Request $request, $bookingId)
	{
		$booking = Booking::findOrFail($bookingId);
		$booking->status = 'confirmed';
		$booking->save();

		// Fetch the employee and driver related to this booking
		$employee = User::find($booking->employee_id);
		$driver = User::find($booking->driver_id);

		// // Send notification to employee
		// $employee->notify(new BookingStatusUpdated($booking, 'employee'));

		// // Send notification to driver
		// $driver->notify(new BookingStatusUpdated($booking, 'driver'));

		return redirect()->back()->with('success', 'Booking status updated and notifications sent.');
	}

	public function activateSOS(Request $request)
	{
		$booking = Bookings::find($request->booking_id);

		if ($booking && $booking->ride_status == 'in_progress') {
			$booking->sos_activated = true;
			$booking->save();

			// You can add more logic here, like sending notifications to authorities

			return response()->json(['status' => 'SOS activated']);
		}

		return response()->json(['status' => 'SOS not activated']);
	}



	public function __construct()
	{
		// $this->middleware(['role:Admin']);
		$this->middleware('permission:Bookings add', ['only' => ['create']]);
		$this->middleware('permission:Bookings edit', ['only' => ['edit']]);
		$this->middleware('permission:Bookings delete', ['only' => ['bulk_delete', 'destroy']]);
		$this->middleware('permission:Bookings list');
	}
	public function transactions()
	{
		$data['data'] = BookingPaymentsModel::orderBy('id', 'desc')->get();
		return view('bookings.transactions', $data);
	}

	public function transactions_fetch_data(Request $request)
	{
		if ($request->ajax()) {
			$date_format_setting = (Hyvikk::get('date_format')) ? Hyvikk::get('date_format') : 'd-m-Y';
			$payments = BookingPaymentsModel::select('booking_payments.*')->with('booking.customer')->orderBy('id', 'desc');

			return DataTables::eloquent($payments)
				->addColumn('customer', function ($row) {
					return ($row->booking->customer->name) ?? "";
				})
				->editColumn('amount', function ($row) {
					return ($row->amount) ? Hyvikk::get('currency') . " " . $row->amount : "";
				})
				->editColumn('created_at', function ($row) use ($date_format_setting) {
					$created_at = '';
					$created_at = [
						'display' => '',
						'timestamp' => '',
					];
					if (!is_null($row->created_at)) {
						$created_at = date($date_format_setting . ' h:i A', strtotime($row->created_at));
						return [
							'display' => date($date_format_setting . ' h:i A', strtotime($row->created_at)),
							'timestamp' => Carbon::parse($row->created_at),
						];
					}
					return $created_at;
				})
				->filterColumn('created_at', function ($query, $keyword) {
					$query->whereRaw("DATE_FORMAT(created_at,'%d-%m-%Y %h:%i %p') LIKE ?", ["%$keyword%"]);
				})
				->make(true);
			//return datatables(User::all())->toJson();

		}
	}
	public function index()
	{

		$data['types'] = IncCats::get();
		$data['reasons'] = ReasonsModel::get();
		return view("bookings.index", $data);
	}


	// 	duplicate start here by dheeraj 
	public function fetch_data(Request $request)
	{
		if ($request->ajax()) {
			$date_format_setting = Hyvikk::get('date_format') ?: 'd-m-Y';

			if (Auth::user()->user_type == "C") {
				$bookings = Bookings::where('customer_id', Auth::id())->latest();
			} elseif (Auth::user()->group_id == null || Auth::user()->user_type == "S" || Auth::user()->user_type == "M" || Auth::user()->user_type == "O") {
				$bookings = Bookings::latest();
			} else {
				$vehicle_ids = VehicleModel::where('group_id', Auth::user()->group_id)->pluck('id')->toArray();
				$bookings = Bookings::whereIn('vehicle_id', $vehicle_ids)->latest();
			}

			$bookings->select('bookings.*')
				->leftJoin('vehicles', 'bookings.vehicle_id', '=', 'vehicles.id')
				->leftJoin('bookings_meta', function ($join) {
					$join->on('bookings_meta.booking_id', '=', 'bookings.id')
						->where('bookings_meta.key', '=', 'vehicle_typeid');
				})
				->leftJoin('vehicle_types', 'bookings_meta.value', '=', 'vehicle_types.id')
				->with(['customer', 'metas']);

			return DataTables::eloquent($bookings)
				->addColumn('check', function ($user) {
					return '<input type="checkbox" name="ids[]" value="' . $user->id . '" class="checkbox" id="chk' . $user->id . '" onclick=\'checkcheckbox();\'>';
				})
				->addColumn('customer', function ($row) {
					$customerIds = json_decode($row->customer_id, true);
					if (is_array($customerIds)) {
						return User::whereIn('id', $customerIds)->pluck('name')->implode(', ');
					} else {
						return $row->customer->pluck('name')->implode(', ') ?? "";
					}
				})
				->addColumn('ride_status', function ($row) {
					return ($row->getMeta('ride_status')) ?? "";
				})
				->editColumn('pickup_addr', function ($row) {
					return $this->formatAddress($row->pickup_addr);
				})
				->editColumn('dest_addr', function ($row) {
					return $this->formatAddress($row->dest_addr);
				})
				->editColumn('pickup', function ($row) use ($date_format_setting) {
					if ($row->booking_type === 'book_week') {
						$pickupDates = json_decode($row->pickup, true);
						$formattedDates = array_map(function ($entry) use ($date_format_setting) {
							return date($date_format_setting . ' H:i', strtotime($entry['date'] . ' ' . $entry['time']));
						}, $pickupDates);
						return implode(', ', $formattedDates);
					} else {
						return date($date_format_setting . ' H:i', strtotime($row->pickup));
					}
				})
				->editColumn('dropoff', function ($row) use ($date_format_setting) {
					return [
						'display' => !is_null($row->dropoff) ? date($date_format_setting . ' H:i', strtotime($row->dropoff)) : '',
						'timestamp' => !is_null($row->dropoff) ? Carbon::parse($row->dropoff) : '',
					];
				})
				->editColumn('payment', function ($row) {
					return $row->payment == 1 ? '<span class="text-success"> ' . __('fleet.paid1') . ' </span>' : '<span class="text-warning"> ' . __('fleet.pending') . ' </span>';
				})
				->editColumn('tax_total', function ($row) {
					return $row->tax_total ? Hyvikk::get('currency') . " " . $row->tax_total : "";
				})
				->addColumn('vehicle', function ($row) {
					$vehicle_type = VehicleTypeModel::find($row->getMeta('vehicle_typeid'));
					return !empty($row->vehicle_id) ? $row->vehicle->make_name . '-' . $row->vehicle->model_name . '-' . $row->vehicle->license_plate : ($vehicle_type->displayname) ?? "";
				})
				->filterColumn('vehicle', function ($query, $keyword) {
					$query->whereRaw("CONCAT(vehicles.make_name , '-' , vehicles.model_name , '-' , vehicles.license_plate) like ?", ["%$keyword%"])
						->orWhereRaw("(vehicle_types.displayname like ? and bookings.vehicle_id IS NULL)", ["%$keyword%"]);
					return $query;
				})
				->filterColumn('ride_status', function ($query, $keyword) {
					$query->whereHas("metas", function ($q) use ($keyword) {
						$q->where('key', 'ride_status');
						$q->whereRaw("value like ?", ["%{$keyword}%"]);
					});
					return $query;
				})
				->filterColumn('tax_total', function ($query, $keyword) {
					$query->whereHas("metas", function ($q) use ($keyword) {
						$q->where('key', 'tax_total');
						$q->whereRaw("value like ?", ["%{$keyword}%"]);
					});
					return $query;
				})
				->addColumn('action', function ($user) {
					return view('bookings.list-actions', ['row' => $user]);
				})
				->filterColumn('payment', function ($query, $keyword) {
					$query->whereRaw("IF(payment = 1 , '" . __('fleet.paid1') . "', '" . __('fleet.pending') . "') like ? ", ["%{$keyword}%"]);
				})
				->filterColumn('pickup', function ($query, $keyword) {
					$query->whereRaw("DATE_FORMAT(pickup,'%d-%m-%Y %h:%i %p') LIKE ?", ["%$keyword%"]);
				})
				->filterColumn('dropoff', function ($query, $keyword) {
					$query->whereRaw("DATE_FORMAT(dropoff,'%d-%m-%Y %h:%i %p') LIKE ?", ["%$keyword%"]);
				})
				->rawColumns(['payment', 'action', 'check', 'pickup_addr', 'dest_addr'])
				->make(true);
		}
	}

	// 	duplicate end here by dheeraj 

	private function formatAddress($address)
	{
		if (empty($address)) {
			return '';
		}

		// Check if the address is a JSON string
		$decoded = json_decode($address, true);
		if (json_last_error() == JSON_ERROR_NONE) {
			// It's a valid JSON
			if (is_array($decoded)) {
				// If it's an array, join all non-empty values
				return implode('<br> ', array_filter($decoded));
			} else if (is_object($decoded)) {
				// If it's an object, join all non-empty values
				return implode('<br> ', array_filter((array) $decoded));
			}
		}

		// If it's not a JSON, return as is
		return $address;
	}


	public function receipt($id)
	{
		$data['id'] = $id;
		$data['i'] = BookingIncome::whereBooking_id($id)->first();
		// Load related data (customer, vehicle, driver) to avoid null reference issues in the view
		$data['booking'] = Bookings::with(['customer', 'vehicle', 'driver'])->find($id);

		if (!$data['i'] || !$data['booking']) {
			abort(404, 'Booking or income details not found');
		}

		return view("bookings.receipt", $data);
	}

	public function print($id)
	{
		$data['i'] = BookingIncome::whereBooking_id($id)->first();
		$data['booking'] = Bookings::with(['customer', 'vehicle', 'driver'])->find($id);

		if (!$data['i'] || !$data['booking']) {
			abort(404, 'Booking or income details not found');
		}

		return view("bookings.print", $data);
	}


	public function payment($id)
	{
		$booking = Bookings::find($id);
		$booking->payment = 1;
		$booking->payment_method = "cash";
		$booking->save();
		BookingPaymentsModel::create(['method' => 'cash', 'booking_id' => $id, 'amount' => $booking->tax_total, 'payment_details' => null, 'transaction_id' => null, 'payment_status' => "@lang('fleet.succeeded')"]);
		return redirect()->route('bookings.index');
	}

	public function complete_post(Request $request)
	{
		// Validate the request total
		if ($request->get('total') < 1) {
			return redirect()->back()->withErrors(["error" => "Invoice amount cannot be Zero or less than 0"]);
		}

		// Find the booking by ID
		$booking = Bookings::find($request->get("booking_id"));
		if (!$booking) {
			return redirect()->back()->withErrors(["error" => "Booking not found"]);
		}

		// Update booking meta data
		$booking->setMeta([
			'customerId' => $request->get('customerId'),
			'vehicleId' => $request->get('vehicleId'),
			'day' => $request->get('day'),
			'mileage' => $request->get('mileage'),
			'waiting_time' => $request->get('waiting_time'),
			'date' => $request->get('date'),
			'total' => round($request->get('total'), 2),
			'total_kms' => $request->get('mileage'),
			'ride_status' => 'Ongoing',
			'tax_total' => round($request->get('tax_total'), 2),
			'total_tax_percent' => round($request->get('total_tax_charge'), 2),
			'total_tax_charge_rs' => round($request->total_tax_charge_rs, 2),
		]);

		// Calculate driver commission if it exists
		if ($booking->driver && $booking->driver->driver_commision != null) {
			$commission = $booking->driver->driver_commision;
			$amount = $commission;

			if ($booking->driver->driver_commision_type == 'percent') {
				$amount = ($booking->total * $commission) / 100;
			}

			$booking->driver_amount = $amount;
			$booking->driver_commision = $commission;
			$booking->driver_commision_type = $booking->driver->driver_commision_type;
		}

		// Save the updated booking
		$booking->save();

		// Create an income record
		$income = IncomeModel::create([
			"vehicle_id" => $request->get("vehicleId"),
			"amount" => $request->get('tax_total'),
			"driver_amount" => $booking->driver_amount ?? $request->get('tax_total'),
			"user_id" => $request->get("customerId"),
			"date" => $request->get('date'),
			"mileage" => $request->get("mileage"),
			"income_cat" => $request->get("income_type"),
			"income_id" => $booking->id,
			"tax_percent" => $request->get('total_tax_charge'),
			"tax_charge_rs" => $request->total_tax_charge_rs,
		]);

		// Link the booking to the income record
		BookingIncome::create([
			'booking_id' => $request->get("booking_id"),
			"income_id" => $income->id
		]);

		// Update booking status
		$booking->receipt = 1;
		$booking->save();

		// Send email if enabled
		if (Hyvikk::email_msg('email') == 1) {
			if ($booking->customer && $booking->customer->email) {
				Mail::to($booking->customer->email)->send(new CustomerInvoice($booking));
			}
		}

		return redirect()->route("bookings.index");
	}

	public function complete($id)
	{
		$booking = Bookings::find($id);
		if ($booking) {
			$booking->status = 1;
			$booking->ride_status = "Completed";
			$booking->save();
		}
		return redirect()->route("bookings.index");
	}

	public function get_driver(Request $request)
	{
		//  dd($request->all());
		$from_date = $request->get("from_date");
		$to_date = $request->get("to_date");
		$driverInterval = Hyvikk::get('driver_interval') . ' MINUTE';
		$req_type = $request->get("req");
		if ($req_type == "new" || $request->req == 'true') {

			// This query is old version 
			// $q="SELECT id, name AS text
			// FROM users
			// WHERE user_type = 'D'
			// AND deleted_at IS NULL
			// AND id NOT IN (
			// 	SELECT DISTINCT driver_id
			// 	FROM bookings
			// 	WHERE deleted_at IS NULL
			// 	AND (
			// 		(dropoff BETWEEN DATE_ADD('" . $from_date . "', INTERVAL ".$driverInterval.") AND DATE_SUB('" . $to_date . "', INTERVAL ".$driverInterval."))
			// 		OR (pickup BETWEEN DATE_ADD('" . $from_date . "', INTERVAL ".$driverInterval.") AND DATE_SUB('" . $to_date . "', INTERVAL ".$driverInterval."))
			// 		OR (pickup < DATE_ADD('" . $from_date . "', INTERVAL ".$driverInterval.") AND dropoff > DATE_SUB('" . $to_date . "', INTERVAL ".$driverInterval."))
			// 	)
			// )";



			// Un comment this if the below query does not work

			// $q = "SELECT id, name AS text
			// FROM users
			// WHERE user_type = 'D'
			// AND deleted_at IS NULL
			// AND id NOT IN (
			// 	SELECT DISTINCT driver_id
			// 	FROM bookings
			// 	WHERE deleted_at IS NULL
			// 	AND (
			// 		(dropoff BETWEEN DATE_ADD('" . $from_date . "', INTERVAL " . $driverInterval . ") AND DATE_SUB('" . $to_date . "', INTERVAL " . $driverInterval . "))
			// 		OR (pickup BETWEEN DATE_ADD('" . $from_date . "', INTERVAL " . $driverInterval . ") AND DATE_SUB('" . $to_date . "', INTERVAL " . $driverInterval . "))
			// 		OR (pickup < DATE_ADD('" . $from_date . "', INTERVAL " . $driverInterval . ") AND dropoff > DATE_SUB('" . $to_date . "', INTERVAL " . $driverInterval . "))
			// 	)
			// )
			// AND id NOT IN (
			// 	SELECT DISTINCT driver_id
			// 	FROM bookings
			// 	WHERE deleted_at IS NULL
			// 	AND (
			// 		(pickup BETWEEN DATE_SUB('" . $from_date . "', INTERVAL " . $driverInterval . ") AND '" . $to_date . "')
			// 		OR (dropoff BETWEEN DATE_SUB('" . $from_date . "', INTERVAL " . $driverInterval . ") AND '" . $to_date . "')
			// 		OR (dropoff > '" . $to_date . "' AND pickup < DATE_ADD('" . $to_date . "', INTERVAL " . $driverInterval . "))
			// 	)
			// )";



			$q = "SELECT id, name AS text
			FROM users
			WHERE user_type = 'D'
			AND deleted_at IS NULL
			AND id NOT IN (
				SELECT DISTINCT driver_id
				FROM bookings
				WHERE deleted_at IS NULL
				AND cancellation = 0
				AND (
					(dropoff BETWEEN DATE_ADD('" . $from_date . "', INTERVAL " . $driverInterval . ") AND DATE_SUB('" . $to_date . "', INTERVAL " . $driverInterval . "))
					OR (pickup BETWEEN DATE_ADD('" . $from_date . "', INTERVAL " . $driverInterval . ") AND DATE_SUB('" . $to_date . "', INTERVAL " . $driverInterval . "))
					OR (pickup < DATE_ADD('" . $from_date . "', INTERVAL " . $driverInterval . ") AND dropoff > DATE_SUB('" . $to_date . "', INTERVAL " . $driverInterval . "))
				)
			)
			AND id NOT IN (
				SELECT DISTINCT driver_id
				FROM bookings
				WHERE deleted_at IS NULL
				AND cancellation = 0
				AND (
					(pickup BETWEEN DATE_SUB('" . $from_date . "', INTERVAL " . $driverInterval . ") AND '" . $to_date . "')
					OR (dropoff BETWEEN DATE_SUB('" . $from_date . "', INTERVAL " . $driverInterval . ") AND '" . $to_date . "')
					OR (dropoff > '" . $to_date . "' AND pickup < DATE_ADD('" . $to_date . "', INTERVAL " . $driverInterval . "))
				)
			)";


			$new = [];
			$d = collect(DB::select($q));
			foreach ($d as $ro) {
				array_push($new, array("id" => $ro->id, "text" => $ro->text));
			}
			$r['data'] = $new;
		} else {
			// dd('test');
			$id = $request->get("id");
			$current = Bookings::find($id);
			// $q = "SELECT id, name AS text
			// FROM users
			// WHERE user_type = 'D'
			// AND deleted_at IS NULL
			// AND id NOT IN (
			// 	SELECT DISTINCT driver_id
			// 	FROM bookings
			// 	WHERE deleted_at IS NULL
			// 	AND cancellation = 0
			// 	AND (
			// 		(dropoff BETWEEN DATE_ADD('" . $from_date . "', INTERVAL ".$driverInterval.") AND DATE_SUB('" . $to_date . "', INTERVAL ".$driverInterval."))
			// 		OR (pickup BETWEEN DATE_ADD('" . $from_date . "', INTERVAL ".$driverInterval.") AND DATE_SUB('" . $to_date . "', INTERVAL ".$driverInterval."))
			// 		OR (pickup < DATE_ADD('" . $from_date . "', INTERVAL ".$driverInterval.") AND dropoff > DATE_SUB('" . $to_date . "', INTERVAL ".$driverInterval."))
			// 	)
			// 	AND driver_id <> '" . $current->driver_id . "'
			// )";

			$q = "SELECT id, name AS text
			FROM users
			WHERE user_type = 'D'
			AND deleted_at IS NULL
			AND id NOT IN (
				SELECT DISTINCT driver_id
				FROM bookings
				WHERE deleted_at IS NULL
				AND cancellation = 0
				AND (
					(dropoff BETWEEN DATE_ADD('" . $from_date . "', INTERVAL " . $driverInterval . ") AND DATE_SUB('" . $to_date . "', INTERVAL " . $driverInterval . "))
					OR (pickup BETWEEN DATE_ADD('" . $from_date . "', INTERVAL " . $driverInterval . ") AND DATE_SUB('" . $to_date . "', INTERVAL " . $driverInterval . "))
					OR (pickup < DATE_ADD('" . $from_date . "', INTERVAL " . $driverInterval . ") AND dropoff > DATE_SUB('" . $to_date . "', INTERVAL " . $driverInterval . "))
				)
			)
			AND id NOT IN (
				SELECT DISTINCT driver_id
				FROM bookings
				WHERE deleted_at IS NULL
				AND cancellation = 0
				AND (
					(pickup BETWEEN DATE_SUB('" . $from_date . "', INTERVAL " . $driverInterval . ") AND '" . $to_date . "')
					OR (dropoff BETWEEN DATE_SUB('" . $from_date . "', INTERVAL " . $driverInterval . ") AND '" . $to_date . "')
					OR (dropoff > '" . $to_date . "' AND pickup < DATE_ADD('" . $to_date . "', INTERVAL " . $driverInterval . "))
				)
				AND driver_id <> '" . $current->driver_id . "'
			)";


			$d = collect(DB::select($q));
			$chk = $d->where('id', $current->driver_id);
			$r['show_error'] = "yes";
			if (count($chk) > 0) {
				$r['show_error'] = "no";
			}
			$new = array();
			foreach ($d as $ro) {
				if ($ro->id === $current->driver_id) {
					array_push($new, array("id" => $ro->id, "text" => $ro->text, 'selected' => true));
				} else {
					array_push($new, array("id" => $ro->id, "text" => $ro->text));
				}
			}
			$r['data'] = $new;
		}
		// dd($r);
		$new1 = [];
		foreach ($r['data'] as $r1) {
			$user = User::where('id', $r1['id'])->first();
			if ($user->getMeta('is_active') == 1) {
				// dd($r1);
				$new1[] = $r1;
			}
		}
		$r['data'] = $new1;
		return $r;
	}

	public function get_vehicle(Request $request)
	{

		$from_date = $request->get("from_date");
		$to_date = $request->get("to_date");
		$req_type = $request->get("req");
		$vehicleInterval = Hyvikk::get('vehicle_interval') . ' MINUTE';
		if ($req_type == "new") {
			$xy = array();
			if (Auth::user()->group_id == null || Auth::user()->user_type == "S") {
				// $q = "select id from vehicles where in_service=1 and deleted_at is null  and  id not in(select vehicle_id from bookings where  deleted_at is null  and ((dropoff between '" . $from_date . "' and '" . $to_date . "' or pickup between '" . $from_date . "' and '" . $to_date . "') or (DATE_ADD(dropoff, INTERVAL 10 MINUTE)>='" . $from_date . "' and DATE_SUB(pickup, INTERVAL 10 MINUTE)<='" . $to_date . "')))";
				$q = "SELECT id
				FROM vehicles
				WHERE in_service = 1
				AND deleted_at IS NULL
				AND id NOT IN (
					SELECT DISTINCT vehicle_id
					FROM bookings
					WHERE deleted_at IS NULL
					AND cancellation = 0
					AND (
						(dropoff BETWEEN '" . $from_date . "' AND '" . $to_date . "'
						OR pickup BETWEEN '" . $from_date . "' AND '" . $to_date . "')
						OR (DATE_ADD(dropoff, INTERVAL " . $vehicleInterval . ") >= '" . $from_date . "' AND DATE_SUB(pickup, INTERVAL " . $vehicleInterval . ") <= '" . $to_date . "')
					)
				)";
			} else {
				// $q = "select id from vehicles where in_service=1 and deleted_at is null and group_id=" . Auth::user()->group_id . " and  id not in(select vehicle_id from bookings where  deleted_at is null  and ((dropoff between '" . $from_date . "' and '" . $to_date . "' or pickup between '" . $from_date . "' and '" . $to_date . "') or (DATE_ADD(dropoff, INTERVAL 10 MINUTE)>='" . $from_date . "' and DATE_SUB(pickup, INTERVAL 10 MINUTE)<='" . $to_date . "')))";

				$q = "SELECT id
				FROM vehicles
				WHERE in_service = 1
				AND deleted_at IS NULL
				AND group_id = " . Auth::user()->group_id . "
				AND id NOT IN (
					SELECT DISTINCT vehicle_id
					FROM bookings
					WHERE deleted_at IS NULL
					AND cancellation = 0
					AND (
						(dropoff BETWEEN '" . $from_date . "' AND '" . $to_date . "'
						OR pickup BETWEEN '" . $from_date . "' AND '" . $to_date . "')
						OR (DATE_ADD(dropoff, INTERVAL " . $vehicleInterval . ") >= '" . $from_date . "' AND DATE_SUB(pickup, INTERVAL " . $vehicleInterval . ") <= '" . $to_date . "')
					)
				)";
			}
			$d = collect(DB::select($q));

			$new = array();
			foreach ($d as $ro) {
				$vhc = VehicleModel::find($ro->id);
				$text = $vhc->make_name . "-" . $vhc->model_name . "-" . $vhc->license_plate;
				array_push($new, array("id" => $ro->id, "text" => $text));

			}
			//dd($new);
			$r['data'] = $new;
			return $r;

		} else {
			$id = $request->get("id");
			$current = Bookings::find($id);
			if ($current->vehicle_typeid != null) {
				$condition = " and type_id = '" . $current->vehicle_typeid . "'";

			} else {
				$condition = "";
			}

			if (Auth::user()->group_id == null || Auth::user()->user_type == "S") {
				// $q = "select id from vehicles where in_service=1 " . $condition . " and id not in (select vehicle_id from bookings where id!=$id and  deleted_at is null  and ((dropoff between '" . $from_date . "' and '" . $to_date . "' or pickup between '" . $from_date . "' and '" . $to_date . "') or (DATE_ADD(dropoff, INTERVAL 10 MINUTE)>='" . $from_date . "' and DATE_SUB(pickup, INTERVAL 10 MINUTE)<='" . $to_date . "')))";
				$q = "SELECT id
				FROM vehicles
				WHERE in_service = 1" . $condition . "
				AND id NOT IN (
					SELECT DISTINCT vehicle_id
					FROM bookings
					WHERE id != $id
					AND deleted_at IS NULL
					AND cancellation = 0
					AND (
						(dropoff BETWEEN DATE_ADD('" . $from_date . "', INTERVAL " . $vehicleInterval . ") AND DATE_SUB('" . $to_date . "', INTERVAL " . $vehicleInterval . "))
						OR (pickup BETWEEN DATE_ADD('" . $from_date . "', INTERVAL " . $vehicleInterval . ") AND DATE_SUB('" . $to_date . "', INTERVAL " . $vehicleInterval . "))
						OR (DATE_ADD(dropoff, INTERVAL " . $vehicleInterval . ") >= '" . $from_date . "' AND DATE_SUB(pickup, INTERVAL " . $vehicleInterval . ") <= '" . $to_date . "')
					)
				)";
			} else {
				// $q = "select id from vehicles where in_service=1 " . $condition . " and group_id=" . Auth::user()->group_id . " and id not in (select vehicle_id from bookings where id!=$id and  deleted_at is null  and ((dropoff between '" . $from_date . "' and '" . $to_date . "' or pickup between '" . $from_date . "' and '" . $to_date . "') or (DATE_ADD(dropoff, INTERVAL 10 MINUTE)>='" . $from_date . "' and DATE_SUB(pickup, INTERVAL 10 MINUTE)<='" . $to_date . "')))";
				$q = "SELECT id
				FROM vehicles
				WHERE in_service = 1" . $condition . "
				AND group_id = " . Auth::user()->group_id . "
				AND id NOT IN (
					SELECT DISTINCT vehicle_id
					FROM bookings
					WHERE id != $id
					AND deleted_at IS NULL
					AND cancellation = 0
					AND (
						(dropoff BETWEEN DATE_ADD('" . $from_date . "', INTERVAL " . $vehicleInterval . ") AND DATE_SUB('" . $to_date . "', INTERVAL " . $vehicleInterval . "))
						OR (pickup BETWEEN DATE_ADD('" . $from_date . "', INTERVAL " . $vehicleInterval . ") AND DATE_SUB('" . $to_date . "', INTERVAL " . $vehicleInterval . "))
						OR (DATE_ADD(dropoff, INTERVAL " . $vehicleInterval . ") >= '" . $from_date . "' AND DATE_SUB(pickup, INTERVAL " . $vehicleInterval . ") <= '" . $to_date . "')
					)
				)";
			}

			$d = collect(DB::select($q));

			$chk = $d->where('id', $current->vehicle_id);
			$r['show_error'] = "yes";
			if (count($chk) > 0) {
				$r['show_error'] = "no";
			}

			$new = array();
			foreach ($d as $ro) {
				$vhc = VehicleModel::find($ro->id);
				$text = $vhc->make_name . "-" . $vhc->model_name . "-" . $vhc->license_plate;
				if ($ro->id == $current->vehicle_id) {
					array_push($new, array("id" => $ro->id, "text" => $text, "selected" => true));
				} else {
					array_push($new, array("id" => $ro->id, "text" => $text));
				}
			}
			$r['data'] = $new;
			return $r;
		}

	}

	public function calendar_event($id)
	{
		$data['booking'] = Bookings::find($id);
		return view("bookings.event", $data);

	}
	public function calendar_view()
	{
		$booking = Bookings::where('user_id', Auth::user()->id)->where('cancellation', 0)->exists();
		return view("bookings.calendar", compact('booking'));
	}

	public function service_view($id)
	{
		$data['service'] = ServiceReminderModel::find($id);
		return view("bookings.service_event", $data);
	}

	public function calendar(Request $request)
	{
		$data = array();
		$start = $request->get("start");
		$end = $request->get("end");
		if (Auth::user()->group_id == null || Auth::user()->user_type == "S") {
			$b = Bookings::where('cancellation', 0)->get();
		} else {
			$vehicle_ids = VehicleModel::where('group_id', Auth::user()->group_id)->pluck('id')->toArray();
			$b = Bookings::whereIn('vehicle_id', $vehicle_ids)->where('cancellation', 0)->get();
		}

		foreach ($b as $booking) {
			$x['start'] = $booking->pickup;
			$x['end'] = $booking->dropoff;
			if ($booking->status == 1) {
				$color = "grey";
			} else {
				$color = "red";
			}
			$x['backgroundColor'] = $color;
			$x['title'] = $booking->customer->name;
			$x['id'] = $booking->id;
			$x['type'] = 'calendar';

			array_push($data, $x);
		}

		$reminders = ServiceReminderModel::get();
		foreach ($reminders as $r) {
			$interval = substr($r->services->overdue_unit, 0, -3);
			$int = $r->services->overdue_time . $interval;
			$date = date('Y-m-d', strtotime($int, strtotime(date('Y-m-d'))));
			if ($r->last_date != 'N/D') {
				$date = date('Y-m-d', strtotime($int, strtotime($r->last_date)));
			}

			$x['start'] = $date;
			$x['end'] = $date;

			$color = "green";

			$x['backgroundColor'] = $color;
			$x['title'] = $r->services->description;
			$x['id'] = $r->id;
			$x['type'] = 'service';
			array_push($data, $x);
		}
		return $data;
	}
	public function create()
	{
		// Get the group ID of the currently authenticated user
		$userGroupId = Auth::user()->group_id;
		$assignedAdminId = Auth::user()->id; // Get the ID of the currently authenticated admin
		$userType = Auth::user()->user_type; // Get the user type

		if ($userType == 'S' || $userType == 'M') {
			// If the user is a Super Admin, fetch all customers
			$data['customers'] = User::where('user_type', 'C')->get();
		} else {
			// Fetch customers assigned to this admin
			$data['customers'] = User::where('user_type', 'C')
				->where('assigned_admin', $assignedAdminId)
				->get();
		}

		// Fetch drivers, filtering for active ones
		$drivers = User::where('user_type', 'D')->get();
		$data['drivers'] = $drivers->filter(function ($driver) {
			return $driver->getMeta('is_active') == 1;
		});

		// Fetch addresses for the currently authenticated user
		$data['addresses'] = Address::where('customer_id', $assignedAdminId)->get();

		// Fetch vehicles based on the user's group ID and service status
		if ($userGroupId == null) {
			$data['vehicles'] = VehicleModel::where('in_service', '1')->get();
		} else {
			$data['vehicles'] = VehicleModel::where([
				['group_id', $userGroupId],
				['in_service', '1']
			])->get();
		}

		// Return the view with the data
		return view("bookings.create", $data);
	}



	public function edit($id)
	{
		try {
			// Fetch the booking
			$booking = Bookings::findOrFail($id);

			// Log the booking details
			$pickup = $booking->pickup ?? '';
			$dropoff = $booking->dropoff ?? '';
			// \Log::info('Booking Details: ', ['id' => $id, 'pickup' => $pickup, 'dropoff' => $dropoff]);

			// Fetch available drivers
			$drivers = User::where('user_type', 'D')
				->whereNull('deleted_at')
				->whereNotIn('id', function ($query) use ($id, $pickup, $dropoff) {
					$query->select('user_id')
						->from('bookings')
						->where('status', 0)
						->where('id', '!=', $id)
						->whereNull('deleted_at')
						->where(function ($query) use ($pickup, $dropoff) {
							$query->whereBetween(DB::raw("DATE_SUB(pickup, INTERVAL 15 MINUTE)"), [$pickup, $dropoff])
								->orWhereBetween(DB::raw("DATE_ADD(dropoff, INTERVAL 15 MINUTE)"), [$pickup, $dropoff])
								->orWhereBetween('dropoff', [$pickup, $dropoff]);
						});
				})
				->get();

			// Prepare the condition based on vehicle_typeid
			$condition = $booking->vehicle_typeid ? " AND type_id = '" . $booking->vehicle_typeid . "'" : "";

			// Fetch available vehicles
			$vehiclesQuery = VehicleModel::where('in_service', 1)
				->whereNull('deleted_at')
				->whereRaw("id NOT IN (
                SELECT vehicle_id FROM bookings
                WHERE status = 0 AND id != ?
                AND deleted_at IS NULL
                AND (
                    (DATE_SUB(pickup, INTERVAL 15 MINUTE) BETWEEN ? AND ?)
                    OR (DATE_ADD(dropoff, INTERVAL 15 MINUTE) BETWEEN ? AND ?)
                    OR (dropoff BETWEEN ? AND ?)
                )
            )", [$id, $pickup, $dropoff, $pickup, $dropoff, $pickup, $dropoff]);

			if (Auth::user()->group_id) {
				$vehiclesQuery->where('group_id', Auth::user()->group_id);
			}

			$vehicles = $vehiclesQuery->get();

			// Check if customer_id is a valid JSON string and decode it
			$customerIds = json_decode($booking->customer_id, true);

			if (json_last_error() === JSON_ERROR_NONE && is_array($customerIds)) {
				// Fetch customers as a collection
				$customers = User::whereIn('id', $customerIds)->get(['id', 'name']);
			} else {
				// Fetch a single customer as a collection
				$customers = User::where('id', $booking->customer_id)->get(['id', 'name']);
			}

			// \Log::info('Test Log: test1');

			// $this->booking_notification($id); // Sending the notification with booking ID
			// \Log::info('Test Log: test2');

			// Prepare data for the view
			$index['drivers'] = $drivers;
			$index['vehicles'] = $vehicles;
			$index['data'] = $booking;
			$index['customers'] = $customers->pluck('name', 'id')->toArray(); // Array of customer names
			$index['udfs'] = unserialize($booking->getMeta('udf'));

			return view("bookings.edit", $index);

		} catch (\Exception $e) {
			\Log::error('Error in edit function: ' . $e->getMessage());
			return response()->json(['error' => 'An error occurred while processing your request.'], 500);
		}
	}






	public function destroy(Request $request)
	{
		// dd($request->get('id'));
		Bookings::find($request->get('id'))->delete();
		IncomeModel::where('income_id', $request->get('id'))->where('income_cat', 1)->delete();

		return redirect()->route('bookings.index');
	}

	protected function check_booking($pickup, $dropoff, $vehicle)
	{

		$chk = DB::table("bookings")
			->where("status", 0)
			->where("vehicle_id", $vehicle)
			->whereNull("deleted_at")
			->where("pickup", ">=", $pickup)
			->where("dropoff", "<=", $dropoff)
			->get();

		if (count($chk) > 0) {
			return false;
		} else {
			return true;
		}

	}

	public function store(BookingRequest $request)
	{
		//   var_dump($request->all());
//       exit;
		// Validate booking
		$xx = $this->check_booking($request->get("pickup"), $request->get("dropoff"), $request->get("vehicle_id"));
		if ($xx) {
			// Prepare booking data
			$bookingData = $request->except(['pickup_addr', 'dest_addr', 'note', 'udf']); // Exclude arrays from booking creation
			$customerIds = $request->input('customer_id', []);
			$bookingData['customer_id'] = json_encode($customerIds);
			// Serialize array fields
			// $bookingData['pickup_addr'] = serialize($request->get('pickup_addr'));
			// $bookingData['dest_addr'] = serialize($request->get('dest_addr'));
			// $bookingData['note'] = serialize($request->get('note'));
			$travellers = intval($request->get('travellers', 1));
			if ($travellers > 1) {
				// Create objects for multiple travelers
				$pickupAddrs = [];
				$destAddrs = [];
				$notes = [];

				for ($i = 0; $i < $travellers; $i++) {
					$pickupAddrs["pickup_add" . ($i + 1)] = $request->get('pickup_addr')[$i] ?? '';
					$destAddrs["dest_addr" . ($i + 1)] = $request->get('dest_addr')[$i] ?? '';
					$notes["note" . ($i + 1)] = $request->get('note')[$i] ?? '';
				}

				$bookingData['pickup_addr'] = json_encode($pickupAddrs);
				$bookingData['dest_addr'] = json_encode($destAddrs);
				$bookingData['note'] = json_encode($notes);
			} else {
				// For single traveler, keep the original array structure
				$bookingData['pickup_addr'] = json_encode($request->get('pickup_addr'));
				$bookingData['dest_addr'] = json_encode($request->get('dest_addr'));
				$bookingData['note'] = json_encode($request->get('note'));
			}

			$bookingData['udf'] = serialize($request->get('udf'));
			// var_dump($bookingData);
			// exit;
			// Create the booking record
			$booking = Bookings::create($bookingData);
			$id = $booking->id;

			// Update additional booking details
			$booking->user_id = $request->get("user_id");
			$booking->driver_id = $request->get('driver_id');
			$dropoff = Carbon::parse($booking->dropoff);
			$pickup = Carbon::parse($booking->pickup);
			$diff = $pickup->diffInMinutes($dropoff);
			$booking->duration = $diff;
			$booking->accept_status = 1; //0=yet to accept, 1=accept
			$booking->ride_status = "Upcoming";
			$booking->booking_type = 1;
			$booking->journey_date = date('d-m-Y', strtotime($booking->pickup));
			$booking->journey_time = date('H:i:s', strtotime($booking->pickup));
			$booking->save();


			\Log::info('Test Log: test1');

			$this->booking_notification($id); // Sending the notification with booking ID
			\Log::info('Test Log: test2');

			// Send notifications
			// $this->booking_notification($booking->id);
			// $this->sms_notification($booking->id);
			// $this->push_notification($booking->id);

			// if (Hyvikk::email_msg('email') == 1) {
			//     Mail::to($booking->customer->email)->send(new VehicleBooked($booking));
			//     Mail::to($booking->driver->email)->send(new DriverBooked($booking));
			// }

			return redirect()->route("bookings.index");
		} else {
			return redirect()->route("bookings.create")->withErrors(["error" => "Selected Vehicle is not Available in Given Timeframe"])->withInput();
		}
	}


	public function sms_notification($booking_id)
	{
		$booking = Bookings::find($booking_id);

		$id = Hyvikk::twilio('sid');
		$token = Hyvikk::twilio('token');
		$from = Hyvikk::twilio('from');
		$to = $booking->customer->mobno; // twilio trial verified number
		$driver_no = $booking->driver->phone_code . $booking->driver->phone;

		$customer_name = $booking->customer->name;
		$customer_contact = $booking->customer->mobno;
		$driver_name = $booking->driver->name;
		$driver_contact = $booking->driver->phone;
		$pickup_address = $booking->pickup_addr;
		$destination_address = $booking->dest_addr;
		$pickup_datetime = date(Hyvikk::get('date_format') . " H:i", strtotime($booking->pickup));
		$dropoff_datetime = date(Hyvikk::get('date_format') . " H:i", strtotime($booking->dropoff));
		$passengers = $booking->travellers;

		$search = ['$customer_name', '$customer_contact', '$pickup_address', '$pickup_datetime', '$passengers', '$destination_address', '$dropoff_datetime', '$driver_name', '$driver_contact'];
		$replace = [$customer_name, $customer_contact, $pickup_address, $pickup_datetime, $passengers, $destination_address, $dropoff_datetime, $driver_name, $driver_contact];

		// send sms to customer
		$body = str_replace($search, $replace, Hyvikk::twilio("customer_message"));

		$url = "https://api.twilio.com/2010-04-01/Accounts/$id/SMS/Messages";

		// $new_body = str_split($body, 120);
		$new_body = explode("\n", wordwrap($body, 120));

		foreach ($new_body as $row) {
			$data = array(
				'From' => $from,
				'To' => $to,
				'Body' => $row,
			);
			$post = http_build_query($data);
			$x = curl_init($url);
			curl_setopt($x, CURLOPT_POST, true);
			curl_setopt($x, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($x, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($x, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($x, CURLOPT_USERPWD, "$id:$token");
			curl_setopt($x, CURLOPT_POSTFIELDS, $post);
			$y = curl_exec($x);
			curl_close($x);
		}

		// send sms to drivers
		$driver_body = str_replace($search, $replace, Hyvikk::twilio("driver_message"));

		$msg_body = explode("\n", wordwrap($driver_body, 120));

		foreach ($msg_body as $row) {
			$data = array(
				'From' => $from,
				'To' => $driver_no,
				'Body' => $row,
			);
			$post = http_build_query($data);
			$x = curl_init($url);
			curl_setopt($x, CURLOPT_POST, true);
			curl_setopt($x, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($x, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($x, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($x, CURLOPT_USERPWD, "$id:$token");
			curl_setopt($x, CURLOPT_POSTFIELDS, $post);
			$y = curl_exec($x);
			curl_close($x);
		}
		// dd($y);

	}

	public function push_notification($id)
	{
		$booking = Bookings::find($id);
		$auth = array(
			'VAPID' => array(
				'subject' => 'Alert about new post',
				'publicKey' => 'BKt+swntut+5W32Psaggm4PVQanqOxsD5PRRt93p+/0c+7AzbWl87hFF184AXo/KlZMazD5eNb1oQVNbK1ti46Y=',
				'privateKey' => 'NaMmQJIvddPfwT1rkIMTlgydF+smNzNXIouzRMzc29c=', // in the real world, this would be in a secret file
			),
		);

		$select1 = DB::table('push_notification')->select('*')->whereIn('user_id', [$booking->user_id])->get()->toArray();

		$webPush = new WebPush($auth);

		foreach ($select1 as $fetch) {
			$sub = Subscription::create([
				'endpoint' => $fetch->endpoint, // Firefox 43+,
				'publicKey' => $fetch->publickey, // base 64 encoded, should be 88 chars
				'authToken' => $fetch->authtoken, // base 64 encoded, should be 24 chars
				'contentEncoding' => $fetch->contentencoding,
			]);
			$user = User::find($fetch->user_id);

			$title = __('fleet.new_booking');
			$body = __('fleet.customer') . ": " . $booking->customer->name . ", " . __('fleet.pickup') . ": " . date(Hyvikk::get('date_format') . ' g:i A', strtotime($booking->pickup)) . ", " . __('fleet.pickup_addr') . ": " . $booking->pickup_addr . ", " . __('fleet.dropoff_addr') . ": " . $booking->dest_addr;
			$url = url('admin/bookings');

			$array = array(
				'title' => $title ?? "",
				'body' => $body ?? "",
				'img' => url('assets/images/' . Hyvikk::get('icon_img')),
				'url' => $url ?? url('admin/'),
			);
			$object = json_encode($array);

			if ($fetch->user_id == $user->id) {
				$test = $webPush->sendOneNotification($sub, $object);
			}
			foreach ($webPush->flush() as $report) {

				$endpoint = $report->getRequest()->getUri()->__toString();

			}

		}

	}

	// public function update(BookingRequest $request) {
	// 	//   dd($request->all());
	// 	$max_seats = VehicleModel::find($request->get('vehicle_id'))->types->seats;
	// 	if($request->get("travellers") > $max_seats){
	// 		return redirect()->route("bookings.edit",$request->get('id'))->withErrors(["error" => "Number of Travellers exceed seating capity of the vehicle | Seats Available : ".$max_seats.""])->withInput();
	// 	}

	// 	$booking = Bookings::whereId($request->get("id"))->first();


	// 	$booking->vehicle_id = $request->get("vehicle_id");
	// 	$booking->user_id = $request->get("user_id");
	// 	$booking->driver_id = $request->get('driver_id');
	// 	$booking->travellers = $request->get("travellers");
	// 	$booking->pickup = $request->get("pickup");
	// 	$booking->dropoff = $request->get("dropoff");
	// 	$booking->pickup_addr = $request->get("pickup_addr");
	// 	$booking->dest_addr = $request->get("dest_addr");
	// 	if ($booking->ride_status == null) {
	// 		$booking->ride_status = "Upcoming";
	// 	}

	// 	$dropoff = Carbon::parse($request->get("dropoff"));
	// 	$pickup = Carbon::parse($request->get("pickup"));
	// 	$booking->note = $request->get('note');
	// 	$diff = $pickup->diffInMinutes($dropoff);
	// 	$booking->duration = $diff;
	// 	$booking->journey_date = date('d-m-Y', strtotime($request->get("pickup")));
	// 	$booking->journey_time = date('H:i:s', strtotime($request->get("pickup")));
	// 	$booking->udf = serialize($request->get('udf'));
	// 	$booking->save();




	// 	return redirect()->route('bookings.index');

	// }


	public function update(BookingRequest $request)
	{
		try {
			// Validate vehicle seat capacity
			$max_seats = VehicleModel::find($request->get('vehicle_id'))->types->seats;
			if ($request->get("travellers") > $max_seats) {
				return redirect()
					->route("bookings.edit", $request->get('id'))
					->withErrors(["error" => "Number of Travellers exceed seating capacity of the vehicle | Seats Available: " . $max_seats])
					->withInput();
			}

			// Retrieve and update booking
			$booking = Bookings::findOrFail($request->get("id"));
			$booking->vehicle_id = $request->get("vehicle_id");
			$booking->user_id = $request->get("user_id");
			$booking->driver_id = $request->get('driver_id');
			$booking->travellers = $request->get("travellers");
			$booking->pickup = $request->get("pickup");
			$booking->dropoff = $request->get("dropoff");
			$booking->pickup_addr = $request->get("pickup_addr");
			$booking->dest_addr = $request->get("dest_addr");
			if ($booking->ride_status === null) {
				$booking->ride_status = "Upcoming";
			}

			// Calculate trip duration
			$dropoff = Carbon::parse($request->get("dropoff"));
			$pickup = Carbon::parse($request->get("pickup"));
			$diff = $pickup->diffInMinutes($dropoff);
			$booking->duration = $diff;

			// Set additional fields
			$booking->journey_date = date('d-m-Y', strtotime($request->get("pickup")));
			$booking->journey_time = date('H:i:s', strtotime($request->get("pickup")));
			$booking->note = $request->get('note');
			$booking->udf = serialize($request->get('udf'));
			$booking->save();

			// Call the booking_notification method to send notification
			$this->booking_notification($booking->id);

			return redirect()->route('bookings.index');
		} catch (\Exception $e) {
			\Log::error('Error updating booking: ' . $e->getMessage());
			return redirect()
				->route('bookings.edit', $request->get('id'))
				->withErrors(['error' => 'Failed to update booking. Please try again.'])
				->withInput();
		}
	}


	public function prev_address(Request $request)
	{
		$booking = Bookings::where('customer_id', $request->get('id'))->orderBy('id', 'desc')->first();
		if ($booking != null) {
			$r = array('pickup_addr' => $booking->pickup_addr, 'dest_addr' => $booking->dest_addr);
		} else {
			$r = array('pickup_addr' => "", 'dest_addr' => "");
		}

		return $r;
	}

	public function print_bookings()
	{
		if (Auth::user()->user_type == "C") {
			$data['data'] = Bookings::where('customer_id', Auth::user()->id)->orderBy('id', 'desc')->get();
		} else {
			$data['data'] = Bookings::orderBy('id', 'desc')->get();
		}

		return view('bookings.print_bookings', $data);
	}
	public function booking_notification($id)
	{
		\Log::info('notification function called');

		$booking = Bookings::find($id);
		$notificationData = [
			'success' => 1,
		];
		
	
		$notificationData['title'] = "New Upcoming Ride for you! test here buddy";

		// Find the driver
		$driver = User::find($booking->driver_id);
		
		if (true) {
			$firebaseService = new \App\Services\FirebaseService();
			
			$title = "New Upcoming Ride Assigned!";
			$body = "You have a new ride. Please check your dashboard for details.";
			$deviceToken = $driver->getMeta('fcm_id');
			// $deviceToken = 'eOQa7orQR2WYThsbID172Z:APA91bFvurTePpIggNjQaZ5oZutoZpYE1-SLjoDUd6MRqVW6rK6_mrccfhIwDByQcO_cypaBZG8MYg2y2APbYk2YH5A5CB8FTvTWRhdgYdYhCc7crL5QOJg';

			$customData = [
				'data' => "You have a new ride. Please check your dashboard for details.",
			];
			// Send notification via FirebaseService
			$result = $firebaseService->sendNotificationWithCustomData($deviceToken, $title, $body, $customData);
			if ($result) {
				\Log::info("Notification sent successfully to driver ID: {$driver->id}");
			} else {
				\Log::error("Failed to send notification to driver ID: {$driver->id}");
			}
		}
		\log::info("test message for customer");
		// Get customer details
		$customerIds = json_decode($booking->customer_id, true);
		$customerFCM = null;
		if (is_array($customerIds) && count($customerIds) > 0) {
			$customer = User::find($customerIds[0]);
			if ($customer) {
				  $customerFCM = $customer->getMeta('fcm_id'); // Fetch FCM token for customer
				// $customerFCM = 'ffiyvpW9RpW3xwU-jNm4bY:APA91bFeD-XhKP0nOHI9PYUK4nKwExP9IZBKerBJRv5COHmzSZOBlzIt1ZFI5C9tde8ahVf0Ci1XLGE34GTugynrTL5KO9zNEK2SpCm7PUm_Wk82sre9r1Y';
			}
		}

		
			// Retrieve the note from the request
			$rawNote = request()->get('note');
			// Ensure $rawNote is properly decoded if it's a JSON string
			if (is_string($rawNote)) {
				$decodedNote = json_decode($rawNote, true); // Decode to an array
			} else {
				$decodedNote = $rawNote; // If already an array, use as is
			}
			// Handle the case where decoding fails
			if (!is_array($decodedNote)) {
				$decodedNote = [$rawNote]; // Wrap non-array values in an array
			}
			// Clean and format the note
			$cleanedNote = implode(', ', $decodedNote); // Join array elements into a single string
			$notificationData['otp'] = $bookingData['note'] = "OTP: $cleanedNote";

			$customerBody =$notificationData['otp'];
			$customerData = [
				'driver_id' => (string)$driver->id, // Convert integer to string
				'driver_name' => $driver->name,    // Already a string, no changes needed
			];
			
		
		// Send notification to Customer/Employee
		if ($customerFCM) {
			$customerTitle =

				$customerResult = $firebaseService->sendNotificationWithCustomData($customerFCM, $title, $customerBody, $customerData);


			if ($customerResult) {
				\Log::info("Notification sent successfully to Customer ID: {$customerIds[0]}");
			} else {
				\Log::error("Failed to send notification to Customer ID: {$customerIds[0]}");
			}
		} else {
			\Log::warning("Customer FCM token not available for Customer ID: {$customerIds[0]}");
		}


	}

	// public function booking_notification($id) {

	// 	$booking = Bookings::find($id);
	// 	$data['success'] = 1;
	// 	$data['key'] = "upcoming_ride_notification";
	// 	$data['message'] = 'New Ride has been Assigned to you.';
	// 	$data['title'] = "New Upcoming Ride for you !";
	// 	$data['description'] = $booking->pickup_addr . " - " . $booking->dest_addr . " on " . date('d-m-Y', strtotime($booking->pickup));
	// 	$data['timestamp'] = date('Y-m-d H:i:s');
	// 	$data['data'] = array('rideinfo' => array(

	// 		'booking_id' => $booking->id,
	// 		'source_address' => $booking->pickup_addr,
	// 		'dest_address' => $booking->dest_addr,
	// 		'book_timestamp' => date('Y-m-d H:i:s', strtotime($booking->created_at)),
	// 		'ridestart_timestamp' => null,
	// 		'journey_date' => date('d-m-Y', strtotime($booking->pickup)),
	// 		'journey_time' => date('H:i:s', strtotime($booking->pickup)),
	// 		'ride_status' => "Upcoming"),
	// 		'user_details' => array('user_id' => $booking->customer_id, 'user_name' => $booking->customer->name, 'mobno' => $booking->customer->getMeta('mobno'), 'profile_pic' => $booking->customer->getMeta('profile_pic')),
	// 	);
	// 	// dd($data);
	// 	$driver = User::find($booking->driver_id);

	// 	if ($driver->getMeta('fcm_id') != null && $driver->getMeta('is_available') == 1) {
	// 		$push = new PushNotification('fcm');
	// 		$push->setMessage($data)
	// 			->setApiKey(env('server_key'))
	// 			->setDevicesToken([$driver->getMeta('fcm_id')])
	// 			->send();
	// 		// PushNotification::app('appNameAndroid')
	// 		//     ->to($driver->getMeta('fcm_id'))
	// 		//     ->send($data);
	// 	}

	// }

	public function bulk_delete(Request $request)
	{
		Bookings::whereIn('id', $request->ids)->delete();
		IncomeModel::whereIn('income_id', $request->ids)->where('income_cat', 1)->delete();
		return back();
	}

	public function cancel_booking(Request $request)
	{
		// dd($request->all());
		$booking = Bookings::find($request->cancel_id);
		$booking->cancellation = 1;
		$booking->ride_status = "Cancelled";
		$booking->reason = $request->reason;
		$booking->save();
		// if booking->status != 1 then delete income record
		IncomeModel::where('income_id', $request->cancel_id)->where('income_cat', 1)->delete();
		// 		if (Hyvikk::email_msg('email') == 1) {
// 			Mail::to($booking->customer->email)->send(new BookingCancelled($booking,$booking->customer->name));
// 			Mail::to($booking->driver->email)->send(new BookingCancelled($booking,$booking->driver->name));
// 		}
		return back()->with(['msg' => 'Booking cancelled successfully!']);
	}




	// public function cancel_booking(Request $request) {
//     try {
//     Log::info('Cancelling booking process started', ['cancel_id' => $request->cancel_id]);

	//     // Validate input
//     $request->validate([
//         'cancel_id' => 'required|exists:bookings,id',
//         'reason' => 'required|string|max:255'
//     ]);
//     Log::info('Validation successful');

	//     // Find booking
//     $booking = Bookings::find($request->cancel_id);
//     if (!$booking) {
//         throw new \Exception("Booking not found for ID {$request->cancel_id}");
//     }
//     Log::info('Booking found', ['booking' => $booking]);

	//     // Update booking status
//     $booking->update([
//         'cancellation' => 1,
//         'ride_status' => "Cancelled",
//         'reason' => $request->reason,
//     ]);
//     Log::info('Booking updated successfully');

	//     // Delete related income record if booking status is not active
//     if ($booking->status != 1) {
//         IncomeModel::where('income_id', $request->cancel_id)
//                   ->where('income_cat', 1)
//                   ->delete();
//         Log::info('Income record deleted');
//     }

	//     // // Send emails
//     // if (Hyvikk::email_msg('email') == 1) {
//     //     Mail::to($booking->customer->email)->send(new BookingCancelled($booking, $booking->customer->name));
//     //     Mail::to($booking->driver->email)->send(new BookingCancelled($booking, $booking->driver->name));
//     //     Log::info('Cancellation emails sent');
//     // }

	//     return back()->with(['msg' => 'Booking cancelled successfully!']);
// } catch (\Exception $e) {
//     Log::error('Error cancelling booking', [
//         'cancel_id' => $request->cancel_id,
//         'reason' => $request->reason,
//         'error_message' => $e->getMessage(),
//         'trace' => $e->getTraceAsString()
//     ]);

	//     return back()->withErrors(['msg' => 'An error occurred while cancelling the booking.']);
// }

	// }
}
