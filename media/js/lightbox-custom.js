$(document).ready(function () {
   $('.enlarge').lightBox({
      imageBtnClose : '/media/img/lightbox/lightbox-btn-close.gif',
      imageLoading :  '/media/img/lightbox/lightbox-ico-loading.gif',
      imageBtnPrev :  '/media/img/lightbox/lightbox-btn-prev.gif',
      imageBtnNext :  '/media/img/lightbox/lightbox-btn-next.gif',
      keyToClose : 'c',
      keyToPrev: 'p',
      keyToNext: 'n',
      fixedNavigation : true
   });
});