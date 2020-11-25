/**
 * @file
 * Updates cipher texts.
 */
(function ($) {
    'use strict';
    Drupal.behaviors.proc = {
        attach: function (context, settings) {

            if (!(window.Blob)) {
                alert(Drupal.t('The File APIs are not fully supported in this browser.'));
            }

            if (document.getElementById('edit-button--2')) {
                document.getElementById('edit-button--2').disabled = "TRUE";
            }


            let passDrupal = Drupal.settings.proc.proc_pass;
            let privkey = Drupal.settings.proc.proc_privkey;
            let cipherTexts = Drupal.settings.proc.proc_ciphers;
            let cipherTextsIndex = Drupal.settings.proc.proc_ciphers_index;

            var ready = 0;

            // Do not submit the form at all.
            $('#-proc-update').submit(
                function (event) {
                    if (ready == 0){
                        $('form#-proc-update').prepend(Drupal.t('<div class="messages error">You can\'t and you don\'t need to submit this form. Instead just click or tap on "Update".</div>'));
                        return false;    
                    }
                }
            );

            // Reset messages and action.
            $('input#edit-pass').once().on(
                'focusin', function () {
                    $('.messages').remove();
                    document.getElementById('update-link').innerText = Drupal.t('Update');
                    // let actionLink = $('#update-link');
                    // if (!(actionLink.hasClass('active'))) {
                    //     //actionLink.text('Update').removeClass('active').removeAttr('download').removeAttr('href');
                    // }
                }
            );

            // $('#update-link').on(
            $('#update-link').on(                
                'click', async function () {
                    let secretPass = $('input[name=pass]')[0].value;
                    let secretPassString = new String(secretPass);
                    let passphrase = passDrupal.concat(secretPassString);
                    const privKeyObj = (await openpgp.key.readArmored(privkey)).keys[0];

                    await privKeyObj.decrypt(passphrase).catch(
                        function (err) {
                            // @TODO: save error log.
                            $('form#-proc-update').prepend('<div class="messages error">' + Drupal.t(err) + '</div>');

                            // if ($('a#update-link')[0].href) {
                            //     const fileUrl = $('a#update-link')[0].href;
                            //     URL.revokeObjectURL(fileUrl);
                            //     $('a#update-link').removeAttr('href');
                            // }
                        }
                    );

                    if (!$('#proc-decrypting-info')[0]) {
                        $('form#-proc-update').prepend(Drupal.t('<div class="messages info" id="proc-decrypting-info">Indroducing key passphrase for decryption...</div>'));
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
                    
                    for (i = 0; i < cipherTextsIndex.length; i++) {
                        document.getElementById('update-link').innerText = Drupal.t('Processing...');

                        procID.push(cipherTextsIndex[i]);


                        const optionsDecription = {
                            message: await openpgp.message.readArmored(cipherTexts[cipherTextsIndex[i]].cipher_text).catch(
                                function(err){
                                    $('form#-proc-update').prepend('<div class="messages error">' + Drupal.t(err) + '</div>')
                                }
                            ),
                            privateKeys: [privKeyObj],
                            // For the sake of simplicity all files are considered binary.
                            format: 'binary'
                        };

                        const decrypted = await openpgp.decrypt(optionsDecription).catch(
                            function(err){
                                $('form#-proc-update').prepend('<div class="messages error">' + Drupal.t(err) + '</div>')
                            }
                        );

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

                                await (document.querySelector('[name=cipher_text_cid_' + procID.pop().toString() + ']').value = cipherPlaintext);
                                // $('input[name=source_file_name]')[0].value = files[0].name;
                                // $('input[name=source_file_size]')[0].value = files[0].size;
                                // $('input[name=source_file_type]')[0].value = files[0].type;
                                // $('input[name=source_file_last_change]')[0].value = files[0].lastModified;
                                // $('input[name=browser_fingerprint]')[0].value = navigator.userAgent + ', (' + screen.width + ' x ' + screen.height + ')';
                                // $('input[name=generation_timestamp]')[0].value = startSeconds;
                                // $('input[name=generation_timespan]')[0].value = total;

                                console.log('-----cipherTextsIndex------');
                                console.log(cipherTextsIndex.length);
                                console.log('-----procID.length------');
                                console.log(procID.length);

                                if (procID.length == 0){
                                    ready = 1;
                                    document.getElementById('update-link').innerText = Drupal.t('Saving...');
                                    $('#-proc-update').submit();
                                }
                            }
                        }
                    }
                }
            );
        }
    }
})(jQuery);
