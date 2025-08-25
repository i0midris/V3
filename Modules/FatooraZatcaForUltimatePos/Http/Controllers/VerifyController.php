<?php

namespace Modules\FatooraZatcaForUltimatePos\Http\Controllers;

use Bl\FatooraZatca\Objects\Setting;
use Bl\FatooraZatca\Zatca;
use Exception;
use Illuminate\Routing\Controller;
use Modules\FatooraZatcaForUltimatePos\Entities\ZatcaBusiness;
use Modules\FatooraZatcaForUltimatePos\Http\Requests\VerifyRequest;

class VerifyController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(VerifyRequest $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $business = ZatcaBusiness::query()->find($business_id);

        $settings = new Setting(
            $request->input('otp'),
            $business->getZatcaField('email'),
            $business->getZatcaField('common_name'),
            $business->getZatcaField('organizational_unit_name'),
            $business->getZatcaField('organization_name'),
            $business->getZatcaField('tax_number'),
            $business->getZatcaField('registered_address'),
            $business->getZatcaField('business_category'),
            $business->getZatcaField('egs_serial_number'),
            $business->getZatcaField('registration_number'),
            $business->getZatcaField('invoice_report_type')
        );

        try {
            config()->set('zatca.app.environment', $business->getZatcaField('environment'));

            $business->zatca_settings = Zatca::generateZatcaSetting($settings);

            $business->setZatcaFields([
                'egs_serial_number' => $settings->egsSerialNumber,
            ]);

            $business->save();

            $output = [
                'success' => 1,
                'msg' => __('receipt.receipt_settings_updated'),
            ];
        } catch (Exception $e) {
            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return back()->with('status', $output);
    }
}
