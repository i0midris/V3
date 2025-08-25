<div class="{{$class ?? ''}} tw-mb-4 tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md hover:tw-translate-y-0.5 tw-ring-gray-200"
    @if (!empty($id)) id="{{ $id }}" @endif>
    <div class="tw-p-2 sm:tw-p-3">
        @if (empty($header))
            @if (!empty($title) || !empty($tool))
                <!-- <div class="box-header"> -->
                <div class="tw-flex tw-justify-between tw-items-center tw-px-4">
                    {!! $icon ?? '' !!}
                    <h4 class="box-title" style="color:black">{{ $title ?? '' }}</h4>
                    

                    
                    {!! $tool ?? '' !!}
                </div>
                @if (isset($help_text))
                    <div class="tw-text-sm text-muted tw-mt-2 tw-px-4">
                        <small>{!! $help_text !!}</small>
                    </div>
                @endif
            @endif
        @else
            <div class="box-header">
                {!! $header !!}
            </div>
        @endif
        <div class="tw-flow-root tw-mt-4 tw-border-gray-200">
            <div class="">
                <div class="tw-py-2 tw-align-middle sm:tw-px-5">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
