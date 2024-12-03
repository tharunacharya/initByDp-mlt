@extends('frontend.layouts.app')
@section('css')
    <style>
    /*book for week button starts*/
    .btn-secondary-book_week {
    background-color: #f0f0f0;
    border: 1px solid #ccc;
    padding: 5px 10px;
    cursor: pointer;
    font-size: 16px;
}

.btn-secondary-book_week:hover {
    background-color: #e0e0e0;
}

.input-group-book_week {
    display: flex;
    align-items: center;
}

.text-input-book_week {
    width: 60px;
    text-align: center;
    border: 1px solid #ccc;
    padding: 5px;
}



#book_later_section-book_week {
    display: none;
}
 /*book for week button ends*/
 
 
        .label_top{
           
    top: -15px;
    -webkit-transform: 0;
    transform: 0;
    font-size: 12px;
    opacity: 1;
    color: rgba(2, 0, 28, 0.5);
    color: rgba(2, 0, 28, 0.5);
}
        
        @media screen and (max-width:1003px) {
            .vehicle-section .custom-controls-container {
                top: unset;
                bottom: 10px;
                
            }
        }

        @media screen and (max-width:390px) {
            .vehicle-slider {
                min-height: 300px;
            }
        }

        @media screen and (max-width: 410px) {
            #image_text {
                color: white;
            }


        }

        @media (max-width: 991px) {
            .hero-section--home {
                background-position-x: 0px !important;
            }
        }

        @media screen and (max-width: 450px) {


            .hero-section--home {
                height: auto;
                width: 100%;
                /* background-size: 100% 100%; */
                background-position: unset;
                /* background-size: 100% 100%;  */
                /* background-repeat: no-repeat */
                /* background-image:none; */
            }

            .book-now-radio-button {
                margin-bottom: 5px;
            }

            .checkboxes {
                display: block;
                text-align: center;
            }
        }

        @media screen and (max-width: 318px) {


            #note_textarea {
                height: 61px;
            }
            
        }
    </style>
@endsection

