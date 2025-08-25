<div class="pos-tab-content">
    <div class="row">
        <div class="col-sm-12 text-center">

            {{-- 🔔 Flash message from session --}}
            @if(session('update_status'))
                <div id="updateResult"
                     class="alert alert-{{ session('update_status.type') }} tw-text-left tw-mt-4"
                     style="white-space: pre-line;">
                    {!! session('update_status.msg') !!}
                </div>
            @endif

            {{-- 🔧 Update UI --}}
            <div class="form-group tw-my-6">
                <h3 class="tw-text-2xl tw-font-bold mb-4">🛠️ @lang('business.system_update')</h3>
                <p class="tw-text-base tw-text-gray-600 mb-3">
                    @lang('business.system_update_description')
                </p>

                <button type="button"
                        id="whatsNewBtn"
                        class="btn btn-outline-primary btn-sm tw-mt-2"
                        style="font-size: 18px;">
                    🆕 @lang('business.whats_new')
                </button>

                <br><br>

                <button type="button"
                        id="runSystemUpdate"
                        class="btn btn-danger btn-lg tw-px-5">
                    🔄 @lang('business.run_system_update')
                    <span id="loadingSpinner"
                          class="spinner-border spinner-border-sm tw-ml-2 tw-hidden"
                          role="status"
                          aria-hidden="true"></span>
                </button>
            </div>

            {{-- 🔁 Result placeholder (AJAX messages) --}}
            <div id="updateAjaxResult"
                 class="alert tw-hidden tw-mt-4 tw-text-left alert-info"
                 style="white-space: pre-line;"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {

        // 🔄 Run system update
        $('#runSystemUpdate').click(function () {
            Swal.fire({
                title: '{{ __("business.are_you_sure") }}',
                text: '{{ __("business.update_warning") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ __("business.confirm_update") }}',
                cancelButtonText: '{{ __("messages.cancel") }}',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const $btn = $('#runSystemUpdate');
                    $btn.prop('disabled', true);
                    $('#loadingSpinner').removeClass('tw-hidden');

                    Swal.fire({
                        title: '{{ __("business.updating") }}',
                        html: '{{ __("business.please_wait_update") }}',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    $.ajax({
                        method: 'POST',
                        url: '{{ route("system.update") }}',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (res) {
                            Swal.close();
                            $('#updateAjaxResult')
                                .removeClass('tw-hidden alert-danger alert-info alert-success')
                                .addClass(res.status === 'success' ? 'alert-success' : 'alert-danger')
                                .html(res.message);

                            toastr.success(res.message);

                            // Optional: auto-scroll
                            $('html, body').animate({
                                scrollTop: $('#updateAjaxResult').offset().top - 100
                            }, 500);
                        },
                        error: function (xhr) {
                            Swal.close();

                            const message = xhr.responseJSON?.message || '{{ __("business.update_failed") }}';

                            $('#updateAjaxResult')
                                .removeClass('tw-hidden alert-info alert-success')
                                .addClass('alert-danger')
                                .html("❌ " + message);

                            toastr.error(message);
                        },
                        complete: function () {
                            $btn.prop('disabled', false);
                            $('#loadingSpinner').addClass('tw-hidden');
                        }
                    });
                }
            });
        });

        // 🆕 View changelog
        $('#whatsNewBtn').click(function () {
            Swal.fire({
                title: '🆕 {{ __("business.whats_new") }}',
                html: '<div id="whatsNewContent">Loading...</div>',
                width: '60%',
                showCloseButton: true,
                showConfirmButton: false,
                didOpen: () => {
                    fetch('{{ route("system.whats_new") }}')
                        .then(res => res.text())
                        .then(html => $('#whatsNewContent').html(html))
                        .catch(() => $('#whatsNewContent').html('<p>Unable to load changelog.</p>'));
                }
            });
        });
    });
</script>
@endpush
