@extends('layouts.app')

@section('title', __('accounting::lang.ledger'))

@section('content')

@include('accounting::layouts.nav')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'accounting::lang.ledger' ) - {{$account->name}}</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-5">
            <div class="box box-solid">
                <div class="box-body" id="header_table2">
                    <table class="table table-condensed">
                        <tr>
                            <th>@lang( 'user.name' ):</th>
                            <td>
                                {{$account->name}}

                                @if(!empty($account->gl_code))
                                    ({{$account->gl_code}})
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th>@lang( 'accounting::lang.account_type' ):</th>
                            <td>
                                @if(!empty($account->account_primary_type))
                                    {{__('accounting::lang.' . $account->account_primary_type)}}
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th>@lang( 'accounting::lang.account_sub_type' ):</th>
                            <td>
                                @if(!empty($account->account_sub_type))
                                    {{__('accounting::lang.' . $account->account_sub_type->name)}}
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th>@lang( 'business.business_locations' ):</th>
                            <td id="location_data">
                              {{ __('lang_v1.all')}}
                            </td>
                        </tr>

                        <tr>
                            <th>@lang( 'accounting::lang.detail_type' ):</th>
                            <td id="account_name">
                                @if(!empty($account->detail_type))
                                    {{__('accounting::lang.' . $account->detail_type->name)}}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>@lang( 'report.date_range' ):</th>
                            <td  id="transaction_date_range_here"></td>
                        </tr>
                        <tr>
                            <th>@lang( 'lang_v1.balance' ):</th>
                            <td  id="all_balance" amount="{{$current_bal}}">@format_currency($current_bal)</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-7">
        
            <div class="box box-solid">
                <div class="box-header">
                    <h3 class="box-title"> <i class="fa fa-filter" aria-hidden="true"></i> @lang('report.filters'):</h3>
                </div>
                <div class="box-body">
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('all_accounts', __( 'accounting::lang.account' ) . ':') !!}
                            {!! Form::select('account_filter', [$account->id => $account->name], $account->id,
                                ['class' => 'form-control accounts-dropdown', 'style' => 'width:100%',
                                'id' => 'account_filter', 'data-default' => $account->id]); !!}
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('location_id', __('business.business_locations') . ':') !!}

                            {!! Form::select('location_id', $business_locations, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="form-group">
                            {!! Form::label('transaction_date_range', __('report.date_range') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                {!! Form::text('transaction_date_range', null, ['class' => 'form-control', 'readonly', 'placeholder' => __('report.date_range')]) !!}
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>

        </div>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-sm-12">
        	<div class="box">
                <div class="box-body">
                    @can('account.access')
                        <div class="table-responsive">
                    	<table class="table table-bordered table-striped" id="ledger">
                    		<thead>
                    			<tr>
                                    <th>@lang( 'messages.date' )</th>
                                    <th>@lang( 'lang_v1.description' )</th>
                                    <th>@lang( 'brand.note' )</th>
                                    <th>@lang( 'lang_v1.added_by' )</th>
                                    <th>@lang('account.debit')</th>
                                    <th>@lang('account.credit')</th>
                    				<!-- <th>@lang( 'lang_v1.balance' )</th> -->
                                    <th>@lang( 'business.location' )</th>
                                    <th>@lang( 'lang_v1.balance' )</th>
{{--                                    <th>@lang( 'messages.action' )</th>--}}
                    			</tr>
                    		</thead>

                            

                            <tfoot id="footer_table">
                                <tr class="bg-gray font-17 footer-total text-center">
                                    <td colspan="4" style="text-align: start;"><strong>@lang('sale.total'):</strong></td>
                                    <td class="footer_total_debit"></td>
                                    <td class="footer_total_credit"></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class="bg-gray font-17 text-center">
                                    <td colspan="4" style="text-align: start;"><strong>@lang('accounting::lang.net_traffic'):</strong></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="movement_balance"></td>
                                </tr>
                                @if($account->gl_code == 4101)
                                <tr class="bg-gray font-17 text-center">
                                    <td colspan="4" style="text-align: start;"><strong>@lang('accounting::lang.Sales Discount'):</strong></td>
                                    <td class="sales_discount_debit"></td>
                                    <td class="sales_discount_credit"></td>
                                    <td></td>
                                    <td class="total_discount_amount"></td>
                                </tr>
                                @endif
                                @if($account->gl_code == 1106)
                                    <tr class="bg-gray font-17 text-center">
                                        <td colspan="4" style="text-align: start;"><strong>@lang('accounting::lang.Discount On Purchases'):</strong></td>
                                        <td class="discount_on_purchases_debit"></td>
                                        <td class="discount_on_purchases_credit"></td>
                                        <td></td>
                                        <td class="total_discount_on_purchases_amount"></td>
                                    </tr>
                                @endif
                                <tr class="bg-gray font-17 text-center">
                                    <td colspan="4" style="text-align: start;"><strong>@lang('accounting::lang.closing_balance'):</strong></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="closing_balance"></td>
                                </tr>
                            </tfoot>
                    	</table>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</section>
<div id="header_table" style="display: none">
    <table style="width:100%;">
        <thead>
        <tr>
            <td>

                @if(isset($invoice_layout) && $invoice_layout != null)
                    <?php $logo = asset( 'uploads/invoice_logos/' . $invoice_layout->logo); ?>
                    @if(!empty($logo) &&  $invoice_layout->show_logo )
                        <div class="text-center" style="text-align: center">
                            <img src="{{$logo}}" style="height: 150px" class="img">
                        </div>
                    @endif
                @endif

                <p class="text-left">
                    @if(!empty($business_location->name))
                        <br>
                        {{__('lang_v1.location_ins1')}}: {{$business_location->name}}
                    @endif
                    @if(!empty($business_location->city) || !empty($business_location->state) || !empty($business_location->country))
                        <br>
                        {{implode(',', array_filter([$business_location->city, $business_location->state, $business_location->country]))}}
                    @endif
                    @if(!empty($business_location->city) || !empty($business_location->state) || !empty($business_location->country))
                        <br>
                        {{__('contact.mobile')}}: {{ $business_location->mobile }}
                    @endif
                    @if(!empty($business_location->custom_field1) || !empty($business_location->custom_field2))
                        <br>
                        @lang('accounting::lang.commercial_registration_no'):
                        {{ $business_location->custom_field1 }}
                        -
                        @lang('contact.tax_no'):
                        {{ $business_location->custom_field2 }}
                    @endif
                </p>
                <hr/>
            </td>
        </tr>
        </thead>
    </table>
</div>
@stop

@section('javascript')
@include('accounting::accounting.common_js')
<script>
    /*start print*/
    (function( factory ){
        if ( typeof define === 'function' && define.amd ) {
            // AMD
            define( ['jquery', 'datatables.net', 'datatables.net-buttons'], function ( $ ) {
                return factory( $, window, document );
            } );
        }
        else if ( typeof exports === 'object' ) {
            // CommonJS
            module.exports = function (root, $) {
                if ( ! root ) {
                    root = window;
                }

                if ( ! $ || ! $.fn.dataTable ) {
                    $ = require('datatables.net')(root, $).$;
                }

                if ( ! $.fn.dataTable.Buttons ) {
                    require('datatables.net-buttons')(root, $);
                }

                return factory( $, root, root.document );
            };
        }
        else {
            // Browser
            factory( jQuery, window, document );
        }
    }(function( $, window, document, undefined ) {
        'use strict';
        var DataTable = $.fn.dataTable;


        var _link = document.createElement( 'a' );


        var _styleToAbs = function( el ) {
            var url;
            var clone = $(el).clone()[0];
            var linkHost;

            if ( clone.nodeName.toLowerCase() === 'link' ) {
                clone.href = _relToAbs( clone.href );
            }

            return clone.outerHTML;
        };


        var _relToAbs = function( href ) {
            // Assign to a link on the original page so the browser will do all the
            // hard work of figuring out where the file actually is
            _link.href = href;
            var linkHost = _link.host;

            // IE doesn't have a trailing slash on the host
            // Chrome has it on the pathname
            if ( linkHost.indexOf('/') === -1 && _link.pathname.indexOf('/') !== 0) {
                linkHost += '/';
            }

            return _link.protocol+"//"+linkHost+_link.pathname+_link.search;
        };


        DataTable.ext.buttons.print = {
            className: 'buttons-print',

            text: function ( dt ) {
                return dt.i18n( 'buttons.print', 'Print' );
            },

            action: function ( e, dt, button, config ) {
                var data = dt.buttons.exportData(
                    $.extend( {decodeEntities: false}, config.exportOptions ) // XSS protection
                );
                var exportInfo = dt.buttons.exportInfo( config );

                var addRow = function ( d, tag ) {
                    var str = '<tr>';

                    for ( var i=0, ien=d.length ; i<ien ; i++ ) {
                        str += '<'+tag+'>'+d[i]+'</'+tag+'>';
                    }

                    return str + '</tr>';
                };

                // Construct a table for printing
                var html = '<table class="'+dt.table().node().className+'">';

                if ( config.header ) {
                    html += '<thead>'+ addRow( data.header, 'th' ) +'</thead>';
                }

                html += '<tbody>';

                if($('#OpeningBalance').length > 0)
                {
                    var OpeningBalance = Array();
                    $('#OpeningBalance').children('td').each(function(i, vv){
                        OpeningBalance[i] = $(this).text();
                    });
                    html += addRow( OpeningBalance, 'td' );
                }

                for ( var i=0, ien=data.body.length ; i<ien ; i++ ) {
                    html += addRow( data.body[i], 'td' );
                }
                html += '</tbody>';

                if ( config.footer && data.footer ) {
                    html += '<tfoot>'+ $('#footer_table').html() +'</tfoot>';
                }
                html += '</table>';

                // Open a new window for the printable table
                var win = window.open( '', '' );
                win.document.close();

                // Inject the title and also a copy of the style and link tags from this
                // document so the table can retain its base styling. Note that we have
                // to use string manipulation as IE won't allow elements to be created
                // in the host document and then appended to the new window.
                var head = '<title>'+ '{{__('accounting::lang.ledger')}}' + $('#account_name').text()+'</title>';
                $('style, link').each( function () {
                    head += _styleToAbs( this );
                } );

                try {
                    win.document.head.innerHTML = head; // Work around for Edge
                }
                catch (e) {
                    $(win.document.head).html( head ); // Old IE
                }



                // Inject the table and other surrounding information
                win.document.body.innerHTML =
                    $('#header_table').html() +
                    $('#header_table2').html() +
                    '<h1>' + '{{__('accounting::lang.ledger')}}' + $('#account_name').text() +'</h1>'+
                    '<div>'+(exportInfo.messageTop || '')+'</div>'+
                    html+
                    '<div>'+(exportInfo.messageBottom || '')+'</div>';

                $(win.document.body).addClass('dt-print-view');

                $('img', win.document.body).each( function ( i, img ) {
                    img.setAttribute( 'src', _relToAbs( img.getAttribute('src') ) );
                } );

                if ( config.customize ) {
                    config.customize( win );
                }

                // Allow stylesheets time to load
                setTimeout( function () {
                    if ( config.autoPrint ) {
                        win.print(); // blocking - so close will not
                        win.close(); // execute until this is done
                    }
                }, 1000 );
            },

            title: '*',

            messageTop: '*',

            messageBottom: '*',

            exportOptions: {},

            header: true,

            footer: false,

            autoPrint: true,

            customize: null
        };


        return DataTable.Buttons;
    }));
    /*end print*/

    /*start export excel */

    (function( factory ){
        if ( typeof define === 'function' && define.amd ) {
            // AMD
            define( ['jquery', 'datatables.net', 'datatables.net-buttons'], function ( $ ) {
                return factory( $, window, document );
            } );
        }
        else if ( typeof exports === 'object' ) {
            // CommonJS
            module.exports = function (root, $, jszip, pdfmake) {
                if ( ! root ) {
                    root = window;
                }

                if ( ! $ || ! $.fn.dataTable ) {
                    $ = require('datatables.net')(root, $).$;
                }

                if ( ! $.fn.dataTable.Buttons ) {
                    require('datatables.net-buttons')(root, $);
                }

                return factory( $, root, root.document, jszip, pdfmake );
            };
        }
        else {
            // Browser
            factory( jQuery, window, document );
        }
    }(function( $, window, document, jszip, pdfmake, undefined ) {
        'use strict';
        var DataTable = $.fn.dataTable;

// Allow the constructor to pass in JSZip and PDFMake from external requires.
// Otherwise, use globally defined variables, if they are available.
        function _jsZip () {
            return jszip || window.JSZip;
        }
        function _pdfMake () {
            return pdfmake || window.pdfMake;
        }


        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * FileSaver.js dependency
         */

        /*jslint bitwise: true, indent: 4, laxbreak: true, laxcomma: true, smarttabs: true, plusplus: true */

        var _saveAs = (function(view) {
            "use strict";
            // IE <10 is explicitly unsupported
            if (typeof view === "undefined" || typeof navigator !== "undefined" && /MSIE [1-9]\./.test(navigator.userAgent)) {
                return;
            }
            var
                doc = view.document
                // only get URL when necessary in case Blob.js hasn't overridden it yet
                , get_URL = function() {
                    return view.URL || view.webkitURL || view;
                }
                , save_link = doc.createElementNS("http://www.w3.org/1999/xhtml", "a")
                , can_use_save_link = "download" in save_link
                , click = function(node) {
                    var event = new MouseEvent("click");
                    node.dispatchEvent(event);
                }
                , is_safari = /constructor/i.test(view.HTMLElement) || view.safari
                , is_chrome_ios =/CriOS\/[\d]+/.test(navigator.userAgent)
                , throw_outside = function(ex) {
                    (view.setImmediate || view.setTimeout)(function() {
                        throw ex;
                    }, 0);
                }
                , force_saveable_type = "application/octet-stream"
                // the Blob API is fundamentally broken as there is no "downloadfinished" event to subscribe to
                , arbitrary_revoke_timeout = 1000 * 40 // in ms
                , revoke = function(file) {
                    var revoker = function() {
                        if (typeof file === "string") { // file is an object URL
                            get_URL().revokeObjectURL(file);
                        } else { // file is a File
                            file.remove();
                        }
                    };
                    setTimeout(revoker, arbitrary_revoke_timeout);
                }
                , dispatch = function(filesaver, event_types, event) {
                    event_types = [].concat(event_types);
                    var i = event_types.length;
                    while (i--) {
                        var listener = filesaver["on" + event_types[i]];
                        if (typeof listener === "function") {
                            try {
                                listener.call(filesaver, event || filesaver);
                            } catch (ex) {
                                throw_outside(ex);
                            }
                        }
                    }
                }
                , auto_bom = function(blob) {
                    // prepend BOM for UTF-8 XML and text/* types (including HTML)
                    // note: your browser will automatically convert UTF-16 U+FEFF to EF BB BF
                    if (/^\s*(?:text\/\S*|application\/xml|\S*\/\S*\+xml)\s*;.*charset\s*=\s*utf-8/i.test(blob.type)) {
                        return new Blob([String.fromCharCode(0xFEFF), blob], {type: blob.type});
                    }
                    return blob;
                }
                , FileSaver = function(blob, name, no_auto_bom) {
                    if (!no_auto_bom) {
                        blob = auto_bom(blob);
                    }
                    // First try a.download, then web filesystem, then object URLs
                    var
                        filesaver = this
                        , type = blob.type
                        , force = type === force_saveable_type
                        , object_url
                        , dispatch_all = function() {
                            dispatch(filesaver, "writestart progress write writeend".split(" "));
                        }
                        // on any filesys errors revert to saving with object URLs
                        , fs_error = function() {
                            if ((is_chrome_ios || (force && is_safari)) && view.FileReader) {
                                // Safari doesn't allow downloading of blob urls
                                var reader = new FileReader();
                                reader.onloadend = function() {
                                    var url = is_chrome_ios ? reader.result : reader.result.replace(/^data:[^;]*;/, 'data:attachment/file;');
                                    var popup = view.open(url, '_blank');
                                    if(!popup) view.location.href = url;
                                    url=undefined; // release reference before dispatching
                                    filesaver.readyState = filesaver.DONE;
                                    dispatch_all();
                                };
                                reader.readAsDataURL(blob);
                                filesaver.readyState = filesaver.INIT;
                                return;
                            }
                            // don't create more object URLs than needed
                            if (!object_url) {
                                object_url = get_URL().createObjectURL(blob);
                            }
                            if (force) {
                                view.location.href = object_url;
                            } else {
                                var opened = view.open(object_url, "_blank");
                                if (!opened) {
                                    // Apple does not allow window.open, see https://developer.apple.com/library/safari/documentation/Tools/Conceptual/SafariExtensionGuide/WorkingwithWindowsandTabs/WorkingwithWindowsandTabs.html
                                    view.location.href = object_url;
                                }
                            }
                            filesaver.readyState = filesaver.DONE;
                            dispatch_all();
                            revoke(object_url);
                        }
                    ;
                    filesaver.readyState = filesaver.INIT;

                    if (can_use_save_link) {
                        object_url = get_URL().createObjectURL(blob);
                        setTimeout(function() {
                            save_link.href = object_url;
                            save_link.download = name;
                            click(save_link);
                            dispatch_all();
                            revoke(object_url);
                            filesaver.readyState = filesaver.DONE;
                        });
                        return;
                    }

                    fs_error();
                }
                , FS_proto = FileSaver.prototype
                , saveAs = function(blob, name, no_auto_bom) {
                    return new FileSaver(blob, name || blob.name || "download", no_auto_bom);
                }
            ;
            // IE 10+ (native saveAs)
            if (typeof navigator !== "undefined" && navigator.msSaveOrOpenBlob) {
                return function(blob, name, no_auto_bom) {
                    name = name || blob.name || "download";

                    if (!no_auto_bom) {
                        blob = auto_bom(blob);
                    }
                    return navigator.msSaveOrOpenBlob(blob, name);
                };
            }

            FS_proto.abort = function(){};
            FS_proto.readyState = FS_proto.INIT = 0;
            FS_proto.WRITING = 1;
            FS_proto.DONE = 2;

            FS_proto.error =
                FS_proto.onwritestart =
                    FS_proto.onprogress =
                        FS_proto.onwrite =
                            FS_proto.onabort =
                                FS_proto.onerror =
                                    FS_proto.onwriteend =
                                        null;

            return saveAs;
        }(
            typeof self !== "undefined" && self
            || typeof window !== "undefined" && window
            || this.content
        ));


// Expose file saver on the DataTables API. Can't attach to `DataTables.Buttons`
// since this file can be loaded before Button's core!
        DataTable.fileSave = _saveAs;


        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * Local (private) functions
         */


        var _sheetname = function ( config )
        {
            var sheetName = 'Sheet1';

            if ( config.sheetName ) {
                sheetName = config.sheetName.replace(/[\[\]\*\/\\\?\:]/g, '');
            }

            return sheetName;
        };


        var _newLine = function ( config )
        {
            return config.newline ?
                config.newline :
                navigator.userAgent.match(/Windows/) ?
                    '\r\n' :
                    '\n';
        };


        var _exportData = function ( dt, config )
        {
            var newLine = _newLine( config );
            var data = dt.buttons.exportData( config.exportOptions );
            var boundary = config.fieldBoundary;
            var separator = config.fieldSeparator;
            var reBoundary = new RegExp( boundary, 'g' );
            var escapeChar = config.escapeChar !== undefined ?
                config.escapeChar :
                '\\';
            var join = function ( a ) {
                var s = '';

                // If there is a field boundary, then we might need to escape it in
                // the source data
                for ( var i=0, ien=a.length ; i<ien ; i++ ) {
                    if ( i > 0 ) {
                        s += separator;
                    }

                    s += boundary ?
                        boundary + ('' + a[i]).replace( reBoundary, escapeChar+boundary ) + boundary :
                        a[i];
                }

                return s;
            };

            var header = config.header ? join( data.header )+newLine : '';
            var footer = config.footer && data.footer ? newLine+join( data.footer ) : '';
            var body = [];

            for ( var i=0, ien=data.body.length ; i<ien ; i++ ) {
                body.push( join( data.body[i] ) );
            }

            return {
                str: header + body.join( newLine ) + footer,
                rows: body.length
            };
        };


        var _isDuffSafari = function ()
        {
            var safari = navigator.userAgent.indexOf('Safari') !== -1 &&
                navigator.userAgent.indexOf('Chrome') === -1 &&
                navigator.userAgent.indexOf('Opera') === -1;

            if ( ! safari ) {
                return false;
            }

            var version = navigator.userAgent.match( /AppleWebKit\/(\d+\.\d+)/ );
            if ( version && version.length > 1 && version[1]*1 < 603.1 ) {
                return true;
            }

            return false;
        };


        function createCellPos( n ){
            var ordA = 'A'.charCodeAt(0);
            var ordZ = 'Z'.charCodeAt(0);
            var len = ordZ - ordA + 1;
            var s = "";

            while( n >= 0 ) {
                s = String.fromCharCode(n % len + ordA) + s;
                n = Math.floor(n / len) - 1;
            }

            return s;
        }

        try {
            var _serialiser = new XMLSerializer();
            var _ieExcel;
        }
        catch (t) {}


        function _addToZip( zip, obj ) {
            if ( _ieExcel === undefined ) {
                // Detect if we are dealing with IE's _awful_ serialiser by seeing if it
                // drop attributes
                _ieExcel = _serialiser
                    .serializeToString(
                        $.parseXML( excelStrings['xl/worksheets/sheet1.xml'] )
                    )
                    .indexOf( 'xmlns:r' ) === -1;
            }

            $.each( obj, function ( name, val ) {
                if ( $.isPlainObject( val ) ) {
                    var newDir = zip.folder( name );
                    _addToZip( newDir, val );
                }
                else {
                    if ( _ieExcel ) {
                        // IE's XML serialiser will drop some name space attributes from
                        // from the root node, so we need to save them. Do this by
                        // replacing the namespace nodes with a regular attribute that
                        // we convert back when serialised. Edge does not have this
                        // issue
                        var worksheet = val.childNodes[0];
                        var i, ien;
                        var attrs = [];

                        for ( i=worksheet.attributes.length-1 ; i>=0 ; i-- ) {
                            var attrName = worksheet.attributes[i].nodeName;
                            var attrValue = worksheet.attributes[i].nodeValue;

                            if ( attrName.indexOf( ':' ) !== -1 ) {
                                attrs.push( { name: attrName, value: attrValue } );

                                worksheet.removeAttribute( attrName );
                            }
                        }

                        for ( i=0, ien=attrs.length ; i<ien ; i++ ) {
                            var attr = val.createAttribute( attrs[i].name.replace( ':', '_dt_b_namespace_token_' ) );
                            attr.value = attrs[i].value;
                            worksheet.setAttributeNode( attr );
                        }
                    }

                    var str = _serialiser.serializeToString(val);

                    // Fix IE's XML
                    if ( _ieExcel ) {
                        // IE doesn't include the XML declaration
                        if ( str.indexOf( atob('PD94bWw=') ) === -1 ) {
					str = atob('PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9InllcyI/Pg==')+str;
                    }

                    // Return namespace attributes to being as such
                    str = str.replace( /_dt_b_namespace_token_/g, ':' );
                }

                // Safari, IE and Edge will put empty name space attributes onto
                // various elements making them useless. This strips them out
                str = str.replace( /<([^<>]*?) xmlns=""([^<>]*?)>/g, '<$1 $2>' );

                zip.file( name, str );
            }
        } );
    }

    function _createNode( doc, nodeName, opts ) {
        var tempNode = doc.createElement( nodeName );

        if ( opts ) {
            if ( opts.attr ) {
                $(tempNode).attr( opts.attr );
            }

            if ( opts.children ) {
                $.each( opts.children, function ( key, value ) {
                    tempNode.appendChild( value );
                } );
            }

            if ( opts.text !== null && opts.text !== undefined ) {
                tempNode.appendChild( doc.createTextNode( opts.text ) );
            }
        }

        return tempNode;
    }


    function _excelColWidth( data, col ) {
        var max = data.header[col].length;
        var len, lineSplit, str;

        if ( data.footer && data.footer[col].length > max ) {
            max = data.footer[col].length;
        }

        for ( var i=0, ien=data.body.length ; i<ien ; i++ ) {
            var point = data.body[i][col];
            str = point !== null && point !== undefined ?
                point.toString() :
                '';

            // If there is a newline character, workout the width of the column
            // based on the longest line in the string
            if ( str.indexOf('\n') !== -1 ) {
                lineSplit = str.split('\n');
                lineSplit.sort( function (a, b) {
                    return b.length - a.length;
                } );

                len = lineSplit[0].length;
            }
            else {
                len = str.length;
            }

            if ( len > max ) {
                max = len;
            }

            // Max width rather than having potentially massive column widths
            if ( max > 40 ) {
                return 52; // 40 * 1.3
            }
        }

        max *= 1.3;

        // And a min width
        return max > 6 ? max : 6;
    }

    // Excel - Pre-defined strings to build a basic XLSX file
    var excelStrings = {
        "_rels/.rels":
            atob('PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9InllcyI/Pg==')+
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'+
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'+
            '</Relationships>',

        "xl/_rels/workbook.xml.rels":
            atob('PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9InllcyI/Pg==')+
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'+
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'+
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'+
            '</Relationships>',

        "[Content_Types].xml":
            atob('PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9InllcyI/Pg==')+
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'+
            '<Default Extension="xml" ContentType="application/xml" />'+
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml" />'+
            '<Default Extension="jpeg" ContentType="image/jpeg" />'+
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml" />'+
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml" />'+
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml" />'+
            '</Types>',

        "xl/workbook.xml":
            atob('PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9InllcyI/Pg==')+
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'+
            '<fileVersion appName="xl" lastEdited="5" lowestEdited="5" rupBuild="24816"/>'+
            '<workbookPr showInkAnnotation="0" autoCompressPictures="0"/>'+
            '<bookViews>'+
            '<workbookView xWindow="0" yWindow="0" windowWidth="25600" windowHeight="19020" tabRatio="500"/>'+
            '</bookViews>'+
            '<sheets>'+
            '<sheet name="" sheetId="1" r:id="rId1"/>'+
            '</sheets>'+
            '</workbook>',

        "xl/worksheets/sheet1.xml":
            atob('PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9InllcyI/Pg==')+
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" mc:Ignorable="x14ac" xmlns:x14ac="http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac">'+
            '<sheetData/>'+
            '<mergeCells count="0"/>'+
            '</worksheet>',

        "xl/styles.xml":
            atob('PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4=')+
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" mc:Ignorable="x14ac" xmlns:x14ac="http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac">'+
            '<numFmts count="6">'+
            '<numFmt numFmtId="164" formatCode="#,##0.00_-\ [$$-45C]"/>'+
            '<numFmt numFmtId="165" formatCode="&quot;£&quot;#,##0.00"/>'+
            '<numFmt numFmtId="166" formatCode="[$€-2]\ #,##0.00"/>'+
            '<numFmt numFmtId="167" formatCode="0.0%"/>'+
            '<numFmt numFmtId="168" formatCode="#,##0;(#,##0)"/>'+
            '<numFmt numFmtId="169" formatCode="#,##0.00;(#,##0.00)"/>'+
            '</numFmts>'+
            '<fonts count="5" x14ac:knownFonts="1">'+
            '<font>'+
            '<sz val="11" />'+
            '<name val="Calibri" />'+
            '</font>'+
            '<font>'+
            '<sz val="11" />'+
            '<name val="Calibri" />'+
            '<color rgb="FFFFFFFF" />'+
            '</font>'+
            '<font>'+
            '<sz val="11" />'+
            '<name val="Calibri" />'+
            '<b />'+
            '</font>'+
            '<font>'+
            '<sz val="11" />'+
            '<name val="Calibri" />'+
            '<i />'+
            '</font>'+
            '<font>'+
            '<sz val="11" />'+
            '<name val="Calibri" />'+
            '<u />'+
            '</font>'+
            '</fonts>'+
            '<fills count="6">'+
            '<fill>'+
            '<patternFill patternType="none" />'+
            '</fill>'+
            '<fill/>'+ // Excel appears to use this as a dotted background regardless of values
            '<fill>'+
            '<patternFill patternType="solid">'+
            '<fgColor rgb="FFD9D9D9" />'+
            '<bgColor indexed="64" />'+
            '</patternFill>'+
            '</fill>'+
            '<fill>'+
            '<patternFill patternType="solid">'+
            '<fgColor rgb="FFD99795" />'+
            '<bgColor indexed="64" />'+
            '</patternFill>'+
            '</fill>'+
            '<fill>'+
            '<patternFill patternType="solid">'+
            '<fgColor rgb="ffc6efce" />'+
            '<bgColor indexed="64" />'+
            '</patternFill>'+
            '</fill>'+
            '<fill>'+
            '<patternFill patternType="solid">'+
            '<fgColor rgb="ffc6cfef" />'+
            '<bgColor indexed="64" />'+
            '</patternFill>'+
            '</fill>'+
            '</fills>'+
            '<borders count="2">'+
            '<border>'+
            '<left />'+
            '<right />'+
            '<top />'+
            '<bottom />'+
            '<diagonal />'+
            '</border>'+
            '<border diagonalUp="false" diagonalDown="false">'+
            '<left style="thin">'+
            '<color auto="1" />'+
            '</left>'+
            '<right style="thin">'+
            '<color auto="1" />'+
            '</right>'+
            '<top style="thin">'+
            '<color auto="1" />'+
            '</top>'+
            '<bottom style="thin">'+
            '<color auto="1" />'+
            '</bottom>'+
            '<diagonal />'+
            '</border>'+
            '</borders>'+
            '<cellStyleXfs count="1">'+
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" />'+
            '</cellStyleXfs>'+
            '<cellXfs count="67">'+
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="0" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="2" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="3" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="4" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="0" fillId="3" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="1" fillId="3" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="2" fillId="3" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="3" fillId="3" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="4" fillId="3" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="0" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="1" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="2" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="3" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="4" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="0" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="1" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="2" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="3" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="4" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="1" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="2" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="3" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="4" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="0" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="3" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="4" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="1" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="2" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="3" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="4" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="0" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="1" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="2" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="3" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="4" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="0" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="1" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="2" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="3" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="4" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'+
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1">'+
            '<alignment horizontal="left"/>'+
            '</xf>'+
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1">'+
            '<alignment horizontal="center"/>'+
            '</xf>'+
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1">'+
            '<alignment horizontal="right"/>'+
            '</xf>'+
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1">'+
            '<alignment horizontal="fill"/>'+
            '</xf>'+
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1">'+
            '<alignment textRotation="90"/>'+
            '</xf>'+
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1">'+
            '<alignment wrapText="1"/>'+
            '</xf>'+
            '<xf numFmtId="9"   fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>'+
            '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>'+
            '<xf numFmtId="165" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>'+
            '<xf numFmtId="166" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>'+
            '<xf numFmtId="167" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>'+
            '<xf numFmtId="168" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>'+
            '<xf numFmtId="169" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>'+
            '<xf numFmtId="3" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>'+
            '<xf numFmtId="4" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>'+
            '<xf numFmtId="1" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>'+
            '<xf numFmtId="2" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>'+
            '</cellXfs>'+
            '<cellStyles count="1">'+
            '<cellStyle name="Normal" xfId="0" builtinId="0" />'+
            '</cellStyles>'+
            '<dxfs count="0" />'+
            '<tableStyles count="0" defaultTableStyle="TableStyleMedium9" defaultPivotStyle="PivotStyleMedium4" />'+
            '</styleSheet>'
    };
    // Note we could use 3 `for` loops for the styles, but when gzipped there is
    // virtually no difference in size, since the above can be easily compressed

    // Pattern matching for special number formats. Perhaps this should be exposed
    // via an API in future?
    // Ref: section 3.8.30 - built in formatters in open spreadsheet
    //   https://www.ecma-international.org/news/TC45_current_work/Office%20Open%20XML%20Part%204%20-%20Markup%20Language%20Reference.pdf
    var _excelSpecials = [
        { match: /^\-?\d+\.\d%$/,       style: 60, fmt: function (d) { return d/100; } }, // Precent with d.p.
        { match: /^\-?\d+\.?\d*%$/,     style: 56, fmt: function (d) { return d/100; } }, // Percent
        { match: /^\-?\$[\d,]+.?\d*$/,  style: 57 }, // Dollars
        { match: /^\-?£[\d,]+.?\d*$/,   style: 58 }, // Pounds
        { match: /^\-?€[\d,]+.?\d*$/,   style: 59 }, // Euros
        { match: /^\-?\d+$/,            style: 65 }, // Numbers without thousand separators
        { match: /^\-?\d+\.\d{2}$/,     style: 66 }, // Numbers 2 d.p. without thousands separators
        { match: /^\([\d,]+\)$/,        style: 61, fmt: function (d) { return -1 * d.replace(/[\(\)]/g, ''); } },  // Negative numbers indicated by brackets
        { match: /^\([\d,]+\.\d{2}\)$/, style: 62, fmt: function (d) { return -1 * d.replace(/[\(\)]/g, ''); } },  // Negative numbers indicated by brackets - 2d.p.
        { match: /^\-?[\d,]+$/,         style: 63 }, // Numbers with thousand separators
        { match: /^\-?[\d,]+\.\d{2}$/,  style: 64 }  // Numbers with 2 d.p. and thousands separators
    ];



    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Buttons
     */

    //
    // Copy to clipboard
    //
    DataTable.ext.buttons.copyHtml5 = {
        className: 'buttons-copy buttons-html5',

        text: function ( dt ) {
            return dt.i18n( 'buttons.copy', 'Copy' );
        },

        action: function ( e, dt, button, config ) {
            this.processing( true );

            var that = this;
            var exportData = _exportData( dt, config );
            var info = dt.buttons.exportInfo( config );
            var newline = _newLine(config);
            var output = exportData.str;
            var hiddenDiv = $('<div/>')
                .css( {
                    height: 1,
                    width: 1,
                    overflow: 'hidden',
                    position: 'fixed',
                    top: 0,
                    left: 0
                } );

            if ( info.title ) {
                output = info.title + newline + newline + output;
            }

            if ( info.messageTop ) {
                output = info.messageTop + newline + newline + output;
            }

            if ( info.messageBottom ) {
                output = output + newline + newline + info.messageBottom;
            }

            if ( config.customize ) {
                output = config.customize( output, config );
            }

            var textarea = $('<textarea readonly/>')
                .val( output )
                .appendTo( hiddenDiv );

            // For browsers that support the copy execCommand, try to use it
            if ( document.queryCommandSupported('copy') ) {
                hiddenDiv.appendTo( dt.table().container() );
                textarea[0].focus();
                textarea[0].select();

                try {
                    var successful = document.execCommand( 'copy' );
                    hiddenDiv.remove();

                    if (successful) {
                        dt.buttons.info(
                            dt.i18n( 'buttons.copyTitle', 'Copy to clipboard' ),
                            dt.i18n( 'buttons.copySuccess', {
                                1: 'Copied one row to clipboard',
                                _: 'Copied %d rows to clipboard'
                            }, exportData.rows ),
                            2000
                        );

                        this.processing( false );
                        return;
                    }
                }
                catch (t) {}
            }

            // Otherwise we show the text box and instruct the user to use it
            var message = $('<span>'+dt.i18n( 'buttons.copyKeys',
                'Press <i>ctrl</i> or <i>\u2318</i> + <i>C</i> to copy the table data<br>to your system clipboard.<br><br>'+
                'To cancel, click this message or press escape.' )+'</span>'
            )
                .append( hiddenDiv );

            dt.buttons.info( dt.i18n( 'buttons.copyTitle', 'Copy to clipboard' ), message, 0 );

            // Select the text so when the user activates their system clipboard
            // it will copy that text
            textarea[0].focus();
            textarea[0].select();

            // Event to hide the message when the user is done
            var container = $(message).closest('.dt-button-info');
            var close = function () {
                container.off( 'click.buttons-copy' );
                $(document).off( '.buttons-copy' );
                dt.buttons.info( false );
            };

            container.on( 'click.buttons-copy', close );
            $(document)
                .on( 'keydown.buttons-copy', function (e) {
                    if ( e.keyCode === 27 ) { // esc
                        close();
                        that.processing( false );
                    }
                } )
                .on( 'copy.buttons-copy cut.buttons-copy', function () {
                    close();
                    that.processing( false );
                } );
        },

        exportOptions: {},

        fieldSeparator: '\t',

        fieldBoundary: '',

        header: true,

        footer: false,

        title: '*',

        messageTop: '*',

        messageBottom: '*'
    };

    //
    // Excel (xlsx) export
    //
    DataTable.ext.buttons.excelHtml5 = {
        className: 'buttons-excel buttons-html5',

        available: function () {
            return window.FileReader !== undefined && _jsZip() !== undefined && ! _isDuffSafari() && _serialiser;
        },

        text: function ( dt ) {
            return dt.i18n( 'buttons.excel', 'Excel' );
        },

        action: function ( e, dt, button, config ) {
            this.processing( true );

            var that = this;
            var rowPos = 0;
            var getXml = function ( type ) {
                var str = excelStrings[ type ];

                //str = str.replace( /xmlns:/g, 'xmlns_' ).replace( /mc:/g, 'mc_' );

                return $.parseXML( str );
            };
            var rels = getXml('xl/worksheets/sheet1.xml');
            var relsGet = rels.getElementsByTagName( "sheetData" )[0];

            var xlsx = {
                _rels: {
                    ".rels": getXml('_rels/.rels')
                },
                xl: {
                    _rels: {
                        "workbook.xml.rels": getXml('xl/_rels/workbook.xml.rels')
                    },
                    "workbook.xml": getXml('xl/workbook.xml'),
                    "styles.xml": getXml('xl/styles.xml'),
                    "worksheets": {
                        "sheet1.xml": rels
                    }

                },
                "[Content_Types].xml": getXml('[Content_Types].xml')
            };

            var data = dt.buttons.exportData( config.exportOptions );
            var currentRow, rowNode;
            var addRow = function ( row ) {
                currentRow = rowPos+1;
                rowNode = _createNode( rels, "row", { attr: {r:currentRow} } );

                for ( var i=0, ien=row.length ; i<ien ; i++ ) {
                    // Concat both the Cell Columns as a letter and the Row of the cell.
                    var cellId = createCellPos(i) + '' + currentRow;
                    var cell = null;

                    // For null, undefined of blank cell, continue so it doesn't create the _createNode
                    if ( row[i] === null || row[i] === undefined || row[i] === '' ) {
                        continue;
                    }

                    row[i] = $.trim( row[i] );

                    // Special number formatting options
                    for ( var j=0, jen=_excelSpecials.length ; j<jen ; j++ ) {
                        var special = _excelSpecials[j];

                        // TODO Need to provide the ability for the specials to say
                        // if they are returning a string, since at the moment it is
                        // assumed to be a number
                        if ( row[i].match && ! row[i].match(/^0\d+/) && row[i].match( special.match ) ) {
                            var val = row[i].replace(/[^\d\.\-]/g, '');

                            if ( special.fmt ) {
                                val = special.fmt( val );
                            }

                            cell = _createNode( rels, 'c', {
                                attr: {
                                    r: cellId,
                                    s: special.style
                                },
                                children: [
                                    _createNode( rels, 'v', { text: val } )
                                ]
                            } );

                            break;
                        }
                    }

                    if ( ! cell ) {
                        if ( typeof row[i] === 'number' || (
                            row[i].match &&
                            row[i].match(/^-?\d+(\.\d+)?$/) &&
                            ! row[i].match(/^0\d+/) )
                        ) {
                            // Detect numbers - don't match numbers with leading zeros
                            // or a negative anywhere but the start
                            cell = _createNode( rels, 'c', {
                                attr: {
                                    t: 'n',
                                    r: cellId
                                },
                                children: [
                                    _createNode( rels, 'v', { text: row[i] } )
                                ]
                            } );
                        }
                        else {
                            // String output - replace non standard characters for text output
                            var text = ! row[i].replace ?
                                row[i] :
                                row[i].replace(/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F-\x9F]/g, '');

                            cell = _createNode( rels, 'c', {
                                attr: {
                                    t: 'inlineStr',
                                    r: cellId
                                },
                                children:{
                                    row: _createNode( rels, 'is', {
                                        children: {
                                            row: _createNode( rels, 't', {
                                                text: text
                                            } )
                                        }
                                    } )
                                }
                            } );
                        }
                    }

                    rowNode.appendChild( cell );
                }

                relsGet.appendChild(rowNode);
                rowPos++;
            };

            $( 'sheets sheet', xlsx.xl['workbook.xml'] ).attr( 'name', _sheetname( config ) );

            if ( config.customizeData ) {
                config.customizeData( data );
            }

            var mergeCells = function ( row, colspan ) {
                var mergeCells = $('mergeCells', rels);

                mergeCells[0].appendChild( _createNode( rels, 'mergeCell', {
                    attr: {
                        ref: 'A'+row+':'+createCellPos(colspan)+row
                    }
                } ) );
                mergeCells.attr( 'count', mergeCells.attr( 'count' )+1 );
                $('row:eq('+(row-1)+') c', rels).attr( 's', '51' ); // centre
            };

            // Title and top messages
            var exportInfo = dt.buttons.exportInfo( config );
            if ( exportInfo.title ) {
                addRow( ['{{__('accounting::lang.ledger')}}'.trim()+' '+$('#account_name').text().trim()], rowPos );
                mergeCells( rowPos, data.header.length-1 );
            }

            if ( exportInfo.messageTop ) {
                addRow( [exportInfo.messageTop], rowPos );
                mergeCells( rowPos, data.header.length-1 );
            }

            // Table itself
            if ( config.header ) {
                $('row:last c', rels).attr( 's', '2' ); // bold
            }

             if($('#OpeningBalance').length > 0)
            {
                var OpeningBalance = Array();
                $('#OpeningBalance').children('td').each(function(i, vv){
                    OpeningBalance[i] = $(this).text();
                });
                addRow( OpeningBalance, rowPos );
            }


            for ( var n=0, ie=data.body.length ; n<ie ; n++ ) {
                addRow( data.body[n], rowPos );
            }

            if ( config.footer && data.footer ) {
                var tfoot_array = splitTFoot('#ledger');
                for ( var n=0, ie=tfoot_array.length ; n<ie ; n++ ) {
                    //console.log(data.footer);
                    addRow( tfoot_array[n], rowPos);
                    $('row:last c', rels).attr( 's', '2' ); // bold
                }
            }

            // Below the table
            if ( exportInfo.messageBottom ) {
                addRow( [exportInfo.messageBottom], rowPos );
                mergeCells( rowPos, data.header.length-1 );
            }

            // Set column widths
            var cols = _createNode( rels, 'cols' );
            $('worksheet', rels).prepend( cols );

            for ( var i=0, ien=data.header.length ; i<ien ; i++ ) {
                cols.appendChild( _createNode( rels, 'col', {
                    attr: {
                        min: i+1,
                        max: i+1,
                        width: _excelColWidth( data, i ),
                        customWidth: 1
                    }
                } ) );
            }

            // Let the developer customise the document if they want to
            if ( config.customize ) {
                config.customize( xlsx );
            }

            var jszip = _jsZip();
            var zip = new jszip();
            var zipConfig = {
                type: 'blob',
                mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            };

            _addToZip( zip, xlsx );

            if ( zip.generateAsync ) {
                // JSZip 3+
                zip
                    .generateAsync( zipConfig )
                    .then( function ( blob ) {
                        _saveAs( blob, exportInfo.filename );
                        that.processing( false );
                    } );
            }
            else {
                // JSZip 2.5
                _saveAs(
                    zip.generate( zipConfig ),
                    exportInfo.filename
                );
                this.processing( false );
            }
        },

        filename: '*',

        extension: '.xlsx',

        exportOptions: {},

        header: true,

        footer: false,

        title: '*',

        messageTop: '*',

        messageBottom: '*'
    };

    //
    // PDF export - using pdfMake - http://pdfmake.org
    //
    DataTable.ext.buttons.pdfHtml5 = {
        className: 'buttons-pdf buttons-html5',

        available: function () {
            return window.FileReader !== undefined && _pdfMake();
        },

        text: function ( dt ) {
            return dt.i18n( 'buttons.pdf', 'PDF' );
        },

        action: function ( e, dt, button, config ) {
            this.processing( true );

            var that = this;
            var data = dt.buttons.exportData( config.exportOptions );
            var info = dt.buttons.exportInfo( config );
            var rows = [];

            if ( config.header ) {
                rows.push( $.map( data.header, function ( d ) {
                    return {
                        text: typeof d === 'string' ? d : d+'',
                        style: 'tableHeader'
                    };
                } ) );
            }

            for ( var i=0, ien=data.body.length ; i<ien ; i++ ) {
                rows.push( $.map( data.body[i], function ( d ) {
                    return {
                        text: typeof d === 'string' ? d : d+'',
                        style: i % 2 ? 'tableBodyEven' : 'tableBodyOdd'
                    };
                } ) );
            }

            if ( config.footer && data.footer) {
                rows.push( $.map( data.footer, function ( d ) {
                    return {
                        text: typeof d === 'string' ? d : d+'',
                        style: 'tableFooter'
                    };
                } ) );
            }

            var doc = {
                pageSize: config.pageSize,
                pageOrientation: config.orientation,
                content: [
                    {
                        table: {
                            headerRows: 1,
                            body: rows
                        },
                        layout: 'noBorders'
                    }
                ],
                styles: {
                    tableHeader: {
                        bold: true,
                        fontSize: 11,
                        color: 'white',
                        fillColor: '#2d4154',
                        alignment: 'center'
                    },
                    tableBodyEven: {},
                    tableBodyOdd: {
                        fillColor: '#f3f3f3'
                    },
                    tableFooter: {
                        bold: true,
                        fontSize: 11,
                        color: 'white',
                        fillColor: '#2d4154'
                    },
                    title: {
                        alignment: 'center',
                        fontSize: 15
                    },
                    message: {}
                },
                defaultStyle: {
                    fontSize: 10
                }
            };

            if ( info.messageTop ) {
                doc.content.unshift( {
                    text: info.messageTop,
                    style: 'message',
                    margin: [ 0, 0, 0, 12 ]
                } );
            }

            if ( info.messageBottom ) {
                doc.content.push( {
                    text: info.messageBottom,
                    style: 'message',
                    margin: [ 0, 0, 0, 12 ]
                } );
            }

            if ( info.title ) {
                doc.content.unshift( {
                    text: info.title,
                    style: 'title',
                    margin: [ 0, 0, 0, 12 ]
                } );
            }

            if ( config.customize ) {
                config.customize( doc, config );
            }

            var pdf = _pdfMake().createPdf( doc );

            if ( config.download === 'open' && ! _isDuffSafari() ) {
                pdf.open();
                this.processing( false );
            }
            else {
                pdf.getBuffer( function (buffer) {
                    var blob = new Blob( [buffer], {type:'application/pdf'} );

                    _saveAs( blob, info.filename );
                    that.processing( false );
                } );
            }
        },

        title: '*',

        filename: '*',

        extension: '.pdf',

        exportOptions: {},

        orientation: 'portrait',

        pageSize: 'A4',

        header: true,

        footer: false,

        messageTop: '*',

        messageBottom: '*',

        customize: null,

        download: 'download'
    };


    return DataTable.Buttons;
    }));


    function splitTFoot(table)
    {
        const arr = [];
        $(table+' tfoot tr').each(function(){
            const str = [];
           $(this).find('td').each(function(){
               boo = true;
               for (let i = 0; i < $(this).attr('colspan'); i++) {
                 if(boo)
                 {
                     if($(this).text() != "")
                     {
                         str.push($(this).text());
                         boo = false;
                     }
                     else
                         str.push('');
                 }
                 else
                 {
                     str.push('');
                 }

               }
            });
            arr.push(str);
        });

        return arr;
    }

    /*end export excel*/


    $(document).ready(function(){

        $(document).on('change', '#location_id',
            function() {
                $("#location_data").html($('#location_id :selected').text());
                ledger.ajax.reload();
            });

        $('#account_filter').change(function(){
            account_id = $(this).val();
            url = base_path + '/accounting/ledger/' + account_id;
            window.location = url;
        })

        dateRangeSettings.startDate = moment().subtract(6, 'days');
        dateRangeSettings.endDate = moment();
        $('#transaction_date_range_here').html(
            dateRangeSettings.startDate.format('YYYY-MM-DD')
            + ' ~ ' +
            dateRangeSettings.endDate.format('YYYY-MM-DD')
        );
        $('#transaction_date_range').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#transaction_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                $('#transaction_date_range_here').html(
                    start.format(moment_date_format)
                    + ' ~ ' +
                    end.format(moment_date_format)
                );
                ledger.ajax.reload();
            }
        );
        
        // Account Book
        ledger = $('#ledger').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: {
                                url: '{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'ledger'],[$account->id])}}',
                                data: function(d) {
                                    var start = '';
                                    var end = '';
                                    if($('#transaction_date_range').val()){
                                        start = $('input#transaction_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                                        end = $('input#transaction_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                                    }
                                    var transaction_type = $('select#transaction_type').val();
                                    d.start_date = start;
                                    d.end_date = end;
                                    d.type = transaction_type;
                                    if ($('#location_id').length) {
                                        d.location_id = $('#location_id').val();
                                    }
                                }
                            },
                            "ordering": false,
                            columns: [
                                {data: 'operation_date', name: 'operation_date'},
                                {data: 'ref_no', name: 'ATM.ref_no'},
                                {data: 'note', name: 'ATM.note'},
                                {data: 'added_by', name: 'added_by'},
                                {data: 'debit', name: 'amount', searchable: false},
                                {data: 'credit', name: 'amount', searchable: false,'title':'{{__("accounting::lang.credit")}}'},
                                //{data: 'balance', name: 'balance', searchable: false},
                                {data: 'location_name', name: 'location_name', searchable: false},
                                {data: 'balance',name: 'balance', searchable: false},
                                // {data: 'action', name: 'action', searchable: false}
                            ],
                            "fnDrawCallback": function (oSettings) {
                                __currency_convert_recursively($('#ledger'));
                            },
                            "footerCallback": function ( row, data, start, end, display ) {
                                var footer_total_debit = 0;
                                var footer_total_credit = 0;

                                for (var r in data){
                                    footer_total_debit += $(data[r].debit).data('orig-value') ? parseFloat($(data[r].debit).data('orig-value')) : 0;
                                    footer_total_credit += $(data[r].credit).data('orig-value') ? parseFloat($(data[r].credit).data('orig-value')) : 0;
                                }

                                $('.footer_total_debit').html(__currency_trans_from_en(footer_total_debit));
                                $('.footer_total_credit').html(__currency_trans_from_en(footer_total_credit));
                            }
                        });
        $('#transaction_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#transaction_date_range').val('');
            ledger.ajax.reload();
        });

        ledger.on( 'draw', function () {

            //Movement Balance
            getMovementBalance();

            //All Blanace
            $.ajax({
                method: 'get',
                url: '/getAllBlanace',
                data:
                    {
                        account_id: {{$account->id}},
                        location_id: $("#location_id").val()
                    },
                success: function(result) {

                    $("#all_balance").html(__currency_trans_from_en(result));
                    $("#all_balance").attr("amount",result);

                },
            });

            //Opening Balance
            $.ajax({
                method: 'get',
                url: '/getOpeningBalance',
                data:
                    {
                        account_id: {{$account->id}},
                        operation_date: $('#transaction_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD'),
                        location_id: $("#location_id").val()
                    },
                success: function(result) {

                    // insertedRow = ledger.row(0).data();
                    // insertedRow["operation_date"] = "sdasds";
                    // ledger.row(0).data(insertedRow);

                    var x=document.getElementById('ledger').insertRow(1);
                    x.id = "OpeningBalance";
                    x.insertCell(0).innerHTML=__currency_trans_from_en(result['amount']);
                    x.insertCell(0).innerHTML="";
                    x.insertCell(0).innerHTML="";
                    x.insertCell(0).innerHTML="";
                    x.insertCell(0).innerHTML="";
                    x.insertCell(0).innerHTML="";
                    x.insertCell(0).innerHTML="{{__('lang_v1.opening_balance')}}";
                    x.insertCell(0).innerHTML=result['operation_date'];

                },
            });

            //Closing Balance
            $.ajax({
                method: 'get',
                url: '/getClosingBalance',
                data:
                    {
                        account_id: {{$account->id}},
                        location_id: $("#location_id").val()
                    },
                success: function(result) {
                    $(".closing_balance").html(__currency_trans_from_en(result));
                    $(".closing_balance").attr("amount",result);

                    @if($account->gl_code == 4101)
                    //Sales Discount
                    $.ajax({
                        method: 'get',
                        url: '/getSalesDiscount',
                        data:
                            {
                                location_id: $("#location_id").val(),
                            },
                        success: function(result) {

                            // $(".sales_discount_debit").html(__currency_trans_from_en(result['debit']));
                            // $(".sales_discount_credit").html(__currency_trans_from_en(result['credit']));

                            var amount = parseFloat(result['credit']) - parseFloat(result['debit']);
                            var res = parseFloat($(".closing_balance").attr("amount")) - parseFloat(amount);
                            $(".closing_balance").html(__currency_trans_from_en(res));
                            $(".closing_balance").attr("amount",res);

                            $(".total_discount_amount").html(__currency_trans_from_en(amount));

                            var res =  parseFloat($("#all_balance").attr("amount")) - parseFloat(amount);
                            $("#all_balance").html(__currency_trans_from_en(res));
                            $("#all_balance").attr("amount",res);

                        },
                    });
                    @endif

                    @if($account->gl_code == 1106)
                    //Discount On Purchases
                    $.ajax({
                        method: 'get',
                        url: '/getDiscountOnPurchases',
                        data:
                            {
                                location_id: $("#location_id").val(),
                            },
                        success: function(result) {

                            // $(".discount_on_purchases_debit").html(__currency_trans_from_en(result['debit']));
                            // $(".discount_on_purchases_credit").html(__currency_trans_from_en(result['credit']));

                            var amount = parseFloat(result['credit']) - parseFloat(result['debit']);
                            var res =  parseFloat($(".closing_balance").attr("amount")) - parseFloat(amount);
                            $(".closing_balance").html(__currency_trans_from_en(res));
                            $(".closing_balance").attr("amount",res);

                            $(".total_discount_on_purchases_amount").html(__currency_trans_from_en(amount));

                            var res =  parseFloat($("#all_balance").attr("amount")) - parseFloat(amount);
                            $("#all_balance").html(__currency_trans_from_en(res));
                            $("#all_balance").attr("amount",res);
                        },
                    });
                    @endif

                },
            });

        });

        function getMovementBalance()
        {
            var num = 0;
            var row = 0;
            $('.movement_balance_row').each(function(index,tag)
            {
                if($(tag).attr("type") == "debit")
                {
                    num += parseFloat($(tag).attr("amount"));
                }
                else
                if($(tag).attr("type") == "credit")
                {
                    num -= parseFloat($(tag).attr("amount"));
                }
                ledger.cell(row, 7).data(__currency_trans_from_en(num));
                ++row;
            });

            $('.movement_balance').html(__currency_trans_from_en(num));
        }
    });
</script>
@stop