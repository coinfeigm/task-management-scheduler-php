$(document).ready(function () {
  $(document).click(function (event) {
      var clickover = $("#foo");
      var clicked = event.target;

      if (clickover.find(clicked).length == 0 && clickover.hasClass("show")) {
         clickover.collapse('hide').fadeOut();
         clickover.children().find('div.collapse.show').collapse('hide');
      } 
  });

});

$('#notifBtn').click(function(){
  if(!$("#foo").hasClass('show')) {
    $("#foo").collapse('show').fadeIn(700);
  }
});

$("body").on("mouseenter", "#list-tab", function() {
  $(this).children().find('div.pull-right').removeClass("hide");
});

$("body").on("mouseleave", "#list-tab", function() {
  $(this).children().find('div.pull-right').addClass("hide");
});