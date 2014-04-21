/**
 * Extend sh_highlightDocument() to highlight our code blocks instead of pre elements.
 * Be sure to have jQuery included before calling this method!
*/
sh_highlightDocument = function(prefix, suffix) {
    $('.listing').each(function() {
        var htmlClasses = sh_getClasses(this);
        var codeElement = $(this).children('code')[0];
        
        for (var i = 0; i < htmlClasses.length; ++i) {
            var htmlClass = htmlClasses[i].toLowerCase();
            
            if (htmlClass === 'sh_sourcecode' || htmlClass === 'listing') {
                continue;
            }
            
            if (htmlClass in sh_languages) {
                sh_highlightElement(codeElement, sh_languages[htmlClass]);
            } else if (typeof(prefix) === 'string' && typeof(suffix) === 'string') {
                sh_load(htmlClass, codeElement, prefix, suffix);
            } else {
                // Don't throw an exception as the original code does, otherwise
                // further code blocks will not be highlighted.
                // Write to Firebug console instead if available.
                if (typeof console != 'undefined') {
                    console.warn('Found code listing element with class="' + htmlClass + '", but no such language exists');
                }
            }
            
            break;
        }
    });
};