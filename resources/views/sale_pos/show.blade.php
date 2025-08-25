<div class="modal-dialog modal-xl no-print" role="document">
  <div class="modal-content">
    <div class="modal-header">
    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalTitle"> @lang('sale.sell_details') (<b>@if($sell->type == 'sales_order') @lang('restaurant.order_no') @else @lang('sale.invoice_no') @endif :</b> {{ $sell->invoice_no }})
    </h4>
</div>
<div class="modal-body">
    <div class="row">
      <div class="col-xs-12">
          <p class="pull-right"><b>@lang('messages.date'):</b> {{ @format_date($sell->transaction_date) }}</p>
      </div>
    </div>
    <div class="row">
      @php
        $custom_labels = json_decode(session('business.custom_labels'), true);
        $export_custom_fields = [];
        if (!empty($sell->is_export) && !empty($sell->export_custom_fields_info)) {
            $export_custom_fields = $sell->export_custom_fields_info;
        }
      @endphp
      <div class="@if(!empty($export_custom_fields)) col-sm-3 @else col-sm-4 @endif">
        <b>@if($sell->type == 'sales_order') {{ __('restaurant.order_no') }} @else {{ __('sale.invoice_no') }} @endif:</b> #{{ $sell->invoice_no }}<br>
        <b>{{ __('sale.status') }}:</b> 
          @if($sell->status == 'draft' && $sell->is_quotation == 1)
            {{ __('lang_v1.quotation') }}
          @else
            {{ $statuses[$sell->status] ?? __('sale.' . $sell->status) }}
          @endif
        <br>
        @if($sell->type != 'sales_order')
          <b>{{ __('sale.payment_status') }}:</b> @if(!empty($sell->payment_status)){{ __('lang_v1.' . $sell->payment_status) }}
          @endif
        @endif
        @if(!empty($custom_labels['sell']['custom_field_1']))
          <br><strong>{{$custom_labels['sell']['custom_field_1'] ?? ''}}: </strong> {{$sell->custom_field_1}}
        @endif
        @if(!empty($custom_labels['sell']['custom_field_2']))
          <br><strong>{{$custom_labels['sell']['custom_field_2'] ?? ''}}: </strong> {{$sell->custom_field_2}}
        @endif
        @if(!empty($custom_labels['sell']['custom_field_3']))
          <br><strong>{{$custom_labels['sell']['custom_field_3'] ?? ''}}: </strong> {{$sell->custom_field_3}}
        @endif
        @if(!empty($custom_labels['sell']['custom_field_4']))
          <br><strong>{{$custom_labels['sell']['custom_field_4'] ?? ''}}: </strong> {{$sell->custom_field_4}}
        @endif

        @if(!empty($sales_orders))
              <br><br><strong>@lang('lang_v1.sales_orders'):</strong>
             <table class="table table-slim no-border">
               <tr>
                 <th>@lang('lang_v1.sales_order')</th>
                 <th>@lang('lang_v1.date')</th>
               </tr>
               @foreach($sales_orders as $so)
                <tr>
                  <td>{{$so->invoice_no}}</td>
                  <td>{{@format_datetime($so->transaction_date)}}</td>
                </tr>
               @endforeach
             </table>
          @endif
        @if($sell->document_path)
          <br>
          <br>
          <a href="{{$sell->document_path}}" 
          download="{{$sell->document_name}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-accent pull-left no-print">
            <i class="fa fa-download"></i> 
              &nbsp;{{ __('purchase.download_document') }}
          </a>
        @endif
      </div>
      <div class="@if(!empty($export_custom_fields)) col-sm-3 @else col-sm-4 @endif">
        @if(!empty($sell->contact->supplier_business_name))
          {{ $sell->contact->supplier_business_name }}<br>
        @endif
        <b>{{ __('sale.customer_name') }}:</b> {{ $sell->contact->name }}<br>
        <b>{{ __('business.address') }}:</b><br>
        @if(!empty($sell->billing_address()))
          {{$sell->billing_address()}}
        @else
          {!! $sell->contact->contact_address !!}
          @if($sell->contact->mobile)
          <br>
              {{__('contact.mobile')}}: {{ $sell->contact->mobile }}
          @endif
          @if($sell->contact->alternate_number)
          <br>
              {{__('contact.alternate_contact_number')}}: {{ $sell->contact->alternate_number }}
          @endif
          @if($sell->contact->landline)
            <br>
              {{__('contact.landline')}}: {{ $sell->contact->landline }}
          @endif
          @if($sell->contact->email)
            <br>
              {{__('business.email')}}: {{ $sell->contact->email }}
          @endif
        @endif
        
      </div>
      <div class="@if(!empty($export_custom_fields)) col-sm-3 @else col-sm-4 @endif">
      @if(in_array('tables' ,$enabled_modules))
         <strong>@lang('restaurant.table'):</strong>
          {{$sell->table->name ?? ''}}<br>
      @endif
      @if(in_array('service_staff' ,$enabled_modules))
          <strong>@lang('restaurant.service_staff'):</strong>
          {{$sell->service_staff->user_full_name ?? ''}}<br>
      @endif

      <strong>@lang('sale.shipping'):</strong>
      <span class="label @if(!empty($shipping_status_colors[$sell->shipping_status])) {{$shipping_status_colors[$sell->shipping_status]}} @else {{'bg-gray'}} @endif">{{$shipping_statuses[$sell->shipping_status] ?? '' }}</span><br>
      @if(!empty($sell->shipping_address()))
        {{$sell->shipping_address()}}
      @else
        {{$sell->shipping_address ?? '--'}}
      @endif
      @if(!empty($sell->delivered_to))
        <br><strong>@lang('lang_v1.delivered_to'): </strong> {{$sell->delivered_to}}
      @endif

      @if(!empty($sell->delivery_person_user->first_name))
        <br><strong>@lang('lang_v1.delivery_person'): </strong> {{$sell->delivery_person_user->surname}} {{$sell->delivery_person_user->first_name}}     {{$sell->delivery_person_user->last_name}}
      @endif

      
      @if(!empty($sell->shipping_custom_field_1))
        <br><strong>{{$custom_labels['shipping']['custom_field_1'] ?? ''}}: </strong> {{$sell->shipping_custom_field_1}}
      @endif
      @if(!empty($sell->shipping_custom_field_2))
        <br><strong>{{$custom_labels['shipping']['custom_field_2'] ?? ''}}: </strong> {{$sell->shipping_custom_field_2}}
      @endif
      @if(!empty($sell->shipping_custom_field_3))
        <br><strong>{{$custom_labels['shipping']['custom_field_3'] ?? ''}}: </strong> {{$sell->shipping_custom_field_3}}
      @endif
      @if(!empty($sell->shipping_custom_field_4))
        <br><strong>{{$custom_labels['shipping']['custom_field_4'] ?? ''}}: </strong> {{$sell->shipping_custom_field_4}}
      @endif
      @if(!empty($sell->shipping_custom_field_5))
        <br><strong>{{$custom_labels['shipping']['custom_field_5'] ?? ''}}: </strong> {{$sell->shipping_custom_field_5}}
      @endif
      @php
        $medias = $sell->media->where('model_media_type', 'shipping_document')->all();
      @endphp
      @if(count($medias))
        @include('sell.partials.media_table', ['medias' => $medias])
      @endif

      @if(in_array('types_of_service' ,$enabled_modules))
        @if(!empty($sell->types_of_service))
          <strong>@lang('lang_v1.types_of_service'):</strong>
          {{$sell->types_of_service->name}}<br>
        @endif
        @if(!empty($sell->types_of_service->enable_custom_fields))
          <strong>{{ $custom_labels['types_of_service']['custom_field_1'] ?? __('lang_v1.service_custom_field_1' )}}:</strong>
          {{$sell->service_custom_field_1}}<br>
          <strong>{{ $custom_labels['types_of_service']['custom_field_2'] ?? __('lang_v1.service_custom_field_2' )}}:</strong>
          {{$sell->service_custom_field_2}}<br>
          <strong>{{ $custom_labels['types_of_service']['custom_field_3'] ?? __('lang_v1.service_custom_field_3' )}}:</strong>
          {{$sell->service_custom_field_3}}<br>
          <strong>{{ $custom_labels['types_of_service']['custom_field_4'] ?? __('lang_v1.service_custom_field_4' )}}:</strong>
          {{$sell->service_custom_field_4}}<br>
          <strong>{{ $custom_labels['types_of_service']['custom_field_5'] ?? __('lang_v1.custom_field', ['number' => 5])}}:</strong>
          {{$sell->service_custom_field_5}}<br>
          <strong>{{ $custom_labels['types_of_service']['custom_field_6'] ?? __('lang_v1.custom_field', ['number' => 6])}}:</strong>
          {{$sell->service_custom_field_6}}
        @endif
      @endif
      </div>
      @if(!empty($export_custom_fields))
          <div class="col-sm-3">
                @foreach($export_custom_fields as $label => $value)
                    <strong>
                        @php
                            $export_label = __('lang_v1.export_custom_field1');
                            if ($label == 'export_custom_field_1') {
                                $export_label =__('lang_v1.export_custom_field1');
                            } elseif ($label == 'export_custom_field_2') {
                                $export_label = __('lang_v1.export_custom_field2');
                            } elseif ($label == 'export_custom_field_3') {
                                $export_label = __('lang_v1.export_custom_field3');
                            } elseif ($label == 'export_custom_field_4') {
                                $export_label = __('lang_v1.export_custom_field4');
                            } elseif ($label == 'export_custom_field_5') {
                                $export_label = __('lang_v1.export_custom_field5');
                            } elseif ($label == 'export_custom_field_6') {
                                $export_label = __('lang_v1.export_custom_field6');
                            }
                        @endphp

                        {{$export_label}}
                        :
                    </strong> {{$value ?? ''}} <br>
                @endforeach
          </div>
      @endif
    </div>
    <br>
    <div class="row">
      <div class="col-sm-12 col-xs-12">
        <h4>{{ __('sale.products') }}:</h4>
      </div>

      <div class="col-sm-12 col-xs-12">
        <div class="table-responsive">
          @include('sale_pos.partials.sale_line_details')
        </div>
      </div>
    </div>
    <div class="row">
      @php
        $total_paid = 0;
      @endphp
      @if($sell->type != 'sales_order')
      <div class="col-sm-12 col-xs-12">
        <h4>{{ __('sale.payment_info') }}:</h4>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="table-responsive">
          <table class="table tw-border table-striped">
            <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
              <tr>
                <th>#</th>
                <th>{{ __('messages.date') }}</th>
                <th>{{ __('purchase.ref_no') }}</th>
                <th>{{ __('sale.amount') }}</th>
                <th>{{ __('sale.payment_mode') }}</th>
                <th>{{ __('sale.payment_note') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach($sell->payment_lines as $payment_line)
                @php
                  if($payment_line->is_return == 1){
                    $total_paid -= $payment_line->amount;
                  } else {
                    $total_paid += $payment_line->amount;
                  }
                @endphp
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ @format_date($payment_line->paid_on) }}</td>
                  <td>{{ $payment_line->payment_ref_no }}</td>
                  <td><span class="display_currency" data-currency_symbol="true">{{ $payment_line->amount }}</span></td>
                  <td>
                    {{ $payment_types[$payment_line->method] ?? $payment_line->method }}
                    @if($payment_line->is_return == 1)
                      <br/>
                      ( {{ __('lang_v1.change_return') }} )
                    @endif
                  </td>
                  <td>@if($payment_line->note) 
                    {{ ucfirst($payment_line->note) }}
                    @else
                    --
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @endif
      <div class="col-md-6 col-sm-12 col-xs-12 @if($sell->type == 'sales_order') col-md-offset-6 @endif">
        <div class="table-responsive">
          <table class="table table-striped tw-border">
            <tr>
              <th>{{ __('sale.total') }}: </th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->total_before_tax }}</span></td>
            </tr>
            <tr>
              <th>{{ __('sale.discount') }}:</th>
              <td><b>(-)</b></td>
              <td><div class="pull-right"><span class="display_currency" @if( $sell->discount_type == 'fixed') data-currency_symbol="true" @endif>{{ $sell->discount_amount }}</span> @if( $sell->discount_type == 'percentage') {{ '%'}} @endif</span></div></td>
            </tr>
            @if(in_array('types_of_service' ,$enabled_modules) && !empty($sell->packing_charge))
              <tr>
                <th>{{ __('lang_v1.packing_charge') }}:</th>
                <td><b>(+)</b></td>
                <td><div class="pull-right"><span class="display_currency" @if( $sell->packing_charge_type == 'fixed') data-currency_symbol="true" @endif>{{ $sell->packing_charge }}</span> @if( $sell->packing_charge_type == 'percent') {{ '%'}} @endif </div></td>
              </tr>
            @endif
            @if(session('business.enable_rp') == 1 && !empty($sell->rp_redeemed) )
              <tr>
                <th>{{session('business.rp_name')}}:</th>
                <td><b>(-)</b></td>
                <td> <span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->rp_redeemed_amount }}</span></td>
              </tr>
            @endif
            <tr>
              <th>{{ __('sale.order_tax') }}:</th>
              <td><b>(+)</b></td>
              <td class="text-right">
                @if(!empty($order_taxes))
                  @foreach($order_taxes as $k => $v)
                    <strong><small>{{$k}}</small></strong> - <span class="display_currency pull-right" data-currency_symbol="true">{{ $v }}</span><br>
                  @endforeach
                @else
                0.00
                @endif
              </td>
            </tr>
            @if(!empty($line_taxes))
            <tr>
              <th>{{ __('lang_v1.line_taxes') }}:</th>
              <td></td>
              <td class="text-right">
                @if(!empty($line_taxes))
                  @foreach($line_taxes as $k => $v)
                    <strong><small>{{$k}}</small></strong> - <span class="display_currency pull-right" data-currency_symbol="true">{{ $v }}</span><br>
                  @endforeach
                @else
                0.00
                @endif
              </td>
            </tr>
            @endif
            <tr>
              <th>{{ __('sale.shipping') }}: @if($sell->shipping_details)({{$sell->shipping_details}}) @endif</th>
              <td><b>(+)</b></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->shipping_charges }}</span></td>
            </tr>

            @if( !empty( $sell->additional_expense_value_1 )  && !empty( $sell->additional_expense_key_1 ))
              <tr>
                <th>{{ $sell->additional_expense_key_1 }}:</th>
                <td><b>(+)</b></td>
                <td><span class="display_currency pull-right" >{{ $sell->additional_expense_value_1 }}</span></td>
              </tr>
            @endif
            @if( !empty( $sell->additional_expense_value_2 )  && !empty( $sell->additional_expense_key_2 ))
              <tr>
                <th>{{ $sell->additional_expense_key_2 }}:</th>
                <td><b>(+)</b></td>
                <td><span class="display_currency pull-right" >{{ $sell->additional_expense_value_2 }}</span></td>
              </tr>
            @endif
            @if( !empty( $sell->additional_expense_value_3 )  && !empty( $sell->additional_expense_key_3 ))
              <tr>
                <th>{{ $sell->additional_expense_key_3 }}:</th>
                <td><b>(+)</b></td>
                <td><span class="display_currency pull-right" >{{ $sell->additional_expense_value_3 }}</span></td>
              </tr>
            @endif
            @if( !empty( $sell->additional_expense_value_4 ) && !empty( $sell->additional_expense_key_4 ))
              <tr>
                <th>{{ $sell->additional_expense_key_4 }}:</th>
                <td><b>(+)</b></td>
                <td><span class="display_currency pull-right" >{{ $sell->additional_expense_value_4 }}</span></td>
              </tr>
            @endif
            <tr>
              <th>{{ __('lang_v1.round_off') }}: </th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->round_off_amount }}</span></td>
            </tr>
            <tr>
              <th>{{ __('sale.total_payable') }}: </th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->final_total }}</span></td>
            </tr>
            @if($sell->type != 'sales_order')
            <tr>
              <th>{{ __('sale.total_paid') }}:</th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true" >{{ $total_paid }}</span></td>
            </tr>
            <tr>
              <th>{{ __('sale.total_remaining') }}:</th>
              <td></td>
              <td>
                <!-- Converting total paid to string for floating point substraction issue -->
                @php
                  $total_paid = (string) $total_paid;
                @endphp
                <span class="display_currency pull-right" data-currency_symbol="true" >{{ $sell->final_total - $total_paid }}</span></td>
            </tr>
            @endif
          </table>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-6">
        <strong>{{ __( 'sale.sell_note')}}:</strong><br>
        <p class="well well-sm no-shadow tw-mt-2" style="background:#f7f7f7">
          @if($sell->additional_notes)
            {!! nl2br($sell->additional_notes) !!}
          @else
            --
          @endif
        </p>
      </div>
      <div class="col-sm-6">
        <strong>{{ __( 'sale.staff_note')}}:</strong><br>
        <p class="well well-sm no-shadow tw-mt-2" style="background:#f7f7f7">
          @if($sell->staff_note)
            {!! nl2br($sell->staff_note) !!}
          @else
            --
          @endif
        </p>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
            <strong>{{ __('lang_v1.activities') }}:</strong><br>
            @includeIf('activity_log.activities', ['activity_type' => 'sell'])
        </div>
    </div>
  </div>
  <div class="modal-footer">
    @if($sell->type != 'sales_order')
    <a href="#" class="print-invoice tw-dw-btn tw-dw-btn-success tw-text-white" data-href="{{route('sell.printInvoice', [$sell->id])}}?package_slip=true"><i class="fas fa-file-alt" aria-hidden="true"></i> @lang("lang_v1.packing_slip")</a>
    @endif
    @can('print_invoice')
      <a href="#" class="print-invoice tw-dw-btn tw-dw-btn-primary tw-text-white" data-href="{{route('sell.printInvoice', [$sell->id])}}"><i class="fa fa-print" aria-hidden="true"></i> @lang("lang_v1.print_invoice")</a>
      <button type="button" onclick="printDiv('print-div')" class="tw-dw-btn tw-dw-btn-info tw-text-white tw-mt-2 no-print">
          <i class="fa fa-print"></i> new print model
      </button>
      @endcan
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function(){
    var element = $('div.modal-xl');
    __currency_convert_recursively(element);
  });
