Description
-----------
This module adds end-to-end encryption for content.

Installation
------------
Install as usual and make sure to have openpgpjs.min.js at your openpgpjs lib
folder.

How to use
----------
1. Access protected-content/key-pair/new/<uid> to generate keys for the user
identified by <uid>.
2. Access protected-content/file/new/<uid> to encrypt a file for the user
identified by <uid>.

Future Roadmap
--------------
Ship a view for listing users with keys.
Add support for text fields and encryption using multiple public keys.
Create custom entities to handle keys and cipher texts.

Author
------
Rodrigo Panchiniak Fernandes
