/**
 * @file
 * Encryption of file given a public PGP armored key.
 */

(function ($) {
    'use strict';
    Drupal.behaviors.proc = {
        attach: function (context, settings) {

            if (!(window.FileReader)) {
                alert(procJsLabels.proc_fileapi_err_msg);
            }

            if (document.getElementById('edit-button')) {
                document.getElementById('edit-button').disabled = "TRUE";
            }

            function handleFileSelect(evt) {
                let procJsLabels        = Drupal.settings.proc.proc_labels,
                    files               = evt.target.files,
                    postMaxSizeBytes    = Drupal.settings.proc.proc_post_max_size_bytes,
                    fileEntityMaxSize   = parseInt(Drupal.settings.proc.proc_file_entity_max_filesize, 10),
                    fileSize            = parseInt(files[0].size, 10),
                    postMaxSizeBytesInt = parseInt(postMaxSizeBytes, 10),
                    // Assuming ciphertexts are at least 4 times bigger than
                    // their plaintexts:
                    dynamicMaximumSize  = postMaxSizeBytesInt / 4;

                document.getElementById('edit-button').value = procJsLabels.proc_button_state_processing;
                $('label[for=edit-pc-upload-description]')[0].innerText = `${procJsLabels.proc_size} ${files[0].size} ${procJsLabels.proc_max_encryption_size_unit} - ${procJsLabels.proc_type} ${files[0].type} - ${procJsLabels.proc_last_modified} ${files[0].lastModifiedDate}`;

                let realMaxSize = dynamicMaximumSize;
                if (fileSize > dynamicMaximumSize || fileSize > fileEntityMaxSize) {
                    if (fileEntityMaxSize < dynamicMaximumSize){
                        realMaxSize = fileEntityMaxSize;
                    }
                    $("form#-proc-encrypt-file").prepend('<div class="messages error">' + `${procJsLabels.proc_max_encryption_size} ${realMaxSize} ${procJsLabels.proc_max_encryption_size_unit}` + '</div>');
                    document.getElementById('edit-button').value = procJsLabels.proc_save_button_label;
                    return;
                }

                let myFile        = files[0],
                    reader        = new FileReader(),
                    fileByteArray = [];

                reader.readAsArrayBuffer(myFile);
                reader.onloadend = async function (evt) {
                    if (evt.target.readyState == FileReader.DONE) {

                        const recipientsPubkeys = [];

                        let arrayBuffer               = evt.target.result,
                            array                     = new Uint8Array(arrayBuffer),
                            // At this moment we only know about validated recipient UIDs
                            // and the time stamps of their keys:
                            recipientsUidsKeysChanged = JSON.parse(Drupal.settings.proc.proc_recipients_pubkeys_changed),
                            remoteKey                 = [],
                            userIdIterator;

                        for (let i = 0; i < array.length; i++) {
                            fileByteArray.push(array[i]);
                        }
                        // False for production.
                        openpgp.config.debug        = false;
                        openpgp.config.show_comment = false;
                        openpgp.config.show_version = false;

                        for (userIdIterator in recipientsUidsKeysChanged) {
                            let localKey = localStorage.getItem(`proc.key_user_id.${userIdIterator}.${recipientsUidsKeysChanged[userIdIterator]}`);
                            if (localKey){
                                recipientsPubkeys.push(localKey);
                            }
                            else{
                                let storageKeys = Object.keys(localStorage);
                                if (storageKeys.length > 0) {
                                    storageKeys.forEach(
                                        function (storageKey,storageKeyIndex){
                                            if (storageKey.startsWith(`proc.key_user_id.${userIdIterator}`)){
                                                localStorage.removeItem(storageKeys[storageKeyIndex]);
                                            }
                                        }
                                    );
                                }
                                remoteKey.push(userIdIterator);
                            }
                        }

                        let pubkeysJson;

                        if (remoteKey.length > 0){
                            let remoteKeyCsv = remoteKey.join(",");

                            const pubKeyAjax = async (remoteKeyCsv) => {
                                let response = await fetch(
                                    `${window.location.origin + Drupal.settings.basePath}proc/api/getpubkey/${remoteKeyCsv}`
                                );
                                pubkeysJson = await response.json();

                                if (pubkeysJson.pubkey.length > 0){
                                    pubkeysJson.pubkey.forEach(
                                        function (pubkey,index){
                                            recipientsPubkeys.push(pubkey.key);
                                            try {
                                                localStorage.setItem(`proc.key_user_id.${remoteKey[index]}.${pubkey.changed}`, pubkey.key);
                                            }
                                            catch (error){
                                                console.warn(error);
                                            }

                                        }
                                    );
                                }
                            };
                            await pubKeyAjax(remoteKeyCsv);
                        }

                        const readableStream = new ReadableStream({
                            start(controller) {
                                controller.enqueue(array);
                                controller.close();
                            }
                        });

                        const recipientsKeys = [];

                        recipientsPubkeys.forEach(
                            async function (entry) {
                                recipientsKeys.push((await openpgp.key.readArmored(entry)).keys[0]);
                            }
                        );

                        await openpgp.key.readArmored(recipientsPubkeys);

                        const options = {
                            message: openpgp.message.fromBinary(readableStream),
                            publicKeys: recipientsKeys,
                            compression: openpgp.enums.compression.zip
                        };

                        let startSeconds = new Date().getTime() / 1000;

                        const encrypted       = await openpgp.encrypt(options),
                              ciphertext      = encrypted.data,
                              // Warning: Readable Stream expires if used twice.
                              cipherPlaintext = await openpgp.stream.readToEnd(ciphertext);

                        let endSeconds = new Date().getTime() / 1000,
                            total = endSeconds - startSeconds;

                        $('input[name=cipher_text]')[0].value = cipherPlaintext;
                        $('input[name=source_file_name]')[0].value = files[0].name;
                        $('input[name=source_file_size]')[0].value = files[0].size;
                        $('input[name=source_file_type]')[0].value = files[0].type;
                        $('input[name=source_file_last_change]')[0].value = files[0].lastModified;
                        $('input[name=browser_fingerprint]')[0].value = `${navigator.userAgent}, (${screen.width} x ${screen.height})`;
                        $('input[name=generation_timestamp]')[0].value = startSeconds;
                        $('input[name=generation_timespan]')[0].value = total;
                        document.getElementById('edit-button').removeAttribute("disabled");
                        document.getElementById('edit-button').value = procJsLabels.proc_save_button_label;
                    }
                };
            }
            if (document.getElementById('edit-upload')) {
                document.getElementById('edit-upload').addEventListener('change', handleFileSelect, false);
            }
        }
    };
})(jQuery);
