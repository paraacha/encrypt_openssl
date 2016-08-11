# OpenSSL-based Encryption for the Encrypt module

[![Build Status](https://travis-ci.org/talhaparacha/encrypt_openssl.svg?branch=8.x)](https://travis-ci.org/talhaparacha/encrypt_openssl)

This module provides an Encryption Method plugin to be used with the Drupal 8 Encrypt module. Accordingly, the plugin uses AES-256-CBC cipher via the OpenSSL PHP extension along with HMAC-SHA256 for authentication of the encrypted data.

For enhanced security, the encrypt()/decrypt() functions are taken directly from this excellent resource [Encryption, Authentication & Data Integrity in PHP](http://zimuel.it/slides/phpbenelux2016/#/) by [Enrico Zimuel](http://www.zimuel.it/).
