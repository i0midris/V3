<?php

namespace Modules\FatooraZatcaForUltimatePos\Http\Controllers;

use App\Transaction;
use Bl\FatooraZatca\Invoices\Invoiceable;
use Illuminate\Routing\Controller;

class DownloadXmlController extends Controller
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

        $invoiceable = new Invoiceable;
        $invoiceable->setResult((array) json_decode($transaction->zatca));

        return response($invoiceable->getXmlInvoice())
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="example.xml"');
    }
}
