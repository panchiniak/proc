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
 * Access protected-content/key-pair/new to generate keys for the current user.
 * Access protected-content/file/new/&lt;uids_csv&gt; to encrypt a file for the users.
   identified in &lt;uids_csv&gt; (a comma separated values list of user IDs). Protect
   Content will provide an Exclusive Access Link for the recipients to decrypt 
   the file.

REQUIREMENTS
------------
OpenPGP.js v4.4.5

INSTALLATION
------------
Install as usual and make sure to have openpgpjs.min.js at your openpgpjs lib
folder.

CONFIGURATION
-------------
Current version doesn't require/allow configurations.

FUTURE ROADMAP
--------------
Ship a view for listing users with their keys.
Add support for text fields and encryption using multiple public keys.
Create custom entities to handle keys and cipher texts.

MAINTAINERS
-----------
Rodrigo Panchiniak Fernandes (rodrigo-panchiniak-fernandes) - https://www.drupal.org/user/411448

Duarte Briz (duartebriz) - https://www.drupal.org/u/duartebriz