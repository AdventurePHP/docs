/**
 * Removes the left border with active navigation nodes.
 */
$(document).ready(function(){
   $('#Navigation li a.selected').parent().prev().children('a').css({'border-right' : 'none'});
});