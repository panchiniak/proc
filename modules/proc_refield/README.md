CONTENTS OF THIS FILE
---------------------
 * Introduction

 * Requirements

 * Installation

 * Configuration

 * Maintainers

INTRODUCTION
------------
The Protected Reference Field (proc_refield) module adds Protected Content 
API settings to entity reference field settings, where Proc API can be enabled
for an autocomplete entity reference field  and Recipient Fetcher View can be
set. It also creates a field (proc_refield) enabled to Proc API by default and
set to be autocompleted with ciphers belonging latu senso to the current user.

REQUIREMENTS
------------
Protected Content (proc)

INSTALLATION
------------
Install as usual.

CONFIGURATION
-------------
Create an entity reference field, set autocomplete widget, choose Disabled,
Simple encryption or Encryption with Signature at Protected Content API.
You may also to choose a user reference view as recipients fetcher.
Check the API for implementing thefuncionality with different widgets.

MAINTAINERS
-----------
Rodrigo Panchiniak Fernandes - https://www.drupal.org/user/411448
