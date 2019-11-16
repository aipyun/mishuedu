let $form = $("#member-upgrade-form");

$(".btn-buy-vip").on('click', function (e){
  e.preventDefault();
  $.post($form.data('tryBuyUrl'), resp => {
    if (resp === true) {
      $form.submit();
    } else {
      $('#modal').modal('show').html(resp);
    }
  });
})