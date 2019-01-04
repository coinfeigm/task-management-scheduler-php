$('main').on({
  mouseenter: function () {
    //trIndex = $(this).index()+1;
    trIndex = $("tr", $(this).closest("table")).index(this)
    $("#trainingtbl_wrapper").each(function (index) {
      $("#trainingtbl_wrapper .dataTables_scrollBody .dataTable").each(function (index) {
        $(this).find("tr:eq(" + trIndex + ")").each(function (index) {
          $(this).find("td").addClass("hover");
        });
      });

      $("#trainingtbl_wrapper .DTFC_LeftBodyLiner .dataTable").each(function (index) {
        $(this).find("tr:eq(" + trIndex + ")").each(function (index) {
          $(this).find("td").addClass("hover");
        });
      });
    });
  },
  mouseleave: function () {
    //trIndex = $(this).index()+1;
    trIndex = $("tr", $(this).closest("table")).index(this)
    $("#trainingtbl_wrapper").each(function (index) {
      $("#trainingtbl_wrapper .dataTables_scrollBody .dataTable").each(function (index) {
        $(this).find("tr:eq(" + trIndex + ")").each(function (index) {
          $(this).find("td").removeClass("hover");
        });
      });

      $("#trainingtbl_wrapper .DTFC_LeftBodyLiner .dataTable").each(function (index) {
        $(this).find("tr:eq(" + trIndex + ")").each(function (index) {
          $(this).find("td").removeClass("hover");
        });
      });
    });
  }
}, ".dataTables_wrapper tr");

$(function () {
  $("#sidebar").mCustomScrollbar({
    theme: "minimal"
  });

  $('body').on('click', '.dismissSidebar, .overlay', function () {
    $('#sidebar').removeClass('active');
    $('.overlay').fadeOut();
  });

  $('body').on('click', '.sidebarCollapse', function () {
    $('#sidebar').addClass('active');
    $('.overlay').fadeIn();
    $('a[aria-expanded=true]').attr('aria-expanded', 'false');
  });

  $('body').on('click', '.dismissMemberSidebar, .overlay', function () {
    $('#memberSidebar').removeClass('active');
    $('.overlay').fadeOut();
  });

  $('body').on('click', '.memberSidebarCollapse', function () {
    $('#memberSidebar').addClass('active');
    $('.overlay').fadeIn();
    $('a[aria-expanded=true]').attr('aria-expanded', 'false');
  });
});