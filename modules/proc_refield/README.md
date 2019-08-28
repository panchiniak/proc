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
The Protected Reference Field (proc_refield) module creates an entity reference
field to be populated with cipher text references that belong to the current
user. It also provides an API for changing the field element. This can be used
for appending to the description of the field an 'Add a new file' link that will
fetch the UIDs of desired encryption recipients and, for example, open the
encryption form (already set with UIDs of recipients) in a Simple Dialog window.
After that you can implment hook_cipher_postsave (see Proc API) in order to
automatically populate proc_refield with the newly created cipher text. This way
it is possible to have encryption smothly integrated into any fieldable content
creation form.

REQUIREMENTS
------------
Protected Content (proc)

INSTALLATION
------------
Install as usual.

CONFIGURATION
-------------
Current version doesn't require/allow global configurations but you should
enable Protected Content settings in the field settings form in order to use
the API.

FUTURE ROADMAP
--------------
Add example module on the use of the API.

MAINTAINERS
-----------
Rodrigo Panchiniak Fernandes - https://www.drupal.org/user/411448
