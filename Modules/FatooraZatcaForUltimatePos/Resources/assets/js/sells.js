$(document).ready(function () {
    // Attach the DataTable event listener
    $('#sell_table').on('draw.dt', function () {        
        // Get all rows from the table body
        var rows = $('#sell_table').DataTable().rows('#sell_table tbody tr').data();

        // Loop through each row and log the data
        rows.each(function(data, index) {
            if(data.uuid === null) {
                return;
            }

            const row = $('#sell_table tbody tr').eq(index);
            const dropdownMenu = row.find('td').first().find('div.btn-group').find('ul.dropdown-menu');

            if(data.zatca === null) {                
                row.css('background-color', 'rgb(225 61 61)');
                dropdownMenu.prepend(
                    `
                    <li>
                        <a href="#" onclick="resendTransactionToZatca('${data.uuid}', ${index})">
                            <i class="fas fa-undo" aria-hidden="true"></i> ZATCA
                        </a>
                    </li>
                    `
                );
            }
            else {
                const decodedString = data.zatca.replace(/&quot;/g, '"')
                const jsonData = JSON.parse(decodedString)

                if(jsonData.validationResults.warningMessages.length) {
                    row.css('background-color', 'rgb(255 255 54)');
                    const warnings = jsonData.validationResults.warningMessages.map(item => item.message);
                    dropdownMenu.prepend(
                        `
                        <li>
                            <a href="#" onclick="showZatcaWarnings('${warnings}')">
                                <i class="fas fa-eye" aria-hidden="true"></i> ZATCA
                            </a>
                        </li>
                        `
                    );
                } 

                dropdownMenu.prepend(
                    `
                    <li>
                        <a href="#" onclick="downloadZatcaXml('${data.uuid}', '${data.invoice_no_text}')">
                            <i class="fas fa-download" aria-hidden="true"></i> XML
                        </a>
                    </li>
                    `
                );
            }
        });
    });
});

function resendTransactionToZatca(uuid, index) {    
    $.ajax({
        method: 'POST',
        url: zatcaResendUrl.replace('transaction_uuid', uuid),
        dataType: 'json',
        data: {},
        success: function(result) {
            if (result.success == true) {
                const row = $('#sell_table tbody tr').eq(index);
                const dropdownMenu = row.find('td').first().find('div.btn-group').find('ul.dropdown-menu');

                row.css('background-color', 'unset');
                dropdownMenu.find('li').first().remove();
                toastr.success(result.msg);
                
            } else {
                toastr.error(result.msg);
            }
        },
    });
}

function showZatcaWarnings(warningMessages) {
    alert(warningMessages)
}

function downloadZatcaXml(uuid, invoice_no) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', zatcaXmlUrl.replace('transaction_uuid', uuid), true);

    // Set the response type to 'blob'
    xhr.responseType = 'blob';

    xhr.onload = function() {
    if (xhr.status === 200) {
        console.log("Success: File is ready to download.");
        // Handle the blob response
        var blob = xhr.response;
        // Example: Create an object URL to view/download the blob
        var url = URL.createObjectURL(blob);
        var link = document.createElement('a');
        link.href = url;
        link.download = `${invoice_no}.xml`;  // specify the filename you want
        link.click();
    }
    };

    xhr.onerror = function() {
    console.error('An error occurred while making the request');
    };

    xhr.send();
}