@section('content')

    
    <!--<section class="hero-section--home">-->
    <!--    <div class="container">-->
    <!--        <div class="row">-->
    <!--            <div class="col-sm-12">-->
    <!--                <div class="hero-content--home w-100 text-center mt-4">-->
    <!--                    <h5 class="light primary">{{ Hyvikk::frontend('contact_phone') }}</h5>-->
    <!--                    <h1 class="mb-3" id="image_text">@lang('frontend.reliable_way')</h1>-->
    <!--                    <a href="#book_now"><button class="btn mx-auto form-submit-button">@lang('frontend.book_now')</button></a>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</section>-->
    
    
    <section class="booking-section py-5 my-5 text-white" id="book_now">
    <h1 class="text-center">@lang('frontend.book_a_cab')</h1>
    <div class="container">
        <div class="row">
            @if (session('success'))
                <div class="alert alert-success col-sm-4 offset-sm-4">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger col-sm-4 offset-sm-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="col-sm-12 flex-col-center">
                <form action="{{ url('book') }}" method="POST" id="booking_form">
                    {!! csrf_field() !!}

                    <div class="checkboxes flex-row-center">
                        <div class="pretty p-default p-round">
                            <input type="radio" name="schedule_type" id="schedule_now" value="now" checked>
                            <div class="state custom-state">
                                <label>@lang('frontend.book_now')</label>
                            </div>
                        </div>
                        <div class="pretty p-default p-round">
                            <input type="radio" name="schedule_type" id="schedule_later" value="later">
                            <div class="state custom-state">
                                <label>@lang('frontend.book_for_later')</label>
                            </div>
                        </div>
                        <div class="pretty p-default p-round">
                            <input type="radio" name="schedule_type" id="schedule_week" value="week">
                            <div class="state custom-state">
                                <label>@lang('frontend.book_for_week')</label>
                            </div>
                        </div>
                        <div class="pretty p-default p-round">
                            <input type="radio" name="schedule_type" id="schedule_month" value="month">
                            <div class="state custom-state">
                                <label>@lang('frontend.book_for_month')</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-inputs mt-5 w-100">
                        <div class="row w-100 m-0 p-0">
                            <div class="col-lg-6 col-md-6">
                                <div class="form-group">
                                    <label for="pickup_address" class="label-animate">@lang('frontend.pickup_address')</label>
                                    <input type="text" class="text-input" name="pickup_address" id="pickup_address" value="{{ old('pickup_address', $lastBooking->pickup_address ?? '') }}" required>
                                    <span class="input-addon">
                                        <img src="{{ asset('assets/images/frontend-icons-fleet-pickup.png') }}" alt="">
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6">
                                <div class="form-group">
                                    <label for="dropoff_address" class="label-animate">@lang('frontend.dropoff_address')</label>
                                    <input type="text" class="text-input" name="dropoff_address" id="dropoff_address" value="{{ old('dropoff_address', $lastBooking->dropoff_address ?? '') }}" required>
                                    <span class="input-addon">
                                        <img src="{{ asset('assets/images/frontend-icons-fleet-drop.png') }}" alt="">
                                    </span>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 d-none">
                                <div class="form-group">
                                    <label for="no_of_person" class="label-animate">@lang('frontend.no_of_person')</label>
                                    <input type="hidden" class="text-input" name="no_of_person" value="1">
                                    <input type="text" class="text-input" value="1" disabled>
                                    <span class="input-addon">
                                        <img src="{{ asset('assets/images/frontend-icons-fleet-person.png') }}">
                                    </span>
                                </div>
                            </div>

                            <div class="col-lg-12 col-md-12 book-week-elements d-none">
                                <div class="form-group d-flex align-items-center">
                                    <label for="increment_date" class="form-label me-3 fw-bold text-white">Number of Days</label>
                                    <div class="input-group-book_week">
                                        <input type="number" id="increment_date" name="increment_date" class="text-input-book_week" value="1" min="1" max="30">
                                    </div>
                                </div>
                            </div>

                            <div id="date_time_container" class="col-lg-12 col-md-12 book-later-elements d-none">
                                <!-- Date and Time fields will be dynamically inserted here -->
                            </div>

                            <div class="col-lg-6 col-md-6 book-later-elements d-none">
                                <div class="form-group">
                                    <label for="datepicker" class="label-animate">@lang('frontend.pickup_date')</label>
                                    <input type="text" class="text-input" id="datepicker" name="pickup_date" value="{{ old('pickup_date') }}">
                                    <span class="input-addon">
                                        <img src="{{ asset('assets/images/frontend-icons-fleet-date.png') }}" alt="">
                                    </span>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 book-later-elements d-none">
                                <div class="form-group">
                                    <label for="timepicker" id="timepicker_label" class="label-animate">@lang('frontend.pickup_time')</label>
                                    <input type="text" class="text-input" id="timepicker" name="pickup_time" value="{{ old('pickup_time') }}">
                                    <span class="input-addon">
                                        <img src="{{ asset('assets/images/frontend-icons-fleet-date.png') }}" alt="">
                                    </span>
                                </div>
                            </div>

                            <div class="col-lg-12 col-md-12" id="map" style="width: 100%; height: 400px;"></div>

                            <div class="col-lg-6 col-md-6 d-none">
                                <input type="hidden" name="vehicle_type" id="vehicle_type" value="{{ old('vehicle_type', $vehicle_type->first()->id ?? '') }}">
                            </div>

                            @php($methods = json_decode(Hyvikk::payment('method')))
                            @if (Hyvikk::frontend('admin_approval') == 0 && Hyvikk::api('api_key') != null)
                                <div class="col-lg-12">
                                    <div class="checkboxes flex-row-center">
                                        <label class="state custom-state">@lang('frontend.select_payment_method'): &nbsp;</label>
                                        @foreach ($methods as $method)
                                            <div class="pretty p-default p-round">
                                                <input type="radio" name="method" id="method_{{ $method }}" value="{{ $method }}" @if ($method == 'cash') checked @endif>
                                                <div class="state custom-state">
                                                    <label>{{ $method }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <button class="tab-button mx-auto mt-3" type="submit" id="booking">@lang('frontend.book_now')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

    
    
    <!-- Ends hero section -->
    <!-- Booking section by dheeraj  -->
    <!-- Booking section -->
<!--<section class="booking-section py-5 my-5 text-white" id="book_now">-->
<!--    <h1 class="text-center">@lang('frontend.book_a_cab')</h1>-->
<!--    <div class="container">-->
<!--        <div class="row">-->
<!--            @if (session('success'))-->
<!--                <div class="alert alert-success col-sm-4 offset-sm-4">-->
<!--                    {{ session('success') }}-->
<!--                </div>-->
<!--            @endif-->
                   
<!--            @if ($errors->any())-->
<!--                <div class="alert alert-danger col-sm-4 offset-sm-4">-->
<!--                    <ul>-->
<!--                        @foreach ($errors->all() as $error)-->
<!--                            <li>{{ $error }}</li>-->
<!--                        @endforeach-->
<!--                    </ul>-->
<!--                </div>-->
<!--            @endif-->

<!--            <div class="col-sm-12 flex-col-center">-->
<!--                <form action="{{ url('book') }}" method="POST" id="booking_form">-->
<!--                    {!! csrf_field() !!}-->
<!--                    <div class="checkboxes flex-row-center">-->
<!--                        <div class="pretty p-default p-round">-->
<!--                            <input type="radio" name="radio1" id="book-later" value="book_later" checked>-->
<!--                            <div class="state custom-state">-->
<!--                                <label>@lang('frontend.book_for_later')</label>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                        <div class="pretty p-default p-round">-->
<!--                            <input type="radio" name="radio1" id="book-week" value="book_week" {{ old('radio1') == 'book_week' ? 'checked' : '' }}>-->
<!--                            <div class="state custom-state">-->
<!--                                <label>@lang('frontend.book_for_week')</label>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

<!--                    <div class="form-inputs mt-5 w-100">-->
<!--                        <div class="row w-100 m-0 p-0">-->
                     
<!--                                <div class="col-lg-6 col-md-6">-->
<!--                                    <div class="form-group">-->
<!--                                        <label for="pickup_address" class="label-animate">@lang('frontend.pickup_address')</label>-->
<!--                                        <input type="text" class="text-input" name="pickup_address" id="pickup_address" value="{{ old('pickup_address', $lastBooking->pickup_address ?? '') }}" required>-->
<!--                                        <span class="input-addon">-->
<!--                                            <img src="{{ asset('assets/images/frontend-icons-fleet-pickup.png') }}" alt="">-->
<!--                                        </span>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                                <div class="col-lg-6 col-md-6">-->
<!--                                    <div class="form-group">-->
<!--                                        <label for="dropoff_address" class="label-animate">@lang('frontend.dropoff_address')</label>-->
<!--                                        <input type="text" class="text-input" name="dropoff_address" id="dropoff_address" value="{{ old('dropoff_address', $lastBooking->dropoff_address ?? '') }}" required>-->
<!--                                        <span class="input-addon">-->
<!--                                            <img src="{{ asset('assets/images/frontend-icons-fleet-drop.png') }}" alt="">-->
<!--                                        </span>-->
<!--                                    </div>-->
<!--                                </div>-->



<!--                            <div class="col-lg-6 col-md-6 d-none">-->
<!--                                <div class="form-group">-->
<!--                                    <label for="no_of_person" class="label-animate">@lang('frontend.no_of_person')</label>-->
<!--                                    <input type="hidden" class="text-input" name="no_of_person" value="1">-->
<!--                                    <input type="text" class="text-input" value="1" disabled>-->
<!--                                    <span class="input-addon">-->
<!--                                        <img src="{{ asset('assets/images/frontend-icons-fleet-person.png') }}">-->
<!--                                    </span>-->
<!--                                </div>-->
<!--                            </div>-->
                                    
                                    
<!--                                    <div class="col-lg-12 col-md-12 book-week-elements  ">-->
<!--                                        <div class="form-group d-flex align-items-center">-->
<!--                                            <label for="increment_date" class="form-label  me-3 fw-bold text-white">Number of Days</label>-->
<!--                                            <div class="input-group-book_week ">-->
<!--                                                <input type="number" id="increment_date" name="increment_date" class="text-input-book_week" value="1" min="1" max="5">-->
<!--                                            </div>-->
<!--                                        </div>-->
<!--                                    </div>-->
                                    
<!--                                        <div id="date_time_container" class="col-lg- col-md-12 book-week-elements  ">-->
                                        <!-- Date and Time fields will be dynamically inserted here -->
<!--                                    </div>-->
                                    


<!--                            <div class="col-lg-6 col-md-6 book-later-elements">-->
<!--                                <div class="form-group">-->
<!--                                    <label for="datepicker" class="label-animate">@lang('frontend.pickup_date')</label>-->
<!--                                    <input type="text" class="text-input" id="datepicker" name="pickup_date" value="{{ old('pickup_date') }}">-->
<!--                                    <span class="input-addon">-->
<!--                                        <img src="{{ asset('assets/images/frontend-icons-fleet-date.png') }}" alt="">-->
<!--                                    </span>-->
<!--                                </div>-->
<!--                            </div>-->
                            
                            
<!--                            <div class="col-lg-6 col-md-6 book-later-elements">-->
<!--                                <div class="form-group">-->
<!--                                    <label for="timepicker" id="timepicker_label" class="label-animate">@lang('frontend.pickup_time')</label>-->
<!--                                    <input type="text" class="text-input" id="timepicker" name="pickup_time" value="{{ old('pickup_time') }}">-->
<!--                                    <span class="input-addon">-->
<!--                                        <img src="{{ asset('assets/images/frontend-icons-fleet-date.png') }}" alt="">-->
<!--                                    </span>-->
<!--                                </div>-->
<!--                            </div>-->
<!--<div class="col-lg-6 col-md-6 book-later-elements book-later-elements" id="map" style="width: 100%; height: 400px;"></div>-->
<!--                            <div class="col-lg-6 col-md-6 d-none">-->
<!--                                <input type="hidden" name="vehicle_type" id="vehicle_type" value="{{ old('vehicle_type', $vehicle_type->first()->id ?? '') }}">-->
<!--                            </div>-->

<!--                            @php($methods = json_decode(Hyvikk::payment('method')))-->
<!--                            @if (Hyvikk::frontend('admin_approval') == 0 && Hyvikk::api('api_key') != null)-->
<!--                                <div class="col-lg-12">-->
<!--                                    <div class="checkboxes flex-row-center">-->
<!--                                        <label class="state custom-state">@lang('frontend.select_payment_method'): &nbsp;</label>-->
<!--                                        @foreach ($methods as $method)-->
<!--                                            <div class="pretty p-default p-round">-->
<!--                                                <input type="radio" name="method" id="method_{{ $method }}" value="{{ $method }}" @if ($method == 'cash') checked @endif>-->
<!--                                                <div class="state custom-state">-->
<!--                                                    <label>{{ $method }}</label>-->
<!--                                                </div>-->
<!--                                            </div>-->
<!--                                        @endforeach-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            @endif-->
<!--                            <button class="tab-button mx-auto mt-3" type="submit" id="booking">@lang('frontend.book_now')</button>-->
<!--                        </div>-->
<!--                    </form>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </section>-->
    <!-- Ends booking section -->

    
    <!-- Vechicles Section -->
    <!-- *Note* : there are two sliders one for vehicle details and one for vehicle images, they both are synchronized -->
    <section class="vehicle-section my-5">
        <!-- Section title -->
        <div class="container mt-4">
            <div class="row">
                <div class="col-sm-12">
                    <h2 class="text-center">@lang('frontend.our_vehicle')</h2>
                </div>
            </div>
        </div>
        <!-- Ends Section title -->
        <div class="vehicle-details-container vehicle-details-slider">
            <!-- Vehicle detail Slides starts -->
            <!-- Slide -->
            @foreach ($vehicle as $v)
                <div class="vehicle-detail animated">
                    <div class="vehicle_name w-100">{{ $v->year }} {{ $v->make_name }} {{ $v->model_name }}</div>
                    <div class="vehicle_details">
                        <div class="passengers"> {{ $v->types->seats }} Passengers </div>
                        <div class="vehicle-class">
                            <img src="{{ asset('assets/images/frontend-icons-fleet-luxurious.png') }}" alt="">
                        </div>
                        <div class="vehicle-data">{{ $v->average }}/100 MPG</div>
                    </div>
                </div>
            @endforeach
            <!-- Slide -->
            <!-- Vehicle image Slides ends -->
        </div>
        <div class="vehicle-container mt-5">
            <div class="row vehicle-slider">
                <!-- Vehicle image Slides starts -->

                @foreach ($vehicle as $v)
                    <div class="col-sm-4 justify-content-center d-flex" id="vehicle_image">
                        @if ($v->vehicle_image)
                            <img src="{{ url('uploads/' . $v->vehicle_image) }}" alt="Vehicle Image"
                                class="img-fluid vehicle-image">
                        @else
                            <img src="{{ asset('assets/images/vehicle.jpeg') }}" alt="Vehicle Image" class="img-fluid">
                        @endif
                    </div>
                @endforeach
                
                <!-- Vehicle image Slides ends -->
            </div>
        </div>
        <!-- Slide dots and current / total slides -->
        <div class="custom-controls-container" id="custom_dots">
            <h6 class="js-vehicle-slide-current"> 1 </h6>
            <div class="custom-dots">
                <!-- Dots will be automatically appended here by js -->
            </div>
            <h6 class="js-vehicle-slide-total">{{ $vehicle->count() }}</h6>
        </div>
    </section>
    <!-- Ends vehicles section -->
    <!-- Services section -->
    <section class="my-5 relative">
        <!-- Section title -->
        <div class="container my-5">
            <div class="row">
                <div class="col-sm-12">
                    <h2 class="text-center">@lang('frontend.our_service')</h2>
                </div>
            </div>
        </div>
        <!-- Ends Section title -->
        <div class="container my-0 my-lg-5">
            <div class="row js-service-slider">
                @foreach ($company_services as $service)
                    <div class="col-sm-6 py-5 ">
                        <div class="row w-100 m-0 p-0">
                            <div class="col-sm-4">
                                <div class="service-round-element">
                                    @if ($service->image != null)
                                        <img src="{{ url('uploads/' . $service->image) }}" alt="Service Image">
                                    @else
                                        <img src="" alt="Service Image">
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-8">
                                <h6>{{ $service->title }}</h6>
                                <p class="mt-3">{{ $service->description }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <!-- Slider arrows -->
        <div class="service-slide-prev">
            <img src="{{ asset('assets/images/frontend-icons-fleet-left.png') }}" alt="">
        </div>
        <div class="service-slide-next">
            <img src="{{ asset('assets/images/frontend-icons-fleet-arrow-right.png') }}" alt="">
        </div>
    </section>
    <!-- Ends services section -->
    <!-- Testimonial section -->
    <section class="pb-5 pt-0">
        <div class="container text-center no-padding-mobile relative">
            <!-- Section title -->
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <h2 class="text-center">@lang('frontend.testimonials')</h2>
                    </div>
                </div>
            </div>
            <!-- Ends Section title -->
            <div class="js-testimonial-slider">
                <!-- Slide -->
                @foreach ($testimonial as $t)
                    <div class="col-sm-12">
                        <div class="row mt-5">
                            <div class="col-lg-4 flex-col-center">
                                <div class="testimonial-image-block">
                                    <div class="shadow-overlay"></div>
                                    @if ($t->image != null)
                                        <img src="{{ url('uploads/' . $t->image) }}" alt="Testimonial Image"
                                            class="testimonial-image">
                                    @else
                                        <img src="{{ url('assets/images/no-user.jpg') }}" alt="Testimonial Image"
                                            class="testimonial-image">
                                    @endif
                                    <div class="quote-round">
                                        <img src="{{ asset('assets/images/frontend-icons-fleet-quote.png') }}" alt="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8 d-flex flex-column align-items-center">
                                <div class="testimonial-content w-100 text-center text-lg-left">
                                    {{ $t->details }}
                                    <br><br>
                                    <i> - {{ $t->name }}</i>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                <!-- Slide -->
                <!-- Slides end -->
            </div>
            <div class="testimonial-dots mx-auto">
            </div>
        </div>
    </section>
    <!-- Ends wrapper  -->
@endsection

<!-- strat scripts section testing by dheeraj-->
@section('scripts')
<script>
// Initialize map and directions services
let map, directionsService, directionsRenderer;

function initMap() {
    // Initialize Google Maps
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 7, // Adjust zoom level as needed
        center: { lat: 12.9716, lng: 77.5946 } // Initial center on Bangalore
    });

    // Initialize Directions Service and Renderer
    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer();
    directionsRenderer.setMap(map);

    // Autocomplete fields for pickup and dropoff addresses
    const pickupInput = document.getElementById('pickup_address');
    const dropoffInput = document.getElementById('dropoff_address');

    const pickupAutocomplete = new google.maps.places.Autocomplete(pickupInput);
    const dropoffAutocomplete = new google.maps.places.Autocomplete(dropoffInput);

    // Event listener to calculate and display the route when addresses are selected
    pickupAutocomplete.addListener('place_changed', function() {
        calculateAndDisplayRoute();
    });

    dropoffAutocomplete.addListener('place_changed', function() {
        calculateAndDisplayRoute();
    });
}

// Function to calculate and display the route between pickup and dropoff
function calculateAndDisplayRoute() {
    const pickupAddress = document.getElementById('pickup_address').value;
    const dropoffAddress = document.getElementById('dropoff_address').value;

    // Ensure both addresses are filled before calculating the route
    if (pickupAddress && dropoffAddress) {
        directionsService.route({
            origin: pickupAddress,
            destination: dropoffAddress,
            travelMode: google.maps.TravelMode.DRIVING
        }, function(response, status) {
            if (status === 'OK') {
                directionsRenderer.setDirections(response);
            } else {
                alert('Unable to find a route: ' + status);
            }
        });
    }
}

</script>


<script src="https://maps.googleapis.com/maps/api/js?key={{ Hyvikk::api('api_key') }}&libraries=places&callback=initMap" async defer></script>
@endsection


