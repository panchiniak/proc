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
            ciphersSigned     = drupalSettings.proc.proc_signed,
            skipSizeMismatch  = drupalSettings.proc.proc_skip_size_mismatch;
            
        let procURLs = [];
        
        async function procOpenFile(decrypted, temporaryDownloadLink, cipherIndex) {
          const plaintext = decrypted.data,
            blob = new Blob([plaintext], {type: 'application/octet-binary',endings: 'native'}),
            link = $('#decryption-link');
          temporaryDownloadLink.setAttribute( 'href', URL.createObjectURL(blob));
          let openActionLabel = procJsLabels.proc_open_file_state;
          if (link.text() != openActionLabel) {
            link.text(openActionLabel);
            // Highlight the link for better UX
            link.removeClass('active');
            $('.messages').after(`<div class="messages status" id="proc-decrypting-status">${procJsLabels.proc_decryption_success}</div>`);
          }
          // Check if file generated is the same size of source file.
          if (blob.size.toString() === sourcesFilesSizes[cipherIndex] || skipSizeMismatch == true) {
            // Restore original file name:
            temporaryDownloadLink.setAttribute( 'download', sourcesFileNames[cipherIndex]);
            temporaryDownloadLink.click();
          } else {
            // @TODO: save error log.
            $('form#-proc-decrypt-to-file').prepend(`<div class="messages error">${procJsLabels.proc_decryption_size_mismatch}</div>`);
          }
        }
        
        
        
        
        
        
      });
    }
  };
})(jQuery, Drupal, once);
