<!-- <strong>{{ $contact->name }}</strong><br><br> -->
<strong class="details-title">
    <svg  xmlns="http://www.w3.org/2000/svg"  width="14"  height="14"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2.5"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-map-pin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" /></svg>
    @lang('business.address')
</strong>
<p class="text-muted details-small">
    {!! $contact->contact_address !!}
</p>
@if($contact->supplier_business_name)
    <strong class="details-title">
        <svg  xmlns="http://www.w3.org/2000/svg"  width="14"  height="14"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2.5"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-briefcase"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /><path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" /><path d="M12 12l0 .01" /><path d="M3 13a20 20 0 0 0 18 0" /></svg>
        @lang('business.business_name')
    </strong>
    <p class="text-muted details-small">
        {{ $contact->supplier_business_name }}
    </p>
@endif

<strong class="details-title">
    <svg  xmlns="http://www.w3.org/2000/svg"  width="14"  height="14"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2.5"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-device-mobile"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 5a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2v-14z" /><path d="M11 4h2" /><path d="M12 17v.01" /></svg>
    @lang('contact.mobile')
</strong>
<p class="text-muted details-small">
    {{ $contact->mobile }}
</p>
@if($contact->landline)
    <strong class="details-title">
        <i class="fa fa-phone margin-r-5"></i>
        @lang('contact.landline')
    </strong>
    <p class="text-muted details-small">
        {{ $contact->landline }}
    </p>
@endif
@if($contact->alternate_number)
    <strong class="details-title">
        <svg  xmlns="http://www.w3.org/2000/svg"  width="14"  height="14"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2.5"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-device-landline-phone"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 3h-2a2 2 0 0 0 -2 2v14a2 2 0 0 0 2 2h2a2 2 0 0 0 2 -2v-14a2 2 0 0 0 -2 -2z" /><path d="M16 4h-11a3 3 0 0 0 -3 3v10a3 3 0 0 0 3 3h11" /><path d="M12 8h-6v3h6z" /><path d="M12 14v.01" /><path d="M9 14v.01" /><path d="M6 14v.01" /><path d="M12 17v.01" /><path d="M9 17v.01" /><path d="M6 17v.01" /></svg>
        @lang('contact.alternate_contact_number')
    </strong>
    <p class="text-muted details-small">
        {{ $contact->alternate_number }}
    </p>
@endif
@if($contact->dob)
    <strong class="details-title">
        <svg  xmlns="http://www.w3.org/2000/svg"  width="14"  height="14"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2.5"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-week"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M7 14h.013" /><path d="M10.01 14h.005" /><path d="M13.01 14h.005" /><path d="M16.015 14h.005" /><path d="M13.015 17h.005" /><path d="M7.01 17h.005" /><path d="M10.01 17h.005" /></svg>
        @lang('lang_v1.dob')
    </strong>
    <p class="text-muted details-small">
        {{ @format_date($contact->dob) }}
    </p>
@endif