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

                    const superUserPubKey =
                    ['-----BEGIN PGP PUBLIC KEY BLOCK-----',
                    'Version: OpenPGP.js v4.4.5',
                    'Comment: admin:empty@mail.localhost',
                    '',
                    'xsBNBFy0laUBB/9pc61WFrz7Va0Mw55GIl5om9k7lKHO8OIp4PwOMi9l8DXX',
                    'PN3Oxhwm96ZCR1apYp/IiSSyCp5Ohy2e/iz+EKV6Hb8XyPrL7BgtNpTuQSTV',
                    'CKBU1ncqxz5mhRwKxQrygrBsRMqeErc03X20y2PjJMQTpPIYt6AEd5c0dvZQ',
                    'uL/9ZvQSdbyyrCL348C9sF4BWqgBcPQI5Vl/rkQdKbTsVrpkRnhkNWwZLBqm',
                    '7WODIAcRYSfflyIbZLsGY5ihEasuE81lPXGYKTutnklOSEC2iJAKiCead/ju',
                    'mthlvU03PHk2K49TNJtUAz4VROH7BGaBsHpcD2DI8pYtZLYHEwinXVKzABEB',
                    'AAHNHGFkbWluIDxlbXB0eUBtYWlsLmxvY2FsaG9zdD7CwHUEEAEIAB8FAly0',
                    'laUGCwkHCAMCBBUICgIDFgIBAhkBAhsDAh4BAAoJEN8l9WkCqz/d1MQH/RAq',
                    '0iUsY+tcWmGyx87j21koJpxBh/yPTMOA4uYQ2wmwK1m6NZnjGVjf3VEHd06v',
                    'z0uge+JU8WUmxCFlLGXfESwGZGa/HdyWVReqRnOm5SNGl6iGgICyFBFGvJ4/',
                    '52ilAW2pE53PuWCpjhMSqabxzElbjZmE7sv4nTnZfDGZfxXZsbGGRSKMiHsc',
                    '6Ci5waMeid7IpxFoGWvRwXM3ApxskszI1ua0/ZjmqZbLIktFhEUCV6u82Pc7',
                    'RqxeeTsKOWxuT41x3TpUQrhV/E4vfuQFBKhfLQ4PjS/FrVqK+sOGiPqh6fv/',
                    'pgRC6pI5pJex+EHhlttFVuRPWhxlbGjxt9Nb38POwE0EXLSVpQEIAMPChSyd',
                    'hrgr2WdbgS9nAvam2ySFIwsWoeIuCjqj4nuIqb6UYq552FryJ6TH56bHkpVu',
                    '/bNSfgU4k1GWHVGDXuTMXyPvpB6qDcvVJj59FKmFXygJCERX9DNT5tdvVK7E',
                    '65aB2nxtv/kHODg6lrVYxH76b4ApfqD3o8LfKzpzLBqyoKpxOltSgNL5qZnw',
                    'epDBvTibzBGuf4XdM8axzczR888fv3oWvHzyp6mkHMBec6GZHmxPh5jDv0oJ',
                    '/PRlURjAfuJQctBZaMjLj1q/cXb0E9LVtBq3mRLlVWU3H/0AdnEX3v3mgyJO',
                    '5TAp00ySiJ9+Y7jN5itHZ9mogdUt72vjd/kAEQEAAcLAXwQYAQgACQUCXLSV',
                    'pQIbDAAKCRDfJfVpAqs/3dsPB/9aQ4D0zGPzDCrQ57ihTx1Mpg1LD0Gd8Tt7',
                    'RIqS4cd7p5IkUjgQc5lX44Mro3rXe23EsE6qP4tyTk7V3uXOaXRZwT8qr6x0',
                    'ZFDr8m0v234rlEBhQjULYCEs5agyrDzrPHpJiwPouzKnTDEeWN8SieQtGg54',
                    '7KTgttZgWqcFPMRRfEqJM+fOSBmoG8orGLmsk1LoTXP0Yjo9aU59QczaEfwA',
                    'xKlAKp4HI+alh6AMGhYoiXDRt+zXU9pBMeoZnb8XiCuPoFhM7pQj2wxo1BWh',
                    'wAmjjErcsDoSO90/JeeOTCaJ9gpP4kCioV5GgT/csACkH+jLIVxtquFl9InA',
                    'MdTC4FV0',
                    '=8B3o',
                    '-----END PGP PUBLIC KEY BLOCK-----'].join('\n');

//                     var superUserPubKey = await `-----BEGIN PGP PUBLIC KEY BLOCK-----
// Version: OpenPGP.js v4.4.5
// Comment: admin:empty@mail.localhost

