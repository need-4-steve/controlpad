// capitalize first letter of string
function ucfirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

// radio buttons
$("input[type='radio'] + label").click(function() {
    $("input[type='radio'] + label").removeClass("active").
    $(this).addClass("active");
});


$(document).ready(function() {

    // initialize bootstrap-select plugin
    $('.selectpicker').selectpicker();

    // check the checkboxes of all rows in a table and enable/disable action button
    $("body").on('click', "thead input[type='checkbox'], #apply_to_all", function() {
        if ($(this).prop("checked") == false) {
            $("tbody td:first-child input[type='checkbox'], .apply_to_all input[type='checkbox']").each(function() {
                $(this).prop("checked", false);
            });
            $('.applyAction').attr('disabled', 'disabled');
        }
        else {
            $("tbody td:first-child input[type='checkbox'], .apply_to_all input[type='checkbox']").each(function() {
                $(this).prop("checked", true);
            });
            $('.applyAction').removeAttr('disabled');
        }
    });
    $(".bulk-check").click(function() {
        var checked = false;
        $(".bulk-check").each(function() {
            if ($(this).prop("checked") == true) checked = true;
        });
        if (checked == false) {
            $('.applyAction').attr('disabled', 'disabled');
        }
        else {
            $('.applyAction').removeAttr('disabled');
        }
    });

    // change method of index form
    $('select.actions').change(function() {
       $('form').attr('action', $(this).val());
    });

    // Ace code editor
    var editor;
    $('.editor').each(function( index ) {
        editor = ace.edit(this);
        // editor.setTheme("ace/theme/Chrome");
        editor.getSession().setMode("ace/mode/html");
    });

    // jQUery UI
    var today = new Date();
    var firstYear = today.getFullYear() - 18;
    $('.datepicker').datetimepicker({
        controlType: 'select',
        timeFormat: 'hh:mm tt'
    });
    $('.dateonlypicker').datepicker({
        controlType: 'select',
        changeMonth: true,
        changeYear: true,
        yearRange: '1900:' + firstYear,
        dateFormat: 'yy-mm-dd',
        //timeFormat: 'hh:mm tt'
    });

    // highlight active page in main-menu
    path = path.split('/');
    $('#main-menu a').each(function() {
        if ($(this)[0].hasAttribute('href') && $(this).attr('href') !== 'javascript:void(0)') href = $(this).attr('href').split('/');
        else if ($(this)[0].hasAttribute('data-href')) href = $(this).attr('data-href').split('/');
        if (href[1] == path[0]) {
            $(this).addClass('active');
        }
    });

    // initialize bootstrap popovers
    $("[data-toggle='popover']").popover({html:true, trigger:'click'});

    // delete label
    $('.form-group .label .fa-times').click(function() {
       $(this).parent().parent().remove();
    });

    // close sidebar menu popovers when clicking outside
    $('[data-toggle="popover"]').popover();
    $('body').on('click', function (e) {
        $('[data-toggle="popover"]').each(function () {
            //the 'is' for buttons that trigger popups
            //the 'has' for icons within a button that triggers a popup
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                   $(this).popover('hide');
            }
        });
    });

/********************
 * Phones
 ********************/
// add phone
$('#add-phone').click( function() {
    addPhone();
});
function addPhone() {

    // append a phone widget
    if ($('#phone-list > .list-group-item').length > 0) {
        phone_count = $('#phone-list > .list-group-item').length;
    }
    else phone_count = 0;
    $('#phone-list').append('' +
        '<li class="list-group-item spacing-top">' +
            '<i class="lnr lnr-cross pull-right removePhone x"></i>' +
            '<div class="clear"></div>' +
            '<div class="form-group">' +
                '<label>Number</label>' +
                '<input class="form-control" type="text" name="phones[' + phone_count + '][number]">' +
            '</div>' +
            '<div class="form-group">' +
                '<label>Type</label>' +
                '<select name="phones[' + phone_count + '][type]" class="form-control">' +
                    '<option>Mobile</option>' +
                    '<option>Land-line</option>' +
                '</select>' +
            '</div>' +
            '<input type="hidden" name="phones[' + phone_count + '][new]" value="1">' +
        '</li>' +
    '');
    $('#phone-list .list-group-item:last-child').hide().slideDown();
    $('#phone-list select').on('change', function() {
        var name = $(this).attr('name');
        if ($(this).val() == 'Other') {
            $(this).after('<div class="margin-top-2 other-' + name + '"><input type="text" class="form-control" name="' + name + '" placeholder="Specify"></div>');
        }
        else {
            $('+.other-' + name, this).remove();
        }
    });
};

// remove phone (remove phone from display)
function removePhone(element, parent) {
    $(element).parents(parent).slideUp(function() {
        $(this).remove();
    });
}

$('body').on('click', '.removePhone', function() {
    element = $(this);
    parent = $(this).attr('data-parent');
    if (typeof parent === 'undefined') parent = '.list-group-item';
    removePhone(element, parent);
});

// delete phone (delete saved phone)
$('body').on('click', '.deletePhone', function() {
    element = $(this);
    parent = $(this).attr('data-parent');
    $.get('/phones/' + $(this).attr('data-phone-id') + '/delete');
    if (typeof parent === 'undefined') parent = '.list-group-item';
    removePhone(element, parent);
});


/*******************************
 * Tabs
 *******************************/
$('.nav-tabs a').click(function() {
    $('.nav-tabs .active').removeClass('active');
    $(this).parent().addClass('active');
    tab = $(this).attr('href');
    $('.tab-content.active').removeClass('active').hide();
    $('.tab-content' + tab).addClass('active').show();
});

});
function getThisMonth(){
    var d = new Date();
    var month = new Array();
    month[0] = "January";
    month[1] = "February";
    month[2] = "March";
    month[3] = "April";
    month[4] = "May";
    month[5] = "June";
    month[6] = "July";
    month[7] = "August";
    month[8] = "September";
    month[9] = "October";
    month[10] = "November";
    month[11] = "December";
    var n = month[d.getMonth()];
    return n;
}

