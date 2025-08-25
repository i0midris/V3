<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;

class CurrencyController extends Controller
{
    /**
     * Get the exchange rate for a given currency code.
     *
     * @param  string  $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExchangeRate($code)
    {
        $currency = Currency::active()->where('code', $code)->first();

        if ($currency) {
            return response()->json([
                'success' => true,
                'rate' => (float) $currency->exchange_rate,
            ]);
        }

        return response()->json([
            'success' => false,
            'rate' => 1,
            'message' => 'Currency not found or inactive.',
        ], 404);
    }

    /**
     * Get a list of all active currencies (code => label).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveCurrencies()
    {
        $currencies = Currency::active()
            ->get()
            ->mapWithKeys(function ($currency) {
                return [$currency->code => "{$currency->code} - {$currency->currency}"];
            });

        return response()->json([
            'success' => true,
            'currencies' => $currencies
        ]);
    }
}
