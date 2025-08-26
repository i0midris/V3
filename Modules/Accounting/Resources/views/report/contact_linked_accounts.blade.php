@extends('layouts.app')

@section('title', __('accounting::lang.contact_linked_accounts_report'))

@section('content')
    <section class="content-header">
        <h1>@lang('accounting::lang.contact_linked_accounts_report')</h1>
    </section>
     {{-- Filter form --}}
    <div class="tw-mx-4">
        @component('components.filters', ['title' => __('report.filters')])
        <form method="GET" action="{{ route('accounting.contact_linked_accounts_report') }}">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="form-group">
                        <label for="contact_type" class="control-label">@lang('accounting::lang.contact_type')</label>
                        <select name="contact_type" id="contact_type" class="form-control">
                            <option value="">@lang('accounting::lang.all')</option>
                            <option value="customer" {{ request()->contact_type == 'customer' ? 'selected' : '' }}>
                                @lang('accounting::lang.customer')
                            </option>
                            <option value="supplier" {{ request()->contact_type == 'supplier' ? 'selected' : '' }}>
                                @lang('accounting::lang.supplier')
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="submit" class="add-btn" style="margin-top:1.5rem">
                        @lang('accounting::lang.filter')
                    </button>
                </div>
            </div>
        </form>
    @endcomponent
    </div>
    <section class="content">
        <div class="box box-solid">
            <div class="box-body">

               
                <!-- <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('accounting::lang.filter')</h3>
                    </div>
                    <div class="box-body">
                        <form method="GET" action="{{ route('accounting.contact_linked_accounts_report') }}">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label for="contact_type" class="control-label">@lang('accounting::lang.contact_type')</label>
                                        <select name="contact_type" id="contact_type" class="form-control">
                                            <option value="">@lang('accounting::lang.all')</option>
                                            <option value="customer" {{ request()->contact_type == 'customer' ? 'selected' : '' }}>
                                                @lang('accounting::lang.customer')
                                            </option>
                                            <option value="supplier" {{ request()->contact_type == 'supplier' ? 'selected' : '' }}>
                                                @lang('accounting::lang.supplier')
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2 mb-3 d-flex align-items-end">
                                    <button type="submit" class="add-btn" style="margin-top:1.5rem">
                                        @lang('accounting::lang.filter')
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div> -->


                {{-- Accounts Table --}}
                <div class="tw-border" style="border-radius:0.75rem;overflow:hidden">
                    <table class="table" style="margin:0 !important;" id="accounts-table">
                        <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
                            <tr>
                                <th>@lang('accounting::lang.account_name')</th>
                                <th>@lang('accounting::lang.balance')</th>
                                <th>@lang('accounting::lang.balance_type')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accounts as $account)
                                <tr>
                                    <td>{{ $account->name }}</td>
                                    <td>{{ number_format($account->formatted_balance, 2) }}</td>
                                    <td>{{ $account->balance_type }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">@lang('accounting::lang.no_data_found')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </section>

    {{-- Total row calculation --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let total = 0;

            document.querySelectorAll('#accounts-table tbody tr').forEach(row => {
                const amountText = row.children[1]?.textContent?.replace(/,/g, '') || '0';
                const typeText = row.children[2]?.textContent?.trim() || '';
                const amount = parseFloat(amountText);

                if (!isNaN(amount)) {
                    total += (typeText === 'دائن') ? -amount : amount;
                }
            });

            const tfoot = document.createElement('tfoot');
            const totalRow = document.createElement('tr');
            totalRow.innerHTML = `
                <th>@lang('accounting::lang.total')</th>
                <th>${Math.abs(total).toFixed(2)}</th>
                <th>${total < 0 ? 'دائن' : 'مدين'}</th>
            `;
            tfoot.appendChild(totalRow);
            document.querySelector('#accounts-table').appendChild(tfoot);
        });
    </script>
@endsection
