<script type="text/javascript">
$(document).ready( function(){
    adding_accounts_dropdown();

    adding_locations_dropdown();
});
function adding_accounts_dropdown(selector = '')
{
    if(selector == '')
    {
        var menu = $("select.accounts-dropdown");
        var dropdownParent = $(document.body);
    }
    else
    {
        var menu = $(selector).find('select.accounts-dropdown');
        var dropdownParent = $(selector);
    }

    menu.select2({
        dropdownParent: dropdownParent,
        ajax: {
            url: '{{route("accounts-dropdown")}}',
            dataType: 'json',
            data: function(d){
                var q_data = '';
                var without_parent_ids_data = '';
                var parent_ids_data = '';
                var same_ids_data = '';
                if (
                    typeof  d.term !== 'undefined'
                ) {
                    q_data = d.term;
                }

                if (
                    typeof  $(this).attr('without_parent_ids') !== 'undefined'
                    &&  $(this).attr('without_parent_ids') !== false
                ) {
                    without_parent_ids_data = $(this).attr('without_parent_ids');
                }

                if (
                    typeof  $(this).attr('parent_ids') !== 'undefined'
                    &&  $(this).attr('parent_ids') !== false
                ) {
                    parent_ids_data = $(this).attr('parent_ids');
                }

                if (
                    typeof  $(this).attr('same_ids') !== 'undefined'
                    &&  $(this).attr('same_ids') !== false
                ) {
                    same_ids_data = $(this).attr('same_ids');
                }

                return {
                    q: q_data,
                    without_parent_ids: without_parent_ids_data,
                    parent_ids: parent_ids_data,
                    same_ids: same_ids_data,
                };

            },
            processResults: function (data) {
                return {
                    results: data
                }
            },
        },
        escapeMarkup: function(markup) {
            return markup;
        },
        templateResult: function(data) {
            return data.html;
        },
        templateSelection: function(data) {
            return data.text;
        }
    });
}

function adding_locations_dropdown(selector = '')
{
    if(selector == '')
    {
        var menu = $("select.locations-dropdown");
        var dropdownParent = $(document.body);
    }
    else
    {
        var menu = $(selector).find('select.locations-dropdown');
        var dropdownParent = $(selector);
    }

    menu.select2({
        dropdownParent: dropdownParent,
        ajax: {
            url: '{{route("locations-dropdown")}}',
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data
                }
            },
        },
        escapeMarkup: function(markup) {
            return markup;
        },
        templateResult: function(data) {
            return data.html;
        },
        templateSelection: function(data) {
            return data.text;
        }
    });
}
$(document).on('mouseover', '.select2-selection__rendered', function(){
    $(this).removeAttr('title');
});
$(document).on('shown.bs.modal', '.modal', function(){
    adding_accounts_dropdown(this);

    adding_locations_dropdown(this);
});
</script>