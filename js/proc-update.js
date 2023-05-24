/**
 * @file
 * Updates cipher texts.
 */
(function ($, Drupal, once) {
  'use strict';
  Drupal.behaviors.ProcBehavior = {
    attach: function (context, settings) {
      once('proc-update', 'html', context)
        .forEach(function (element) {

          const messages = new Drupal.Message();
          const submit = document.querySelector('#edit-submit');

          let passDrupal = drupalSettings.proc.proc_data.proc_pass,
            privkey = drupalSettings.proc.proc_data.proc_privkey,
            cipherTexts = drupalSettings.proc.proc_data.proc_ciphers,
            cipherTextsIndex = drupalSettings.proc.proc_data.proc_ciphers_index,
            procJsLabels = drupalSettings.proc.proc_labels;

          const introducingKeyDecryptionMsgElement = procJsLabels.proc_introducing_decryption;

          if (!(window.Blob) || !(window.FileReader)) {
            alert(procJsLabels.proc_fileapi_err_msg);
          }

          // Reset messages and action.
          $('input#edit-password').once().on(
            'focusin',
            function () {
              messages.clear();
              document.querySelector('#edit-password').value = '';
              // const submit = document.querySelector('#edit-submit');
              if (submit.disabled == true) {
                $('.messages').remove();
                submit.value = procJsLabels.proc_button_update_label;
                submit.classList.add('active');
                submit.disabled = false;
                document.querySelector('#edit-password').value = '';
                messages.clear();
              }
            },
          );

          // Do not submit the form if encryption did not happen:
          var ready = 0;
          $('#proc-update-form').submit(
            function () {
              if (ready == 0) {
                // @todo: add error message and disable Submit
                // document.querySelector('#edit-submit').disabled = true;
                return false;
              }
            },
          );

          $('#edit-submit').on(
            'click', async function () {
              let secretPass = $('input[name=password]')[0].value,
                secretPassString = secretPass,
                passphrase = passDrupal.concat(secretPassString),
                recipientsPubkeys = drupalSettings.proc.proc_data.proc_recipients_pubkeys;

              const privateKey = await openpgp.readPrivateKey({
                armoredKey: privkey,
              });
              const decryptedPrivateKey = await openpgp.decryptKey({
                privateKey,
                passphrase,
              }).catch(
                function (err) {
                  console.info(err);
                  messages.add(String(err), { type: 'error' });
                  document.querySelector('#edit-submit').disabled == true;
                },
              );

              // @todo: add message "Introducing key passphrase for decryption"...
              // if (!$('#proc-decrypting-info')[0]) {
              //   $('form#-proc-update').prepend(introducingKeyDecryptionMsgElement);
              // }

              recipientsPubkeys = JSON.parse(recipientsPubkeys);
              let recipientsPubkeysArmored = [];

              for (let i = 0; i < recipientsPubkeys.length; i++) {
                recipientsPubkeysArmored.push(recipientsPubkeys[i].key);
              }

              const recipientsKeys = await Promise.all(recipientsPubkeysArmored.map(armoredKey => openpgp.readKey({
                armoredKey,
              })));

              var procID = [];
              const BROWSER_FINGERPRINT = `${navigator.userAgent}, (${screen.width} x ${screen.height})`;

              // False for production.
              openpgp.config.showComment = false;
              openpgp.config.showVersion = false;

              cipherTextsIndex.forEach(
                async function (item, i) {
                  document.querySelector('#edit-submit').innerText = procJsLabels.proc_button_state_processing;
                  procID.push(cipherTextsIndex[i]);

                  const message = await openpgp.readMessage({
                    armoredMessage: cipherTexts[cipherTextsIndex[i]].cipher_text,
                  }).catch(
                    function (err) {
                      messages.add(String(err), { type: 'error' });
                    },
                  );

                  const decrypted = await openpgp.decrypt({
                    decryptionKeys: decryptedPrivateKey,
                    message,
                    // @todo make format dynamic for allowing the update of texts (ie. armored format instead of binary)
                    format: 'binary',
                  });

                  if (decrypted) {
                    const plaintext = decrypted.data;
                    const blob = new Blob([plaintext], {
                      type: 'application/octet-binary',
                      endings: 'native',
                    });

                    let reader = new FileReader();
                    reader.readAsArrayBuffer(blob);

                    reader.onloadend = async function (evt) {
                      if (evt.target.readyState == FileReader.DONE) {

                        let array = new Uint8Array(evt.target.result);
                        const startSeconds = new Date().getTime() / 1000;

                        const message = await openpgp.createMessage({
                          binary: array,
                        });
                        const encrypted = await openpgp.encrypt({
                          encryptionKeys: recipientsKeys,
                          message,
                          format: 'armored',
                          config: {
                            preferredCompressionAlgorithm: openpgp.enums.compression.zip,
                          },
                        });
                        let endSeconds = new Date().getTime() / 1000,
                          total = endSeconds - startSeconds;

                        const ciphertext = encrypted;

                        var procIDString = procID.pop().toString();
                        await (document.querySelector('[name=cipher_text_cid_' + procIDString + ']').value = ciphertext);
                        await (document.querySelector('[name=generation_timespan_cid_' + procIDString + ']').value = total);
                        await (document.querySelector('[name=browser_fingerprint_cid_' + procIDString + ']').value = BROWSER_FINGERPRINT);
                        await (document.querySelector('[name=generation_timestamp_cid_' + procIDString + ']').value = startSeconds);

                        console.info('Content encrypted. Local ID: ' + procIDString);

                        if (procID.length == 0) {
                          ready = 1;
                          // document.querySelector('.proc-update-submit').innerText = procJsLabels.proc_submit_saving_state;
                          // Do not submit the password:
                          var passPlaceHolder = new Array($('input[name=password]')[0].value.length + 1).join(Math.random().toString(36).replace(/[^a-z]+/g, '').substr(0, 1));
                          $('input[name=password]')[0].value = passPlaceHolder;
                          // Make sure password is not submited:
                          if ($('input[name=password]')[0].value != secretPass) {
                            $('#proc-update-form').submit();
                            // console.info()
                          }
                        }
                      }
                    };
                  }
                },
              );
            },
          );
        });
    },
  };
})(jQuery, Drupal, once);

