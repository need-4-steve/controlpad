// bulk action checkboxes
function checkbox() {
    var checked = false;
    $('.bulk-check').each(function() {
        if ($(this).is(":checked"))
            checked = true;
    });
    if (checked == true) $('.applyAction').removeAttr('disabled');
    else $('.applyAction').attr('disabled', 'disabled');

};

//find any variables in the current url
function getUrlVariables(variable)
{
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i=0;i<vars.length;i++) {
        var pair = vars[i].split("=");
        if(pair[0] == variable){return pair[1];}
    }
    return(false);
}

String.prototype.capitalize = function(){
     return this.toLowerCase().replace( /\b\w/g, function (m) {
         return m.toUpperCase();
     });
 };

 //puts cursor to end of pre-filled input
function moveCursorToEnd(input) {
    "use strict";
    var index = input.value.length;
    input.focus();
    input.setSelectionRange(index, index);
}
