$(document).ready(function() {
    $(document).delegate('.preview', 'click', function() {
        var url = $(this).data('url');
        $('#previewFrame').attr('src', url).promise().done(function() {
            $("#previewFrame").ready(function() {
                $('#previewFrame').dialog({
                    autoOpen: true,
                    width: 870,
                    modal: true,
                    resizable: false
                }).promise().done(function() {
                    $('.ui-dialog-titlebar').remove();
                });
            })
        });
    });
    $("body").on("click", ".ui-widget-overlay", function() {
        $('#previewFrame').attr('src', '/clientportal/css/images/ajax-loader.gif');
        $('#previewFrame').dialog("close");
    });
    $(document).delegate(".export", 'click', function(event) {
        exportTableToCSV.apply(this, [$('#report_datatable'), 'export.csv']);
        $(this).blur();
    });
    $(document).delegate('#offer_active_level', 'change', function() {
        updateDashboard();
    })
    $(document).delegate('.close_detail', 'click', function() {
        $('#offer_details').hide();
        $('#offer_full_list').show();
    })
    $(document).delegate('#report_datatable tbody tr', 'click', function() {
        //$('#report_datatable tbody tr').removeClass('selected_tr').promise().done(function(){
        //    $(this).addClass('selected_tr');
        //})
        //var offer_id=$(this).find('td:first').text();
        ////getOfferStats(offer_id);
        //getOfferDetails(offer_id);
    });
    updateDashboard();
});

var updateDashboard = function() {
    var advertiser_id = $('#advertiser_id').data('advertiser_id');
    var status = $('#offer_active_level').val();
    var url = WEB_ROOT + 'home/ajax/do/getAdvertiserOffers/advertiser_id/' + advertiser_id + '/status/' + status;
    $.ajax({
        url: url,
        success: function(data) {
            if (data.status == 'error') {
                $('.export').hide();
                showError(data.error);
                $('#report_datatable').empty().removeClass('table_updating');
            } else {
                $('.export').show().data('csv', data.csv);
                $('#report_datatable').removeClass('table_updating');
                var new_table = $("<table><thead><tr></tr></thead><tbody></tbody></table>");
                $(data.columns).each(function(idx, item) {
                    $(new_table).find('thead tr').append($("<th/>").append(item.title).addClass(item.class));
                }).promise().done(function() {
                    $(data.data).each(function(rowIndex, row) {
                        var tr = $("<tr/>");
                        $(row).each(function(columnIndex, column) {
                            $(tr).append($("<td/>").append(column.value).addClass(column.class));
                        });
                        $(new_table).find('tbody').append($(tr));
                    }).promise().done(function() {
                        $('#report_datatable').closest('.replaceInto').html($(new_table)).promise().done(function() {
                            $(new_table).attr('id', 'report_datatable').dataTable({
                                autoWidth: false
                            });
                        });
                    });
                });
            }
        },
        dataType: "json"
    });
}

var getOfferDetails = function(offerId) {
    $.ajax({
        url: WEB_ROOT + 'home/ajax/do/getOfferDetails/offer_id/' + offerId + '/advertiser_id/' + $('#advertiser_id').data('advertiser_id'),
        success: function(data) {
            $(data.data).each(function(idx, item) {
                $('#offer_details').find('.' + item.class).html(item.value);
                $('#offer_details').show();
                $('#offer_full_list').hide();
            })
        },
        dataType: "json"
    });
}