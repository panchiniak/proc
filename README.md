CONTENTS OF THIS FILE
---------------------
 * Introduction

 * Requirements

 * Installation

 * Future roadmap

 * Learn more about Protected Content

 * Similar project

 * Maintainers

INTRODUCTION
------------
The Protected Content (proc) module adds to your Drupal installation end-to-end/client side encryption and
decryption of content (texts or files).
It is very simple to use. Once it is installed (see Installation below):

 * Access proc/keyring/add to generate keys for the current user.

 * Access proc/add/&lt;uids_csv&gt; to encrypt a file for the users identified
   in &lt;uids_csv&gt; (a comma separated values list of user IDs). Use proc/sign/&lt;uids_csv&gt; for encrypting with the author's signature. Protect Content will provide an Exclusive Access Link for the recipients to decrypt
   the file.

 * Access proc/add/&lt;uids_csv&gt;/armored to encrypt a text area content for the users identified
   in &lt;uids_csv&gt;.

 * As recipient, access proc/update/&lt;pids_csv&gt;/&lt;uids_csv&gt; to
   re-encrypt the contents identified in &lt;pids_csv&gt; for the users identified
   in &lt;uids_csv&gt;.


REQUIREMENTS
------------
OpenPGP.js v5.0.1

INSTALLATION
------------
Install as usual and make sure to have openpgpjs/openpgp.min.js inside
your libraries folder.

FUTURE ROADMAP
--------------
Multiple signatures per content with recursive encryption.

LEARN MORE ABOUT PROTECTED CONTENT
----------------------------------

[Protected Content, Secure open source day - Haarlem (2019)](https://youtu.be/rVWrkZPGj3s "Protected Content, Secure open source day - Haarlem (2019)")  
  
[Protected Content: end-to-end PGP encryption for Drupal, Drupal Camp - Kyiv (2019)](https://youtu.be/Gx8uxEpi4Po " end-to-end PGP encryption for Drupal, Drupal Camp - Kyiv (2019)")  
  
[Client side encryption with OpenPGPjs, Secure open source days - Sofia (2019)](https://twitter.com/SecOSday/status/1185518649555197953/photo/1 "Client side encryption with OpenPGPjs, Secure open source days - Sofia (2019)")  
  
[Protected Content, Drupal Dev Days - Cluj-Napoca (2019)](https://cluj2019.drupaldays.org/protected-content "Protected Content, Drupal Dev Days - Cluj-Napoca (2019)")  
  
[Protected Content by Asymmetrical Client Side Encryption, Drupal Dev Days - Ghent (2022)](https://drupalcamp.be/en/drupal-dev-days-2020/session/protected-content-asymmetrical-client-side-encryption "Protected Content by Asymmetrical Client Side Encryption, Drupal Dev Days - Ghent (2022)")  
  
[A pretty good content protection (Workshop), Drupal Con - Prague (2022)](https://events.drupal.org/prague2022/sessions/pretty-good-content-protection-workshop "A pretty good content protection (Workshop), Drupal Con - Prague (2022)")

SIMILAR PROJECT
---------------

[Client-Side File Crypto](https://www.drupal.org/project/client_side_file_crypto "Client-Side File Crypto")  


MAINTAINERS
-----------
Rodrigo Panchiniak Fernandes - https://www.drupal.org/user/411448

Duarte Briz (duartebriz) - https://www.drupal.org/u/duartebriz
