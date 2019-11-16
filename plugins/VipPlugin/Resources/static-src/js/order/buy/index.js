import 'moment';
let $form = $('#member-buy-form');
let validator = $form.validate({
  rules: {
    num: {
      required: true,
      number: true,
      digits: true,
      min: 1,
      max: 999
    }
  },
  messages: {
    num: {
      required: '请输入开通时长',
      digits: '开通时长必须为整数',
	          max: '开通时长不能大于999'
    }
  }
});

$form.find('[name=level]').on('change', function() {
  refresh();
});
$form.find('[name=targetId]').on('change', function() {
  refresh_price();
});

$form.find('[name=unit]').on('change', function() {
  if ($form.find('[name=unit]:checked').val() == 'month') {
    let $defaultBuyMonth = $('[name=defaultBuyMonth]').val();
    $form.find('[name=num]').val($defaultBuyMonth);
  } else {
    let $defaultBuyYear = $('[name=defaultBuyYear]').val();
    $form.find('[name=num]').val($defaultBuyYear);
  }
  refresh();
});

$form.find('[name=num]').on('change', function() {
  refresh();
});

$('#member-order-confirm-btn').on('click', function() {
  $form[0].submit();
});

refresh();
refresh_price();

function refresh_price() {
  let $form = $('#member-buy-form');
  let target = $form.find('[name=targetId]:checked').val() || $form.find('[name=targetId]').val();
  let prices = $form.find('.js-vip-price').data('vipPrice');
  let coinDisplay = $form.find('.js-vip-price').data('coinDisplay');
  let cashRate = $form.find('.js-vip-price').data('cashRate');
  let coinName = $form.find('.js-vip-price').data('coinName');
  if(coinDisplay) {
    $form.find('[name=unit]').each(function(){
      let price = '<span class="text-primary">(' + prices[target][$(this).val()] * cashRate + ' ' + coinName + ')</span>';
      $(this).parent().find('span').remove();
      $(this).parent().append(price);
    });
  } else {
    $form.find('[name=unit]').each(function(){
      let price = '<span class="text-primary">(' + prices[target][$(this).val()] + ' 元)</span>';
      $(this).parent().find('span').remove();
      $(this).parent().append(price);
    });
  }
}

function refresh() {
  let $form = $('#member-buy-form');

  let unit = $form.find('[name=unit]:checked').val();
  let duration = $form.find('[name=num]').val();

  $form.find('.unit-label').hide();
  $form.find('.unit-label-' + unit).show();

  let startDate = $form.find('[name=startDate]').val();
  let deadline = '';
  if (startDate) {
    deadline = moment(startDate);
  } else {
    deadline = moment();
  }

  deadline = deadline.add(unit + 's', duration).format('YYYY-MM-DD');

  $form.find('.deadline').html(deadline).parent().removeClass('hide');
  refresh_price();
}

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
