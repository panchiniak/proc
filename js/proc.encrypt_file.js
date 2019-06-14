/**
 * @file
 * Provides encryption of file given a public PGP armored key.
 */

(function ($) {
    'use strict';
    Drupal.behaviors.proc = {
        attach: function (context, settings) {

          // Check for the various File API support.
          if (!(window.FileReader)) {
            // Error.
            alert(Drupal.t('The File APIs are not fully supported in this browser.'));
          }
          document.getElementById('edit-submit').disabled = "TRUE";

          function handleFileSelect(evt) {

            document.getElementById('edit-submit').value = Drupal.t('Processing...');

            let files = evt.target.files;

            $('label[for=edit-pc-upload-description]')[0].innerText =
            Drupal.t(' Size: ') + files[0].size + Drupal.t(' bytes - Type: ') + files[0].type +
            Drupal.t(' - Last modified: ') + files[0].lastModifiedDate;

            let postMaxSizeBytes = Drupal.settings.proc.proc_post_max_size_bytes;

            let fileSize = parseInt(files[0].size, 10);
            let postMaxSizeBytesInt = parseInt(postMaxSizeBytes, 10);
            // Assuming ciphertexts are at least 4 times bigger than their plaintexts.
            let dynamicMaximumSize = postMaxSizeBytesInt / 4;

            if (fileSize > dynamicMaximumSize) {
                $("form#-proc-encrypt-file").prepend('<div class="messages error">' + Drupal.t('Sorry. Dynamic maximum file size exceed. Please add a file smaller than ') + dynamicMaximumSize + Drupal.t(' bytes') + '</div>');
                document.getElementById('edit-submit').value = Drupal.t('Save');
                return;
            }

            let myFile = files[0];
            let reader = new FileReader();
            let fileByteArray = [];
            reader.readAsArrayBuffer(myFile);
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

                const readableStream = new ReadableStream({
                  start(controller) {
                    controller.enqueue(array);
                    controller.close();
                  }
                });

                const recipientsKeys = new Array();
                recipientsPubkeys.forEach(async function(entry) {
                  recipientsKeys.push((await openpgp.key.readArmored(entry)).keys[0]);
                });

                // @TODO: manage to faultlessly remove unsused armoredPubkeys.
                // or use it to implement default recipient optional setting.
                let armoredPubkeys = (await openpgp.key.readArmored(recipientsPubkeys[0])).keys[0];

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

                $('input[name=cipher_text]')[0].value = cipherPlaintext;
                $('input[name=source_file_name]')[0].value = files[0].name;
                $('input[name=source_file_size]')[0].value = files[0].size;
                $('input[name=source_file_type]')[0].value = files[0].type;
                $('input[name=source_file_last_change]')[0].value = files[0].lastModified;
                // @TODO: store fingerprint data structured instead of concatenating.
                $('input[name=browser_fingerprint]')[0].value = navigator.userAgent + ', (' + screen.width + ' x ' + screen.height + ')';
                $('input[name=generation_timestamp]')[0].value = startSeconds;
                $('input[name=generation_timespan]')[0].value = total;

                document.getElementById('edit-submit').removeAttribute("disabled");
                document.getElementById('edit-submit').value = Drupal.t('Save');
              }
            }
          }
          document.getElementById('edit-upload').addEventListener('change', handleFileSelect, false);
        }
  }
})(jQuery);
