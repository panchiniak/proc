CONTENTS OF THIS FILE
---------------------
 * Introduction

 * Requirements

 * Installation

 * Future roadmap

 * Maintainers

INTRODUCTION
------------
The Protected Content (proc) module adds to your Drupal installation end-to-end/client side encryption and
decryption of content (texts or files).
It is very simple to use. Once it is installed (see Installation below):

 * Access proc/keyring/add to generate keys for the current user.

 * Access proc/add/&lt;uids_csv&gt; to encrypt a file for the users identified
   in &lt;uids_csv&gt; (a comma separated values list of user IDs). Use proc/sign/&lt;uids_csv&gt; for encrypting with the author's signature. Protect Content will provide an Exclusive Access Link for the recipients to decrypt
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

MAINTAINERS
-----------
Rodrigo Panchiniak Fernandes - https://www.drupal.org/user/411448

Duarte Briz (duartebriz) - https://www.drupal.org/u/duartebriz
