/**
 * @file
 * Public and private keys generation, symmetric encryption and storage.
 */

(function ($) {
    'use strict';
    Drupal.behaviors.proc = {
        attach: function (context, settings) {

            let procJsLabels = Drupal.settings.proc.proc_labels;

            function resetPassword() {
                $('#edit-pass-fields-pass1').val("");
                $('#edit-pass-fields-pass2').val("");
            }

            $('#edit-submit').on(
                'click', async function (e) {

                    e.preventDefault();

                    let pass = $('#edit-pass-fields-pass1')[0].value,
                    passConfirm = $('#edit-pass-fields-pass2')[0].value;

                    // Replace the password by a placeholder string for not submiting the
                    // real one chosen while stil validating its requirednes and
                    // confirmation and keep it filled in.
                    if (pass.length > 0 && passConfirm.length > 0) {
                        let passPlaceholder = 'x';
                        var passConfirmationPlaceholder;
                        if (pass === passConfirm) {
                            // Do not send confirmed password but keep original sizes.
                            passConfirmationPlaceholder = passPlaceholder;
                        }
                        // Do not send chosen password even if its confirmation fails and
                        // keep original sizes.
                        if (pass !== passConfirm) {
                            passConfirmationPlaceholder = 'y';
                        }

                        let passPlaceholderString = passPlaceholder,
                            passConfirmPlaceholderString = passConfirmationPlaceholder;

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
                        if (pass === passConfirm && $('.password-strength-text').text() === procJsLabels.proc_minimal_password_strenght) {

                            $('#edit-submit')[0].value = procJsLabels.proc_button_state_processing;

                            openpgp.config.debug         = false;
                            openpgp.config.show_comment  = true;
                            openpgp.config.show_version  = true;
                            openpgp.config.commentstring = `${Drupal.settings.proc.proc_name}:${Drupal.settings.proc.proc_mail}`;


                            let passDrupal       = Drupal.settings.proc.proc_pass,
                                passString       = new String(pass),
                                passDrupalString = new String(passDrupal),
                                cryptoPass       = passDrupalString.concat(passString),
                                startSeconds     = new Date().getTime() / 1000,
                                options          = {userIds: [{
                                                        name: Drupal.settings.proc.proc_name,
                                                        email: Drupal.settings.proc.proc_mail
                                                        }],
                                                        // The two options are 2048 and 4096.
                                                        numBits: 2048,
                                                        passphrase: cryptoPass
                                                    },
                                encryptionData   = await openpgp.generateKey(options).catch(
                                    // This error is possibly due to tampering atempt.
                                    function (err) {
                                        $('form#-proc-generate-keys').prepend(`<div class="messages error">${Drupal.t(err)}</div>`);
                                        // Reset password and action label.
                                        resetPassword();
                                        $('#edit-submit')[0].value = procJsLabels.proc_generate_keys_submit_label;
                                        return;
                                    }).then(
                                        async function (key) {
                                            let privkey    = await key.privateKeyArmored,
                                                pubkey     = await key.publicKeyArmored,
                                                endSeconds = new Date().getTime() / 1000,
                                                total      = endSeconds - startSeconds;

                                            return [pubkey, privkey, startSeconds, total, navigator.userAgent];
                                        }
                                    );

                            $('#edit-submit')[0].value                      = procJsLabels.proc_submit_saving_state;
                            $('input[name=public_key]')[0].value            = encryptionData[0];
                            $('input[name=encrypted_private_key]')[0].value = encryptionData[1];
                            $('input[name=generation_timestamp]')[0].value  = encryptionData[2];
                            $('input[name=generation_timespan]')[0].value   = encryptionData[3];
                            $('input[name=browser_fingerprint]')[0].value   = `${encryptionData[4]} , (${screen.width} x ${screen.height})`;

                            $('#-proc-generate-keys').submit();

                        }
                        else{
                            alert(procJsLabels.proc_password_match);
                            resetPassword();
                        }
                    }
                    else{
                        alert(procJsLabels.proc_password_required);
                    }
                }
            );
        }
    };
})(jQuery);
