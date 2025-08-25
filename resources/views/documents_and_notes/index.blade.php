<div class="table-responsive">
    @if(in_array('create', $permissions))
        <div class="pull-right">
            <button 
                type="button" 
                class="docs_and_notes_btn tw-inline-flex tw-transition-all hover:tw-text-white tw-cursor-pointer tw-duration-50 tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 hover:tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-700 tw-py-2 tw-px-4 tw-rounded-full tw-items-center tw-justify-center tw-text-sm tw-font-medium tw-text-white tw-gap-1" 
                data-href="{{action([\App\Http\Controllers\DocumentAndNoteController::class, 'create'], ['notable_id' => $notable_id, 'notable_type' => $notable_type])}}"
            >
                @lang('messages.add')&nbsp;
                <i class="fa fa-plus"></i>
            </button> 
        </div> <br><br>
    @endif
    <table 
        class="table table-striped" 
        style="width: 100%; border-radius:0.5rem !important;overflow: hidden;" 
        id="documents_and_notes_table"
    >
        <thead class="tw-text-white tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800">
            <tr>
                <th>@lang('messages.action')</th>
                <th>@lang('lang_v1.heading')</th>
                <th>@lang('lang_v1.added_by')</th>
                <th>@lang('lang_v1.created_at')</th>
                <th>@lang('lang_v1.updated_at')</th>
            </tr>
        </thead>
    </table>
</div>
<div class="modal fade docus_note_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>