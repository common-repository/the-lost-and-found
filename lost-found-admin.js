

function add_user(type) {
    // type = admins or email_people
    var select_object = document.getElementById("lfselect" + type);
    var index = select_object.selectedIndex;
    var selection = select_object.options[index].value;
    //put together the post data
    var data = {
        action: 'lost_found_add_user',
        selection: selection,
        type: type,
        security: nonce
    };
    document.getElementById("lf" + type).innerHTML = please_wait_image;


    jQuery.post(ajaxurl, data, function(response) {
        document.getElementById("lf" + type).innerHTML = response;
    });
}

function remove_user(type, key) {
    // type = admins or email_people
    //put together the post data
    var data = {
        action: 'lost_found_remove_user',
        selection: key,
        type: type,
        security: nonce
    };
    document.getElementById("lf" + type).innerHTML = please_wait_image;


    jQuery.post(ajaxurl, data, function(response) {
        document.getElementById("lf" + type).innerHTML = response;
    });

}

function add_all_user() {
    // allow any user with upload permissions to add an item
    var data = {
        action: 'lost_found_add_all_users',
        security: nonce,
        checked: 0
    }
    if (document.getElementById('lfallowallusers').checked) {
        data.checked = 1;
    }
    jQuery.post(ajaxurl, data, function(response) {
        document.getElementById("lfallusercheck").innerHTML = response;
    });
}

// Change the sort order in the options page
function sort_option() {

    var select_object = document.getElementById("lfsort_options");
    var index = select_object.selectedIndex;
    var lf_sort_option = select_object.options[index].value;

    var data = {
        action: 'lost_found_sort_order',
        selection: lf_sort_option,
        security: nonce
    };
    document.getElementById("lf_cur_sort").innerHTML = please_wait_image;


    jQuery.post(ajaxurl, data, function(response) {
        document.getElementById("lf_cur_sort").innerHTML = response;
    });

}

// Change the date format in the options page
function submit_date() {

    var lf_date_format = document.getElementById("lf_date").value;

    var data = {
        action: 'lost_found_date_format',
        selection: lf_date_format,
        security: nonce
    };
    document.getElementById("lf_date_example").innerHTML = please_wait_image;


    jQuery.post(ajaxurl, data, function(response) {
        document.getElementById("lf_date_example").innerHTML = response;
    });

}

// reset to default date format
function reset_date() {


    var lf_date_format = "m/d/Y";

    var data = {
        action: 'lost_found_date_format',
        selection: lf_date_format,
        security: nonce
    };
    document.getElementById("lf_date").value = "m/d/Y";
    document.getElementById("lf_date_example").innerHTML = please_wait_image;


    jQuery.post(ajaxurl, data, function(response) {
        document.getElementById("lf_date_example").innerHTML = response;
    });

}

