/**
 * @file
 * Decrypts cipher texts into a file given a correct privkey passphrase.
 */
 (function ($, Drupal, once) {
    'use strict';
    Drupal.behaviors.ProcBehavior = {
        attach: function (context, settings) {
          once('proc-decrypt', 'html', context).forEach(function (element) {
            
            let procJsLabels      = drupalSettings.proc.proc_labels,
                passDrupal        = drupalSettings.proc.proc_pass,
                privkey           = drupalSettings.proc.proc_privkey,
                cipherIds         = drupalSettings.proc.proc_ids,
                ciphersChanged    = drupalSettings.proc.procs_changed,
                sourcesFileNames  = drupalSettings.proc.proc_sources_file_names,
                sourcesFilesSizes = drupalSettings.proc.proc_sources_file_sizes,
                sourcesInputModes = drupalSettings.proc.proc_sources_input_modes,
                fileApiErrMsg     = drupalSettings.proc.proc_fileapi_err_msg,
                ciphersSigned     = drupalSettings.proc.proc_signed,
                skipSizeMismatch  = drupalSettings.proc.proc_skip_size_mismatch;

            
            // ,
            //     passDrupal        = drupalSettings.proc.proc_pass,
            //     privkey           = drupalSettings.proc.proc_privkey,
            //     cipherIds         = drupalSettings.proc.proc_ids,
            //     ciphersChanged    = drupalSettings.proc.procs_changed,
            //     sourcesFileNames  = drupalSettings.proc.proc_sources_file_names,
            //     sourcesFilesSizes = drupalSettings.proc.proc_sources_file_sizes,
            //     sourcesInputModes = drupalSettings.proc.proc_sources_input_modes,
            //     fileApiErrMsg     = drupalSettings.proc.proc_fileapi_err_msg,
            //     ciphersSigned     = drupalSettings.proc.proc_signed,
            //     skipSizeMismatch  = drupalSettings.proc.proc_skip_size_mismatch;
            
            
            
          });
          
          
          


    }
  };
})(jQuery, Drupal, once);
