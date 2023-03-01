/**
 * @file
 * Decrypts cipher texts into a file given a correct privkey passphrase.
 */
(function ($, Drupal, once) {
  'use strict';
  Drupal.behaviors.ProcBehavior = {
    attach: function (context, settings) {
      once('proc-decrypt', 'html', context)
        .forEach(function (element) {
          let procJsLabels    = drupalSettings.proc.proc_labels,
            passDrupal        = drupalSettings.proc.proc_pass,
            privkey           = drupalSettings.proc.proc_privkey,
            cipherIds         = drupalSettings.proc.proc_ids,
            ciphersChanged    = drupalSettings.proc.procs_changed,
            sourcesFileNames  = drupalSettings.proc.proc_sources_file_names,
            sourcesFilesSizes = drupalSettings.proc.proc_sources_file_sizes,
            sourcesInputModes = drupalSettings.proc.proc_sources_input_modes,
            ciphersSigned     = drupalSettings.proc.proc_signed,
            skipSizeMismatch  = drupalSettings.proc.proc_skip_size_mismatch,
            basePath          = drupalSettings.proc.base_path;
          const messages = new Drupal.Message();
          if (!(window.Blob)) {
            messages.add(drupalSettings.proc.proc_labels.proc_fileapi_err_msg, {
              type: 'error'
            });
          }
          let procURLs = [];
          async function procOpenFile(decrypted, temporaryDownloadLink, cipherIndex) {
            const plaintext = decrypted.data,
              blob = new Blob([plaintext], {
                type: 'application/octet-binary',
                endings: 'native'
              }),
              link = $('#decryption-link');
            temporaryDownloadLink.setAttribute('href', URL.createObjectURL(blob));
            let openActionLabel = procJsLabels.proc_open_file_state;
            if (link.text() != openActionLabel) {
              link.text(openActionLabel);
              // Highlight the link for better UX
              link.removeClass('active');
              $('.messages')
                .after(`<div class="messages status" id="proc-decrypting-status">${procJsLabels.proc_decryption_success}</div>`);
            }
            // Check if file generated is the same size of source file.
            if (blob.size.toString() === sourcesFilesSizes[cipherIndex] || skipSizeMismatch == true) {
              // Restore original file name:
              temporaryDownloadLink.setAttribute('download', sourcesFileNames[cipherIndex]);
              temporaryDownloadLink.click();
            } else {
              // @TODO: save error log.
              $('form#-proc-decrypt-to-file')
                .prepend(`<div class="messages error">${procJsLabels.proc_decryption_size_mismatch}</div>`);
            }
          }
          cipherIds.forEach(function (cipherId, cipherIdIndex) {
            procURLs[cipherIdIndex] = `${window.location.origin + basePath}api/proc/getcipher/${cipherIds[cipherIdIndex]}/?cipherchanged=${ciphersChanged[cipherIdIndex]}`;
          });
          let cipherResponse,
            cipher;
          const introducingKeyDecryptionMsgElement = `<div class="messages info proc-info" id="proc-decrypting-info">${procJsLabels.proc_introducing_decryption}</div>`;
          // const messages = new Drupal.Message();
          // const messageId = messages.add('test message');
          // messages.remove(messageId);
          // messages.add('test message', {type: 'warning'});
          // messages.add('test message', {type: 'error'});
          // Clear ALL
          // messages.clear();
          // Reset messages and action.
          $('input#edit-pass')
            .once()
            .on('focusin', function () {
              // $('.messages').remove();
              messages.clear();
              this.value = '';
              let actionLink = $('#decryption-link');
              if (!(actionLink.hasClass('active'))) {
                actionLink.text('Decrypt')
                  .removeClass('active')
                  .removeAttr('download')
                  .removeAttr('href');
              }
            });
          // Never submit the form:
          $('#proc-decrypt-form')
            .submit(function (e) {
              e.preventDefault();
              // Click decrypt instead:
              $('#decryption-link')
                .click();
            });
          if ('caches' in self) {
            let cache = caches.open('proc');
            cipherIds.forEach(function (cipherId, cipherIdIndex) {
              let cachedCiphers = [];
              cachedCiphers[cipherIdIndex] = cache.then(async function (cache) {
                let response = await cache.match(procURLs[cipherIdIndex]);
                if (response) {
                  console.info('Reusing cipher from cache');
                  return;
                } else {
                  console.info('Adding cipher to cache');
                  cipherResponse = (await fetch(procURLs[cipherIdIndex]));
                  cipher = cipherResponse.clone();
                  cache.put(procURLs[cipherIdIndex], cipherResponse);
                  return cipher;
                  // @TODO: add clean up of expired cache if any!
                }
              });
            });
          } else {
            messages.add(drupalSettings.proc.proc_labels.proc_caches_unsupported, {
              type: 'error'
            });
          }
          $('#decryption-link')
            .on('click', async function (e) {
              e.preventDefault();
              let secretPassString = $('input[name=password]')[0].value,
                passphrase = passDrupal.concat(secretPassString);
              const privateKey = await openpgp.readPrivateKey({
                armoredKey: privkey
              });
              const decryptedPrivateKey = await openpgp.decryptKey({
                  privateKey,
                  passphrase
                })
                .catch(function (err) {
                  $('form#-proc-decrypt-to-file')
                    .prepend(`<div class="messages error">${Drupal.t(err)}</div>`);
                  if ($('a#decryption-link')[0].href) {
                    const fileUrl = $('a#decryption-link')[0].href;
                    URL.revokeObjectURL(fileUrl);
                    $('a#decryption-link')
                      .removeAttr('href');
                  }
                });
              if (!$('#proc-decrypting-info')[0]) {
                $('form#-proc-decrypt-to-file')
                  .prepend(introducingKeyDecryptionMsgElement);
              }
              let temporaryDownloadLink = document.createElement("a");
              temporaryDownloadLink.style.display = 'none';
              document.body.appendChild(temporaryDownloadLink);
              cipherIds.forEach(async function (cipherId, cipherIndex) {
                let cache = caches.match(procURLs[cipherIndex]);
                cache.then(async function (response) {
                  response.json()
                    .then(async function (data) {
                      let cipherText = data.pubkey[0].armored,
                        decrypted,
                        expectedSignedValue = false,
                        response,
                        pubkeysJson,
                        pubKeyObj;
                      if (ciphersSigned[cipherIndex] != 0) {
                        expectedSignedValue = true;
                        response = await fetch(`${window.location.origin + Drupal.settings.basePath}proc/api/getpubkey/${ciphersSigned[cipherIndex]}/pid`);
                        pubkeysJson = await response.json();
                        pubKeyObj = (await openpgp.key.readArmored(pubkeysJson.pubkey[0].key))
                          .keys[0];
                        const optionsVerify = {
                          message: await openpgp.message.readArmored(cipherText)
                            .catch(function (err) {
                              let messageError = `<div class="messages error">${Drupal.t(err)}</div>`;
                              $('form#-proc-decrypt-to-file')
                                .prepend(messageError);
                            }),
                          publicKeys: pubKeyObj,
                          compression: openpgp.enums.compression.zip,
                        };
                        decrypted = await openpgp.verify(optionsVerify);
                        decrypted.signatures[0].verified.then(
                          (val) => {
                            if (val) {
                              console.info('signed by key id ' + decrypted.signatures[0].keyid.toHex());
                            } else {
                              $('form#-proc-sign-file')
                                .prepend(`<div class="messages error">${Drupal.t('Signature could not be verified')}</div>`);
                              throw new Error('Signature could not be verified');
                            }
                          });
                        if (decrypted) {
                          procOpenFile(decrypted, temporaryDownloadLink, cipherIndex);
                        }
                      } else {
                        const message = await openpgp.readMessage({
                          armoredMessage: cipherText
                        });
                        let procFormat = 'binary';
                        if (sourcesInputModes[cipherIndex]) {
                          procFormat = sourcesInputModes[cipherIndex];
                        }
                        decrypted = await openpgp.decrypt({
                            decryptionKeys: decryptedPrivateKey,
                            // verificationKeys: publicKeys,
                            message,
                            format: procFormat
                          })
                          .catch(function (err) {
                            alert(err);
                          });
                        if (decrypted) {
                          if ($('textarea')
                            .length != 0 && (typeof decrypted.data === 'string' || decrypted.data instanceof String)) {
                            $(`#edit-${cipherId}`)[0].innerText = decrypted.data;
                          } else {
                            procOpenFile(decrypted, temporaryDownloadLink, cipherIndex);
                          }
                        }
                      }
                    });
                });
              });
              document.body.removeChild(temporaryDownloadLink);
            });
        });
    }
  };
})(jQuery, Drupal, once);