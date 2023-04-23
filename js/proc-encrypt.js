/**
 * @file
 * Protected Content key generation.
 */
(async function ($, Drupal, once) {
  'use strict';
  Drupal.behaviors.ProcBehavior = {
    attach: async function (context, settings) {
      once('proc-encrypt', 'html', context)
        .forEach(async function (element) {

          $('#edit-submit').on(
            'click', async function (e) {
                e.preventDefault();
                console.log('submit was clicked');
            }
          );
  

          const messages = new Drupal.Message();
          if (!(window.FileReader)) {
            messages.add(drupalSettings.proc.proc_labels.proc_fileapi_err_msg, {
              type: 'error'
            });
          }
          if (document.getElementById('edit-submit')) {
            document.getElementById('edit-submit')
              .disabled = "TRUE";
          }
          async function handleFileSelect(evt) {
            let procJsLabels = drupalSettings.proc.proc_labels,
              procData = drupalSettings.proc.proc_data,
              files = evt.target.files,
              postMaxSizeBytes = drupalSettings.proc.proc_post_max_size_bytes,
              fileEntityMaxSize = parseInt(drupalSettings.proc.proc_file_entity_max_filesize, 10),
              fileSize = parseInt(files[0].size, 10),
              postMaxSizeBytesInt = parseInt(postMaxSizeBytes, 10),
              // Assuming ciphertexts are at least 4 times bigger than
              // their plaintexts:
              dynamicMaximumSize = postMaxSizeBytesInt / 4;

            $('#edit-file--description')[0].innerText = `${procJsLabels.proc_size} ${files[0].size} ${procJsLabels.proc_max_encryption_size_unit}
          ${procJsLabels.proc_type} ${files[0].type}
          ${procJsLabels.proc_last_modified} ${files[0].lastModifiedDate}`;
            let realMaxSize = dynamicMaximumSize;
            if (fileSize > dynamicMaximumSize || fileSize > fileEntityMaxSize) {
              if (fileEntityMaxSize < dynamicMaximumSize) {
                realMaxSize = fileEntityMaxSize;
              }
              messages.add(`${procJsLabels.proc_max_encryption_size} ${realMaxSize} ${procJsLabels.proc_max_encryption_size_unit}`, {
                type: 'error'
              });
              return;
            }

            let file = evt.target.files[0];
            let reader = new FileReader();
            reader.readAsArrayBuffer(file);
            reader.onloadend = async function (evt) {
              if (evt.target.readyState == FileReader.DONE) {
                const recipientsPubkeys = [];

                // At this moment we only know about validated recipient UIDs
                // and the time stamps of their keys:
                let recipientsUidsKeysChanged = JSON.parse(drupalSettings.proc.proc_recipients_pubkeys_changed),
                  remoteKey = [],
                  userIdIterator;
                // False for production.
                openpgp.config.showComment = false;
                openpgp.config.showVersion = false;

                for (userIdIterator in recipientsUidsKeysChanged) {
                  let localKey = localStorage.getItem(`proc.key_user_id.${userIdIterator}.${recipientsUidsKeysChanged[userIdIterator]}`);
                  if (localKey) {
                    recipientsPubkeys.push(localKey);
                  } else {
                    let storageKeys = Object.keys(localStorage);
                    if (storageKeys.length > 0) {
                      storageKeys.forEach(async function (storageKey, storageKeyIndex) {
                        if (storageKey.startsWith(`proc.key_user_id.${userIdIterator}`)) {
                          localStorage.removeItem(storageKeys[storageKeyIndex]);
                        }
                      });
                    }
                    remoteKey.push(userIdIterator);
                  }
                }
                let pubkeysJson;
                
                if (remoteKey.length > 0) {
                  let remoteKeyCsv = remoteKey.join(",");
                  
                  const pubKeyAjax = async (remoteKeyCsv) => {
                    let response = await fetch(`${window.location.origin + drupalSettings.proc.basePath}api/proc/getpubkey/${remoteKeyCsv}/user_id`);
                    pubkeysJson = await response.json();
                    if (pubkeysJson.pubkey.length > 0) {
                      pubkeysJson.pubkey.forEach(function (pubkey, index) {
                        recipientsPubkeys.push(pubkey.key);
                        try {
                          localStorage.setItem(`proc.key_user_id.${remoteKey[index]}.${pubkey.changed}`, pubkey.key);
                        } catch (error) {
                          console.warn(error);
                        }
                      });
                    }
                  };
                  await pubKeyAjax(remoteKeyCsv);
                }
                const publicKeys = await Promise.all(recipientsPubkeys.map(armoredKey => openpgp.readKey({
                  armoredKey
                })));
                let startSeconds = new Date()
                  .getTime() / 1000;
                let array = new Uint8Array(evt.target.result);
                const message = await openpgp.createMessage({
                  binary: array
                });
                
                document.getElementById('edit-submit').value = procJsLabels.proc_button_state_processing;
                
                const encrypted = await openpgp.encrypt({
                  encryptionKeys: publicKeys,
                  message,
                  format: 'armored',
                  config: {
                    preferredCompressionAlgorithm: openpgp.enums.compression.zip
                  }
                });
                let endSeconds = new Date()
                  .getTime() / 1000,
                  total = endSeconds - startSeconds;
                $('input[name=cipher_text]')[0].value = encrypted;
                $('input[name=source_file_name]')[0].value = files[0].name;
                $('input[name=source_file_size]')[0].value = files[0].size;
                $('input[name=source_file_type]')[0].value = files[0].type;
                $('input[name=source_file_last_change]')[0].value = files[0].lastModified;
                $('input[name=browser_fingerprint]')[0].value = `${navigator.userAgent}, (${screen.width} x ${screen.height})`;
                $('input[name=generation_timestamp]')[0].value = startSeconds;
                $('input[name=generation_timespan]')[0].value = total;
                $('input[name=signed]')[0].value = 0;
                document.getElementById('edit-submit')
                  .removeAttribute("disabled");
                document.getElementById('edit-submit').value = procJsLabels.proc_save_button_label;
                // If there is a dialog, close it:
                // document.getElementsByClassName('ui-button ui-corner-all ui-widget ui-button-icon-only ui-dialog-titlebar-close')[0].click();
              }
            };
          }
          if (document.getElementById('edit-file')) {
            document.getElementById('edit-file')
              .addEventListener('change', handleFileSelect, false);
          }
        });

        // once('#edit-submit', 'html', context).on('click', async function (e) {
        //   e.preventDefault();
        //   console.log('submit was clicked');
        // });

        // once('#edit-submit', function (settings, context, drupalSettings)).on('click', async function (e) {
        //   e.preventDefault();
        //   console.log('submit was clicked');
        // }
        // $('#edit-submit').on(
        //   'click', async function (e) {
        //       e.preventDefault();
        //       console.log('submit was clicked');
        //   }
        // );

    }
  };
})(jQuery, Drupal, once);