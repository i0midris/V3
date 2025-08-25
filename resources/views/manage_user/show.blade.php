@extends('layouts.app')

@section('title', __( 'lang_v1.view_user' ))

@section('content')
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <h3>@lang( 'lang_v1.view_user' )</h3>
            </div>
            <div class="col-md-3 col-xs-12 tw-mt-4 pull-right">
                {!! Form::select('user_id', $users, $user->id , ['class' => 'form-control select2', 'id' => 'user_id']); !!}
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-3">
                <!-- User info card -->
                <div class="box box-primary" style="border:none;border-radius: 0.5rem;border:1px solid #ddd">
                    <div class="box-body box-profile" style="padding: 1rem !important;">
                        @php
                            if(isset($user->media->display_url)) {
                                $img_src = $user->media->display_url;
                            } else {
                                $img_src = 'https://ui-avatars.com/api/?name='.$user->first_name;
                            }
                        @endphp

                        <img 
                            class="profile-user-img img-responsive img-circle tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800" 
                            src="{{$img_src}}" 
                            alt="User profile picture"
                        >

                        <h3 class="profile-username text-center" style="font-size: 1.3rem;">
                            {{$user->user_full_name}}
                        </h3>

                        <p 
                            class="user-role tw-mx-auto tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800"
                            title="@lang('user.role')"
                        >
                            {{$user->role_name}}
                        </p>

                        <ul class="list-group list-group-unbordered tw-mt-4" style="margin-bottom:0px;">
                            <li class="list-group-item">
                                <b>@lang( 'business.username' )</b>
                                <a class="pull-right">{{$user->username}}</a>
                            </li>
                            <li class="list-group-item">
                                <b>@lang( 'business.email' )</b>
                                <a class="pull-right">{{$user->email}}</a>
                            </li>
                            <li class="list-group-item">
                                <b>{{ __('lang_v1.status_for_user') }}</b>
                                @if($user->status == 'active')
                                    <span class="label label-success active-status pull-right">
                                        @lang('business.is_active')
                                    </span>
                                @else
                                    <span class="label label-danger active-status pull-right">
                                        @lang('lang_v1.inactive')
                                    </span>
                                @endif
                            </li>
                        </ul>
                        @can('user.update')
                           
                            <a 
                                href="{{action([\App\Http\Controllers\ManageUserController::class, 'edit'], [$user->id])}}" 
                                class=" tw-p-1 tw-bg-white tw-shadow-md tw-rounded-lg hover:tw-bg-gray-100 text-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif" 
                                style="position: absolute;top: 0.5rem;right: 0.5rem;"
                            >
                                <svg  xmlns="http://www.w3.org/2000/svg"  class="tw-size-5" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1.5"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-pencil"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                                <!-- <svg  xmlns="http://www.w3.org/2000/svg" class="tw-size-5" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1.5"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-edit"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg> -->
                            </a>
                        @endcan
                        </div>
                </div>
                <!-- orginal user info card -->
                <!-- <div class="box box-primary" style="border:none;border-radius: 0.5rem;">
                    <div class="box-body box-profile">
                        @php
                            if(isset($user->media->display_url)) {
                                $img_src = $user->media->display_url;
                            } else {
                                $img_src = 'https://ui-avatars.com/api/?name='.$user->first_name;
                            }
                        @endphp

                        <img 
                            class="profile-user-img img-responsive img-circle tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800" 
                            src="{{$img_src}}" 
                            alt="User profile picture"
                        >

                        <h3 class="profile-username text-center">
                            {{$user->user_full_name}}
                        </h3>

                        <p class="text-muted text-center" title="@lang('user.role')">
                            {{$user->role_name}}
                        </p>

                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item">
                                <b>@lang( 'business.username' )</b>
                                <a class="pull-right">{{$user->username}}</a>
                            </li>
                            <li class="list-group-item">
                                <b>@lang( 'business.email' )</b>
                                <a class="pull-right">{{$user->email}}</a>
                            </li>
                            <li class="list-group-item">
                                <b>{{ __('lang_v1.status_for_user') }}</b>
                                @if($user->status == 'active')
                                    <span class="label label-success pull-right">
                                        @lang('business.is_active')
                                    </span>
                                @else
                                    <span class="label label-danger pull-right">
                                        @lang('lang_v1.inactive')
                                    </span>
                                @endif
                            </li>
                        </ul>
                        @can('user.update')
                            <a href="{{action([\App\Http\Controllers\ManageUserController::class, 'edit'], [$user->id])}}" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white">
                                <i class="glyphicon glyphicon-edit"></i>
                                @lang("messages.edit")
                            </a>
                        @endcan
                        </div>
                </div> -->
                <!-- /.box -->
            </div>
            <!-- info container -->
            <div class="col-md-9">
                <div class="nav-tabs-custom" style="border-radius: 0.5rem;overflow:hidden;border:1px solid #ddd">
                    <ul class="nav nav-tabs nav-justified custom-nav-tabs">
                    <!-- <ul class="tw-flex tw-justify-between custom-nav-tabs"> -->
                        <li class="active">
                            <a href="#user_info_tab" data-toggle="tab" aria-expanded="true"><i class="fas fa-user" aria-hidden="true"></i> @lang( 'lang_v1.user_info')</a>
                        </li>
                        
                        <li>
                            <a href="#documents_and_notes_tab" data-toggle="tab" aria-expanded="true"><i class="fas fa-paperclip" aria-hidden="true"></i> @lang('lang_v1.documents_and_notes')</a>
                        </li>
    
                        <li>
                            <a href="#activities_tab" data-toggle="tab" aria-expanded="true">
                                <i class="fas fa-pen-square" aria-hidden="true"></i> 
                                @lang('lang_v1.activities')
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active tw-border-2 tw-p-2 tw-m-2" id="user_info_tab" style="border-radius: 0.5rem;">
                            <div class="row tw-mt-4">
                                <div class="col-md-12">
                                    <div class="col-md-6">
                                            <p><strong>@lang( 'lang_v1.cmmsn_percent' ): </strong> {{$user->cmmsn_percent}}%</p>
                                    </div>
                                    <div class="col-md-6">
                                        @php
                                            $selected_contacts = ''
                                        @endphp
                                        @if(count($user->contactAccess)) 
                                            @php
                                                $selected_contacts_array = [];
                                            @endphp
                                            @foreach($user->contactAccess as $contact) 
                                                @php
                                                    $selected_contacts_array[] = $contact->name; 
                                                @endphp
                                            @endforeach 
                                            @php
                                                $selected_contacts = implode(', ', $selected_contacts_array);
                                            @endphp
                                        @else 
                                            @php
                                                $selected_contacts = __('lang_v1.all'); 
                                            @endphp
                                        @endif
                                        <p>
                                            <strong>@lang( 'lang_v1.allowed_contacts' ): </strong>
                                                {{$selected_contacts}}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @include('user.show_details')
                        </div>
                        <div class="tab-pane" id="documents_and_notes_tab">
                            <!-- model id like project_id, user_id -->
                            <input type="hidden" name="notable_id" id="notable_id" value="{{$user->id}}">
                            <!-- model name like App\User -->
                            <input type="hidden" name="notable_type" id="notable_type" value="App\User">
                            <div class="document_note_body tw-p-4">
                            </div>
                        </div>
                        <div class="tab-pane" id="activities_tab">
                            <div class="row">
                                <div class="col-md-12">
                                    @include('activity_log.activities')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>    
@endsection
@section('javascript')
    <!-- document & note.js -->
    @include('documents_and_notes.document_and_note_js')

    <script type="text/javascript">
        $(document).ready( function(){
            $('#user_id').change( function() {
                if ($(this).val()) {
                    window.location = "{{url('/users')}}/" + $(this).val();
                }
            });
        });
    </script>
@endsection