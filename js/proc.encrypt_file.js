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

            var files = evt.target.files;

            $('label[for=edit-pc-upload-description]')[0].innerText =
            Drupal.t(' Size: ') + files[0].size + Drupal.t(' bytes - Type: ') + files[0].type +
            Drupal.t(' - Last modified: ') + files[0].lastModifiedDate;

            var postMaxSizeBytes = Drupal.settings.proc.proc_post_max_size_bytes;

            var fileSize = parseInt(files[0].size, 10);
            var postMaxSizeBytesInt = parseInt(postMaxSizeBytes, 10);
            // Assuming ciphertexts are at least 4 times bigger than their plaintexts.

            var dynamicMaximumSize = postMaxSizeBytesInt / 4;

            if (fileSize > dynamicMaximumSize) {
                $("form#-proc-encrypt-file").prepend('<div class="messages error">' + Drupal.t('Sorry. Dynamic maximum file size exceed. Please add a file smaller than ') + dynamicMaximumSize + Drupal.t(' bytes') + '</div>');
                return;
            }

            var myFile = files[0];
            var reader = new FileReader();
            var fileByteArray = [];
            reader.readAsArrayBuffer(myFile);
            reader.onloadend = async function (evt) {
              if (evt.target.readyState == FileReader.DONE) {
                var arrayBuffer = evt.target.result;
                var array = new Uint8Array(arrayBuffer);
                for (var i = 0; i < array.length; i++) {
                  fileByteArray.push(array[i]);
                }
                // False for production.
                openpgp.config.debug = false;
                openpgp.config.show_comment = false;
                openpgp.config.show_version = false;

                var recipientsPubkeys = await Drupal.settings.proc.proc_recipients_pubkeys;
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
                var armoredPubkeys = (await openpgp.key.readArmored(recipientsPubkeys[0])).keys[0];

                const options = {
                  message: openpgp.message.fromBinary(readableStream),
                  publicKeys: recipientsKeys,
                  compression: openpgp.enums.compression.zip
                };

                var startSeconds = new Date().getTime() / 1000;
                const encrypted = await openpgp.encrypt(options);

                const ciphertext = encrypted.data;
                // Warning: Readable Stream expires if used twice.
                const cipherPlaintext = await openpgp.stream.readToEnd(ciphertext);

                var endSeconds = new Date().getTime() / 1000;
                var total = endSeconds - startSeconds;

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
