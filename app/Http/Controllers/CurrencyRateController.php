<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CurrencyRate;
use Illuminate\Support\Facades\Session;

class CurrencyRateController extends Controller
{
    /**
     * Display a listing of the currency rates.
     */
    public function index()
    {
        $business_id = session('user.business_id');
        $currency_rates = CurrencyRate::where('business_id', $business_id)->get();

        return view('currency.index', compact('currency_rates'));
    }

    /**
     * Store a newly created currency rate in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'currency_name' => 'required|string|max:255',
                'currency_code' => 'required|string|max:10',
                'exchange_rate' => 'required|numeric|min:0.000001',
            ]);

            $business_id = session('user.business_id');

            CurrencyRate::create([
                'business_id' => $business_id,
                'currency_name' => $request->currency_name,
                'currency_code' => strtoupper($request->currency_code),
                'exchange_rate' => $request->exchange_rate,
                'status' => $request->has('status') ? 1 : 0,
            ]);

            return back()->with('status', [
                'success' => 1,
                'msg' => __('lang_v1.currency_added_successfully')
            ]);
        } catch (\Exception $e) {
            \Log::error("CurrencyRateController@store: " . $e->getMessage());
            return back()->with('status', [
                'success' => 0,
                'msg' => __('messages.something_went_wrong')
            ]);
        }
    }

    public function edit($id)
{
    $business_id = session('user.business_id');
    $currency_rate = \App\Models\CurrencyRate::where('business_id', $business_id)->findOrFail($id);

    return view('currency.edit', compact('currency_rate'));
}


    /**
     * Update the specified currency rate in storage.
     */
    public function update(Request $request, $id)
{
    $request->validate([
        'currency_name' => 'required|string|max:255',
        'currency_code' => 'required|string|max:10',
        'exchange_rate' => 'required|numeric|min:0.0000000001',
    ]);

    $business_id = session('user.business_id');
    $currency_rate = \App\Models\CurrencyRate::where('business_id', $business_id)->findOrFail($id);

    $currency_rate->update([
        'currency_name' => $request->currency_name,
        'currency_code' => strtoupper($request->currency_code),
        'exchange_rate' => $request->exchange_rate,
        'status' => $request->has('status') ? 1 : 0,
    ]);

    return redirect('/currency')->with('status', [
        'success' => 1,
        'msg' => __('lang_v1.updated_success'),
    ]);
}

public function getRate($code)
{
    $business_id = session('user.business_id');

    $currency = \App\Models\CurrencyRate::where('business_id', $business_id)
        ->where('currency_code', strtoupper($code))
        ->where('status', 1)
        ->first();

    if ($currency) {
        return response()->json([
            'rate' => $currency->exchange_rate,
            'symbol' => $currency->currency_code // Optional: you can change this if you store a symbol
        ]);
    }

    return response()->json([
        'rate' => 1,
        'symbol' => ''
    ]);
}

    /**
     * Remove the specified currency rate from storage.
     */
    public function destroy($id)
    {
        try {
            $business_id = session('user.business_id');
            $rate = CurrencyRate::where('business_id', $business_id)->findOrFail($id);
            $rate->delete();

            return back()->with('status', [
                'success' => 1,
                'msg' => __('lang_v1.deleted_success')
            ]);
        } catch (\Exception $e) {
            \Log::error("CurrencyRateController@destroy: " . $e->getMessage());
            return back()->with('status', [
                'success' => 0,
                'msg' => __('messages.something_went_wrong')
            ]);
        }
    }
}
