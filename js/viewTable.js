$(document).ready(function(){
    function formResize(){
        $("body").height($(window).height() - ($("body").outerHeight(true) - $("body").height()));
        $("form").height($("body").height() - $("#heading").outerHeight(true) - $("footer").outerHeight(true) - ($("form").outerHeight(true) - $("form").height()));
        $("#table").height($("form").height() - $("#buttons").outerHeight(true) - ($("#table").outerHeight(true) - $("#table").height()));
    }
    $(window).resize(formResize);
    formResize();
});