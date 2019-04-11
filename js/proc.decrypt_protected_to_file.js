/**
 * @file
 * Decrypts cipher texts into a file given a correct privkey passphrase.
 */

(async function () {
    'use strict';
    Drupal.behaviors.proc = {
        attach: function (context, settings) {

            // @TODO: replace var by let whenever possible
            var passDrupal = Drupal.settings.proc.proc_pass;
            var privkey = Drupal.settings.proc.proc_privkey;
            var cipherText = Drupal.settings.proc.proc_cipher;
            var sourceFileName = Drupal.settings.proc.proc_source_file_name;
            var sourceFileSize = Drupal.settings.proc.proc_source_file_size;

            jQuery('#decryption-link').on(
                'click', async function () {

                    var secretPass = jQuery('input[name=pass]')[0].value;

                    var secretPassString = new String(secretPass);
                    var passphrase = passDrupal.concat(secretPassString);
                    const privKeyObj = (await openpgp.key.readArmored(privkey)).keys[0];

                    await privKeyObj.decrypt(passphrase).catch(
                        function (err) {
                            // @TODO: rephrase openpgpjs default error messages to allow for translations.
                            // @TODO: add to error log
                            jQuery("form#-proc-decrypt-to-file").prepend('<div class="messages error">' + err + '</div>');
                            if (jQuery("a#decryption-link")[0].href){
                                const fileUrl = jQuery("a#decryption-link")[0].href;
                                URL.revokeObjectURL(fileUrl);
                                jQuery("a#decryption-link").removeAttr("href");
                            }
                        }
                    );

                    const optionsDecription = {
                        message: await openpgp.message.readArmored(cipherText),
                        privateKeys: [privKeyObj],
                        // For the sake of simplicity all files are considered binary.
                        format: 'binary'
                    };

                    const decrypted = await openpgp.decrypt(optionsDecription);
                    const plaintext = await openpgp.stream.readToEnd(decrypted.data);
                    const blob = new Blob([ plaintext ], { type: 'application/octet-binary', endings:'native' });
                    const objectURL = URL.createObjectURL(blob);
                    const link = document.getElementById('decryption-link');
                    link.href = objectURL;
                    link.href = URL.createObjectURL(blob);
                    
                    // Check if file generated is the same size of source file.
                    if (blob.size.toString() === sourceFileSize) {
                        link.download = sourceFileName;
                    }
                    else {
                        // @TODO: use t
                        // @TODO: add to error log
                        jQuery("form#-proc-decrypt-to-file").prepend('<div class="messages error">Error: size mismatch.</div>');
                    }
                }
            );

        }
    }
})(jQuery);
