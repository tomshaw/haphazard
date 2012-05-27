$(function() {    
  $.getJSON("/admin/users/getitems", function(resp) {
    renderGrid(resp.paginator);
    renderPaginator(resp.properties);
  }); 
});

function renderGrid(names) {
  var customerTemplate = _.template($('#customerTemplate').text());
  var html = customerTemplate({'customerList' : names});
  $("#datagrid").html(html);
  $('a#inline').click(function(e) {
    e.preventDefault();
    var fadeColor = '#FB6C6C'
      , parent = $(this).closest('tr')
      , id = $(this).closest('tr').attr('id').replace('record-','');
    $.ajax({
        type: 'GET'
      , url: '/admin/users/delete/id/' + id
      , beforeSend: function() {
        parent.animate({'backgroundColor': fadeColor}, 750);
      },
      success: function() {
        parent.slideUp(750, function() {
          parent.remove();
	    });
	  }
    });
  });
};

function renderForm(resp) {
  var editTemplate = _.template($('#editTemplate').text());
  var html = editTemplate({'data' : resp});
  $("#editform").html(html);
  $('#submit').click(function (e) {
    e.preventDefault();
    var btn = $(this);
    btn.button('loading');
    setTimeout(function () {
	    btn.button('reset');
	}, 3000);
    var action = $('#account-form').attr('action');
    var form = {
        id: $('#id').val()
	  , name: $('#name').val()
	  , email: $('#email').val()
	  , password: $('#password').val()
	  , username: $('#username').val()
	  , newsletter: $('#newsletter').is(':checked') ? true : ''
	  , token: $('#token').val()
	};
	$.ajax({
	    type: 'PUT'
	  , url: action
	  , data: form
	  , success: function (response) {
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
	  }
	});
    return false;
  });
}

function renderPaginator(data) {
    
	var paginatorTemplate = _.template($('#paginatorTemplate').text());
    
    var html = paginatorTemplate({'data' : data});
    
    $("#paginator").html(html);
    
    $('li a[data-href]').click(function (e) {
    	var href = $(this).attr("data-href");
        $.getJSON(href, function(resp) {
            renderGrid(resp.paginator);
            renderPaginator(resp.properties);
        }); 
    	e.preventDefault();
    });
    
    var selector = 'tbody tr[data-href]'
      , namespace = 'click.table-row.data-api'
      , checkboxes = '#checkall'
      , checknamespace = 'click.checkall.data-api'
      , Row = function (element) {
          this.input($(element).on(namespace, this.resource));
        };

      Row.prototype = {
          constructor: Row,
          resource: function () {
        	$("#jumping").hide();
        	$("#names").hide();
          	var href = $(this).attr("data-href");
            $.getJSON(href, function(resp) {
                renderForm(resp);
            });
          },
          input: function (element) {
              $(element).find('input').on('mouseenter', this.enter);
              $(element).find('input').on('mouseleave', this.leave);
              $(element).find('#inline').on('click', this.enter);
          },
          enter: function (e) {
              $(this).parents('tr').unbind(namespace);
          },
          leave: function (e) {
              $(this).attr('data-href');
          },
          checkall: function () {
              $(this).parents('table:eq(0)').find(':checkbox').attr('checked', this.checked);
          }
      };

      $.fn.gridrow = function (option) {
          return this.each(function () {
              var $this = $(this),
                  data = $this.data('row');
              if (!data) $this.data('row', (data = new Row(this)));
              if (typeof option == 'string') data[option].call($this);
          });
      };

      $.fn.gridrow.Constructor = Row;

      $(function () {
          $('body').on(checknamespace, checkboxes, Row.prototype.checkall);
          $(selector).each(function () {
              var $this = $(this);
              if ($this.data('gridrow')) return;
              $this.gridrow($this.data());
          });
      });
}

