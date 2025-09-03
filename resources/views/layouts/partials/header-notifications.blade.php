@php
    $all_notifications = auth()->user()->notifications;
    $unread_notifications = $all_notifications->where('read_at', null);
    $total_unread = count($unread_notifications);
@endphp
<!-- Notifications: style can be found in dropdown.less -->
<li class="dropdown notifications-menu tw-list-none">
    <a type="button"
        class="dropdown-toggle load_notifications iconClass tw-cursor-pointer tw-inline-flex tw-items-center tw-justify-center tw-text-sm tw-font-medium tw-text-white hover:tw-text-white tw-transition-all tw-duration-50 tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 hover:tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-700 tw-rounded-full"
        style="border:1px solid white;"
        data-toggle="dropdown" id="show_unread_notifications" data-loaded="false">
        <span class="tw-sr-only">
            Notifications
        </span>
        <svg aria-hidden="true" class="iconSize" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
            stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
            <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
        </svg>
        <!-- <span class="label label-warning notifications_count">@if (!empty($total_unread)){{$total_unread}}@endif</span> -->
    </a>
    <ul class="dropdown-menu hide-scrollbar !tw-p-2 tw-absolute !tw-z-10 !tw-mt-2 !tw-origin-top-right !tw-bg-white !tw-rounded-lg !tw-shadow-lg !tw-ring-1 !tw-ring-gray-200 !focus:tw-outline-none" style="left:-1rem ; height:80vh; overflow-y: auto;top:2.5rem;width:18rem !important">
        <!-- <li class="header">You have 10 unread notifications</li> -->
        <li>
            <!-- inner menu: contains the actual data -->

            <ul class="menu" id="notifications_list">
            </ul>
        </li>

        @if (count($all_notifications) > 10)
            <li class="footer load_more_li">
                <a href="#" class="load_more_notifications">@lang('lang_v1.load_more')</a>
            </li>
        @endif
    </ul>
</li>

<input type="hidden" id="notification_page" value="1">