var isStoppedImport = false;
function amFinderRunImportFile(url, progress)
{
    progress = progress || 0;
    jQuery(modalAlertImport).find('#am_finder_popup_progress').width(progress + '%');

    //jQuery('#am_finder_popup').show();
    isStoppedImport = false;


    var onSuccessCallBack = function(response) {
        var data = response;
        /*if (!data || !data.isJSON()) {
            alert('System error: ' + data);
            window.location.reload();
        }*/
        //data = data.evalJSON();
        jQuery(modalAlertImport).find('#am_finder_popup_log').append('<li>' + data.message + '</li>', {position: 'content'});

        jQuery(modalAlertImport).find('#am_finder_popup_progress').width(data.progress + '%');

        if(data.isCompleted) {
            jQuery(modalAlertImport).find("#importSpinner").hide();
        }

        if (!data.isCompleted && !isStoppedImport) {
            setTimeout(function() { amFinderRequestImport(url, onSuccessCallBack); }, 1000);
        }

    };

    amFinderRequestImport(url, onSuccessCallBack);
    return false;
}

function amFinderRequestImport(url, onSuccessCallBack)
{
    if(isStoppedImport) {
        return;
    }
    jQuery.getJSON(url, onSuccessCallBack);
}

function amFinderCloseImportPopUp()
{
    //jQuery('#am_finder_popup').hide();
    isStoppedImport = true;
    jQuery(modalAlertImport).find('#am_finder_popup_log').html('');
    jQuery(modalAlertImport).find('#am_finder_popup_progress').width('0%');
    amasty_finder_finder_import_log_gridJsObject.reload();
    amasty_finder_finder_import_history_gridJsObject.reload();
    amasty_finder_finder_products_gridJsObject.reload();
    return false;
}