// clean URL
function cleanURL(text) {
    text = text.toLowerCase();
    text = text.replace(/ a /g, "-");
    text = text.replace(/ an /g, "-");
    text = text.replace(/ it /g, "-");
    text = text.replace(/ the /g, "-");
    text = text.replace(/\ and /g, "-");
    text = text.replace(/\ /g, "-");
    text = text.replace(/\,/g, "-");
    text = text.replace(/\./g, "-");
    text = text.replace(/\&/g, "-");
    text = text.replace(/\?/g, "-");
    text = text.replace(/\!/g, "-");
    text = text.replace(/\@/g, "-");
    text = text.replace(/\#/g, "-");
    text = text.replace(/\$/g, "-");
    text = text.replace(/\%/g, "-");
    text = text.replace(/\^/g, "-");
    text = text.replace(/\*/g, "-");
    text = text.replace(/\(/g, "-");
    text = text.replace(/\)/g, "-");
    text = text.replace(/\+/g, "-");
    text = text.replace(/\=/g, "-");
    text = text.replace(/\~/g, "-");
    text = text.replace(/\`/g, "-");
    text = text.replace(/\:/g, "-");
    text = text.replace(/\;/g, "-");
    text = text.replace(/\'/g, "-");
    text = text.replace(/\"/g, "-");
    text = text.replace(/\[/g, "-");
    text = text.replace(/\{/g, "-");
    text = text.replace(/\]/g, "-");
    text = text.replace(/\}/g, "-");
    text = text.replace(/\\/g, "-");
    text = text.replace(/\|/g, "-");
    text = text.replace(/\</g, "-");
    text = text.replace(/\>/g, "-");
    text = text.replace(/\--/g, "");
    text = text.replace(/\__/g, "");
    text = text.replace(/\_-/g, "");
    text = text.replace(/\-_/g, "");
    return text;
}
