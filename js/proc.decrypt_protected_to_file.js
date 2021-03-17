/**
 * @file
 * Decrypts cipher texts into a file given a correct privkey passphrase.
 */
(function ($) {
    'use strict';
    Drupal.behaviors.proc = {
        attach: function (context, settings) {

            let procJsLabels      = Drupal.settings.proc.proc_labels,
                passDrupal        = Drupal.settings.proc.proc_pass,
                privkey           = Drupal.settings.proc.proc_privkey,
                cipherIds         = Drupal.settings.proc.proc_ids,
                ciphersChanged    = Drupal.settings.proc.procs_changed,
                sourcesFileNames  = Drupal.settings.proc.proc_sources_file_names,
                sourcesFilesSizes = Drupal.settings.proc.proc_sources_file_sizes,
                fileApiErrMsg     = Drupal.settings.proc.proc_fileapi_err_msg;

            let procURLs = [];

            cipherIds.forEach(
                function (cipherId,cipherIdIndex){
                    procURLs[cipherIdIndex] = `${window.location.origin + Drupal.settings.basePath}proc/api/get/${cipherIds[cipherIdIndex]}/?cipherchanged=${ciphersChanged[cipherIdIndex]}`;
                }
            );

            let cipherResponse,
                cipher;

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

            if ('caches' in self){
                let cache = caches.open('proc');

                cipherIds.forEach(
                    function (cipherId,cipherIdIndex){
                        let cachedCiphers = [];

                        cachedCiphers[cipherIdIndex] = cache.then(async function (cache){
                            let response = await cache.match(procURLs[cipherIdIndex]);
                            if (response){
                                console.info('Reusing cipher from cache');
                                return;
                            }
                            else{
                                console.info('Adding cipher to cache');
                                cipherResponse = (await fetch(procURLs[cipherIdIndex]));
                                cipher = cipherResponse.clone();
                                cache.put(procURLs[cipherIdIndex], cipherResponse);
                                return cipher;
                                // @TODO: add clean up of expired cache if any!
                            }
                        });
                    }
                );

                $('#decryption-link').on(
                    'click', async function (e) {
                        e.preventDefault();

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

                        let temporaryDownloadLink = document.createElement("a");
                        temporaryDownloadLink.style.display = 'none';
                        document.body.appendChild(temporaryDownloadLink);

                        cipherIds.forEach(
                            async function (cipherId, cipherIndex){
                                let cache = caches.match(procURLs[cipherIndex]);
                                cache.then(
                                    async function (response){
                                        response.json().then(async function (data){
                                            let cipherText = data.ciphers[0];

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
                                                decrypted = await openpgp.decrypt(optionsDecription).catch(
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

                                                    temporaryDownloadLink.setAttribute( 'href', URL.createObjectURL(blob));

                                                    let openActionLabel = procJsLabels.proc_open_file_state;
                                                    if (link.text() != openActionLabel) {
                                                        link.text(openActionLabel);
                                                        // Highlight the link for better UX
                                                        link.removeClass('active');
                                                        $('.messages').after(`<div class="messages status" id="proc-decrypting-status">${procJsLabels.proc_decryption_success}</div>`);
                                                    }

                                                    // Check if file generated is the same size of source file.
                                                    if (blob.size.toString() === sourcesFilesSizes[cipherIndex]) {
                                                        // Restore original file name:
                                                        temporaryDownloadLink.setAttribute( 'download', sourcesFileNames[cipherIndex]);
                                                        temporaryDownloadLink.click();
                                                    } else {
                                                        // @TODO: save error log.
                                                        $('form#-proc-decrypt-to-file').prepend(`<div class="messages error">${procJsLabels.proc_decryption_size_mismatch}</div>`);
                                                    }
                                                }
                                        });
                                    }
                                );
                            }
                        );
                        document.body.removeChild( temporaryDownloadLink );
                    }
                );
            }
            else{
                alert('Error: this browser does not suport cache API. Please use an updated browser.');
            }
        }
    };
})(jQuery);
