/**
 * @file
 * Encryption of text given public PGP armored key(s).
 */
(async function ($) {
  'use strict';
  Drupal.behaviors.proc = {
    attach: function (context, settings) {
      async function encrypt() {
        let procJsLabels = Drupal.settings.proc.proc_labels,
          plaintext = document.getElementById('edit-plaintext')
          .value,
          plainTextSize = (new TextEncoder()
            .encode(plaintext))
          .length,
          postMaxSizeBytes = Drupal.settings.proc.proc_post_max_size_bytes,
          fileEntityMaxSize = parseInt(Drupal.settings.proc.proc_file_entity_max_filesize, 10),
          // plainTextSize       = parseInt(files[0].size, 10),
          postMaxSizeBytesInt = parseInt(postMaxSizeBytes, 10),
          // Assuming ciphertexts are at least 4 times bigger than
          // their plaintexts:
          dynamicMaximumSize = postMaxSizeBytesInt / 4;
        if (plainTextSize) {
          let realMaxSize = dynamicMaximumSize;
          if (plainTextSize > dynamicMaximumSize || plainTextSize > fileEntityMaxSize) {
            if (fileEntityMaxSize < dynamicMaximumSize) {
              realMaxSize = fileEntityMaxSize;
            }
            $("form#-proc-encrypt-file")
              .prepend('<div class="messages error">' + `${procJsLabels.proc_max_encryption_size} ${realMaxSize} ${procJsLabels.proc_max_encryption_size_unit}` + '</div>');
            document.getElementById('edit-button')
              .value = procJsLabels.proc_save_button_label;
            return;
          }
          const recipientsPubkeys = [];
          // At this moment we only know about validated recipient UIDs
          // and the time stamps of their keys:
          let recipientsUidsKeysChanged = JSON.parse(Drupal.settings.proc.proc_recipients_pubkeys_changed),
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
              let response = await fetch(`${window.location.origin + Drupal.settings.basePath}proc/api/getpubkey/${remoteKeyCsv}/uid`);
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
          // let array = new Uint8Array(plaintext);
          const message = await openpgp.createMessage({
            text: plaintext
          });
          // const message = await openpgp.createMessage({ binary: array });
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
          if (encrypted) {
            $('input[name=cipher_text]')[0].value = encrypted;
            $('input[name=source_file_name]')[0].value = 'redacted.txt';
            $('input[name=source_file_size]')[0].value = plainTextSize;
            $('input[name=source_file_type]')[0].value = 'text/plain';
            $('input[name=source_file_last_change]')[0].value = '0';
            $('input[name=browser_fingerprint]')[0].value = `${navigator.userAgent}, (${screen.width} x ${screen.height})`;
            $('input[name=generation_timestamp]')[0].value = startSeconds;
            $('input[name=generation_timespan]')[0].value = total;
            $('input[name=signed]')[0].value = 0;
            // Do not send the plain text!
            $('#edit-plaintext')[0].value = '';
            if ($('input[name=cipher_text]')[0].value) {
              $('#-proc-encrypt')
                .submit();
            }
          }
        }
      }
      if (document.getElementById('edit-button')) {
        document.getElementById('edit-button')
          .addEventListener('click', encrypt, false);
      }
    }
  };
})(jQuery);