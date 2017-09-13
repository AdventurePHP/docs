$(document).ready(function () {
    var headerDe = $('#AboutHeaderDe');
    if (headerDe.length) {
        headerDe.css({'opacity': 0}).animate({opacity: 1}, 2000);
    }

    var headerEn = $('#AboutHeaderEn');
    if (headerEn.length) {
        headerEn.css({'opacity': 0}).animate({opacity: 1}, 2000);
    }
});