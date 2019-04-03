/**
 * @file
 * Provides encryption of file given a public PGP armored key.
 */

(async function () {
    'use strict';
    Drupal.behaviors.proc = {
        attach: function (context, settings) {

            document.getElementById('edit-submit').disabled = "TRUE";

            // Check for the various File API support.
            if (!(window.File && window.FileReader && window.FileList && window.Blob)) {
                // Error.
                alert('The File APIs are not fully supported in this browser.');
            }

            function handleFileSelect(evt) {

                document.getElementById('edit-submit').value = "Processing...";

                var files = evt.target.files;
                var output = [];

                // @TODO: add multiple files support.
                for (var i = 0, f; f = files[i]; i++) {
                    output.push(
                        '<li><strong>',
                        escape(f.name),
                        '</strong> (',
                        f.type || 'n/a', ') - ',
                        f.size,
                        ' bytes, last modified: ',
                        f.lastModified,
                        '</li>'
                    );
                }

                jQuery('label[for=edit-pc-upload-description]')[0].innerText =
                ' Size: ' + files[0].size + ' bytes - Type: ' + files[0].type +
                ' - Last modified: ' + files[0].lastModifiedDate;

                var postMaxSizeBytes = Drupal.settings.proc.proc_post_max_size_bytes;

                // @TODO: add multiple files support.
                var fileSize = parseInt(files[0].size, 10);
                var postMaxSizeBytesInt = parseInt(postMaxSizeBytes, 10);
                // Assuming cipher texts are at least 4 times bigger than their sources.
                // @TODO: improve heuristics
                var dynamicMaximumSize = postMaxSizeBytesInt / 4;

                if (fileSize > dynamicMaximumSize) {
                    jQuery("form#emnies-rsc-encrypt-file").prepend('<div class="messages error">Sorry. Dynamic maximum file size exceed. Please add a file smaller than ' + dynamicMaximumSize + ' bytes</div>');
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
                        openpgp.config.debug = true;
                        openpgp.config.show_comment = false;
                        openpgp.config.show_version = false;

                        // Still allowing a single recipient: [0] (the first taken)
                        var recipientPubkey = await Drupal.settings.proc.proc_recipient_pubkey[0];

                        // @TODO: in case of multiple pubkeys:
                        // const pubkeys = [`-----BEGIN PGP PUBLIC KEY BLOCK-----
                        // ...
                        // -----END PGP PUBLIC KEY BLOCK-----`,
                        // `-----BEGIN PGP PUBLIC KEY BLOCK-----
                        // ...
                        // -----END PGP PUBLIC KEY BLOCK-----`
                        // pubkeys = pubkeys.map(async (key) => {
                        // return (await openpgp.key.readArmored(key)).keys[0]
                        // });
                        // const options = {
                        // message: openpgp.message.fromBinary(readableStream),
                        // publicKeys: pubkeys
                        // compression: openpgp.enums.compression.zip
                        // };
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
                            publicKeys: (await openpgp.key.readArmored(recipientPubkey)).keys,
                            compression: openpgp.enums.compression.zip
                        };

                        // console.log(options);
                        var startSeconds = new Date().getTime() / 1000;

                        const encrypted = await openpgp.encrypt(options);
                        const ciphertext = encrypted.data;
                        // Warning: Readable Stream expires if used twice.
                        const cipherPlaintext = await openpgp.stream.readToEnd(ciphertext);

                        var endSeconds = new Date().getTime() / 1000;
                        var total = endSeconds - startSeconds;

                        jQuery('input[name=cipher_text]')[0].value = cipherPlaintext;
                        jQuery('input[name=source_file_name]')[0].value = files[0].name;
                        jQuery('input[name=source_file_size]')[0].value = files[0].size;
                        jQuery('input[name=source_file_type]')[0].value = files[0].type;
                        jQuery('input[name=source_file_last_change]')[0].value = files[0].lastModified;
                        // @TODO: store fingerprint data structured instead of concatenating.
                        jQuery('input[name=browser_fingerprint]')[0].value = navigator.userAgent + ', (' + screen.width + ' x ' + screen.height + ')';
                        jQuery('input[name=generation_timestamp]')[0].value = startSeconds;
                        jQuery('input[name=generation_timespan]')[0].value = total;

                        document.getElementById('edit-submit').removeAttribute("disabled");
                        document.getElementById('edit-submit').value = "Save";
                    }
                }
            }
            document.getElementById('edit-upload').addEventListener('change', handleFileSelect, false);
        }
    }
})(jQuery);
