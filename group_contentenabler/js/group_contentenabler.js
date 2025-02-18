(function ($, Drupal) {
  Drupal.behaviors.group_contentenabler = {
    attach: function (context, settings) {
      // Get the select element
      $('#group-contentenabler-lga-switcher-settings select').on('change', function (e) {
        $("#group-contentenabler-lga-switcher-settings").submit();
      });
    },
  };
})(jQuery, Drupal);
