/**
 * Progressive enhancement version selector for the APF docs page.
 *
 * @author Christian Achatz
 */
$(document).ready(function () {

    var lang = $('#Versionswitch').data('lang');
    var pageId = $('#Versionswitch').data('page-id');

    var label = $('#Versionswitch span').text();
    var buttonLabel = $('#Versionswitch').data('button-label');

    $('#VersionBox').append('<div id="Versionswitch-PE">'
        + '<form method="post" action="/~/APF_sites_apf_biz-action/changeLang/"><div>'
        + '<label for="Versionswitch-Selection">' + label + '</label>'
        + '<select id="Versionswitch-Selection" name="version-id"></select>'
        + '<input type="hidden" value="' + lang + '" name="lang"/>'
        + '<input type="hidden" value="' + pageId + '" name="page-id"/>'
        + '<input type="submit" value="' + buttonLabel + '" name="changeLang"/>'
        + '</div></form></div>');

    $('#Versionswitch li a').each(function () {
        var versionIdDisplayName = $(this).data('version-id');
        var versionId = $(this).data('version-id');
        var current = $(this).hasClass('current') ? 'selected="selected" ' : '';
        $('#Versionswitch-PE select').append('<option ' + current + 'value="' + versionId + '">' + versionIdDisplayName + '</option>');
    });

    $('#Versionswitch').css({
        'display': 'none'
    });

});