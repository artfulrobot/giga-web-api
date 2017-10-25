<?php
define('GIGA_EMAIL_SUBSCRIPTION_API_ENDPOINT', 'http://ns47.localhost/civicrm/giga-subscriptions-api');
define('GIGA_EMAIL_SUBSCRIPTION_API_NO_THROW', TRUE);
define('GIGA_EMAIL_SUBSCRIPTION_API_PSK', 'aabbccddeeff');
include './giga-signup-api.php';
$result = GigaEmailSubscriptionAPI::request('getContactHash',
  ['email' => 'forums@artfulrobot.uk']);
print_r($result);
print_r(GigaEmailSubscriptionAPI::request('getContactData',
  ['email' => 'forums@artfulrobot.uk', 'hash' => $result['data']]));
