@if(!empty($__subscription) && env('APP_ENV') != 'demo')
<a
    title="@lang('superadmin::lang.active_package_description')"
    class="tw-inline-flex tw-items-center tw-justify-center tw-text-md tw-font-medium tw-text-white tw-transition-all tw-duration-50 tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 hover:tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-700 tw-p-2 tw-rounded-full"
    style="border:1px solid white;"
    aria-hidden="true" 
    data-toggle="popover" 
    data-html="true"
    data-placement="right"
    data-trigger="hover" 
    data-content="
        <table style='font-size:13px; width:100%; border-collapse:collapse;' class='table tw-absolute tw-top-0 tw-left-0 table-condensed custom-popover-table'>
        <tr class='text-center'> 
            <td colspan='2'>
                {{$__subscription->package_details['name'] }}
            </td>
        </tr>
        <tr class='text-center'>
            <td colspan='2'>
                {{ @format_date($__subscription->start_date) }} - {{@format_date($__subscription->end_date) }}
            </td>
        </tr>
        <tr> 
            <td colspan='2'>
                <i class='fa fa-check text-success'></i>
                @if($__subscription->package_details['location_count'] == 0)
                    @lang('superadmin::lang.unlimited')
                @else
                    {{$__subscription->package_details['location_count']}}
                @endif

                @lang('business.business_locations')
            </td>
        </tr>
        <tr>
            <td colspan='2'>
                <i class='fa fa-check text-success'></i>
                @if($__subscription->package_details['user_count'] == 0)
                    @lang('superadmin::lang.unlimited')
                @else
                    {{$__subscription->package_details['user_count']}}
                @endif

                @lang('superadmin::lang.users')
            </td>
        <tr>
        <tr>
            <td colspan='2'>
                <i class='fa fa-check text-success'></i>
                @if($__subscription->package_details['product_count'] == 0)
                    @lang('superadmin::lang.unlimited')
                @else
                    {{$__subscription->package_details['product_count']}}
                @endif

                @lang('superadmin::lang.products')
            </td>
        </tr>
        <tr>
            <td colspan='2'>
                <i class='fa fa-check text-success'></i>
                @if($__subscription->package_details['invoice_count'] == 0)
                    @lang('superadmin::lang.unlimited')
                @else
                    {{$__subscription->package_details['invoice_count']}}
                @endif

                @lang('superadmin::lang.invoices')
            </td>
        </tr>
        
        </table>                     
    "
    >
    <svg  xmlns="http://www.w3.org/2000/svg"  class="tw-size-5" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-info-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
</a>
<!-- <i class="fas fa-info-circle pull-left cursor-pointer" style= "color:white" aria-hidden="true" data-toggle="popover" data-html="true" title="@lang('superadmin::lang.active_package_description')" data-placement="right" data-trigger="hover" data-content="
    <table class='table table-condensed'>
     <tr class='text-center'> 
        <td colspan='2'>
            {{$__subscription->package_details['name'] }}
        </td>
     </tr>
     <tr class='text-center'>
        <td colspan='2'>
            {{ @format_date($__subscription->start_date) }} - {{@format_date($__subscription->end_date) }}
        </td>
     </tr>
     <tr> 
        <td colspan='2'>
            <i class='fa fa-check text-success'></i>
            @if($__subscription->package_details['location_count'] == 0)
                @lang('superadmin::lang.unlimited')
            @else
                {{$__subscription->package_details['location_count']}}
            @endif

            @lang('business.business_locations')
        </td>
     </tr>
     <tr>
        <td colspan='2'>
            <i class='fa fa-check text-success'></i>
            @if($__subscription->package_details['user_count'] == 0)
                @lang('superadmin::lang.unlimited')
            @else
                {{$__subscription->package_details['user_count']}}
            @endif

            @lang('superadmin::lang.users')
        </td>
     <tr>
     <tr>
        <td colspan='2'>
            <i class='fa fa-check text-success'></i>
            @if($__subscription->package_details['product_count'] == 0)
                @lang('superadmin::lang.unlimited')
            @else
                {{$__subscription->package_details['product_count']}}
            @endif

            @lang('superadmin::lang.products')
        </td>
     </tr>
     <tr>
        <td colspan='2'>
            <i class='fa fa-check text-success'></i>
            @if($__subscription->package_details['invoice_count'] == 0)
                @lang('superadmin::lang.unlimited')
            @else
                {{$__subscription->package_details['invoice_count']}}
            @endif

            @lang('superadmin::lang.invoices')
        </td>
     </tr>
     
    </table>                     
">
</i> -->
@endif