jQuery(function ($) {
  function rtRecojoToggle() {
    var checked = $('#billing_recojo').is(':checked');
    ['nombre','apellido','dni'].forEach(function (key) {
      var $field = $('#billing_recojo_' + key + '_field');
      $field.toggle(checked);
      $field.find('input').prop('required', checked).attr('aria-required', checked ? 'true' : 'false');
    });
  }
  $(document.body).on('change', '#billing_recojo', rtRecojoToggle);
  $(document.body).on('updated_checkout', rtRecojoToggle);
  rtRecojoToggle();
});
