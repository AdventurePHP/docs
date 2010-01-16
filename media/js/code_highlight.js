/**
 * Adjustment of the code boxes.
 */
$(document).ready(function(){
   $('.listing code').each(function() {
       if(parseInt($(this).css('height')) < '100') {
           $(this).css({'background-image' : 'none' });
       }
   });
});