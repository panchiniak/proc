/**
 * @file
 * Decrypts cipher texts into a file given a correct privkey passphrase.
 */
(function($) {
  'use strict';
  Drupal.behaviors.proc = {
    attach: function(context, settings) {

      if (!(window.Blob)) {
        alert(Drupal.t('The File APIs are not fully supported in this browser.'));
      }

      let passDrupal = Drupal.settings.proc.proc_pass;
      let privkey = Drupal.settings.proc.proc_privkey;
      let cipherText = Drupal.settings.proc.proc_cipher;
      let sourceFileName = Drupal.settings.proc.proc_source_file_name;
      let sourceFileSize = Drupal.settings.proc.proc_source_file_size;

      // Do not submit the form at all.
      $('#-proc-decrypt-to-file').submit(function(event) {
        $('form#-proc-decrypt-to-file').prepend(Drupal.t('<div class="messages error">You can\'t and you don\'t need to submit this form. Instead just click or tap on "Get it" button.</div>'));
        return false;
      });

      // Reset messages
      $('input#edit-pass').once().on('focusin', function() {
        $('.messages').remove();
      });

      $('#decryption-link').on(
        'click', async function() {
          let secretPass = $('input[name=pass]')[0].value;
          let secretPassString = new String(secretPass);
          let passphrase = passDrupal.concat(secretPassString);
          const privKeyObj = (await openpgp.key.readArmored(privkey)).keys[0];

          await privKeyObj.decrypt(passphrase).catch(
            function(err) {
              // @TODO: save error log.
              $('form#-proc-decrypt-to-file').prepend('<div class="messages error">' + Drupal.t(err) + '</div>');

              if ($('a#decryption-link')[0].href) {
                const fileUrl = $('a#decryption-link')[0].href;
                URL.revokeObjectURL(fileUrl);
                $('a#decryption-link').removeAttr('href');
              }
            }
          );

          $('form#-proc-decrypt-to-file').prepend(Drupal.t('<div class="messages info">Indroducing key passphrase for decryption. Your browser may become unresponsive during this process. Please keep it open and wait...</div>'));

          const optionsDecription = {
            message: await openpgp.message.readArmored(cipherText),
            privateKeys: [privKeyObj],
            // For the sake of simplicity all files are considered binary.
            format: 'binary'
          };

          const decrypted = await openpgp.decrypt(optionsDecription);
          const plaintext = await openpgp.stream.readToEnd(decrypted.data);
          const blob = new Blob([plaintext], {
            type: 'application/octet-binary',
            endings: 'native'
          });

          const link = document.getElementById('decryption-link');
          link.href = URL.createObjectURL(blob);
          let saveActionString = Drupal.t('Save');
          if (link.text != saveActionString) {
            link.text = saveActionString;
          }

          // Check if file generated is the same size of source file.
          if (blob.size.toString() === sourceFileSize) {
            link.download = sourceFileName;

          } else {
            // @TODO: save error log.
            $('form#-proc-decrypt-to-file').prepend(Drupal.t('<div class="messages error">Error: size mismatch.</div>'));
          }
        }
      );
    }
  }
})(jQuery);
