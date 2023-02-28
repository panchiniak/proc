/**
 * @file
 * Decrypts cipher texts into a file given a correct privkey passphrase.
 */
 (function ($, Drupal, once) {
    'use strict';
    Drupal.behaviors.ProcBehavior = {
        attach: function (context, settings) {
          once('proc-decrypt', 'html', context).forEach(function (element) {
            console.log('----------------');
            
            let procJsLabels      = drupalSettings.proc.proc_labels;
            
            console.log(procJsLabels);
            // ,
            //     passDrupal        = Drupal.settings.proc.proc_pass,
            //     privkey           = Drupal.settings.proc.proc_privkey,
            //     cipherIds         = Drupal.settings.proc.proc_ids,
            //     ciphersChanged    = Drupal.settings.proc.procs_changed,
            //     sourcesFileNames  = Drupal.settings.proc.proc_sources_file_names,
            //     sourcesFilesSizes = Drupal.settings.proc.proc_sources_file_sizes,
            //     sourcesInputModes = Drupal.settings.proc.proc_sources_input_modes,
            //     fileApiErrMsg     = Drupal.settings.proc.proc_fileapi_err_msg,
            //     ciphersSigned     = Drupal.settings.proc.proc_signed,
            //     skipSizeMismatch  = Drupal.settings.proc.proc_skip_size_mismatch;
            
            
            
          });
          
          
          


    }
  };
})(jQuery, Drupal, once);
