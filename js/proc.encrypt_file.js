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

                    var recipientsPubkeys = await Drupal.settings.proc.proc_recipients_pubkeys;

                    

                    const superUserPubKey =
                    ['-----BEGIN PGP PUBLIC KEY BLOCK-----',
                    'Version: OpenPGP.js v4.4.5',
                    'Comment: admin:empty@mail.localhost',
                    '',
                    'xsBNBFy17BEBCADP7UPwGdngWgXkRFaFWzTOSK7xilrXUZYAy9FPIAnuJJfI',
                    '49L6U65nlCAJQcPLKv6rKl+AAq+hdD4ipO9+nc8Y6oW7YHdhnpSMPtX83ynE',
                    'W/Cs9kG/AeikOW7lrych3/4Zj9eFyXq7xrFCB44mFPNbac1w5VJygPKMIv4f',
                    'co05jONMtHwDKrCdWnjGdYAX4QxFetPJ0UDc/knZYIh5YjXPerASYXoEL9wb',
                    '2cgGzjyMrk4U90krFXHZ9aOQyjT2sL72QCg0GYdLYyiSrb8jSV+a4UqzCg1Q',
                    'x2wEhAbkKnZfhNCUCN+uc3w01H5t7iyWwJa6A3SkzPVCZ4Q3E+d8dO3DABEB',
                    'AAHNHGFkbWluIDxlbXB0eUBtYWlsLmxvY2FsaG9zdD7CwHUEEAEIAB8FAly1',
                    '7BEGCwkHCAMCBBUICgIDFgIBAhkBAhsDAh4BAAoJEDmOsVi5nRL24jcIALx4',
                    'PjsYU3y65k5uoHiZniHrkZ1JuXUM/FgsuQo+xOXL6bWyz+yEo3GOCz6CgqZd',
                    'IwsrTVeutrwdHQIR6dCxeu9lmuN2g73AvHMbdqX3mja8FxNanwZdqveVpGTu',
                    '4wZNeVFpGYBxGbr5xLMg1jNszUWCNZ/WKBEq9V2qc1aSp8VcvqsCmV09eOMa',
                    'TaVHLxRSK6jDe4LJ11l7zF07snOwNM4pbJyf8m98flB7cvMSb35ZzSGlVv1O',
                    'WXbsB2N8ffXWoN4YQBBx6FcZ3+0tep60Ahf+TqaD8AUFuf0XautdG1LbWhVu',
                    'UsmEPVrOFPyqSVsDmHfkQsOT9Z73Sqaq2SDz4x7OwE0EXLXsEQEIAIDXnEoe',
                    'Ahv1mwnYLYCk41cDVKvLaGCdgE/KQ5espBbfnYkxQRKrGek6nL3l11dB/kdZ',
                    'MHxodtuiYBoPEsC80g7WDJrY2oeXB/1Nh/HPPslTnmZLo2fnMjQAgRaTVAtJ',
                    's16H4KO5ypkUmeg7KSoIZ6M+WnpowXixhe4q+NRj9tHOjzcWLLjh7n/0O51E',
                    'qsU36FXJyYarEuwUSUAHpZpP0hlmBS9rCdyNfvp7Or7syPT3aNElTff1rzSs',
                    'FI7o3QeI0x0JtzizE6A1ztyuKTsjL2Z7Iv+RC76flPPeziHtAEpy47a/h9Tk',
                    'Fv2EoTYu+0zV5kGDzSpjnvhU/HbW8JR9W3cAEQEAAcLAXwQYAQgACQUCXLXs',
                    'EQIbDAAKCRA5jrFYuZ0S9qFSCACsYOpMxGOUCaSRdpf4LWGhgH4+eIX47Ivz',
                    'M/o95lpUsZXFJQxp9zYceu/ogJLtpxpQiNng/JE+4VUZCuh2fTniR0bqDmzw',
                    'lfmslal1PEOrO5UeMAVtsH+d/p+txgtUrwx6QzvCNrSl2ztMCGucqH1KzOOn',
                    '1ci+JsyEYfsY0mXeFnduOnS33TyBJy2RUuh78wZK+NBi5Ey3UpAQ4txT1sgJ',
                    'JEE+tPJrL0d+vnxr7AZj6bIXIru2Be/179u1ou33c126oCCGa9eNUD9mrZVv',
                    'tTsnYh8L9AtxJYPspsd04mRlYiJzoRRpJ9F6IFjjTyIZ4Chj2e9ur0fWkrJ9',
                    'HleyqVja',
                    '=Tzy3',
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
                    console.log(keys);


                    //const testKeys = recipientsPubkeys.map(keys => recipientsPubkeys);
                    // const testKeys = await recipientsPubkeys.map(async function (recipientsPubkeys) {
                    //   return await openpgp.key.readArmored(recipientsPubkeys).keys[0];
                    // });
                    console.log(recipientsPubkeys);
                    var i;
                    var keyObjects = Array;
                    for (i = 0; i < recipientsPubkeys.length; i++){
                      keyObjects[i] = await openpgp.key.readArmored(recipientsPubkeys[i]).keys[0];
                      //keyObjects[i] = recipientsPubkeys[i];
                    }

                    console.log(keyObjects);



                    

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
