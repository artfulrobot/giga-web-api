<?php
exit(); // This is a hacky script used by developers in testing. It is not portable.
/**
 * Examples.
 */

define('GIGA_EMAIL_SUBSCRIPTION_API_ENDPOINT', 'http://ns47.localhost/civicrm/giga-subscriptions-api');
define('GIGA_EMAIL_SUBSCRIPTION_API_NO_THROW', TRUE);
define('GIGA_EMAIL_SUBSCRIPTION_API_PSK', 'aabbccddeeff');

include './giga-signup-api.php';

// The test contact's email.
$email = 'foo@example.com';

// Various example calls here. Comment out appropriately.

$result = GigaEmailSubscriptionAPI::request('getContactHash', ['email' => $email]);
$x = GigaEmailSubscriptionAPI::request('getContactData', ['email' => $email, 'hash' => $result['hash']]);
$result = GigaEmailSubscriptionAPI::request('getContactHash', ['email' => $email]);
// This should return an error.
$x = GigaEmailSubscriptionAPI::request('getContactData', ['email' => 'forumsc@artfulrobot.uk', 'hash' => $result['hash']]);
// This should update the contact.
$x = GigaEmailSubscriptionAPI::request('setContactData', ['email' => $email, 'hash' => $result['hash'], 'first_name' => 'Rich']);
// This should not return an error.
$x = GigaEmailSubscriptionAPI::request('getContactData', ['email' => $email, 'hash' => $result['hash']]);
$x = GigaEmailSubscriptionAPI::request('addSubscriber', ['new_email' => $email]);
$x = GigaEmailSubscriptionAPI::request('addSubscriber', ['new_email' => 'doesnotexist@example.com', 'giga_en_latinamerica' => 1, 'first_name' => 'Reech', 'last_name' => 'loot']);
$result = GigaEmailSubscriptionAPI::request('getContactHash', ['email' => $email]);
// This should update the contact.
$x = GigaEmailSubscriptionAPI::request('setContactData', ['email' => $email, 'hash' => $result['hash'], 'individual_prefix' => 'Monkey']);
print_r($x);
