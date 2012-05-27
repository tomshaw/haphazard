$(document).ready(function () {
  $('#submit').click(function () {        
    var action = $('#login-form').attr('action');
    var form = {
        email: $('#email').val()
      , password: $('#password').val()
      , token: $('#token').val()
    };
    $.ajax({
        type: 'POST'
      , url: action
      , data: form
      , success: function (response) {
        var result = JSON.parse(response);
        if (result.hasOwnProperty("success")) {
          window.location = action;
          return;
        } else {
          var errorTemplate = _.template($('#errorTemplate').text());
          var html = errorTemplate({'errors' : result.errors});
          $("#errorsReview").html(html);
        }
      }
    });
   return false;
  });
});