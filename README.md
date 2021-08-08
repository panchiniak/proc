CONTENTS OF THIS FILE
---------------------
 * Introduction

 * Requirements

 * Installation

 * Configuration

 * Future roadmap

 * Maintainers

INTRODUCTION
------------
The Protected Content (proc) module adds end-to-end/client side encryption and
decryption of content.
It is very simple to use. Once it is installed (see Installation below):

 * Access proc/keyring/add to generate keys for the current user.

 * Access proc/add/&lt;uids_csv&gt; to encrypt a file for the users identified
   in &lt;uids_csv&gt; (a comma separated values list of user IDs), proc/add/&lt;uids_csv&gt; for encrypting with the author's signature. Protect Content will provide an Exclusive Access Link for the recipients to decrypt
   the file.

 * As recipient, access proc/update/&lt;pids_csv&gt;/&lt;uids_csv&gt; to
   re-encrypt the files identified in &lt;pids_csv&gt; for the users identified
   in &lt;uids_csv&gt;.


REQUIREMENTS
------------
OpenPGP.js v4.10.10

INSTALLATION
------------
Install as usual and make sure to have openpgpjs/dist/openpgpjs.min.js inside
your libraries folder.

CONFIGURATION
-------------
Current version doesn't require/allow configurations.

FUTURE ROADMAP
--------------
Multiple signatures per file with recursive encryption.

MAINTAINERS
-----------
Rodrigo Panchiniak Fernandes - https://www.drupal.org/user/411448

Duarte Briz (duartebriz) - https://www.drupal.org/u/duartebriz
