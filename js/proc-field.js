/**
 * @file
 * Helper for entity reference proc field.
 */
(function ($, Drupal, once) {
  'use strict';
  Drupal.behaviors.ProcBehavior = {
    attach: function (context, settings) {
      once('proc-decrypt', 'html', context)
        .forEach(function (element) {

        });
    }
  };
})(jQuery, Drupal, once);