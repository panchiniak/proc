/**
 * @file
 * Decrypts cipher texts into a file given a correct privkey passphrase.
 */
(function ($) {
    'use strict';
    Drupal.behaviors.proc = {
        attach: function (context, settings) {

            let procJsLabels   = Drupal.settings.proc.proc_labels,
                passDrupal     = Drupal.settings.proc.proc_pass,
                privkey        = Drupal.settings.proc.proc_privkey,
                cipherId       = Drupal.settings.proc.proc_id,
                cipherChanged  = Drupal.settings.proc.proc_changed,
                sourceFileName = Drupal.settings.proc.proc_source_file_name,
                sourceFileSize = Drupal.settings.proc.proc_source_file_size,
                fileApiErrMsg  = Drupal.settings.proc.proc_fileapi_err_msg,
                localCihper    = localStorage.getItem(`proc.proc_id.${cipherId}.${cipherChanged}`);


            var cipherText;

            if (localCihper){
                cipherText = localCihper;
            }
            else{
                let storageItems = Object.keys(localStorage);
                if (storageItems.length > 0) {
                    storageItems.forEach(
                        function (storageItemKey,storageItemIndex){
                            if (storageItemKey.startsWith(`proc.proc_id.${cipherId}`)){
                                localStorage.removeItem(storageItems[storageItemIndex]);
                            }
                        }
                    );
                }
                const cipherTextAjax = async (cipherId) => {
                    let response = await fetch(`${window.location.origin + Drupal.settings.basePath}proc/api/get/${cipherId}`),
                        json     = await response.json();
                    cipherText = json.cipher;
                    try{
                        localStorage.setItem(`proc.proc_id.${cipherId}.${cipherChanged}`, json.cipher);
                    }
                    catch (error){
                        console.warn(error);
                    }
                };
                cipherTextAjax(cipherId);
            }


            const introducingKeyDecryptionMsgElement = `<div class="messages info proc-info" id="proc-decrypting-info">${procJsLabels.proc_introducing_decryption}</div>`;

            if (!(window.Blob)) {
                alert(fileApiErrMsg);
            }

            // Reset messages and action.
            $('input#edit-pass').once().on(
                'focusin', function () {
                    $('.messages').remove();
                    this.value = '';
                    let actionLink = $('#decryption-link');
                    if (!(actionLink.hasClass('active'))) {
                        actionLink.text('Decrypt').removeClass('active').removeAttr('download').removeAttr('href');
                    }
                }
            );

            // Never submit the form:
            $('#-proc-decrypt-to-file').submit(
                function (e) {
                    e.preventDefault();
                    // Click decrypt instead:
                    $('#decryption-link').click();
                }
            );

            $('#decryption-link').on(
                'click', async function () {
                    let secretPass       = $('input[name=pass]')[0].value,
                        secretPassString = secretPass,
                        passphrase       = passDrupal.concat(secretPassString);

                    const privKeyObj = (await openpgp.key.readArmored(privkey)).keys[0];

                    await privKeyObj.decrypt(passphrase).catch(
                        function (err) {
                            $('form#-proc-decrypt-to-file').prepend(`<div class="messages error">${Drupal.t(err)}</div>`);
                            if ($('a#decryption-link')[0].href) {
                                const fileUrl = $('a#decryption-link')[0].href;
                                URL.revokeObjectURL(fileUrl);
                                $('a#decryption-link').removeAttr('href');
                            }
                        }
                    );

                    if (!$('#proc-decrypting-info')[0]) {
                        $('form#-proc-decrypt-to-file').prepend(introducingKeyDecryptionMsgElement);
                    }

                    const
                        optionsDecription = {
                            message: await openpgp.message.readArmored(cipherText).catch(
                                function (err) {
                                    let messageError = `<div class="messages error">${Drupal.t(err)}</div>`;
                                    $('form#-proc-decrypt-to-file').prepend(messageError);
                                }
                            ),
                            privateKeys: [privKeyObj],
                            // For the sake of simplicity all files are considered binary.
                            format: 'binary'
                        },
                        decrypted         = await openpgp.decrypt(optionsDecription).catch(
                            function (err) {
                                let messageError = `<div class="messages error">${Drupal.t(err)}</div>`;
                                $('form#-proc-decrypt-to-file').prepend(messageError);
                                $(":focus").blur();
                                if ($('.messages.info.proc-info:first')){
                                    $('.messages.info.proc-info:first').remove();
                                }
                            }
                        );

                    if (decrypted){
                        const
                            plaintext = await openpgp.stream.readToEnd(decrypted.data),
                            blob = new Blob(
                                [plaintext], {
                                    type: 'application/octet-binary',
                                    endings: 'native'
                                }
                            ),
                            link = $('#decryption-link');

                        link.attr('href', URL.createObjectURL(blob));
                        let openActionLabel = procJsLabels.proc_open_file_state;
                        if (link.text() != openActionLabel) {
                            link.text(openActionLabel);
                            // Highlight the link for better UX
                            link.removeClass('active');
                            $('.messages').after(`<div class="messages status" id="proc-decrypting-status">${procJsLabels.proc_decryption_success}</div>`);
                        }

                        // Check if file generated is the same size of source file.
                        if (blob.size.toString() === sourceFileSize) {
                            // Restore original file name:
                            link.attr('download', sourceFileName);
                        } else {
                            // @TODO: save error log.
                            $('form#-proc-decrypt-to-file').prepend(`<div class="messages error">${procJsLabels.proc_decryption_size_mismatch}</div>`);
                        }
                    }
                }
            );
        }
    };
})(jQuery);
