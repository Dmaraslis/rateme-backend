$(function() {
    $('#sortMe').sortable({
        update: function(event , ui) {
          var postData = $(this).sortable('serialize');

          $.post('savepriority.php', {list: postData}, function(o) { 
          }, 'json');
      window.location.reload();}
      });
  });

$(function() {
    $('#sortMe2').sortable({
        update: function(event , ui) {
          var postData = $(this).sortable('serialize');

          $.post('savepriority2.php', {list: postData}, function(o) { 
            console.log(o);
          }, 'json');
       window.location.reload();}
      });
    $('#sortMe2').disableSelection();
  });

$(function() {
    $('#sortMe3').sortable({
        update: function(event , ui) {
          var postData = $(this).sortable('serialize');
          console.log(postData);
          $.post('savepriority3.php', {list: postData}, function(o) { 
            console.log(o);
          }, 'json');
        window.location.reload();}
      });
    $('#sortMe3').disableSelection();
  });

$(function() {
    $('#sortMe4').sortable({
        update: function(event , ui) {
          var postData = $(this).sortable('serialize');
          console.log(postData);
          $.post('savepriority4.php', {list: postData}, function(o) { 
            console.log(o);
          }, 'json');
        window.location.reload();}
      });
    $('#sortMe4').disableSelection();
  });


