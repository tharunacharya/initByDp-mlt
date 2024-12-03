@extends('layouts.app')
@section("breadcrumb")
<li class="breadcrumb-item active">@lang('fleet.chat')</li>
@endsection
@section('content')
    <div class="row" id="chat-body">
        <div class="col-md-12">
            <div class="card card-info">
                <div class="card-header with-border">
                  <h3 class="card-title">Pick a user to chat with</h3>
                
                </div>
                
                <div class="card-body">
                    @if($users->count() > 0)
                        
                        <div class="row" id="users">
                               @if(Auth::user()->user_type == 'S' || Auth::user()->user_type == 'O' || Auth::user()->user_type == 'D')
                                @foreach($users as $user)
                                    @if(Auth::user()->user_type == 'S' || (Auth::user()->user_type == 'O' && $user->user_type != 'O' && Auth::user()->id == $user->assigned_admin || $user->user_type == 'S') || (Auth::user()->assigned_admin == $user->id ))
                                        <div class="col-md-4">
                                            <a href="javascript:void(0);" class="chat-toggle" data-id="{{ $user->id }}" data-user="{{ $user->name }}">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-info"><i class="fa fa-users"></i></span>
                                            
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">
                                                            {{ $user->name }}
                                                        </span>
                                                    </div>                
                                                </div>                               
                                            </a>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    @else
                        <p>No users found!</p>
                    @endif
                </div>
            </div>        
        </div>
    </div>

    @include('chat.chat-box')

    <input type="hidden" id="current_user" value="{{ \Auth::user()->id }}" />
    <input type="hidden" id="pusher_app_key" value="{{ env('PUSHER_APP_KEY') }}" />
    <input type="hidden" id="pusher_cluster" value="{{ env('PUSHER_APP_CLUSTER') }}" />
@stop
@section('extra_css')
<link rel="stylesheet" href="{{ asset('assets/css/chat.css') }}" />
    
@endsection
@section('script')
    <script src="https://js.pusher.com/4.1/pusher.min.js"></script>
    <script src="{{ asset('assets/js/chat.js') }}"></script>

@stop