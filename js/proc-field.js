/**
 * @file
 * Decrypts cipher texts into a file given a correct privkey passphrase.
 */
(function ($, Drupal, once) {
  'use strict';
  Drupal.behaviors.ProcBehavior = {
    attach: function (context, settings) {
      once('proc-decrypt', 'html', context)
        .forEach(function (element) {
          console.log('---------------');
          
        });
    }
  };
})(jQuery, Drupal, once);