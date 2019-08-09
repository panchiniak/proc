/**
 * @file
 * Public and private keys generation, symmetric encryption and storage.
 */

(function ($) {
    'use strict';
    Drupal.behaviors.proc = {
        attach: function (context, settings) {

            $('#edit-submit').on(
                'click', async function (e) {

                    e.preventDefault();

                    let pass = $('#edit-pass-fields-pass1')[0].value;
                    let passConfirm = $('#edit-pass-fields-pass2')[0].value;

                    // Replace the password by a placeholder string for not submiting the
                    // real one chosen while stil validating its requirednes and
                    // confirmation and keep it filled in.
                    if (pass.length > 0 && passConfirm.length > 0) {
                        let passPlaceholder = 'x';
                        if (pass === passConfirm) {
                            // Do not send confirmed password but keep original sizes.
                            var passConfirmationPlaceholder = passPlaceholder;
                        }
                        // Do not send chosen password even if its confirmation fails and
                        // keep original sizes.
                        if (pass !== passConfirm) {
                            var passConfirmationPlaceholder = 'y';
                        }
                        let passPlaceholderString = passPlaceholder;
                        let passConfirmPlaceholderString = passConfirmationPlaceholder;

                        for (let passPlaceholderIndex = 1; passPlaceholderIndex < pass.length; passPlaceholderIndex++) {
                            passPlaceholderString = passPlaceholderString + passPlaceholder;
                        }

                        $('#edit-pass-fields-pass1')[0].value = passPlaceholderString;

                        for (let passConfirmPlaceholderIndex = 1; passConfirmPlaceholderIndex < passConfirm.length; passConfirmPlaceholderIndex++) {
                            passConfirmPlaceholderString = passConfirmPlaceholderString + passConfirmationPlaceholder;
                        }
                        $('#edit-pass-fields-pass2')[0].value = passConfirmPlaceholderString;
                    }
                    // If there is some password.
                    if (pass !== "" && pass.length > 0) {
                        // If the passwords are the same.
                        if (pass === passConfirm && $('.password-strength-text').text() === Drupal.t('Strong')) {

                            $('#edit-submit')[0].value = Drupal.t('Processing...');

                            let passDrupal = Drupal.settings.proc.proc_pass;
                            let name = Drupal.settings.proc.proc_name;
                            let mail = Drupal.settings.proc.proc_mail;

                            openpgp.config.debug = false;
                            openpgp.config.show_comment = true;
                            openpgp.config.show_version = true;
                            openpgp.config.commentstring = name + ":" + mail;

                            let passString = new String(pass);
                            let passDrupalString = new String(passDrupal);
                            let cryptoPass = passDrupalString.concat(passString);
                            let options = {
                                userIds: [{
                                    name: name,
                                    email: mail
                                }],
                                // The two options are 2048 and 4096.
                                numBits: 2048,
                                passphrase: cryptoPass
                            };

                            let startSeconds = new Date().getTime() / 1000;

                            let encryptionData = await openpgp.generateKey(options).then(
                                async function (key) {

                                    $('#edit-submit')[0].value = Drupal.t('Saving...');
                                    let privkey = await key.privateKeyArmored;
                                    let pubkey = await key.publicKeyArmored;
                                    let endSeconds = new Date().getTime() / 1000;
                                    let total = endSeconds - startSeconds;
                                    return [pubkey, privkey, startSeconds, total, navigator.userAgent];
                                }
                            );
                            $('input[name=public_key]')[0].value = encryptionData[0];
                            $('input[name=encrypted_private_key]')[0].value = encryptionData[1];
                            $('input[name=generation_timestamp]')[0].value = encryptionData[2];
                            $('input[name=generation_timespan]')[0].value = encryptionData[3];
                            // @TODO: store fingerprint data structured in a JSON.
                            $('input[name=browser_fingerprint]')[0].value = encryptionData[4] + ', (' + screen.width + ' x ' + screen.height + ')';
                            $('#-proc-generate-keys').submit();

                        }
                        else{
                            // @TODO: use a message instead of alert().
                            alert(Drupal.t('You must type in both password fields the same strong password.'));
                            $('#edit-pass-fields-pass1').val("");
                            $('#edit-pass-fields-pass2').val("");
                        }
                    }
                    else{
                        // @TODO: use a message instead of alert().
                        alert(Drupal.t('Password is required.'));
                    }
                }
            );
        }
    }
})(jQuery);