</script>


<!-- deneme section -->
<div id="print-div" style="background-color: white; margin-top:1rem;display:none">
    
    <div style="direction: rtl;background:white;width:100%">
       <div>
         <div style="display:flex; flex-direction:column; align-items:center;">
          <!-- <img src="{{ asset('img/logo-small.png') }}" class="img-rounded" alt="Logo" width="300"> -->
            @if(file_exists(public_path('uploads/logo.png')))
              <img src="/uploads/logo.png"  alt="Logo" width="200">
            @else
              <img src="{{ asset('img/logo-small.png') }}"  alt="Logo" width="200">
            @endif
            <h4 style="margin:0 !important">{{ session('business.name') }}</h4>
            <h5 style="margin:2px !important">{{ session('business.address') }} {{ session('business.email') }} {{ session('business.mobile') }}</h5>
        </div>
        <div style="display:flex; justify-content:space-between; margin-top:1.25rem;">
          <div style="display:flex; gap:0.5rem; align-items:center;">
            <p style="font-weight:bold">@lang('messages.date'): </p>
            <span class="rounded-box">{{ @format_date($sell->transaction_date) }}<span>
          </div>
          <div style="display:flex; gap:0.5rem; align-items:center;">
              <p style="font-weight:bold">@lang('sale.invoice_no'): </p>
              <span class="rounded-box">{{ @format_date($sell->transaction_date) }}<span>
          </div>
        </div>

        <!-- here -->
        <div style="margin:1rem 0;">
              <p><b>{{ __('sale.customer_name') }}:</b> {{ $sell->contact->name }}</p>
              <p><b>{{ __('sale.status') }}:</b> 
                @if($sell->status == 'draft' && $sell->is_quotation == 1)
                  {{ __('lang_v1.quotation') }}
                @else
                  {{ $statuses[$sell->status] ?? __('sale.' . $sell->status) }}
                @endif
              </p>
              <p><b>{{ __('sale.payment_status') }}:</b> 
                @if(!empty($sell->payment_status)){{ __('lang_v1.' . $sell->payment_status) }}@endif
              </p>
          </div>
        
        </div>
        <div style="border:1px solid #ddd; border-radius:0.75rem; overflow:hidden">
          <table class="table" style="min-height:28%">
            <thead style="color:white; background:#337ab7 !important" >
              <tr>
                <td style="width: 5%;">الرقم</td>
                <td style="width: 15%;">القيمة الإجمالية</td>
                <td style="width: 15%;">السعر الافرادي</td>
                <td style="width: 15%;">الكمية</td>
                <td style="width: 15%;">الوحدة</td>
                <td style="width: 35%;">البيان</td>
              </tr>
            </thead>

            <tbody>
              @foreach($sell->sell_lines as $line)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td><span class="display_currency" data-currency_symbol="true">{{ $line->quantity * $line->unit_price }}</span></td>
                <td><span class="display_currency" data-currency_symbol="true">{{ $line->unit_price }}</span></td>
                <td>{{ $line->quantity }}</td>
                <td>{{ $line->product->unit->short_name ?? '' }}</td>
                <td>{{ $line->product->name ?? '' }}</td>
              </tr>
              @endforeach
            </tbody>

          </table>
          <div style="background-color: #e9ecef !important;display:flex">
            <div style="width:33%;text-align: center;" >
                <p style="font-weight:bolder">الإجمالي <br> Total <br> ................</p>
            </div>
            <div style="width:34%;text-align: center;">
                <p style="font-weight:bolder">القيمة المضافة<br>VAT <br>................</</p>
            </div>
            <div style="width:33%;text-align: center;">
                <p style="font-weight:bolder">المبلغ المستحق<br>Total Amount <br>................</</p>
            </div>
          </div>

        </div>
        <div>
          <p style="color:red!important; margin:1rem 0 ;">
            <strong>ملاحظة:</strong>
            ضمان المنتج على الوكالة والمحل غير مسؤول
          </p>
          <div style="display:flex;margin-top:3rem">
            <div style="width:33.33%; text-align:center;">
              <p style="text-align:center;font-weight:bolder">البائع &nbsp;&nbsp; Powered by</p>
              <p class="">................</</p>
            </div>
            <div style="width:33.33%; text-align:center;">
              <p style="text-align:center;font-weight:bolder">صرفت بمعرفة &nbsp;&nbsp; Approved by</p>
              <p class="">................</</p>
            </div>
            <div style="width:33.33%; text-align:center;">
              <p style="text-align:center;font-weight:bolder">المستلم &nbsp;&nbsp; Recived by</p>
              <p class="">................</</p>
            </div>
          </div>
        </div>
       </div>
    </div>
