/**
 * @file
 * Updates cipher texts.
 */
(function ($) {
    'use strict';
    Drupal.behaviors.proc = {
        attach: function (context, settings) {

            let passDrupal = Drupal.settings.proc.proc_pass;
            let privkey    = Drupal.settings.proc.proc_privkey;

            let cipherTexts      = Drupal.settings.proc.proc_ciphers;
            let cipherTextsIndex = Drupal.settings.proc.proc_ciphers_index;

            let fileApiErrMsg = Drupal.settings.proc.proc_fileapi_err_msg;

            const introducingKeyDecryptionMsg = Drupal.t('Indroducing key passphrase for decryption...');
            const introducingKeyDecryptionMsgElement = `<div class="messages info proc-info" id="proc-decrypting-info">${introducingKeyDecryptionMsg}</div>`;

            if (!(window.Blob) || !(window.FileReader)) {
                alert(fileApiErrMsg);
            }

            // Reset messages and action.
            $('input#edit-pass').once().on(
                'focusin', function () {
                    const submit = document.querySelector('.proc-update-submit');

                    if (submit.disabled == true){
                        $('.messages').remove();
                        submit.value = Drupal.t('Update');
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
                    let secretPass = $('input[name=pass]')[0].value;
                    let secretPassString = new String(secretPass);
                    let passphrase = passDrupal.concat(secretPassString);
                    const privKeyObj = (await openpgp.key.readArmored(privkey)).keys[0];

                    await privKeyObj.decrypt(passphrase).catch(
                        function (err) {
                            $('form#-proc-update').prepend(`<div class="messages error">${Drupal.t(err)}</div>`);
                        }
                    );

                    if (!$('#proc-decrypting-info')[0]) {
                        $('form#-proc-update').prepend(introducingKeyDecryptionMsgElement);
                    }


                    let recipientsPubkeys = await Drupal.settings.proc.proc_recipients_pubkeys;
                    recipientsPubkeys = JSON.parse(recipientsPubkeys);

                    const recipientsKeys = new Array();
                    recipientsPubkeys.forEach(
                        async function (entry) {
                            recipientsKeys.push((await openpgp.key.readArmored(entry)).keys[0]);
                        }
                    );

                    var procID = [];
                    const BROWSER_FINGERPRINT = navigator.userAgent + ', (' + screen.width + ' x ' + screen.height + ')';
                    
                    for (i = 0; i < cipherTextsIndex.length; i++) {
                        document.querySelector('.proc-update-submit').innerText = Drupal.t('Processing...');
                        procID.push(cipherTextsIndex[i]);
                        const optionsDecription = {
                            message: await openpgp.message.readArmored(cipherTexts[cipherTextsIndex[i]].cipher_text).catch(
                                function (err) {
                                    let messageError = `<div class="messages error">${Drupal.t(err)}</div>`;
                                    //$('form#-proc-update').prepend('<div class="messages error">' + Drupal.t(err) + '</div>');
                                    $('form#-proc-update').prepend(messageError);
                                }
                            ),
                            privateKeys: [privKeyObj],
                            // For the sake of simplicity all files are considered binary.
                            format: 'binary'
                        };

                        const decrypted = await openpgp.decrypt(optionsDecription).catch(
                            function (err) {
                                $('form#-proc-update').prepend('<div class="messages error">' + Drupal.t(err) + '</div>');
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
                                    let arrayBuffer = evt.target.result;
                                    let array = new Uint8Array(arrayBuffer);
                                    for (let i = 0; i < array.length; i++) {
                                        fileByteArray.push(array[i]);
                                    }
                                    // False for production.
                                    openpgp.config.debug = false;
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

                                    let startSeconds = new Date().getTime() / 1000;
                                    const encrypted = await openpgp.encrypt(options);

                                    const ciphertext = encrypted.data;
                                    // Warning: Readable Stream expires if used twice.
                                    const cipherPlaintext = await openpgp.stream.readToEnd(ciphertext);

                                    let endSeconds = new Date().getTime() / 1000;
                                    let total = endSeconds - startSeconds;

                                    var procIDString = procID.pop().toString();
                                    await (document.querySelector('[name=cipher_text_cid_' + procIDString + ']').value = cipherPlaintext);

                                    document.querySelector('[name=generation_timespan_cid_' + procIDString + ']').value = total;
                                    document.querySelector('[name=browser_fingerprint_cid_' + procIDString + ']').value = BROWSER_FINGERPRINT;
                                    document.querySelector('[name=generation_timestamp_cid_' + procIDString + ']').value = startSeconds;

                                    if (procID.length == 0){
                                        ready = 1;
                                        document.querySelector('.proc-update-submit').innerText = Drupal.t('Saving...');
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
