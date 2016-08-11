<?php

namespace Drupal\encrypt_openssl\Tests;

use Drupal\Component\Utility\Crypt;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the OpenSSL encryption method.
 *
 * @group encrypt_openssl
 */
class OpenSSLEncryptionTest extends WebTestBase {

  /**
   * Modules to enable for this test.
   *
   * @var string[]
   */
  public static $modules = array('key', 'encrypt', 'encrypt_openssl');

  /**
   * An administrator user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'administer encrypt',
      'administer keys',
    ]);
  }

  /**
   * Test adding an encryption profile and encrypting / decrypting with it.
   */
  public function testEncryptAndDecrypt() {
    $this->drupalLogin($this->adminUser);

    // Create a test Key entity.
    $this->drupalGet('admin/config/system/keys/add');
    $edit = [
      'key_type' => 'encryption',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'key_type');
    $edit = [
      'key_provider' => 'config',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'key_provider');

    $edit = [
      'id' => 'testing_key',
      'label' => 'Testing Key',
      'key_type' => "encryption",
      'key_type_settings[key_size]' => '256',
      'key_input_settings[key_value]' => Crypt::randomBytes(32),
      'key_provider' => 'config',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    $saved_key = \Drupal::service('key.repository')->getKey('testing_key');
    $this->assertTrue($saved_key, 'Key was succesfully saved.');

    // Create an encryption profile config entity.
    $this->drupalGet('admin/config/system/encryption/profiles/add');

    // Check if our plugin exists.
    $this->assertOption('edit-encryption-method', 'openssl', t('OpenSSL encryption method option is present.'));
    $this->assertText('AES (OpenSSL) + HMAC-SHA256', t('OpenSSL encryption method text is present'));

    $edit = [
      'encryption_method' => 'openssl',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'encryption_method');

    $edit = [
      'id' => 'test_encryption_profile',
      'label' => 'Test encryption profile',
      'encryption_method' => 'openssl',
      'encryption_key' => 'testing_key',
    ];
    $this->drupalPostForm('admin/config/system/encryption/profiles/add', $edit, t('Save'));

    $encryption_profile = \Drupal::service('entity.manager')->getStorage('encryption_profile')->load('test_encryption_profile');
    $this->assertTrue($encryption_profile, 'An encryption profile was successfully saved with our encryption method');

    // Test the working of our encryption profile by encrypting and decrypting
    // a piece of content through it.
    $original_string = $this->randomString();
    $encrypted_string = \Drupal::service('encryption')->encrypt($original_string, $encryption_profile);
    $decrypted_string = \Drupal::service('encryption')->decrypt($encrypted_string, $encryption_profile);
    $this->assertEqual($decrypted_string, $original_string, 'The encryption profile with our encryption method is working properly');
  }

}
