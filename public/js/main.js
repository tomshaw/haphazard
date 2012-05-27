/**
 * Dynamic submit buttons.
 */
!function ($) {
  $(function () {
    $('#submit').click(function () {
      var btn = $(this);
      btn.button('loading');
      setTimeout(function () {
	    btn.button('reset');
	  }, 3000);
	});
  }); 
}(window.jQuery);