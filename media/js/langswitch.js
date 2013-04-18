/**
 * Progressive enhancement language selector for the APF docs page.
 *
 * @author Janek Bevendorff
 */
$(document).ready(function () {

    var pageIdMatch = /\/\w+\/(\d{3})\-/.exec($('#Langswitch li a').attr('href'));
    var pageId = '';
    if (pageIdMatch !== null) {
        pageId = pageIdMatch[1];
    }

    var versionIdMatch = /\/Version\/([A-Za-z0-9\.]{3})/.exec($('#Langswitch li a').attr('href'));
    var versionId = '';
    if (versionIdMatch !== null) {
        versionId = versionIdMatch[1];
    }

    $('#LanguageBox').append('<div id="Langswitch-PE">'
        + '<label for="LanguageBox-Selection">Current Language: </label>'
        + '<form method="post" action="/~/APF_sites_apf_biz-action/changeLang/"><div>'
        + '<select id="LanguageBox-Selection" name="lang"></select>'
        + '<input type="hidden" value="' + pageId + '" name="page-id"/>'
        + '<input type="hidden" value="' + versionId + '" name="version-id"/>'
        + '<input type="submit" value="Change" name="changeLanguage"/>'
        + '</div></form></div>');

    $('#Langswitch li a').each(function () {
        var current = ($(this).attr('class').split(' ')[0] == 'current') ? 'selected="selected" ' : '';
        var langCode = $(this).attr('class').split(' ').slice(-1);
        var lang = $(this).text();
        $('#Langswitch-PE select').append('<option ' + current + 'value="' + langCode + '">' + lang + '</option>');
    });

    $('#Langswitch').css({
        'display': 'none'
    });

});