// xsBNBFy0laUBB/9pc61WFrz7Va0Mw55GIl5om9k7lKHO8OIp4PwOMi9l8DXX
// PN3Oxhwm96ZCR1apYp/IiSSyCp5Ohy2e/iz+EKV6Hb8XyPrL7BgtNpTuQSTV
// CKBU1ncqxz5mhRwKxQrygrBsRMqeErc03X20y2PjJMQTpPIYt6AEd5c0dvZQ
// uL/9ZvQSdbyyrCL348C9sF4BWqgBcPQI5Vl/rkQdKbTsVrpkRnhkNWwZLBqm
// 7WODIAcRYSfflyIbZLsGY5ihEasuE81lPXGYKTutnklOSEC2iJAKiCead/ju
// mthlvU03PHk2K49TNJtUAz4VROH7BGaBsHpcD2DI8pYtZLYHEwinXVKzABEB
// AAHNHGFkbWluIDxlbXB0eUBtYWlsLmxvY2FsaG9zdD7CwHUEEAEIAB8FAly0
// laUGCwkHCAMCBBUICgIDFgIBAhkBAhsDAh4BAAoJEN8l9WkCqz/d1MQH/RAq
// 0iUsY+tcWmGyx87j21koJpxBh/yPTMOA4uYQ2wmwK1m6NZnjGVjf3VEHd06v
// z0uge+JU8WUmxCFlLGXfESwGZGa/HdyWVReqRnOm5SNGl6iGgICyFBFGvJ4/
// 52ilAW2pE53PuWCpjhMSqabxzElbjZmE7sv4nTnZfDGZfxXZsbGGRSKMiHsc
// 6Ci5waMeid7IpxFoGWvRwXM3ApxskszI1ua0/ZjmqZbLIktFhEUCV6u82Pc7
// RqxeeTsKOWxuT41x3TpUQrhV/E4vfuQFBKhfLQ4PjS/FrVqK+sOGiPqh6fv/
// pgRC6pI5pJex+EHhlttFVuRPWhxlbGjxt9Nb38POwE0EXLSVpQEIAMPChSyd
// hrgr2WdbgS9nAvam2ySFIwsWoeIuCjqj4nuIqb6UYq552FryJ6TH56bHkpVu
// /bNSfgU4k1GWHVGDXuTMXyPvpB6qDcvVJj59FKmFXygJCERX9DNT5tdvVK7E
// 65aB2nxtv/kHODg6lrVYxH76b4ApfqD3o8LfKzpzLBqyoKpxOltSgNL5qZnw
// epDBvTibzBGuf4XdM8axzczR888fv3oWvHzyp6mkHMBec6GZHmxPh5jDv0oJ
// /PRlURjAfuJQctBZaMjLj1q/cXb0E9LVtBq3mRLlVWU3H/0AdnEX3v3mgyJO
// 5TAp00ySiJ9+Y7jN5itHZ9mogdUt72vjd/kAEQEAAcLAXwQYAQgACQUCXLSV
// pQIbDAAKCRDfJfVpAqs/3dsPB/9aQ4D0zGPzDCrQ57ihTx1Mpg1LD0Gd8Tt7
// RIqS4cd7p5IkUjgQc5lX44Mro3rXe23EsE6qP4tyTk7V3uXOaXRZwT8qr6x0
// ZFDr8m0v234rlEBhQjULYCEs5agyrDzrPHpJiwPouzKnTDEeWN8SieQtGg54
// 7KTgttZgWqcFPMRRfEqJM+fOSBmoG8orGLmsk1LoTXP0Yjo9aU59QczaEfwA
// xKlAKp4HI+alh6AMGhYoiXDRt+zXU9pBMeoZnb8XiCuPoFhM7pQj2wxo1BWh
// wAmjjErcsDoSO90/JeeOTCaJ9gpP4kCioV5GgT/csACkH+jLIVxtquFl9InA
// MdTC4FV0
// =8B3o
// -----END PGP PUBLIC KEY BLOCK-----`;

                    // @TODO: in case of multiple pubkeys:
                    // const pubkeys = [`-----BEGIN PGP PUBLIC KEY BLOCK-----
                    // ...
                    // -----END PGP PUBLIC KEY BLOCK-----`,
                    // `-----BEGIN PGP PUBLIC KEY BLOCK-----
                    // ...
                    // -----END PGP PUBLIC KEY BLOCK-----`]
                    // pubkeys = pubkeys.map(async (key) => {
                    // return (await openpgp.key.readArmored(key)).keys[0]
                    // });
                    // const options = {
                    // message: openpgp.message.fromBinary(readableStream),
                    // publicKeys: pubkeys
                    // compression: openpgp.enums.compression.zip
                    // };

                    var pubkeys = [[recipientPubkey],[superUserPubKey]];
                    
                    // console.log('pubkeys');
                    // console.log(pubkeys);

                    pubkeys = pubkeys.map(async (key) => {
                      return (await openpgp.key.readArmored(key)).keys[0]
                    });                    

                    

                    //console.log(pubkeys);
                    // console.log('pubkeys[0]');
                    // console.log(pubkeys[0]);
                    //pubkeys = (await pubkeys.map(async (key) => { return (await openpgp.key.readArmored(key))).keys[0];
                    // console.log('pubkeys');
                    // console.log(pubkeys);
                    //recipientPubkey = doubleRecipientsPubkeys;



                    const readableStream = new ReadableStream({
                        start(controller) {
                            controller.enqueue(array);
                            controller.close();
                        }
                    });

                    //var pascalPubkey = await openpgp.key.readArmored(recipientPubkey);
                    //console.log(pascalPubkey);

                    const options = {
                        message: openpgp.message.fromBinary(readableStream),
                        //publicKeys: pubkeys
                        //publicKeys: [(await openpgp.key.readArmored(superUserPubKey)).keys,(await openpgp.key.readArmored(recipientPubkey)).keys]
                        //publicKeys: (await pascalPubkey).keys[0],
                        publicKeys: [(await openpgp.key.readArmored(recipientPubkey)).keys[0],(await openpgp.key.readArmored(superUserPubKey)).keys[0]],
                        //compression: openpgp.enums.compression.zip
                    };

                    

                    //console.log(pubkeys);



                    

                    // const options = {
                    //   message: openpgp.message.fromBinary(readableStream),
                    //   publicKeys: (await pubkeys.map(async (key) => { return (await openpgp.key.readArmored(key))})).keys[0],
                    //   //publicKeys: pubkeys,
                    //   //compression: openpgp.enums.compression.zip
                    // };


                    
                    // console.log(recipientPubkey);
                    // console.log(superUserPubKey);

                    

                    // console.log(options);
                    var startSeconds = new Date().getTime() / 1000;

                    const encrypted = await openpgp.encrypt(options);

                    console.log(options);
                    
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
