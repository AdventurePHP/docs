/**
 * Progressive enhancement language selector for the APF docs page.
 *
 * @author Janek Bevendorff
 */
$(document).ready(function () {

    var versionId = $('#Langswitch').data('version-id');
    var pageId = $('#Langswitch').data('page-id');

    $('#LanguageBox').append('<div id="Langswitch-PE">'
        + '<label for="LanguageBox-Selection">Current Language: </label>'
        + '<form method="post" action="/~/DOCS_biz-action/changeLang/"><div>'
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