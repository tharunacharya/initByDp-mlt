@extends('layouts.app')
@section('extra_css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item "><a href="{{ route('bookings.index') }}">@lang('menu.bookings')</a></li>
    <li class="breadcrumb-item active">@lang('fleet.new_booking')</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        @lang('fleet.new_booking')
                    </h3>
                </div>

                <div class="card-body">
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
    
                    {!! Form::open(['route' => 'bookings.store', 'method' => 'post']) !!}
                    {!! Form::hidden('user_id', Auth::user()->id) !!}
                    {!! Form::hidden('status', 0) !!}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('customer_id', __('fleet.selectCustomer'), ['class' => 'form-label']) !!}
                                @if (Auth::user()->user_type != 'C')
                                    <a href="#" data-toggle="modal" data-target="#exampleModal">@lang('fleet.new_customer')</a>
                                @endif
                                <!--<select id="customer_id" name="customer_id" class="form-control" required>-->
                                <!--    <option value="">-</option>-->
                                <!--    @if (Auth::user()->user_type == 'C')-->
                                <!--        <option value="{{ Auth::user()->id }}"-->
                                <!--            data-address="{{ Auth::user()->getMeta('address') }}" data-address2=""-->
                                <!--            data-id="{{ Auth::user()->id }}" selected>{{ Auth::user()->name }}-->
                                <!--        </option>-->
                                <!--    @else-->
                                <!--        @foreach ($customers as $customer)-->
                                <!--            <option value="{{ $customer->id }}"-->
                                <!--                data-address="{{ $customer->getMeta('address') }}" data-address2=""-->
                                <!--                data-id="{{ $customer->id }}">{{ $customer->name }}</option>-->
                                <!--        @endforeach-->
                                <!--    @endif-->
                                <!--</select>-->
                                <select id="customer_id" name="customer_id[]" class="form-control" required multiple>
                                    <option value="">-</option>
                                    @if (Auth::user()->user_type == 'C')
                                        <option value="{{ Auth::user()->id }}"
                                            data-address="{{ Auth::user()->getMeta('address') }}" data-address2=""
                                            data-id="{{ Auth::user()->id }}" selected>{{ Auth::user()->name }}
                                        </option>
                                    @else
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                data-address="{{ $customer->getMeta('address') }}" 
                                                data-address2="" 
                                                data-id="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach

                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('pickup', __('fleet.pickup'), ['class' => 'form-label']) !!}
                                <div class='input-group mb-3 date'>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"> <span class="fa fa-calendar"></span></span>
                                    </div>
                                    {!! Form::text('pickup', date('Y-m-d H:i:s'), ['class' => 'form-control', 'required']) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('dropoff', __('fleet.dropoff'), ['class' => 'form-label']) !!}
                                <div class='input-group date'>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                                    </div>
                                    {!! Form::text('dropoff', null, ['class' => 'form-control', 'required']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @if(Auth::user()->user_type !='O')
                        <div class="col-md-4">
                            <div class="form-group">
                              {!! Form::label('vehicle_id',__('fleet.selectVehicle'), ['class' => 'form-label']) !!}
                              <select id="vehicle_id" name="vehicle_id" class="form-control" required>
                                <option value="">-</option>
                                @foreach($vehicles as $vehicle)
                                <option value="{{$vehicle->id}}" data-driver="{{$vehicle->getMeta('assign_driver_id')}}">{{$vehicle->make_name}} -
                                  {{$vehicle->model_name}} - {{$vehicle->license_plate}}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('driver_id', __('fleet.selectDriver'), ['class' => 'form-label']) !!}
                                <select id="driver_id" name="driver_id" class="form-control" required>
                                    <option value="">-</option>
                                    @foreach ($drivers as $driver)
                                        @if(Auth::user()->user_type == 'O')
                                            @if($driver->assigned_admin == Auth::user()->id)
                                                <option value="{{ $driver->id }}">{{ $driver->name }} @if ($driver->getMeta('is_active') != 1)
                                                        (@lang('fleet.in_active'))
                                                    @endif
                                                </option>
                                            @endif
                                        @else
                                            <option value="{{ $driver->id }}">{{ $driver->name }} @if ($driver->getMeta('is_active') != 1)
                                                    (@lang('fleet.in_active'))
                                                @endif
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
@endif
                        <div class="col-md-4">
                            <div class="form-group">
                                <!--{!! Form::label('travellers', __('fleet.no_travellers'), ['class' => 'form-label']) !!}-->
                                <!--{!! Form::number('travellers', 1, ['class' => 'form-control', 'min' => 1]) !!}-->
                                {!! Form::label('travellers', __('fleet.no_travellers'), ['class' => 'form-label']) !!}
                                {!! Form::number('travellers', 0, ['class' => 'form-control', 'min' => 1, 'id' => 'travellers']) !!}
                            </div>
                        </div>
                    </div>
                    @if (Auth::user()->user_type == 'C')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('d_pickup', __('fleet.pickup_addr'), ['class' => 'form-label']) !!}
                                    <select id="d_pickup" name="d_pickup" class="form-control">
                                        <option value="">-</option>
                                        @foreach ($addresses as $address)
                                            <option value="{{ $address->id }}" data-address="{{ $address->address }}">
                                                {{ $address->address }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('d_dest', __('fleet.dropoff_addr'), ['class' => 'form-label']) !!}
                                    <select id="d_dest" name="d_dest" class="form-control">
                                        <option value="">-</option>
                                        @foreach ($addresses as $address)
                                            <option value="{{ $address->id }}" data-address="{{ $address->address }}">
                                                {{ $address->address }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row">
                        <!--<div class="col-md-4">-->
                        <!--    <div class="form-group">-->
                        <!--        {!! Form::label('pickup_addr', __('fleet.pickup_addr'), ['class' => 'form-label']) !!}-->
                        <!--        {!! Form::text('pickup_addr', null, ['class' => 'form-control', 'required', 'style' => 'height:100px']) !!}-->
                        <!--        <div id="pickup_addresses_container">-->
                                    
                        <!--        </div>-->
                        <!--    </div>-->
                        <!--</div>-->
                       
                        <!--<div class="col-md-4">-->
                        <!--    <div class="form-group">-->
                        <!--        {!! Form::label('dest_addr', __('fleet.dropoff_addr'), ['class' => 'form-label']) !!}-->
                        <!--        {!! Form::text('dest_addr', null, ['class' => 'form-control', 'required', 'style' => 'height:100px']) !!}-->
                        <!--    </div>-->
                        <!--</div>-->
                        <div class="col-md-4"  style="display:none">
                            <div class="form-group">
                                {!! Form::label('note', __('fleet.note'), ['class' => 'form-label']) !!}
                                {!! Form::textarea('note', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('fleet.book_note'),
                                    'style' => 'height:100px',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                     <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div id="pickup_addresses_container"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div id="dropoff_addresses_container"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div id="notes_container"></div>
                                </div>
                            </div>
                        </div>
                    <hr>
                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('udf1', __('fleet.add_udf'), ['class' => 'col-xs-5 control-label']) !!}
                            <div class="row">
                                <div class="col-md-8">
                                    {!! Form::text('udf1', null, ['class' => 'form-control']) !!}
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-info add_udf"> @lang('fleet.add')</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="blank"></div>
                    <div class="col-md-12">
                        {!! Form::submit(__('fleet.save_booking'), ['class' => 'btn btn-success']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">@lang('fleet.new_customer')</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                {!! Form::open(['route' => 'customers.ajax_store', 'method' => 'post', 'id' => 'create_customer_form']) !!}
                <div class="modal-body">
                    <div class="alert alert-danger print-error-msg" style="display:none">
                        <ul></ul>
                    </div>
                    <div class="form-group">
                        {!! Form::label('first_name', __('fleet.firstname'), ['class' => 'form-label']) !!}
                        {!! Form::text('first_name', null, ['class' => 'form-control', 'required']) !!}
                    </div>

                    <div class="form-group">
                        {!! Form::label('last_name', __('fleet.lastname'), ['class' => 'form-label']) !!}
                        {!! Form::text('last_name', null, ['class' => 'form-control', 'required']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('gender', __('fleet.gender'), ['class' => 'form-label']) !!}<br>
                        <input type="radio" name="gender" class="flat-red gender" value="1" checked>
                        @lang('fleet.male')<br>

                        <input type="radio" name="gender" class="flat-red gender" value="0"> @lang('fleet.female')
                    </div>

                    <div class="form-group">
                        {!! Form::label('phone', __('fleet.phone'), ['class' => 'form-label']) !!}
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-phone"></i></span>
                            </div>
                            {!! Form::number('phone', null, ['class' => 'form-control', 'required']) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('email', __('fleet.email'), ['class' => 'form-label']) !!}
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                            </div>
                            {!! Form::email('email', null, ['class' => 'form-control', 'required']) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('address', __('fleet.address'), ['class' => 'form-label']) !!}
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-address-book-o"></i></span>
                            </div>
                            {!! Form::textarea('address', null, ['class' => 'form-control', 'size' => '30x2','required']) !!}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('fleet.close')</button>
                    <button type="submit" class="btn btn-info">@lang('fleet.save_cust')</button>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

@endsection

@section('script')
    <script>
      var datet = "{{date('Y-m-d H:i:s')}}";
      var getDriverRoute='{{ url("admin/get_driver") }}';
      var getVehicleRoute='{{ url("admin/get_vehicle") }}';
      var prevAddress='{{ url("admin/prev-address") }}';
      var selectDriver="@lang('fleet.selectDriver')";
      var selectCustomer="@lang('fleet.selectCustomer')";
      var selectVehicle="@lang('fleet.selectVehicle')";
      var addCustomer="@lang('fleet.add_customer')";
      var prevAddressLang="@lang('fleet.prev_addr')";
     
      var fleet_email_already_taken="@lang('fleet.email_already_taken')";
    </script>
    <script src="{{asset('assets/js/bookings/create.js')}}"></script>    @if (Hyvikk::api('google_api') == '1')
        <script>
            function initMap() {
                $('#pickup_addr').attr("placeholder", "");
                $('#dest_addr').attr("placeholder", "");
                var input = document.getElementById('searchMapInput');
                // var pickup_addr = document.getElementById('pickup_addr');
                // new google.maps.places.Autocomplete(pickup_addr);

                // var dest_addr = document.getElementById('dest_addr');
                // new google.maps.places.Autocomplete(dest_addr);

                // autocomplete.addListener('place_changed', function() {
                //     var place = autocomplete.getPlace();
                //     document.getElementById('pickup_addr').innerHTML = place.formatted_address;
                // });
            }
        </script>
        <script>
document.getElementById('vehicle_id').addEventListener('change', function() {
    var vehicleSelect = this;
    var driverSelect = document.getElementById('driver_id');
    var selectedDriverId = vehicleSelect.options[vehicleSelect.selectedIndex].getAttribute('data-driver');

    // Hide all driver options
    for (var i = 0; i < driverSelect.options.length; i++) {
        driverSelect.options[i].style.display = 'none';
    }

    // Show the default option
    driverSelect.options[0].style.display = '';

    if (selectedDriverId) {
        // Show and select the assigned driver
        var assignedDriverOption = driverSelect.querySelector('option[value="' + selectedDriverId + '"]');
        if (assignedDriverOption) {
            assignedDriverOption.style.display = '';
            assignedDriverOption.selected = true;
        }
    } else {
        // If no driver is assigned, reset to default option
        driverSelect.value = '';
    }
});
</script>
<script>
let autocompleteInitialized = false;

function initAutocomplete() {
    autocompleteInitialized = true;
}

function setupAutocomplete(input) {
    if (!autocompleteInitialized) return;
    const autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.setFields(['address_components', 'geometry', 'name']);
}

function createPickupAddressField() {
    const newAddress = document.createElement('div');
    newAddress.className = 'pickup-address';
    newAddress.innerHTML = `
        <label for="pickup_addr[]" class="form-label">{{ __('fleet.pickup_addr') }}</label>
        <input type="text" name="pickup_addr[]" class="form-control pickup-addr" required style="height:100px">
    `;
    return newAddress;
}

function createDropoffAddressField() {
    const newAddress = document.createElement('div');
    newAddress.className = 'dropoff-address';
    newAddress.innerHTML = `
        <label for="dropoff_addr[]" class="form-label">{{ __('fleet.dropoff_addr') }}</label>
        <input type="text" name="dest_addr[]" class="form-control dropoff-addr" required style="height:100px">
    `;
    return newAddress;
}


// create by dheeraj
function generateOTP() {
    const otp = Math.floor(100000 + Math.random() * 900000); // Generates a 6-digit OTP
    return otp;
}


function createNoteField() {
    const otpValue = generateOTP(); // Generate the OTP
    
    
    const newNote = document.createElement('div');
    newNote.className = 'note-field';
    newNote.innerHTML = `
         <label for="note[]" class="form-label"> OTP </label>
        <input type="text" name="note[]" class="form-control note" value="${otpValue}" readonly style="height:100px"">
        <input type="hidden" name="note_hidden[]" value="${otpValue}">
    `;
    return newNote;
}
// end opt function

// comment by dheeraj
// function createNoteField() {
//     const newNote = document.createElement('div');
//     newNote.className = 'note-field';
//     newNote.innerHTML = `
//         <label for="note[]" class="form-label">{{ __('fleet.note') }}</label>
//         <textarea name="note[]" class="form-control note" required style="height:100px"></textarea>
//     `;
//     return newNote;
// }

document.addEventListener('DOMContentLoaded', function() {
    const travellersInput = document.getElementById('travellers');
    const pickupContainer = document.getElementById('pickup_addresses_container');
    const dropoffContainer = document.getElementById('dropoff_addresses_container');
    const notesContainer = document.getElementById('notes_container');

    if (travellersInput) {
        travellersInput.addEventListener('change', function() {
            const newCount = parseInt(this.value);
            
            // Clear existing fields
            pickupContainer.innerHTML = '';
            dropoffContainer.innerHTML = '';
            notesContainer.innerHTML = '';

            // Create new fields based on traveler count
            for (let i = 0; i < newCount; i++) {
                const newPickupAddress = createPickupAddressField();
                const newDropoffAddress = createDropoffAddressField();
                const newNoteField = createNoteField();

                pickupContainer.appendChild(newPickupAddress);
                dropoffContainer.appendChild(newDropoffAddress);
                notesContainer.appendChild(newNoteField);
                // notesContainer.appendChild(createOTPField());

                setupAutocomplete(newPickupAddress.querySelector('.pickup-addr'));
                setupAutocomplete(newDropoffAddress.querySelector('.dropoff-addr'));
            }
        });
    }
});
</script>
       <!--<script-->
       <!--     src="https://maps.googleapis.com/maps/api/js?key={{ Hyvikk::api('api_key') }}&libraries=places&callback=initMap"-->
       <!--     async defer></script>-->
       <script
    src="https://maps.googleapis.com/maps/api/js?key={{ Hyvikk::api('api_key') }}&libraries=places&callback=initAutocomplete"
    async defer></script>
    @endif
@endsection