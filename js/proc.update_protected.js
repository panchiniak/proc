/**
 * @file
 * Updates cipher texts.
 */
(function ($) {
    'use strict';
    Drupal.behaviors.proc = {
        attach: function (context, settings) {

            let passDrupal       = Drupal.settings.proc.proc_pass,
                privkey          = Drupal.settings.proc.proc_privkey,
                cipherTexts      = Drupal.settings.proc.proc_ciphers,
                cipherTextsIndex = Drupal.settings.proc.proc_ciphers_index,
                fileApiErrMsg    = Drupal.settings.proc.proc_fileapi_err_msg,
                procJsLabels     = Drupal.settings.proc.proc_labels;

            const introducingKeyDecryptionMsgElement = `<div class="messages info proc-info" id="proc-decrypting-info">${procJsLabels.proc_introducing_decryption}</div>`;

            if (!(window.Blob) || !(window.FileReader)) {
                alert(fileApiErrMsg);
            }

            // Reset messages and action.
            $('input#edit-pass').once().on(
                'focusin', function () {
                    const submit = document.querySelector('.proc-update-submit');

                    if (submit.disabled == true){
                        $('.messages').remove();
                        submit.value = procJsLabels.proc_button_update_label;

                        submit.classList.add('active');
                        submit.disabled = false;
                        document.querySelector('#edit-pass').value = '';
                    }
                }
            );

            // Do not submit the form if encryption did not happen:
            var ready = 0;
            $('#-proc-update').submit(
                function () {
                    if (ready == 0){
                        return false;
                    }
                }
            );

            $('.proc-update-submit').on(
                'click', async function () {

                    let secretPass        = $('input[name=pass]')[0].value,
                        secretPassString  = new String(secretPass),
                        passphrase        = passDrupal.concat(secretPassString),
                        recipientsPubkeys = Drupal.settings.proc.proc_recipients_pubkeys;

                    const privKeyObj = (await openpgp.key.readArmored(privkey)).keys[0];

                    await privKeyObj.decrypt(passphrase).catch(
                        function (err) {
                            $('form#-proc-update').prepend(`<div class="messages error">${Drupal.t(err)}</div>`);
                        }
                    );

                    if (!$('#proc-decrypting-info')[0]) {
                        $('form#-proc-update').prepend(introducingKeyDecryptionMsgElement);
                    }

                    recipientsPubkeys = JSON.parse(recipientsPubkeys);

                    const recipientsKeys = new Array();
                    recipientsPubkeys.forEach(
                        async function (entry) {
                            recipientsKeys.push((await openpgp.key.readArmored(entry.key)).keys[0]);
                        }
                    );

                    var procID = [];
                    const BROWSER_FINGERPRINT = navigator.userAgent + ', (' + screen.width + ' x ' + screen.height + ')';

                    for (i = 0; i < cipherTextsIndex.length; i++) {
                        document.querySelector('.proc-update-submit').innerText = procJsLabels.proc_button_state_processing;
                        procID.push(cipherTextsIndex[i]);
                        const optionsDecription = {
                            message: await openpgp.message.readArmored(cipherTexts[cipherTextsIndex[i]].cipher_text).catch(
                                function (err) {
                                    let messageError = `<div class="messages error">${Drupal.t(err)}</div>`;
                                    $('form#-proc-update').prepend(messageError);
                                }
                            ),
                            privateKeys: [privKeyObj],
                            // For the sake of simplicity all files are considered binary.
                            format: 'binary'
                        };

                        const decrypted = await openpgp.decrypt(optionsDecription).catch(
                            function (err) {
                                $('form#-proc-update').prepend(`<div class="messages error">${Drupal.t(err)}</div>`);
                                if ($('.messages.info.proc-info:first')){
                                    $('.messages.info.proc-info:first').remove();
                                }
                            }
                        );

                        if (decrypted){
                            const plaintext = await openpgp.stream.readToEnd(decrypted.data);

                            const blob = new Blob(
                                [plaintext], {
                                    type: 'application/octet-binary',
                                    endings: 'native'
                                }
                            );

                            let reader = new FileReader();
                            reader.readAsArrayBuffer(blob);
                            let fileByteArray = [];
                            reader.onloadend = async function (evt) {
                                if (evt.target.readyState == FileReader.DONE) {
                                    let arrayBuffer = evt.target.result,
                                        array       = new Uint8Array(arrayBuffer);
                                    for (let i = 0; i < array.length; i++) {
                                        fileByteArray.push(array[i]);
                                    }
                                    // False for production.
                                    openpgp.config.debug        = false;
                                    openpgp.config.show_comment = false;
                                    openpgp.config.show_version = false;
                                    let recipientsPubkeys = await Drupal.settings.proc.proc_recipients_pubkeys;
                                    recipientsPubkeys = JSON.parse(recipientsPubkeys);
                                    const readableStream = new ReadableStream(
                                        {
                                            start(controller) {
                                                controller.enqueue(array);
                                                controller.close();
                                            }
                                        }
                                    );
                                    const options = {
                                        message: openpgp.message.fromBinary(readableStream),
                                        publicKeys: recipientsKeys,
                                        compression: openpgp.enums.compression.zip
                                    };

                                    // Warning: Readable Stream expires if used twice.
                                    const startSeconds    = new Date().getTime() / 1000,
                                          encrypted       = await openpgp.encrypt(options),
                                          ciphertext      = encrypted.data,
                                          cipherPlaintext = await openpgp.stream.readToEnd(ciphertext),
                                          endSeconds      = new Date().getTime() / 1000,
                                          total           = endSeconds - startSeconds;

                                    var procIDString = procID.pop().toString();
                                    await(document.querySelector('[name=cipher_text_cid_' + procIDString + ']').value = cipherPlaintext);
                                    document.querySelector('[name=generation_timespan_cid_' + procIDString + ']').value = total;
                                    document.querySelector('[name=browser_fingerprint_cid_' + procIDString + ']').value = BROWSER_FINGERPRINT;
                                    document.querySelector('[name=generation_timestamp_cid_' + procIDString + ']').value = startSeconds;

                                    if (procID.length == 0){
                                        ready = 1;
                                        document.querySelector('.proc-update-submit').innerText = procJsLabels.proc_submit_saving_state;
                                        // Do not submit the password:
                                        var passPlaceHolder = new Array($('input[name=pass]')[0].value.length + 1).join( Math.random().toString(36).replace(/[^a-z]+/g, '').substr(0, 1));
                                        $('input[name=pass]')[0].value = passPlaceHolder;
                                        // Make sure password is not submited:
                                        if ($('input[name=pass]')[0].value != secretPass){
                                            $('#-proc-update').submit();
                                        }
                                    }
                                }
                            };
                        }
                    }
                }
            );
        }
    };
})(jQuery);
