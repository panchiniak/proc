/**
* @file
* Updates cipher texts.
*/
(function ($, Drupal, once) {
  'use strict';
  Drupal.behaviors.ProcBehavior = {
    attach: function (context, settings) {
      once('proc-update', 'html', context)
        .forEach(function (element) {
          
          let passDrupal       = drupalSettings.proc.proc_data.proc_pass,
              privkey          = drupalSettings.proc.proc_data.proc_privkey,
              cipherTexts      = drupalSettings.proc.proc_data.proc_ciphers,
              cipherTextsIndex = drupalSettings.proc.proc_ciphers_index,
              fileApiErrMsg    = drupalSettings.proc.proc_labels.proc_fileapi_err_msg,
              procJsLabels     = drupalSettings.proc.proc_labels;

          const introducingKeyDecryptionMsgElement = procJsLabels.proc_introducing_decryption;

          console.log(passDrupal);
          console.log(privkey);
          console.log(cipherTexts);
          console.log(fileApiErrMsg);
          console.log(procJsLabels);

          // console.log(cipherTextsIndex);
          // console.log(fileApiErrMsg);
          

        });
    }
  };
})(jQuery, Drupal, once);

