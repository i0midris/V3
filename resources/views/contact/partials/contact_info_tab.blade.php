<span id="view_contact_page"></span>
<div class="row">
    <div class="col-md-8">
        <h3 class="profile-username tw-text-black">
            <i class="fas fa-user-tie"></i>
            {{ $contact->full_name_with_business }}
            <small>
                @if($contact->type == 'both')
                    {{__('role.customer')}} & {{__('role.supplier')}}
                @elseif(($contact->type != 'lead'))
                    {{__('role.'.$contact->type)}}
                @endif
            </small>
        </h3>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="col-sm-3">
            @include('contact.contact_basic_info')
        </div>
        <div class="col-sm-3 mt-56">
            @include('contact.contact_more_info')
        </div>
        @if( $contact->type != 'customer')
            <div class="col-sm-3 mt-56">
                @include('contact.contact_tax_info')
            </div>
        @endif
        {{--
        <div class="col-sm-3 mt-56">
            @include('contact.contact_payment_info') 
        </div>
        @if( $contact->type == 'customer' || $contact->type == 'both')
            <div class="col-sm-3 @if($contact->type != 'both') mt-56 @endif">
                <strong>@lang('lang_v1.total_sell_return')</strong>
                <p class="text-muted">
                    <span class="display_currency" data-currency_symbol="true">
                    {{ $contact->total_sell_return }}</span>
                </p>
                <strong>@lang('lang_v1.total_sell_return_due')</strong>
                <p class="text-muted">
                    <span class="display_currency" data-currency_symbol="true">
                    {{ $contact->total_sell_return -  $contact->total_sell_return_paid }}</span>
                </p>
            </div>
        @endif
        --}}

        @if( $contact->type == 'supplier' || $contact->type == 'both')
            <div class="clearfix"></div>
            <div class="col-sm-12">
                @if(($contact->total_purchase - $contact->purchase_paid) > 0)
                    <a 
                        href="{{action([\App\Http\Controllers\TransactionPaymentController::class, 'getPayContactDue'], [$contact->id])}}?type=purchase" 
                        class="pay_purchase_due pull-right tw-flex tw-items-center tw-gap-2 tw-justify-center tw-text-md tw-font-medium tw-text-white tw-transition-all tw-duration-50 tw-bg-sky-800 hover:tw-bg-sky-700 tw-p-2 tw-rounded-full"
                        style="width:10rem"    
                    >
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="20"  height="20"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2.5"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-cash-banknote"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M3 8a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /><path d="M18 12h.01" /><path d="M6 12h.01" /></svg>
                        @lang("contact.pay_due_amount")
                    </a>
                @endif
            </div>
        @endif
        <div class="col-sm-12" style="margin-top:0.5rem !important">
            <button type="button" 
                data-toggle="modal" 
                data-target="#add_discount_modal"
                class="pull-right tw-flex tw-items-center tw-gap-2 tw-justify-center tw-text-md tw-font-medium tw-text-white tw-transition-all tw-duration-50 tw-bg-sky-800 hover:tw-bg-sky-700 tw-p-2 tw-rounded-full"
                style="width:10rem" 
            >
                @lang('lang_v1.add_discount')
            </button>
        </div>
    </div>
</div>