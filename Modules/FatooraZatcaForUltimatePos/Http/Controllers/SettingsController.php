<?php

namespace Modules\FatooraZatcaForUltimatePos\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\FatooraZatcaForUltimatePos\Entities\ZatcaBusiness;
use Modules\FatooraZatcaForUltimatePos\Http\Requests\SettingsRequest;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        $business = ZatcaBusiness::query()->find($business_id);

        return view('fatoorazatcaforultimatepos::settings')
            ->with('business', $business)
            ->with('zatca_fields', $business->zatca_fields)
            ->with('zatca_verified', $business->zatca_verified)
            ->with('invoice_types', [
                '0100' => __('fatoorazatcaforultimatepos::lang.invoice_report_type.simplified'),
                '1000' => __('fatoorazatcaforultimatepos::lang.invoice_report_type.standard'),
                '1100' => __('fatoorazatcaforultimatepos::lang.invoice_report_type.both'),
            ])
            ->with('environments', [
                'local' => __('fatoorazatcaforultimatepos::lang.environments.local'),
                'simulation' => __('fatoorazatcaforultimatepos::lang.environments.simulation'),
                'production' => __('fatoorazatcaforultimatepos::lang.environments.production'),
            ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(SettingsRequest $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $zatca = ZatcaBusiness::query()
            ->where('id', $business_id)
            ->first();

        $zatca->zatca_fields = $request->validated();

        $zatca->save();

        return back()->with('status', [
            'success' => 1,
            'msg' => __('receipt.receipt_settings_updated'),
        ]);
    }
}
