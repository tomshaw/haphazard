$(document).ready(function () {
  $('#submit').click(function (e) {
    e.preventDefault();
    var action = $('#account-form').attr('action');
    var obj = new Object();
    obj.name = $('#name').val();
    obj.email = $('#email').val();
    obj.password = $('#password').val();
    obj.username = $('#username').val();
    obj.newsletter = $('#newsletter').is(':checked') ? true : '';
    obj.token = $('#token').val();
    $.ajax({
      type: 'PUT',
      url: action,
      data: obj,
      contentType: "application/json; charset=utf-8",
      success: function (response) {
        var result = JSON.parse(response);
        if (result.hasOwnProperty("success")) {
            var successTemplate = _.template($('#successTemplate').text());
            var html = successTemplate({'success' : result.success});
            $("#successReview").html(html);
        } else {
          var errorTemplate = _.template($('#errorTemplate').text());
          var html = errorTemplate({'errors' : result.errors});
          $("#errorsReview").html(html);
        }
      }, 
      error: function (xhr, ajaxOptions, thrownError) {
        var errorTemplate = _.template($('#errorTemplate').text());
    	var errors = JSON.parse(xhr.responseText);
        var html = errorTemplate({'errors' : errors.errors});
        $("#errorsReview").html(html);
      }
    });
   return false;
  });
});