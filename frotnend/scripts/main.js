$(document).ready(function() {
    setInterval(function() {
        $('.form-control').css('border-color', '#00FFFF').animate({
            'border-color': '#8A2BE2'
        }, 1000).animate({
            'border-color': '#00FFFF'
        }, 1000);
    }, 2000);
});
