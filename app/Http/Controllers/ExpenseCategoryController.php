<?php

namespace App\Http\Controllers;

use App\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Modules\Accounting\Entities\AccountingAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ExpenseCategoryController extends Controller
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name', 'code', 'business_id', 'parent_id'
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! (auth()->user()->can('expense.add') || auth()->user()->can('expense.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $expense_category = ExpenseCategory::where('business_id', $business_id)
                ->select(['name', 'code', 'id', 'parent_id']);

            return Datatables::of($expense_category)
                ->addColumn(
                    'action',
                    '<button data-href="{{action(\'App\Http\Controllers\ExpenseCategoryController@edit\', [$id])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary btn-modal" data-container=".expense_category_modal"><i class="glyphicon glyphicon-edit"></i>  @lang("messages.edit")</button>
                        @if((new App\Utils\ModuleUtil)->getModuleData("MKamel_checkTreeAccountingDefined") == true)
                        <button data-href="{{action(\'App\Http\Controllers\ExpenseCategoryController@destroy\', [$id])}}" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error delete_expense_category"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                        @endif'
                )
                ->editColumn('name', function ($row) {
                    if (! empty($row->parent_id)) {
                        return '--'.$row->name;
                    } else {
                        return $row->name;
                    }
                })
                ->removeColumn('id')
                ->removeColumn('parent_id')
                ->rawColumns([2])
                ->make(false);
        }

        return view('expense_category.index');
    } 

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! (auth()->user()->can('expense.add') || auth()->user()->can('expense.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $categories = ExpenseCategory::where('business_id', $business_id)
            ->whereNull('parent_id')
            ->pluck('name', 'id');

        return view('expense_category.create')->with(compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */

     public function store(Request $request)
     {
         if (! auth()->user()->can('expense.add') && ! auth()->user()->can('expense.edit')) {
             abort(403, 'Unauthorized action.');
         }
     
         try {
             DB::beginTransaction();
     
             $business_id = $request->session()->get('user.business_id');
     
             $input = $request->only(['name']);
             $input['business_id'] = $business_id;
     
             if (! empty($request->input('add_as_sub_cat')) &&
                 $request->input('add_as_sub_cat') == 1 &&
                 ! empty($request->input('parent_id'))) {
                 $input['parent_id'] = $request->input('parent_id');
             }
     
             // Determine account_sub_type_id
             $sub_type_id = $request->input('account_sub_type_id');
     
             // Generate the next gl_code
             $last_gl_code = AccountingAccount::where('business_id', $business_id)
                 ->where('account_sub_type_id', $sub_type_id)
                 ->where('gl_code', 'like', $sub_type_id . '%')
                 ->orderBy('gl_code', 'desc')
                 ->value('gl_code');
     
             $next_number = 1;
             if ($last_gl_code) {
                 $last_number = (int)substr($last_gl_code, 2); // extract last 2 digits
                 $next_number = $last_number + 1;
             }
     
             $gl_code = $sub_type_id . str_pad($next_number, 2, '0', STR_PAD_LEFT);
     
             // Assign gl_code to category code
             $input['code'] = $gl_code;
     
             // Create Expense Category
             $expense_category = ExpenseCategory::create($input);
     
             // Create COA entry
             AccountingAccount::create([
                 'name' => $expense_category->name,
                 'business_id' => $business_id,
                 'account_primary_type' => 'expenses',
                 'account_sub_type_id' => $sub_type_id,
                 'detail_type_id' => 0, // optional or adjust if needed
                 'gl_code' => $gl_code,
                 'status' => 'active',
                 'created_by' => auth()->user()->id,
                 'link_table' => 'expense_categories',
                 'link_id' => $expense_category->id,
             ]);
     
             DB::commit();
     
             return [
                 'success' => true,
                 'msg' => __('expense.added_success'),
             ];
         } catch (\Exception $e) {
             DB::rollBack();
     
             Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
     
             return [
                 'success' => false,
                 'msg' => __('messages.something_went_wrong'),
             ];
         }
     }
     

    

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(ExpenseCategory $expenseCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!(auth()->user()->can('expense.add') || auth()->user()->can('expense.edit'))) {
            abort(403, 'Unauthorized action.');
        }
    
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $expense_category = ExpenseCategory::where('business_id', $business_id)->findOrFail($id);
    
            // Fetch COA account for this category
            $account = AccountingAccount::where('business_id', $business_id)
                ->where('link_table', 'expense_categories')
                ->where('link_id', $id)
                ->first();
    
            $expense_category->account_sub_type_id = $account->account_sub_type_id ?? 52;
    
            $categories = ExpenseCategory::where('business_id', $business_id)
                ->whereNull('parent_id')
                ->pluck('name', 'id');
    
            return view('expense_category.edit')->with(compact('expense_category', 'categories'));
        }
    }
    
    
    public function update(Request $request, $id)
    {
        if (!(auth()->user()->can('expense.add') || auth()->user()->can('expense.edit'))) {
            abort(403, 'Unauthorized action.');
        }
    
        if (request()->ajax()) {
            try {
                DB::beginTransaction();
    
                $business_id = $request->session()->get('user.business_id');
                $expense_category = ExpenseCategory::where('business_id', $business_id)->findOrFail($id);
    
                $name = $request->input('name');
                $sub_type_id = $request->input('account_sub_type_id');
    
                $expense_category->name = $name;
    
                if (!empty($request->input('add_as_sub_cat')) &&
                    $request->input('add_as_sub_cat') == 1 &&
                    !empty($request->input('parent_id'))) {
                    $expense_category->parent_id = $request->input('parent_id');
                } else {
                    $expense_category->parent_id = null;
                }
    
                // Fetch the linked COA account
                $coa = AccountingAccount::where('link_table', 'expense_categories')
                    ->where('link_id', $expense_category->id)
                    ->where('business_id', $business_id)
                    ->first();
    
                $new_gl_code = $coa->gl_code ?? null;
    
                if ($coa && $coa->account_sub_type_id != $sub_type_id) {
                    // Sub type changed: generate new gl_code
                    $last_gl_code = AccountingAccount::where('business_id', $business_id)
                        ->where('account_sub_type_id', $sub_type_id)
                        ->where('gl_code', 'like', $sub_type_id . '%')
                        ->orderBy('gl_code', 'desc')
                        ->value('gl_code');
    
                    $next_number = 1;
                    if ($last_gl_code) {
                        $last_number = (int)substr($last_gl_code, 2);
                        $next_number = $last_number + 1;
                    }
    
                    $new_gl_code = $sub_type_id . str_pad($next_number, 2, '0', STR_PAD_LEFT);
                }
    
                // Update category code
                $expense_category->code = $new_gl_code;
                $expense_category->save();
    
                if ($coa) {
                    $coa->update([
                        'name' => $name,
                        'account_sub_type_id' => $sub_type_id,
                        'gl_code' => $new_gl_code,
                    ]);
                }
    
                DB::commit();
    
                return ['success' => true, 'msg' => __('expense.updated_success')];
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::emergency("File: " . $e->getFile() . " Line: " . $e->getLine() . " Message: " . $e->getMessage());
    
                return ['success' => false, 'msg' => __('messages.something_went_wrong')];
            }
        }
    }
    

    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
{
    if (! (auth()->user()->can('expense.add') || auth()->user()->can('expense.edit'))) {
        abort(403, 'Unauthorized action.');
    }

    try {
        DB::beginTransaction();

        $business_id = request()->session()->get('user.business_id');

        // Soft delete the expense category
        $expense_category = ExpenseCategory::where('business_id', $business_id)->findOrFail($id);
        $expense_category->delete();

        // Soft delete the linked accounting account
        AccountingAccount::where('link_table', 'expense_categories')
            ->where('link_id', $id)
            ->where('business_id', $business_id)
            ->delete();

        DB::commit();

        return ['success' => true, 'msg' => __('expense.deleted_success')];
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());

        return ['success' => false, 'msg' => __('messages.something_went_wrong')];
    }
}


    public function getSubCategories(Request $request)
    {
        if (! empty($request->input('cat_id'))) {
            $category_id = $request->input('cat_id');
            $business_id = $request->session()->get('user.business_id');
            $sub_categories = ExpenseCategory::where('business_id', $business_id)
                ->where('parent_id', $category_id)
                ->select(['name', 'id'])
                ->get();
        }

        $html = '<option value="">'.__('lang_v1.none').'</option>';
        if (! empty($sub_categories)) {
            foreach ($sub_categories as $sub_category) {
                $html .= '<option value="'.$sub_category->id.'">'.$sub_category->name.'</option>';
            }
        }
        echo $html;
        exit;
    }
}
