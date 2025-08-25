<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountingAccountTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounting_account_types', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('business_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->string('account_primary_type')->nullable();
            $table->string('account_type')->nullable();
            $table->bigInteger('parent_id')->nullable();
            $table->text('description')->nullable();
            $table->boolean('show_balance')->default(1);
            $table->timestamps();
        });

        $sql = "INSERT INTO `accounting_account_types` (`id`, `name`, `business_id`, `created_by`, `account_primary_type`, `account_type`, `parent_id`, `description`, `show_balance`, `created_at`, `updated_at`) VALUES
                    (3,	'owners_equity',	NULL,	NULL,	'equity',	'sub_type',	NULL,	NULL,	1,	NULL,	NULL),
                    (11,	'current_assets',	NULL,	NULL,	'asset',	'sub_type',	NULL,	NULL,	1,	NULL,	NULL),
                    (12,	'non_current_assets',	NULL,	NULL,	'asset',	'sub_type',	NULL,	NULL,	1,	NULL,	NULL),
                    (21,	'current_liabilities',	NULL,	NULL,	'liability',	'sub_type',	NULL,	NULL,	1,	NULL,	NULL),
                    (22,	'non_current_liabilities',	NULL,	NULL,	'liability',	'sub_type',	NULL,	NULL,	1,	NULL,	NULL),
                    (31,	'Issued Capital',	NULL,	NULL,	NULL,	'detail_type',	3,	'issued_capital_desc',	1,	NULL,	NULL),
                    (32,	'Other Equity',	NULL,	NULL,	NULL,	'detail_type',	3,	'other_equity_desc',	1,	NULL,	NULL),
                    (33,	'Reserve',	NULL,	NULL,	NULL,	'detail_type',	3,	'reserve_desc',	1,	NULL,	NULL),
                    (34,	'Retained Earnings Or Losses',	NULL,	NULL,	NULL,	'detail_type',	3,	'retained_earnings_or_losses_desc',	1,	NULL,	NULL),
                    (41,	'Operational Revenue',	NULL,	NULL,	'income',	'sub_type',	NULL,	'',	1,	NULL,	NULL),
                    (42,	'Non Operating Revenues',	NULL,	NULL,	'income',	'sub_type',	NULL,	'',	1,	NULL,	NULL),
                    (51,	'Direct Cost',	NULL,	NULL,	'expenses',	'sub_type',	NULL,	'',	1,	NULL,	NULL),
                    (52,	'Operational Cost',	NULL,	NULL,	'expenses',	'sub_type',	NULL,	'',	1,	NULL,	NULL),
                    (53,	'Non Operational Expenses',	NULL,	NULL,	'expenses',	'sub_type',	NULL,	'',	1,	NULL,	NULL),
                    (1101,	'Cash And Equivalents',	NULL,	NULL,	NULL,	'detail_type',	11,	'non_bank_cash_and_equivalents_desc',	1,	NULL,	NULL),
                    (1102,	'Cash In Bank',	NULL,	NULL,	NULL,	'detail_type',	11,	'cash_in_bank_desc',	1,	NULL,	NULL),
                    (1103,	'Accounts Receivable',	NULL,	NULL,	NULL,	'detail_type',	11,	'accounts_receivable_desc',	1,	NULL,	NULL),
                    (1104,	'Prepaid Expenses',	NULL,	NULL,	NULL,	'detail_type',	11,	'prepaid_expenses_desc',	1,	NULL,	NULL),
                    (1105,	'Staff Advances',	NULL,	NULL,	NULL,	'detail_type',	11,	'staff_advances_desc',	1,	NULL,	NULL),
                    (1106,	'Inventory',	NULL,	NULL,	NULL,	'detail_type',	11,	'inventory_desc',	1,	NULL,	NULL),
                    (1201,	'Property Plant And Equipmen',	NULL,	NULL,	NULL,	'detail_type',	12,	'property_plant_and_equipmen_desc',	1,	NULL,	NULL),
                    (1202,	'Intangible Assets',	NULL,	NULL,	NULL,	'detail_type',	12,	'intangible_assets_desc',	1,	NULL,	NULL),
                    (1203,	'Investment Property',	NULL,	NULL,	NULL,	'detail_type',	12,	'investment_property_desc',	1,	NULL,	NULL),
                    (2101,	'Accounts Payable',	NULL,	NULL,	NULL,	'detail_type',	21,	'accounts_payable_desc',	1,	NULL,	NULL),
                    (2102,	'Accrued Expenses',	NULL,	NULL,	NULL,	'detail_type',	21,	'accrued_expenses_desc',	1,	NULL,	NULL),
                    (2103,	'Accrued Salaries',	NULL,	NULL,	NULL,	'detail_type',	21,	'accrued_salaries_desc',	1,	NULL,	NULL),
                    (2104,	'Short Term Loans',	NULL,	NULL,	NULL,	'detail_type',	21,	'short_term_loans_desc',	1,	NULL,	NULL),
                    (2105,	'VAT Payable',	NULL,	NULL,	NULL,	'detail_type',	21,	'vat_payable_desc',	1,	NULL,	NULL),
                    (2106,	'Accrued Taxes',	NULL,	NULL,	NULL,	'detail_type',	21,	'accrued_taxes_desc',	1,	NULL,	NULL),
                    (2107,	'Unearned Revenues',	NULL,	NULL,	NULL,	'detail_type',	21,	'unearned_revenues_desc',	1,	NULL,	NULL),
                    (2108,	'General Organization For Social Insurance Payable',	NULL,	NULL,	NULL,	'detail_type',	21,	'general_organization_for_social_insurance_payable_desc',	1,	NULL,	NULL),
                    (2109,	'Accumulated Depreciation',	NULL,	NULL,	NULL,	'detail_type',	21,	'accumulated_depreciation_desc',	1,	NULL,	NULL),
                    (2201,	'Long Term Loans',	NULL,	NULL,	NULL,	'detail_type',	22,	'long_term_loans_desc',	1,	NULL,	NULL),
                    (2202,	'End Of Services Provision',	NULL,	NULL,	NULL,	'detail_type',	22,	'end_of_services_provision_desc',	1,	NULL,	NULL),
                    (4101,	'Revenue Of Products And Services Sales',	NULL,	NULL,	NULL,	'detail_type',	41,	'revenue_of_products_and_services_sales_desc',	1,	NULL,	NULL),
                    (4201,	'Other Income',	NULL,	NULL,	NULL,	'detail_type',	42,	'other_income_desc',	1,	NULL,	NULL),
                    (5101,	'Cost Of Goods Sold',	NULL,	NULL,	NULL,	'detail_type',	5,	'cost_of_goods_sold_desc',	1,	NULL,	NULL),
                    (5102,	'Salaries And Wages',	NULL,	NULL,	NULL,	'detail_type',	5,	'salaries_and_wages_desc',	1,	NULL,	NULL),
                    (5103,	'Sales Commissions',	NULL,	NULL,	NULL,	'detail_type',	5,	'sales_commissions_desc',	1,	NULL,	NULL),
                    (5104,	'Shipping And Custom Fees',	NULL,	NULL,	NULL,	'detail_type',	5,	'shipping_and_custom_fees_desc',	1,	NULL,	NULL),
                    (5201,	'Salaries And Administrative Fees',	NULL,	NULL,	NULL,	'detail_type',	5,	'salaries_and_administrative_fees_desc',	1,	NULL,	NULL),
                    (5202,	'Medical Insurance And Treatment',	NULL,	NULL,	NULL,	'detail_type',	5,	'medical_insurance_and_treatment_desc',	1,	NULL,	NULL),
                    (5203,	'Marketing And Advertising',	NULL,	NULL,	NULL,	'detail_type',	5,	'marketing_and_advertising_desc',	1,	NULL,	NULL),
                    (5204,	'Rental Expenses',	NULL,	NULL,	NULL,	'detail_type',	5,	'rental_expenses_desc',	1,	NULL,	NULL),
                    (5205,	'Commissions And Incentives',	NULL,	NULL,	NULL,	'detail_type',	5,	'commissions_and_incentives_desc',	1,	NULL,	NULL),
                    (5206,	'Travel Expenses',	NULL,	NULL,	NULL,	'detail_type',	5,	'travel_expenses_desc',	1,	NULL,	NULL),
                    (5207,	'Social Insurance Expense',	NULL,	NULL,	NULL,	'detail_type',	5,	'social_insurance_expense_desc',	1,	NULL,	NULL),
                    (5208,	'Government Fees',	NULL,	NULL,	NULL,	'detail_type',	5,	'government_fees_desc',	1,	NULL,	NULL),
                    (5209,	'Fees And Subscriptions',	NULL,	NULL,	NULL,	'detail_type',	5,	'fees_and_subscriptions_desc',	1,	NULL,	NULL),
                    (5210,	'Utilities Expenses',	NULL,	NULL,	NULL,	'detail_type',	5,	'utilities_expenses_desc',	1,	NULL,	NULL),
                    (5211,	'Stationery And Prints',	NULL,	NULL,	NULL,	'detail_type',	5,	'stationery_and_prints_desc',	1,	NULL,	NULL),
                    (5212,	'Hospitality And Cleanliness',	NULL,	NULL,	NULL,	'detail_type',	5,	'hospitality_and_cleanliness_desc',	1,	NULL,	NULL),
                    (5213,	'Bank Commissions',	NULL,	NULL,	NULL,	'detail_type',	5,	'bank_commissions_desc',	1,	NULL,	NULL),
                    (5214,	'Other Expenses',	NULL,	NULL,	NULL,	'detail_type',	5,	'other_expenses_desc',	1,	NULL,	NULL),
                    (5215,	'Depreciation',	NULL,	NULL,	NULL,	'detail_type',	5,	'depreciation_desc',	1,	NULL,	NULL),
                    (5216,	'Transportation Expense',	NULL,	NULL,	NULL,	'detail_type',	5,	'transportation_expense_desc',	1,	NULL,	NULL),
                    (5301,	'Zakat',	NULL,	NULL,	NULL,	'detail_type',	5,	'zakat_desc',	1,	NULL,	NULL),
                    (5302,	'TAX',	NULL,	NULL,	NULL,	'detail_type',	5,	'tax_desc',	1,	NULL,	NULL),
                    (5303,	'Change In Currency Value Gains Or Losses',	NULL,	NULL,	NULL,	'detail_type',	5,	'change_in_currency_value_gains_or_losses_desc',	1,	NULL,	NULL),
                    (5304,	'Interest',	NULL,	NULL,	NULL,	'detail_type',	5,	'interest_desc',	1,	NULL,	NULL),
                    (110101,	'Cash On Hand',	NULL,	NULL,	NULL,	'detail_type',	11,	'cash_on_hand_desc',	1,	NULL,	NULL),
                    (110102,	'Petty Cash',	NULL,	NULL,	NULL,	'detail_type',	11,	'petty_cash_desc',	1,	NULL,	NULL),
                    (110201,	'Bank Current Account Bank Name',	NULL,	NULL,	NULL,	'detail_type',	11,	'bank_current_account_bank_name_desc',	1,	NULL,	NULL),
                    (110202,	'Bank Demo',	NULL,	NULL,	NULL,	'detail_type',	11,	'bank_demo_desc',	1,	NULL,	NULL),
                    (110401,	'Prepaid Medical Insurance',	NULL,	NULL,	NULL,	'detail_type',	11,	'prepaid_medical_insurance_desc',	1,	NULL,	NULL),
                    (110402,	'Prepaid Rent',	NULL,	NULL,	NULL,	'detail_type',	11,	'prepaid_rent_desc',	1,	NULL,	NULL),
                    (120101,	'Lands',	NULL,	NULL,	NULL,	'detail_type',	12,	'lands_desc',	1,	NULL,	NULL),
                    (120102,	'Buildings',	NULL,	NULL,	NULL,	'detail_type',	12,	'buildings_desc',	1,	NULL,	NULL),
                    (120103,	'Equipment',	NULL,	NULL,	NULL,	'detail_type',	12,	'equipment_desc',	1,	NULL,	NULL),
                    (120104,	'Computers Printers',	NULL,	NULL,	NULL,	'detail_type',	12,	'computers_printers_desc',	1,	NULL,	NULL),
                    (210901,	'Buildings Accumulated Depreciation',	NULL,	NULL,	NULL,	'detail_type',	21,	'buildings_accumulated_depreciation_desc',	1,	NULL,	NULL),
                    (210902,	'Equipment Accumulated Depreciation',	NULL,	NULL,	NULL,	'detail_type',	21,	'equipment_accumulated_depreciation_desc',	1,	NULL,	NULL),
                    (210903,	'Computers Printers Accumulated Depreciation',	NULL,	NULL,	NULL,	'detail_type',	21,	'computers_printers_accumulated_depreciation_desc',	1,	NULL,	NULL),
                    (521501,	'Buildings Depreciation Expense',	NULL,	NULL,	NULL,	'detail_type',	5,	'buildings_depreciation_expense_desc',	1,	NULL,	NULL),
                    (521502,	'Equipment Depreciation Expense',	NULL,	NULL,	NULL,	'detail_type',	5,	'equipment_depreciation_expense_desc',	1,	NULL,	NULL),
                    (521503,	'Computers Printers Depreciation Expense',	NULL,	NULL,	NULL,	'detail_type',	5,	'computers_printers_depreciation_expense_desc',	1,	NULL,	NULL);";

        \Illuminate\Support\Facades\DB::statement($sql);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_account_types');
    }
}
