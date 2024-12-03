@extends('frontend.layouts.app')

@section('content')
        <section class="mt-120 mb-4">
            <h2 class="primary text-center bg-strip">@lang('frontend.booking_history')</h2>
        </section>
        <!-- Start bookings by dheeraj  -->
     <section>
    <div class="container pb-5">
        <!-- Booking Filter Section -->
        <!--<div class="booking-filter mb-5">-->
        <!--    <div class="checkboxes flex-row-center">-->
        <!--        <div class="pretty p-default p-round">-->
        <!--            <input type="radio" name="booking_type" id="all" value="all" checked>-->
        <!--            <div class="state custom-state">-->
        <!--                <label>@lang('frontend.all_bookings')</label>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--        <div class="pretty p-default p-round">-->
        <!--            <input type="radio" name="booking_type" id="book-later" value="book_later">-->
        <!--            <div class="state custom-state">-->
        <!--                <label>@lang('frontend.book_for_later')</label>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--        <div class="pretty p-default p-round">-->
        <!--            <input type="radio" name="booking_type" id="book-week" value="book_week">-->
        <!--            <div class="state custom-state">-->
        <!--                <label>@lang('frontend.book_for_week')</label>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--</div>-->

                <div id="bookings-container">
                    <div  style="margin-bottom: 30px;"> 
                    <!-- Pagination Links -->
                         {{ $bookings->links('pagination::bootstrap-4') }}
                <!--<div class="pagination-wrapper">-->
                <!--    {{ $bookings->appends(['booking_type' => request()->get('booking_type')])->links() }}-->
                <!--</div>-->
                </div>
                    @if($bookings->count() > 0)
                        @foreach($bookings as $booking)
                            @if($booking->booking_type === 'book_week')
                                @php
                                    $pickupDates = json_decode($booking->pickup, true);
                                @endphp
                                @foreach($pickupDates as $entry)
                                    <div class="booking booking-item" data-type="book_week">
                                        <span class="booking-date">
                                            <img src="{{ asset('assets/images/frontend-icons-fleet-booking-time.png') }}" alt="booking-time">
                                            {{ $entry['date'] . ' ' . $entry['time'] }}
                                        </span>
                                        <span class="booking-status pill danger filled">
                                            {{ ($booking->ride_status)?$booking->ride_status:"Pending" }}
                                        </span>
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <h6 class="primary">@lang('frontend.from')</h6>
                                                <p>{{ $booking->pickup_addr }}</p>
                                            </div>
                                            <div class="col-lg-4">
                                                <h6 class="primary">@lang('frontend.to')</h6>
                                                <p>{{ $booking->dest_addr }}</p>
                                            </div>
                                            <div class="col-lg-4">
                                            <h6 class="primary">OTP</h6>
                                            <span class="ml-2"><strong>OTP:</strong> 
                                            {{ $booking->note }}
                                            </span>
                                        </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                            
                          <div class="booking booking-item" data-type="book_later">
                                <span class="booking-date">
                                    <img src="{{ asset('assets/images/frontend-icons-fleet-booking-time.png') }}" alt="booking-time">
                                    {{ $booking->pickup }}
                                </span>
                                <span class="booking-status pill danger filled">
                                    {{ ($booking->ride_status) ? $booking->ride_status : "Pending" }}
                                </span>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <h6 class="primary">@lang('frontend.from')</h6>
                                        @php
                                            $pickupAddr = json_decode($booking->pickup_addr, true);
                                        @endphp
                                        @if(json_last_error() == JSON_ERROR_NONE && is_array($pickupAddr))
                                            <p>
                                                @foreach($pickupAddr as $address)
                                                    {{ $address }}
                                                @endforeach
                                            </p>
                                        @else
                                            <p>{{ $booking->pickup_addr }}</p>
                                        @endif
                                    </div>
                                    <div class="col-lg-4">
                                        <h6 class="primary">@lang('frontend.to')</h6>
                                        @php
                                            $destAddr = json_decode($booking->dest_addr, true);
                                        @endphp
                                        @if(json_last_error() == JSON_ERROR_NONE && is_array($destAddr))
                                            <p>
                                                @foreach($destAddr as $address)
                                                    {{ $address }}
                                                @endforeach
                                            </p>
                                        @else
                                            <p>{{ $booking->dest_addr }}</p>
                                        @endif
                                    </div>
                                    <div class="col-lg-4">
                                        <h6 class="primary">OTP</h6>
                                        @php
                                            $notes = json_decode($booking->note, true);
                                        @endphp
                                        @if(json_last_error() == JSON_ERROR_NONE && is_array($notes))
                                            <span class="ml-2">
                                                @foreach($notes as $note)
                                                   <strong>OTP:</strong> {{ $note }}
                                                @endforeach
                                        </span>
                                            
                                        @else
                                            <span class="ml-2"><strong>OTP:</strong> {{ $booking->note }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>


                                <!--<div class="booking booking-item" data-type="book_later">-->
                                <!--    <span class="booking-date">-->
                                <!--        <img src="{{ asset('assets/images/frontend-icons-fleet-booking-time.png') }}" alt="booking-time">-->
                                <!--        {{ $booking->pickup }}-->
                                <!--    </span>-->
                                <!--    <span class="booking-status pill danger filled">-->
                                <!--        {{ ($booking->ride_status)?$booking->ride_status:"Pending" }}-->
                                <!--    </span>-->
                                <!--    <div class="row">-->
                                <!--        <div class="col-lg-4">-->
                                <!--            <h6 class="primary">@lang('frontend.from')</h6>-->
                                <!--            <p>{{ $booking->pickup_addr }}</p>-->
                                <!--        </div>-->
                                <!--        <div class="col-lg-4">-->
                                <!--            <h6 class="primary">@lang('frontend.to')</h6>-->
                                <!--            <p>{{ $booking->dest_addr }}</p>-->
                                <!--        </div>-->
                                <!--         <div class="col-lg-4">-->
                                <!--            <h6 class="primary">OTP</h6>-->
                                <!--            <span class="ml-2"><strong>OTP:</strong> -->
                                <!--            {{ $booking->note }}-->
                                <!--            </span>-->
                                <!--        </div>-->
                                        
                                <!--    </div>-->
                                <!--</div>-->
                            @endif
                        @endforeach
                        
                         <!-- Pagination Links -->
                         {{ $bookings->links('pagination::bootstrap-4') }}
                <!--<div class="pagination-wrapper">-->
                <!--    {{ $bookings->appends(['booking_type' => request()->get('booking_type')])->links() }}-->
                <!--</div>-->
                    @else
                        <h4 class="text-center">No Record Found.</h4>    
                    @endif
                </div>
                
                
                



        
    </div>
</section>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bookingTypeRadios = document.querySelectorAll('input[name="booking_type"]');
        const bookingsContainer = document.getElementById('bookings-container');
        const bookings = bookingsContainer.querySelectorAll('.booking-item');

       function filterBookings() {
    const selectedType = document.querySelector('input[name="booking_type"]:checked').value;

    bookings.forEach(booking => {
        const type = booking.getAttribute('data-type');
        if (selectedType === 'all' || type === selectedType) {
            booking.classList.remove('hidden');
        } else {
            booking.classList.add('hidden');
        }
    });
}

// In your CSS:
.hidden {
    display: none;
}


        bookingTypeRadios.forEach(radio => {
            radio.addEventListener('change', filterBookings);
        });

        // Initial filter
        filterBookings();
        
        
        // to check the book later entries are json or not if they are mean it will decode and send to display
        function is_json($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

    });
