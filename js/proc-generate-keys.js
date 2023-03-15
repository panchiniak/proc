/**
 * @file
 * Protected Content key generation.
 */
(function ($, Drupal, once) {
  'use strict';
  Drupal.behaviors.ProcBehavior = {
    attach: function (context, settings) {
      once('proc-generate-keys', 'html', context)
        .forEach(function (element) {
          let procJsLabels = drupalSettings.proc.proc_labels,
            procData = drupalSettings.proc.proc_data,
            procKeySize = drupalSettings.proc.proc_key_size;

          function resetPassword() {
            $('#edit-password-confirm-pass1')
              .val("");
            $('#edit-password-confirm-pass2')
              .val("");
          }

          function validatePassword(pass, passConfirm, strenght) {
            let error_message = 0;
            if (pass.length === 0) {
              error_message = procJsLabels.proc_password_required;
              return error_message;
            }
            if (pass != passConfirm) {
              error_message = procJsLabels.proc_password_match;
              return error_message;
            }
            if ($('.password-strength__text')
              .text() != procJsLabels.proc_minimal_password_strenght) {
              error_message = procJsLabels.proc_pass_weak;
              return error_message;
            }
            return error_message;
          }
          $('#edit-submit')
            .on('click', async function (e) {
              e.preventDefault();
              let pass = $('#edit-password-confirm-pass1')[0].value,
                passConfirm = $('#edit-password-confirm-pass2')[0].value;
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
                $('#edit-password-confirm-pass1')[0].value = passPlaceholderString;
                for (let passConfirmPlaceholderIndex = 1; passConfirmPlaceholderIndex < passConfirm.length; passConfirmPlaceholderIndex++) {
                  passConfirmPlaceholderString = passConfirmPlaceholderString + passConfirmationPlaceholder;
                }
                $('#edit-password-confirm-pass2')[0].value = passConfirmPlaceholderString;
              }
              let password_error = validatePassword(pass, passConfirm, procJsLabels.proc_minimal_password_strenght);
              // If there is some password.
              if (!password_error) {
                // Switch label of Submit button:
                $('#edit-submit')[0]['value'] = procJsLabels.proc_button_state_processing;
                // Configure OpenPGP.js
                // @todo: bring the values from static property in DrupalProc class
                openpgp.config.useIndutnyElliptic = false;
                openpgp.config.showComment = true;
                openpgp.config.showVersion = true;
                openpgp.config.commentString = `${procData.proc_name}:${procData.proc_email}`;
                let passDrupal = procData.proc_pass,
                  passString = pass,
                  passDrupalString = passDrupal,
                  cryptoPass = passDrupalString.concat(passString),
                  startSeconds = new Date()
                  .getTime() / 1000;
                const {
                  privateKey,
                  publicKey,
                  revocationCertificate
                } = await openpgp.generateKey({
                    userIDs: [{
                      name: procData.proc_name,
                      email: procData.proc_email
                    }],
                    type: 'rsa',
                    passphrase: cryptoPass,
                    rsaBits: procKeySize,
                    format: 'armored',
                  })
                  .catch(
                    // This error is possibly due to tampering atempt.
                    function (err) {
                      $('form#-proc-generate-keys')
                        .prepend(`<div class="messages error">${Drupal.t(err)}</div>`);
                      // Reset password and action label.
                      resetPassword();
                      $('#edit-submit')[0].value = procJsLabels.proc_generate_keys_submit_label;
                      return;
                    });
                let endSeconds = new Date()
                  .getTime() / 1000;
                let total = endSeconds - startSeconds;
                $('#edit-submit')[0].value = procJsLabels.proc_submit_saving_state;
                $('input[name=public_key]')[0].value = publicKey;
                $('input[name=encrypted_private_key]')[0].value = privateKey;
                $('input[name=generation_timestamp]')[0].value = endSeconds;
                $('input[name=generation_timespan]')[0].value = total;
                $('input[name=browser_fingerprint]')[0].value = `${navigator.userAgent} , (${screen.width} x ${screen.height})`,
                  $('input[name=proc_email]')[0].value = procData.proc_email;
                $('#proc-keys-generation-form')
                  .submit();
              } else {
                alert(password_error);
                resetPassword();
              }
            });
        });
    }
  };
})(jQuery, Drupal, once);