</div>


<script>
function printDiv(divId) {
    let content = document.getElementById(divId).innerHTML;
    let myWindow = window.open('', '', 'width=800,height=600');
    myWindow.document.write(`
        <html>
            <head>
                <title>Print Invoice</title>
                <style>
                    * {
                        -webkit-print-color-adjust: exact !important; /* Chrome, Safari */
                        color-adjust: exact !important; /* Firefox */
                    }
                    body { 
                        font-family: Arial, sans-serif; 
                        padding: 20px; 
                    }
                    /* force print-div to show in print popup */
                    #print-div {
                        display: block !important;
                        visibility: visible !important;
                    }
                    .table {
                        display: table;
                        width: 100% !important;
                        border-collapse: collapse;    
                        margin-bottom: 0;
                        border-spacing: 0;
                        border-color: inherit;
                        text-indent: 0;
                    }
                    .table > tbody > tr > td, 
                    .table > tbody > tr > th, 
                    .table > tfoot > tr > td, 
                    .table > tfoot > tr > th, 
                    .table > thead > tr > td, 
                    .table > thead > tr > th {
                        padding: 8px;
                        line-height: 1.42857143;
                        vertical-align: top;
                    }
                    td, th {
                        border: 1px solid #dddddd;
                    }
                    .rounded-box {
                        border: 1px solid #ddd; 
                        border-radius: 0.25rem;
                        padding: 0.5rem;
                    }
                </style>
            </head>
            <body>
                <div id="print-div">${content}</div>
            </body>
        </html>
    `);
    myWindow.document.close();
    myWindow.focus();
    myWindow.print();
}
</script>
