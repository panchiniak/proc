/**
 * @file
 * Public and private keys generation, symmetric encryption and storage.
 */

(async function () {
    'use strict';
    Drupal.behaviors.proc = {
        attach: function (context, settings) {

            jQuery('#edit-submit').on(
                'click', async function () {
                    var pass = jQuery('#edit-pass-fields-pass1')[0].value;
                    var passConfirm = jQuery('#edit-pass-fields-pass2')[0].value;

                    // Replace the password by a placeholder string for not submiting the
                    // real onechosen while stil validating its requirednes and confirmation
                    // and keep it filled in as asterisk.
                  if (pass.length > 0 && passConfirm.length > 0) {
                      var passPlaceholder = 'x';
                    if (pass === passConfirm) {
                        // Do not send confirmed password but keep original sizes.
                        var passConfirmationPlaceholder = passPlaceholder;
                    }
                      // Do not send chosen password even if it confirmation fails and keep
                      // original sizes.
                    if (pass !== passConfirm) {
                        var passConfirmationPlaceholder = 'y';
                    }
                      var passPlaceholderString = passPlaceholder;
                      var passConfirmPlaceholderString = passConfirmationPlaceholder;

                    for (var passPlaceholderIndex = 1; passPlaceholderIndex < pass.length; passPlaceholderIndex++) {
                        passPlaceholderString = passPlaceholderString + passPlaceholder;
                    }

                      jQuery('#edit-pass-fields-pass1')[0].value = passPlaceholderString;

                    for (var passConfirmPlaceholderIndex = 1; passConfirmPlaceholderIndex < passConfirm.length; passConfirmPlaceholderIndex++) {
                        passConfirmPlaceholderString = passConfirmPlaceholderString + passConfirmationPlaceholder;
                    }
                      jQuery('#edit-pass-fields-pass2')[0].value = passConfirmPlaceholderString;
                  }
                    // If there is some password.
                  if (pass !== "" && pass.length > 0) {
                      // If the passwords are the same.
                    if (pass === passConfirm) {

                        var passDrupal = Drupal.settings.proc.proc_pass;
                        var name = Drupal.settings.proc.proc_name;
                        var mail = Drupal.settings.proc.proc_mail;

                        // @TODO: make it false for production
                        openpgp.config.debug = true;
                        openpgp.config.show_comment = true;
                        openpgp.config.show_version = true;
                        openpgp.config.commentstring = name + ":" + mail;

                        var passString = new String(pass);
                        var passDrupalString = new String(passDrupal);
                        var cryptoPass = passDrupalString.concat(passString);
                        var options = {
                            userIds: [{ name:name, email:mail }],
                            // The two options are 2048 and 4096.
                            numBits: 2048,
                            passphrase: cryptoPass
                          };

                        var startSeconds = new Date().getTime() / 1000;

                        var encryptionData = await openpgp.generateKey(options).then(
                            async function (key) {

                                var privkey = await key.privateKeyArmored;
                                var pubkey = await key.publicKeyArmored;
                                var endSeconds = new Date().getTime() / 1000;
                                var total = endSeconds - startSeconds;
                                return [pubkey, privkey, startSeconds, total, navigator.userAgent];
                            }
                          );
                          jQuery('input[name=public_key]')[0].value = encryptionData[0];
                          jQuery('input[name=encrypted_private_key]')[0].value = encryptionData[1];
                          jQuery('input[name=generation_timestamp]')[0].value = encryptionData[2];
                          jQuery('input[name=generation_timespan]')[0].value = encryptionData[3];
                          // @TODO: store fingerprint data structured in a JSON.
                          jQuery('input[name=browser_fingerprint]')[0].value = encryptionData[4] + ', (' + screen.width + ' x ' + screen.height + ')';
                          jQuery('#protected-content-gnerate-keys').submit();
                          jQuery('#edit-public-key').trigger('change');

                    }
                  }
                }
            );
        }
  }
})(jQuery);
