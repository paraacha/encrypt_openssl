<?php

namespace Drupal\encrypt_openssl\Plugin\EncryptionMethod;

use Drupal\encrypt\EncryptionMethodInterface;
use Drupal\encrypt\Plugin\EncryptionMethod\EncryptionMethodBase;
use Drupal\Component\Utility\Crypt;

/**
 * PHPSecLibEncryption class.
 *
 * @EncryptionMethod(
 *   id = "openssl",
 *   title = @Translation("OpenSSL PHP extension"),
 *   description = "Uses AES-128-CBC cipher via the OpenSSL PHP extension.",
 *   key_type = {"encryption"}
 * )
 */
class OpenSSLEncryptionMethod extends EncryptionMethodBase implements EncryptionMethodInterface {

  /**
   * {@inheritdoc}
   */
  public function encrypt($text, $key) {
    // IV is a crypto-secure random binary string of 16 bytes as required by the
    // AES-128-CBC cipher.
    $iv = Crypt::randomBytes(16);

    // Encrypt the data.
    $encrypted_data = openssl_encrypt($text, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

    // Concatenate IV with the encrypted data so to make it available during
    // decryption.
    $processed_text = $iv . $encrypted_data;

    return $processed_text;
  }

  /**
   * {@inheritdoc}
   */
  public function decrypt($text, $key) {
    // Separate encrypted data and IV from the cipher text.
    $iv = substr($text, 0, 16);
    $encrypted_data = substr($text, 16);

    // Decrypt the data.
    $plain_text  = openssl_decrypt($encrypted_data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

    return $plain_text;
  }

  /**
   * {@inheritdoc}
   */
  public function checkDependencies($text = NULL, $key = NULL) {
    $errors = [];
    // Check for OpenSSL extension.
    if (!extension_loaded('openssl')) {
      $errors[] = 'OpenSSL PHP extension is missing.';
    }

    // Check if we have a 128 bit key as required by the AES-128-CBC cipher.
    if (strlen($key) != 16) {
      $errors[] = t('This encryption method requires a 128 bit key.');
    }

    return $errors;
  }

}