</script>

        <!-- End bookings  by dheeraj  -->
        
         <!--original comment by dheeraj -->
        <!-- Start bookings --> 
        <!--<section>-->
        <!--    <div class="container pb-5">-->
        <!--        {{-- <div class="booking-search mb-5">-->
        <!--            <input type="text" placeholder="Search Bookings here.." id="bookingSearch">-->
        <!--            <span class="search-icon" data-toggle="bookingSearch">-->
        <!--                <img src="{{ asset('assets/frontend/icons/fleet-search-box.png')}}" alt="" class="js-iconChange" data-one="{{ asset('assets/frontend/icons/fleet-close-black.png')}}" data-two="{{ asset('assets/frontend/icons/fleet-search-box.png')}}">-->
        <!--            </span>-->
        <!--        </div> --}}-->
        <!--        @if($bookings->count() > 0)-->
        <!--            @foreach($bookings as $booking)-->
        <!--            <div class="booking">-->
        <!--                <span class="booking-date">-->
                            <!--{{ date((Hyvikk::get('date_format')) ? Hyvikk::get('date_format') : 'd-m-Y', strtotime($booking->journey_date)) }}-->
        <!--                    {{ date((Hyvikk::get('date_format')) ? Hyvikk::get('date_format') . ' H:i:s' : 'd-m-Y H:i:s', strtotime($booking->journey_date)) }}-->

        <!--                </span>-->
                        
                         
        <!--                <span class="booking-status pill danger filled"> {{ ($booking->ride_status)?$booking->ride_status:"Pending" }} </span>-->
        <!--                <div class="row">-->
        <!--                    <div class="col-lg-4">-->
        <!--                        <h6 class="primary">@lang('frontend.from')</h6>-->
        <!--                        <p> {{ $booking->pickup_addr }}</p>-->
        <!--                    </div>-->
        <!--                    <div class="col-lg-4">-->
        <!--                        <h6 class="primary">@lang('frontend.to')</h6>-->
        <!--                        <p> {{ $booking->dest_addr }}</p>-->
        <!--                    </div>-->
        <!--                    <div class="col-lg-4">-->
                                <!--<h6 class="primary">@lang('frontend.payment')</h6>-->
                                <!--<p> {{ ($booking->payment == 1)?"Success":"Pending" }}</p>-->
        <!--                    </div>-->
        <!--                    <div class="col-sm-12 mt-3">-->
        <!--                        <div class="pills-container">-->
        <!--                            @if($booking->driving_time)-->
        <!--                            <span class="pill dark">-->
        <!--                                <img src="{{ asset('assets/images/frontend-icons-fleet-booking-time.png')}}" alt="booking-time">-->
        <!--                                {{ $booking->driving_time }}-->
        <!--                            </span>-->
        <!--                            @endif-->

                                    <!--@if($booking->tax_total)<span class="pill dark"> <span class="rupees"> {{ Hyvikk::get('currency') }} </span>{{ $booking->tax_total }}</span>@endif-->
                                    <!--@if($booking->total_kms)<span class="pill dark"> <img src="{{ asset('assets/images/frontend-icons-fleet-kilometer.png')}}" alt="fleet-kilometer"> {{ $booking->total_kms }} {{ Hyvikk::get('dis_format') }} </span>@endif-->
                                    
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="col-sm-12 mt-3">-->
        <!--                        <div class="pills-container">-->
        <!--                            @php($methods = json_decode(Hyvikk::payment('method')))-->
        <!--                            @if($booking->receipt == 1 && $booking->payment == 0)-->
        <!--                            {!! Form::open(['route' => 'redirect-payment','method'=>'post']) !!}-->
        <!--                            {!! Form::hidden('booking_id',$booking->id) !!}-->
        <!--                            <div class="form-group">-->
        <!--                                @foreach($methods as $method)-->
        <!--                                <div class="pretty p-default p-round">-->
        <!--                                    <input type="radio" name="method" value="{{ $method }}" checked>-->
        <!--                                    <div class="state custom-state">-->
        <!--                                        <label class="">{{ $method }}</label>-->
        <!--                                    </div>-->
        <!--                                </div>-->
        <!--                                @endforeach-->
        <!--                                <button type="submit" class="btn btn-success">@lang('frontend.pay_now')</button>-->
        <!--                            </div>                                   -->
        <!--                            {!! Form::close() !!}-->
        <!--                            @endif-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                </div>-->
        <!--            </div>-->
        <!--            @endforeach-->
        <!--        @else-->
        <!--            <h4 class="text-center">No Record Found.</h4>    -->
        <!--        @endif-->
        <!--    </div>-->
        <!--</section>-->
        <!-- End bookings -->
        <!-- Contact tiles -->
@endsection