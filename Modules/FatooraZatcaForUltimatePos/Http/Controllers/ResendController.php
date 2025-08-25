<?php

namespace Modules\FatooraZatcaForUltimatePos\Http\Controllers;

use App\Transaction;
use Exception;
use Illuminate\Routing\Controller;
use Modules\FatooraZatcaForUltimatePos\Services\ZatcaService;

class ResendController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function __invoke($transactionUuid)
    {
        $transaction = Transaction::query()->where('uuid', $transactionUuid)->firstOrFail();

        $business_id = request()->session()->get('user.business_id');

        if ($business_id !== $transaction->business_id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            ZatcaService::report($transaction);

            return response()->json(['success' => true, 'msg' => __('fatoorazatcaforultimatepos::lang.invoice_resend_successfully')]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }

    }
}
