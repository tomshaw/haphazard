$(function() {    
  $.getJSON("/admin/users/post", function(resp) {
    renderForm(resp);
  }); 
});

function renderForm(resp) {
  var createTemplate = _.template($('#createTemplate').text());
  var html = createTemplate({'data' : resp});
  $("#createForm").html(html);
  
  $('#submit').click(function (e) {
    e.preventDefault();
    var action = $('#account-form').attr('action');
    var form = {
        name: $('#name').val()
	  , email: $('#email').val()
	  , password: $('#password').val()
	  , username: $('#username').val()
	  , newsletter: $('#newsletter').is(':checked') ? 1 : 0
	  , token: $('#token').val()
	};
	$.ajax({
	    type: 'POST'
	  , url: action
	  , data: form
	  , success: function (resp) {
	    var result = JSON.parse(resp);
	    if (result.hasOwnProperty("success")) {
	      var successTemplate = _.template($('#successTemplate').text());
	      var html = successTemplate({'success' : result.success});
	      $("#successReview").html(html);
	    } else {
	      var errorTemplate = _.template($('#errorTemplate').text());
	      var html = errorTemplate({'errors' : result.errors});
	      $("#errorsReview").html(html);
	    }
	  }
	});
    return false;
  });
};