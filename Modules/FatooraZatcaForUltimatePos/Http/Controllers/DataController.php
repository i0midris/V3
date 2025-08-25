<?php

namespace Modules\FatooraZatcaForUltimatePos\Http\Controllers;

use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Routing\Controller;
use Menu;

class DataController extends Controller
{
    public static function isEnable()
    {
        return ! is_null(config('fatoorazatcaforultimatepos'));
    }

    /**
     * Superadmin package permissions
     *
     * @return array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'fatoorazatcaforultimatepos_module',
                'label' => __('fatoorazatcaforultimatepos::lang.zatca'),
                'default' => false,
            ],
        ];
    }

    /**
     * Adds cms menus
     *
     * @return null
     */
    public function modifyAdminMenu()
{
    $business_id = session()->get('user.business_id');
    $module_util = new ModuleUtil();
    $commonUtil = new Util();
    $is_admin = $commonUtil->is_admin(auth()->user(), $business_id);

    // ✅ Superadmin check using config list
    $superadmins = explode(',', strtolower(config('constants.administrator_usernames')));
    $current_username = strtolower(auth()->user()->username);

    if (!self::isEnable() || !in_array($current_username, $superadmins)) {
        return;
    }

    Menu::modify('admin-sidebar-menu', function ($menu): void {
        $menu->url(
            action([SettingsController::class, 'index']),
            __('fatoorazatcaforultimatepos::lang.zatca_settings'),
            ['icon' => 'fa fas fa-cogs', 'active' => request()->segment(1) == 'zatca']
        )->order(80);
    });
}


    /**
     * Defines user permissions for the module.
     *
     * @return array
     */
     public function user_permissions()
     {
         return [
             [
                 'value' => 'accounting.access_accounting_module',
                 'label' => __('accounting::lang.access_accounting_module'),
                 'default' => false,
             ],
         ];
     }

    public function get_additional_script()
    {
        if (! self::isEnable()) {
            return;
        }

        $additional_js = '';
        $additional_css = '';
        $additional_html = '';
        $additional_views = '';

        if (request()->routeIs('sells.index') || request()->routeIs('pos.index')) {
            $zatcaResendUrl = route('zatca.resend', 'transaction_uuid');
            $zatcaXmlUrl = route('zatca.download-xml', 'transaction_uuid');
            $additional_js .= "<script>\n";
            $additional_js .= "\n const zatcaResendUrl = '$zatcaResendUrl'; \n";
            $additional_js .= "\n const zatcaXmlUrl = '$zatcaXmlUrl'; \n";
            $additional_js .= file_get_contents(module_path('FatooraZatcaForUltimatePos').'/Resources/assets/js/sells.js');
            $additional_js .= "\n</script>";
        } elseif (request()->routeIs('sell-return.index')) {
            $zatcaResendUrl = route('zatca.resend', 'transaction_uuid');
            $zatcaXmlUrl = route('zatca.download-xml', 'transaction_uuid');
            $additional_js .= "<script>\n";
            $additional_js .= "\n const zatcaResendUrl = '$zatcaResendUrl'; \n";
            $additional_js .= "\n const zatcaXmlUrl = '$zatcaXmlUrl'; \n";
            $additional_js .= file_get_contents(module_path('FatooraZatcaForUltimatePos').'/Resources/assets/js/sell_return.js');
            $additional_js .= "\n</script>";
        }

        return compact('additional_js', 'additional_css', 'additional_html', 'additional_views');
    }
}
