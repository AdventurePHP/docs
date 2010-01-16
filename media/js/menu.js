/**
 * Removes the left border with active navigation nodes.
 */
$(document).ready(function(){
   $('#Navigation span').parent().prev().children('a').css({'border-right' : 'none'});
});