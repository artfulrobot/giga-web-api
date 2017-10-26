<?php
/**
 * Examples.
 */

define('GIGA_EMAIL_SUBSCRIPTION_API_ENDPOINT', 'http://ns47.localhost/civicrm/giga-subscriptions-api');
define('GIGA_EMAIL_SUBSCRIPTION_API_NO_THROW', TRUE);
define('GIGA_EMAIL_SUBSCRIPTION_API_PSK', 'aabbccddeeff');

include './giga-signup-api.php';


//$x = GigaEmailSubscriptionAPI::request('getContactData', ['email' => 'forums@artfulrobot.uk', 'hash' => $result['hash']]);
if (FALSE) {
$result = GigaEmailSubscriptionAPI::request('getContactHash', ['email' => 'forums@artfulrobot.uk']);
// This should return an error.
$x = GigaEmailSubscriptionAPI::request('getContactData', ['email' => 'forumsc@artfulrobot.uk', 'hash' => $result['hash']]);
// This should update the contact.
$x = GigaEmailSubscriptionAPI::request('setContactData', ['email' => 'forums@artfulrobot.uk', 'hash' => $result['hash'], 'first_name' => 'Rich']);
// This should not return an error.
$x = GigaEmailSubscriptionAPI::request('getContactData', ['email' => 'forums@artfulrobot.uk', 'hash' => $result['hash']]);
$x = GigaEmailSubscriptionAPI::request('addSubscriber', ['new_email' => 'forums@artfulrobot.uk']);
$x = GigaEmailSubscriptionAPI::request('addSubscriber', ['new_email' => 'forums2@artfulrobot.uk', 'giga_en_latinamerica' => 1, 'first_name' => 'Reech', 'last_name' => 'loot']);
}
$result = GigaEmailSubscriptionAPI::request('getContactHash', ['email' => 'forums@artfulrobot.uk']);
// This should update the contact.
$x = GigaEmailSubscriptionAPI::request('setContactData', ['email' => 'forums@artfulrobot.uk', 'hash' => $result['hash'], 'individual_prefix' => 'Monkey']);
print_r($x);
