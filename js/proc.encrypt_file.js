/**
 * @file
 * Provides encryption of file given a public PGP armored key.
 */

(function () {
    'use strict';
    Drupal.behaviors.proc = {
        attach: function (context, settings) {

            document.getElementById('edit-submit').disabled = "TRUE";

            // Check for the various File API support.
          if (!(window.File && window.FileReader && window.FileList && window.Blob)) {
              // Error.
              alert(Drupal.t('The File APIs are not fully supported in this browser.'));
          }

          function handleFileSelect(evt) {

              document.getElementById('edit-submit').value = Drupal.t('Processing...');

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
                    var recipientPubkey = await Drupal.settings.proc.proc_recipient_pubkey;

                    //var recipientsPubkeys = await Drupal.settings.proc.proc_recipients_pubkeys;

                    

                    const superUserPubKey =
                    ['-----BEGIN PGP PUBLIC KEY BLOCK-----',
                    'Version: OpenPGP.js v4.4.5',
                    'Comment: admin:empty@mail.localhost',
                    '',
                    'xsBNBFy/KycBB/97FbTbwrrG5y+691cQ2u8jAmJSTGQKriBfmlbQ2pR2JpQd',
                    'fO2R5RdWtlBF16syC7qiYu50HR5BX9WJZjrex4VBsx7/oOUG0/KYtmPnxWqn',
                    '3elmdf+hoAorIu5goxhWwOmgG5H0kUxflIUGYPX01qTnpUCKLbKDmWAI8Y/Z',
                    'QTAdn6HuIkyjIZrOATbQFneNiurJKI8FSfbLeAqShxVsRbKmdCc1ekVFIw3h',
                    'm5fcIuzuQaMRFeVwiSSS99YoPGT5Q/anBiZSLydOyjeRGiCqqbon5t3dsHKq',
                    'aGFQtpm3PSE9NwA3CndDJJtnzVq/ICFLTL4LaY/NGgTyraWjL+gUxTtnABEB',
                    'AAHNHGFkbWluIDxlbXB0eUBtYWlsLmxvY2FsaG9zdD7CwHUEEAEIAB8FAly/',
                    'KycGCwkHCAMCBBUICgIDFgIBAhkBAhsDAh4BAAoJEG+u4OTKgvhcLcIH/A4L',
                    'bgD/wvhKpcpvwDsX7dlqgaMN6JMvjNJli0lJdjFOJNkdsPj9IYuX2N2GIsLn',
                    'lMpOLSgEeSYnIiAz4nax4eJ6KdzEOTS/mW+1wySUkue691z06dPP2bpcUPYb',
                    'ZCnPsM42tG0Mq+0gpElRp4tcFQ/oKSrK3Ik303mEReE21LMDIj1ZI+WEbmbL',
                    'dsSXQ3xJ8Va6BqMlQncoOVCNgXtMdWzkhkOWWMDI3Dl+YQ1PS5QeAJssosO0',
                    'mhCFW3MuOFZ60DYONEwTVStfdqGRn+oMjnOGYjN9IbaZJoT50NBVZ/x+yehr',
                    'AOTMGU13CirY/AoeDWmfa/h1abfL1tl05NCy4qjOwE0EXL8rJwEIAJm12v4l',
                    'wYsMk3DmZg+VLRK/n4wRmIAFMHe143DQIzeGcBX8JWwxti3z4Rhp0g6zx08x',
                    '0xF0kEJ+CeY9DXDq8UV2m7rQIgvjZ8ysUMgy/n0o2nH3auJiC3AqzPHv1EWJ',
                    'EQKoYPmKcKKPaR64l0MJvV54IQApliQGZ/+mV+FzP4li+KTODwhr1kSRUrgM',
                    '5Y7G5ZMfCzGVkjtlwLRQXtzUi5iFRFhHmnPYh343WqErgqV1TFJQjYQtsGQP',
                    'vOtZGAOZJqjw+r4grQUn++fbITL5srusixzXgZDE5DuopT8jz3k4Xt7f8ICD',
                    '21iDE01VMBgnw6WWtj4hRmI3XmBlPAh7PIcAEQEAAcLAXwQYAQgACQUCXL8r',
                    'JwIbDAAKCRBvruDkyoL4XHAXB/9Njk7vycZjXJ/Qu5tgLdCi/VCkTw624t+w',
                    '9G/Z9uWJHzCe150uPSuV0rCSygz3Gt40fMrvpnPWrsLDVi7i2BlKCgH3JiIK',
                    'EX+2SywlSNvQkv99lwsTnj+NlmAeuapKNQBQ7tz8YmNHNP/8n0qPfSXHR1Wj',
                    'Rx9s+j+lYFfLIl2hFjJGVx+iXQV5p8d9L5I0WIdO5ATiLf1ytma3ZNiJGIji',
                    'BwpLSXHJTx/ts6DIAfnSppmZNZlpesay9IHE2KGeCmWWp6y5U5/1LWwCkc9+',
                    '/SZizAKO50EchgGztGS80UGM0f832mp9M5dld2bf9febsDPB4a1h3Xldp9C8',
                    'bAd3p2OI',
                    '=/zNT',
                    '-----END PGP PUBLIC KEY BLOCK-----'].join('\n');


                    const readableStream = new ReadableStream({
                        start(controller) {
                            controller.enqueue(array);
                            controller.close();
                        }
                    });

                    //console.log(recipientsPubkeys);

                    // var pubKeys = new Object;
                    
                    // //const keysResults = recipientsPubkeys.map(recipientsPubkeys => recipientsPubkeys);
                    // var i;
                    
                    // for (i = 0; i < recipientsPubkeys.length; i++) {
                    //   // console.log(i);
                    //   // console.log(recipientsPubkeys[i]);
                    //   //pubKeys.push({armoredKey: (await openpgp.key.readArmored(recipientPubkey[i])).keys[0]});
                    //   pubKeys[i] = await {armoredKey: (await openpgp.key.readArmored(recipientPubkey[i])).keys[0]};
                    // }

                    // console.log(pubKeys);

                    //console.log(keysResults);

                    //var keysResults = recipientsPubkeys.map(this);
                    
                    

                    var armoredPubkeys = [
                      {armoredKey : (await openpgp.key.readArmored(recipientPubkey)).keys[0]}, 
                      {armoredKey : (await openpgp.key.readArmored(superUserPubKey)).keys[0]}
                    ];
                    

                    const keys = armoredPubkeys.map(pubkey => pubkey.armoredKey);
                    
                    console.log('----------------keys--------------');
                    console.log(keys);


                    //const testKeys = recipientsPubkeys.map(keys => recipientsPubkeys);
                    // const testKeys = await recipientsPubkeys.map(async function (recipientsPubkeys) {
                    //   return await openpgp.key.readArmored(recipientsPubkeys).keys[0];
                    // });
                    // console.log(recipientsPubkeys);
                    // var i;
                    // var keyObjects = Array;
                    // for (i = 0; i < recipientsPubkeys.length; i++){
                    //   keyObjects[i] = await openpgp.key.readArmored(recipientsPubkeys[i]).keys[0];
                    //   //keyObjects[i] = recipientsPubkeys[i];
                    // }

                    // console.log('-------------keyObjects-------------------');
                    // console.log(keyObjects);



                    

                    const options = {
                        message: openpgp.message.fromBinary(readableStream),
                        publicKeys: keys,
                        //publicKeys: [(await openpgp.key.readArmored(superUserPubKey)).keys,(await openpgp.key.readArmored(recipientPubkey)).keys]
                        //publicKeys: (await pascalPubkey).keys[0],
                        //publicKeys: [(await openpgp.key.readArmored(recipientPubkey)).keys[0],(await openpgp.key.readArmored(superUserPubKey)).keys[0]],
                        //publicKeys: [(openpgp.key.readArmored(recipientPubkey)).keys[0]],
                        compression: openpgp.enums.compression.zip
                    };

                    // console.log(options);
                    var startSeconds = new Date().getTime() / 1000;

                    const encrypted = await openpgp.encrypt(options);

                    // console.log('----------------1-------------');
                    // console.log(options);
                    
                    
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
                    document.getElementById('edit-submit').value = Drupal.t('Save');
                }
              }
          }
            document.getElementById('edit-upload').addEventListener('change', handleFileSelect, false);
        }
  }
})(jQuery);
