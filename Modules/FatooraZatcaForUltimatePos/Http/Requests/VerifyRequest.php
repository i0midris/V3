<?php

namespace Modules\FatooraZatcaForUltimatePos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'otp' => 'required|string', // TODO |size:5
        ];
    }
}
