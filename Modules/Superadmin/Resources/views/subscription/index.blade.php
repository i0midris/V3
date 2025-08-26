@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | ' . __('superadmin::lang.subscription'))

@section('content')

    <!-- Main content -->
    <section class="content">

        @include('superadmin::layouts.partials.currency')

        {{-- <div class="box">
        <div class="box-header">
            <h3 class="box-title">@lang('superadmin::lang.active_subscription') </h3>
        </div>

        <div class="box-body">
        	@if (!empty($active))
        		<div class="col-sm-5 col-lg-3">
	        		<div class="box box-custom">
						<div class="box-header with-border text-center">
							<h2 class="box-title">
								{{$active->package_details['name']}}
							</h2>

							<div class="box-tools pull-right tw-mt-1">
								<span class="badge bg-green">
									@lang('superadmin::lang.running')
								</span>
              				</div>

						</div>
						<div class="box-body text-center">
							@lang('superadmin::lang.start_date') : {{@format_date($active->start_date)}} <br/>
							@lang('superadmin::lang.end_date') : {{@format_date($active->end_date)}} <br/>

							@lang('superadmin::lang.remaining', ['days' => \Carbon::today()->diffInDays($active->end_date)])

						</div>
					</div>
				</div>
        	@else
        		<h3 class="text-danger">@lang('superadmin::lang.no_active_subscription')</h3>
        	@endif

        	@if (!empty($nexts))
        		<div class="clearfix"></div>
        		@foreach ($nexts as $next)
        			<div class="col-sm-5 col-lg-3">
		        		<div class="box box-success">
							<div class="box-header with-border text-center">
								<h2 class="box-title">
									{{$next->package_details['name']}}
								</h2>
								<div class="box-tools pull-right">
									<span class="badge bg-green">
										@lang('superadmin::lang.upcoming')
									</span>
								</div>
							</div>
							<div class="box-body text-center">
								@lang('superadmin::lang.start_date') : {{@format_date($next->start_date)}} <br/>
								@lang('superadmin::lang.end_date') : {{@format_date($next->end_date)}}
							</div>
							<div class="box-footer bg-gray disabled text-center">
								
								<a href="{{ route('force-active', $next->id) }}"
								class="btn btn-block btn-success force_activate_now">
								 @lang('superadmin::lang.force_activate_now')
								</a>
					</div>
						</div>
					</div>
        		@endforeach
        	@endif

        	@if (!empty($waiting))
        		<div class="clearfix"></div>
        		@foreach ($waiting as $row)
        			<div class="col-md-4">
		        		<div class="box box-success">
							<div class="box-header with-border text-center">
								<h2 class="box-title">
									{{$row->package_details['name']}}
								</h2>
							</div>
							<div class="box-body text-center">
                                @if ($row->paid_via == 'offline')
                                    @lang('superadmin::lang.waiting_approval')
                                @else
                                    @lang('superadmin::lang.waiting_approval_gateway')
                                @endif
							</div>
						</div>
					</div>
        		@endforeach
        	@endif

        </div>
    </div> --}}

        
                <section class="tw-mx-4">
                    <h3 class="box-title tw-mb-4">@lang('superadmin::lang.active_subscription') </h3>
                    @if (!empty($active))
                        <div class="col-sm-5 col-lg-3">
                            <div class="box box-custom">
                                <div class="box-header with-border text-center">
                                    <h2 class="box-title">
                                        {{ $active->package_details['name'] }}
                                    </h2>

                                    <div class="box-tools pull-right">
                                        <span class="badge bg-green">
                                            @lang('superadmin::lang.running')
                                        </span>
                                    </div>

                                </div>
                                <!-- <div class="box-body text-center">
                                    @lang('superadmin::lang.start_date') : {{ @format_date($active->start_date) }} <br />
                                    @lang('superadmin::lang.end_date') : {{ @format_date($active->end_date) }} <br />

                                    @lang('superadmin::lang.remaining', ['days' => \Carbon::today()->diffInDays($active->end_date)])

                                </div> -->
                                <div class="box-body text-center">
                                    <p class="tw-mt-2"><strong>@lang('superadmin::lang.start_date')</strong>: {{ @format_date($active->start_date) }}</p>
                                    <p class="tw-mt-2"><strong>@lang('superadmin::lang.end_date')</strong>: {{ @format_date($active->end_date) }}</p>
                                    <p class="tw-mt-2"><strong>@lang('superadmin::lang.remaining')</strong>: {{ \Carbon::today()->diffInDays($active->end_date) }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <h3 class="text-danger">@lang('superadmin::lang.no_active_subscription')</h3>
                    @endif

                    @if (!empty($nexts))
                        <div class="clearfix"></div>
                        @foreach ($nexts as $next)
                            <div class="col-md-4">
                                <div class="box box-success">
                                    <div class="box-header with-border text-center">
                                        <h2 class="box-title">
                                            {{ $next->package_details['name'] }}
                                        </h2>
                                        <div class="box-tools pull-right">
                                            <span class="badge bg-green">
                                                @lang('superadmin::lang.upcoming')
                                            </span>
                                        </div>
                                    </div>
                                    <div class="box-body text-center">
                                        @lang('superadmin::lang.start_date') : {{ @format_date($next->start_date) }} <br />
                                        @lang('superadmin::lang.end_date') : {{ @format_date($next->end_date) }}
                                    </div>
                                    <div class="box-footer bg-gray disabled text-center">

                                        <a href="{{ route('force-active', $next->id) }}"
                                            class="tw-dw-btn tw-dw-btn-success tw-text-white tw-dw-btn-sm tw-dw-btn-wide force_activate_now">
                                            @lang('superadmin::lang.force_activate_now')
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    @if (!empty($waiting))
                        <div class="clearfix"></div>
                        @foreach ($waiting as $row)
                            <div class="col-md-4">
                                <div class="box box-success">
                                    <div class="box-header with-border text-center">
                                        <h2 class="box-title">
                                            {{ $row->package_details['name'] }}
                                        </h2>
                                    </div>
                                    <div class="box-body text-center">
                                        @if ($row->paid_via == 'offline')
                                            @lang('superadmin::lang.waiting_approval')
                                        @else
                                            @lang('superadmin::lang.waiting_approval_gateway')
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </section>

        {{-- <div class="box">
            <div class="box-header">
                <h3 class="box-title">@lang('superadmin::lang.all_subscriptions')</h3>
            </div>

            <div class="box-body">
                <div class="row">
                    <div class ="col-xs-12">
                        <div class="table-responsive">
                            <!-- location table-->
                            <table class="table tw-border table-hover" id="all_subscriptions_table">
                                <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                                    <tr>
                                        <th>@lang('superadmin::lang.package_name')</th>
                                        <th>@lang('superadmin::lang.start_date')</th>
                                        <th>@lang('superadmin::lang.trial_end_date')</th>
                                        <th>@lang('superadmin::lang.end_date')</th>
                                        <th>@lang('superadmin::lang.price')</th>
                                        <th>@lang('superadmin::lang.paid_via')</th>
                                        <th>@lang('superadmin::lang.payment_transaction_id')</th>
                                        <th>@lang('sale.status')</th>
                                        <th>@lang('lang_v1.created_at')</th>
                                        <th>@lang('business.created_by')</th>
                                        <th>@lang('messages.action')</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        <div class="row tw-mb-5 tw-px-5">
            <h3 class="">@lang('superadmin::lang.all_subscriptions')</h3>
        
            <div class="box box-custom tw-p-2">
                <div class="table-responsive">
                    <!-- location table-->
                    <table class="table tw-border table-striped table-hover" id="all_subscriptions_table">
                        <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                            <tr>
                                <th>@lang('superadmin::lang.package_name')</th>
                                <th>@lang('superadmin::lang.start_date')</th>
                                <th>@lang('superadmin::lang.trial_end_date')</th>
                                <th>@lang('superadmin::lang.end_date')</th>
                                <th>@lang('superadmin::lang.price')</th>
                                <th>@lang('superadmin::lang.paid_via')</th>
                                <th>@lang('superadmin::lang.payment_transaction_id')</th>
                                <th>@lang('sale.status')</th>
                                <th>@lang('lang_v1.created_at')</th>
                                <th>@lang('business.created_by')</th>
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        {{-- <div class="box">
            <div class="box-header">
                <h3 class="box-title">@lang('superadmin::lang.packages')</h3>
            </div>

            <div class="box-body">
                @include('superadmin::subscription.partials.packages')
            </div>
        </div> --}}

        <div class="row tw-mb-5 tw-px-5">
            <h3 class="">@lang('superadmin::lang.packages')</h3>
            <div class="tw-mt-5">
                @include('superadmin::subscription.partials.packages')
            </div>
        </div>
    </section>
@endsection

@section('javascript')

    <script type="text/javascript">
        $(document).ready(function() {
            $('#all_subscriptions_table').DataTable({
                processing: true,
                serverSide: true,
                dom: `
                    <"tw-mt-4 dt-section-toolbar tw-mb-4 tw-flex tw-flex-wrap tw-justify-between tw-items-center tw-gap-2"
                        <"drps-section tw-flex tw-gap-2 tw-items-center"
                            <" tw-flex tw-items-center tw-gap-2"B>
                            l
                        >
                        f
                    >
                    rt
                    <"tw-mt-4 tw-flex tw-justify-between tw-items-center"ip>
                `,
                ajax: '{{ action([\Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'allSubscriptions']) }}',
                columns: [{
                        data: 'package_name',
                        name: 'P.name'
                    },
                    {
                        data: 'start_date',
                        name: 'start_date'
                    },
                    {
                        data: 'trial_end_date',
                        name: 'trial_end_date'
                    },
                    {
                        data: 'end_date',
                        name: 'end_date'
                    },
                    {
                        data: 'package_price',
                        name: 'package_price'
                    },
                    {
                        data: 'paid_via',
                        name: 'paid_via'
                    },
                    {
                        data: 'payment_transaction_id',
                        name: 'payment_transaction_id'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'created_by',
                        name: 'created_by'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        searchable: false,
                        orderable: false
                    },
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#all_subscriptions_table'), true);
                }
            });
            $(document).on('click', '.force_activate_now', function(e) {

                e.preventDefault();
                swal({
                    title: 'This will End your current plan and activate this plan from today. Do you want to continue?',
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willActive) => {
                    if (willActive) {
                        var href = $(this).attr('href');
                        $.ajax({
                            method: "GET",
                            url: href,
                            dataType: "json",
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    location.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
