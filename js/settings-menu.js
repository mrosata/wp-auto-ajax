/**
 * Created by michael on 4/18/15.
 */


jQuery(function ($) {
  // When one of the checkboxes is hit, toggle that sections disabled inputs
  var radios = $('input.auto-ajax-lvl');
  
  if (radios.length) {
    radios.on('click', function (evt) {
      // Toggle the disabled form fields
      if (!evt.target || !evt.target.value)
        return;
      var disableLvl = (evt.target.value === 'advanced') ?
        'basic' : 'advanced';

      // Disable inputs not meant for newly selected setting level
      $('input[data-setting-lvl]').each(function (inp, thing) {
        var settingLvl = $(this).data('setting-lvl');
        $(this).attr('disabled', settingLvl === disableLvl);
      });

    });

    $('form#autoAjaxSettings').submit(function(evt) {
      // Undisable the radios on submit (or else their values don't get saved
      // which is annoying for anyone debugging their settings).
      $('input[data-setting-lvl]').each(function (inp, thing) {
        $(this).attr('disabled', false);
      });
    })
  }

});
