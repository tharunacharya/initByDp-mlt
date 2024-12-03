use App\Models\User;
use App\Notifications\BookingStatusUpdated;

public function updateBookingStatus(Request $request, $bookingId)
{
    $booking = Booking::findOrFail($bookingId);
    $booking->status = 'confirmed';
    $booking->save();

    // Fetch the employee and driver related to this booking
    $employee = User::find($booking->employee_id);
    $driver = User::find($booking->driver_id);

    // Send notification to employee
    $employee->notify(new BookingStatusUpdated($booking, 'employee'));

    // Send notification to driver
    $driver->notify(new BookingStatusUpdated($booking, 'driver'));

    return redirect()->back()->with('success', 'Booking status updated and notifications sent.');
}
