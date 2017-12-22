<?php
/**
 * Custom API client for GIGA email subscriptions.
 *
 * @author Rich Lott / Artful Robot for Systopia.
 *
 * Configuration
 * =============
 *
 * The pre-shared key must be defined before the API is called, e.g. in
 * your settings config file. Important that the file it is defined in is
 * never checked into a public repository like github!
 *
 * eg. define('GIGA_EMAIL_SUBSCRIPTION_API_PSK', 'insert-some-hash-here');
 *
 * Usage
 * =====
 *
 * require_once() this file. Then you can make calls like:
 *
 *     $result = GigaEmailSubscriptionAPI::request('getContactData', $params);
 *     $result = GigaEmailSubscriptionAPI::request('setContactData', $params);
 *     $result = GigaEmailSubscriptionAPI::request('addSubscriber', $params);
 *     $result = GigaEmailSubscriptionAPI::request('getContactHash', $params);
 *
 * You should check !empty($result['error']) to see if there was a problem.
 *
 * Most functions require a contact hash to authenticate and email. We will
 * have to ensure this is created by CiviCRM's mailing and present in the URL.
 * e.g.
 *
 * https://your-web-server.com/subscription-updates?email=foo%46example.com&hash=aabb112233dd4411dbsfkjdfhsjdh
 *
 * getContactData
 * --------------
 *
 * $params = ['hash' => $_GET['hash'], 'email' => $_GET['email']];
 * $result = GigaEmailSubscriptionAPI::request('getContactData', $params);
 *
 * Result: Array(
 *     [contact_id] => 12345
 *     [first_name] => Wilma
 *     [last_name] => Flintstone
 *     [prefix_id] =>
 *     [individual_prefix] => Ms.
 *     [id] => 12345
 *     [email] => wilma@example.com
 *     [giga_en_latinamerica] => 0
 *     [giga_de_latinamerica] => 0
 *     [giga_en_middleeast] => 0
 *     [giga_de_middleeast] => 0
 *     [giga_en_asia] => 0
 *     [giga_de_asia] => 0
 *     [giga_en_global] => 0
 *     [giga_de_global] => 0
 *     [giga_en_africa] => 0
 *     [giga_de_afrika] => 0
 *     [journal_africa_spectrum_de] => 0
 *     [journal_africa_spectrum_en] => 0
 *     [journal_chinese_affairs_de] => 0
 *     [journal_chinese_affairs_en] => 0
 *     [journal_latin_america_de] => 0
 *     [journal_latin_america_en] => 0
 *     [journal_se_asia_de] => 0
 *     [journal_se_asia_en] => 0
 *     [working_papers_en] => 0
 *     [working_papers_de] => 0
 *     [events] => 0
 *     [press_global] => 0
 *     [press_africa] => 0
 *     [press_asia] => 0
 *     [press_latin_america] => 0
 *     [press_middle_east] => 0
 *     [professional_background] => string: research|agency|policy|foundation|ngo|media|business|other
 *     [institution] => 'Foo'
 * )
 *
 * Possible Errors
 *
 * - Unauthorised. Invalid hash.
 * - Bad Request. email missing
 * - Bad Request. Problem with input email
 *   email not found, or belongs to 2+ contacts.
 *
 * setContactData
 * --------------
 *
 * $params = [
 *  // As with getContactData():
 *  'hash'                    => $_GET['hash'],
 *  'email'                   => $_GET['email'],
 *  // All that follow are optional. If you don't provide one, no change will happen.
 *  // Change subscriptions like this:
 *  'giga_en_latinamerica'    => 1, // Subscribe to this list
 *  'giga_de_latinamerica'    => 0, // Unsubscribe from this list
 *  // You can also change contact details.
 *  'first_name'              => 'Fred',
 *  'last_name'               => 'Flintstone',
 *  'individual_prefix'       => 'Mr.', // must be registered with CiviCRM
 *  'new_email'               => 'new@example.com', // Note 'new_email'
 *  'professional_background' => '', // One of research|agency|policy|foundation|ngo|media|business|other
 *  'institution'             => 'Institute of Something',
 * ];
 * $result = GigaEmailSubscriptionAPI::request('getContactData', $params);
 *
 * Normally the result is an empty array []. However you should check for
 * $result['errors'] which could be one of the following:
 *
 * - Unauthorised. Invalid hash.
 * - Bad Request. email missing
 * - Bad Request. Problem with input email
 *   email not found, or belongs to 2+ contacts.
 * - Bad Request. Unknown prefix
 *   Each individual_prefix must be registered in CiviCRM.
 *
 * getContactHash
 * --------------
 *
 * This enables you to generate authentication link for the given email.
 *
 * $params = ['email' => $_GET['email']];
 * $result = GigaEmailSubscriptionAPI::request('getContactHash', $params);
 *
 * // Over-simplified example:
 * mail($_GET['email'],
 *   'Update your mailing preferences',
 *   'You need this link: https://your-web-server.com/subscription-updates'
 *   . 'email=' . urlencode($_GET['email']) . '&hash=' . $result['hash']
 *   );
 *
 * Possible Errors
 *
 * - Bad Request. email missing
 * - Bad Request. Problem with input email
 *   email not found, or belongs to 2+ contacts.
 *
 * addSubscriber
 * -------------
 *
 * If you are adding a new subscriber you won't have a hash or existing email
 * and you must use this method. $params is the same as for setContactData()
 * except that you don't supply the 'hash' and 'email' keys. The email must be
 * provided as 'new_email'.
 *
 * $result = GigaEmailSubscriptionAPI::request('addSubscriber', $params);
 *
 * As with setContactData(), the result is normally an empty array []. However
 * you should check for $result['errors'] which could be one of the following:
 *
 * - Unauthorised. Invalid hash.
 * - Bad Request. email missing
 * - Bad Request. Problem with input email
 *   email not found, or belongs to 2+ contacts.
 * - Bad Request. Unknown prefix
 *   Each individual_prefix must be registered in CiviCRM.
 * - Bad Request. Email is required to create a contact.
 * - Bad Request. At least one subscription is required.
 * - Bad Request. Name is required to create a contact.
 * - Authentication Required
 *
 * The last one of those errors is important. It means that you tried to send
 * data for a contact that is already in the database. We cannot allow updating
 * data of existing contacts through the addSubscriber API because otherwise it
 * would be possible for a malicious user to change someone else's data. This
 * is the purpose of the hash in setContactData(). So if you try to add a contact
 * that already exists you'll get this error.
 *
 * However you will also be sent the hash for that contact, in $result['hash']
 * so you can send them an email with the authentication link in which should
 * allow them to subscribe.
 *
 */
if (!defined('GIGA_EMAIL_SUBSCRIPTION_API_ENDPOINT')) {
  define('GIGA_EMAIL_SUBSCRIPTION_API_ENDPOINT', 'https://crm.giga-hamburg.de/civicrm/giga-subscriptions-api');
}
/**
 * If set TRUE, then no exceptions will be thrown (by this code) and errors
 * will need to be checked for in $output['error']
 */
if (!defined('GIGA_EMAIL_SUBSCRIPTION_API_NO_THROW')) {
  define('GIGA_EMAIL_SUBSCRIPTION_API_NO_THROW', TRUE);
}

/**
 * @class Handle all API requests.
 *
 * Call like:
 * $result = GigaEmailSubscriptionAPI::request('getContactHash', ['email' => 'foo']);
 *
 */
class GigaEmailSubscriptionAPI
{
  /**
   * Make API call
   *
   * @param string $method
   * @param array $params
   */
  public static function request($method, $params=[]) {

    // Ensure PSK is defined.
    if (!defined('GIGA_EMAIL_SUBSCRIPTION_API_PSK')) {
      throw new Exception("You need to define GIGA_EMAIL_SUBSCRIPTION_API_PSK to match that on the API server.");
    }
    if (!in_array($method, ['getContactData', 'setContactData', 'getContactHash', 'addSubscriber'])) {
      throw new InvalidArgumentException("Unimplemented method, '$method'");
    }
    $api = new static(GIGA_EMAIL_SUBSCRIPTION_API_ENDPOINT, GIGA_EMAIL_SUBSCRIPTION_API_PSK);
    return $api->$method($params);
  }
  /**
   * Constructor.
   * @param string $url
   * @param string $psk pre-shared key
   */
  public function __construct($url, $psk) {
    $this->url = $url;
    $this->psk = $psk;
  }
  /**
   * Get contact data to enable user to update their existing data.
   *
   * @param array $params
   * @return array
   */
  public function getContactData($params) {
    return $this->sendRequest('GET', ['method' => 'getContactData'] + $params);
  }
  /**
   * Send updated or new subscription data.
   *
   * @param array $params
   * @return array
   */
  public function setContactData($params) {
    $query = ['method' => 'setContactData', 'hash' => $params['hash'], 'email' => $params['email']];
    return $this->sendRequest('POST', $query, $params);
  }
  /**
   * Send updated or new subscription data.
   *
   * @param array $params
   * @return array
   */
  public function addSubscriber($params) {
    $query = ['method' => 'addSubscriber'];
    return $this->sendRequest('POST', $query, $params);
  }
  /**
   * Get a URL to authenticate a particular email to edit their data.
   *
   * @param array $params
   * @return string
   */
  public function getContactHash($params) {
    return $this->sendRequest('GET', ['method' => 'getContactHash'] + $params);
  }

  public function sendRequest($method, $query, $data=[]) {

    // If debugging, this can be handy.
    // $query['XDEBUG_SESSION_START'] = 'my_debug_key';

    $query['psk'] = $this->psk;
    $url = $this->url . '?' . http_build_query($query);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    // curl_setopt($curl, CURLOPT_HEADER, TRUE);

    if ($data) {
      $data = json_encode($data);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      $headers = [
        'Content-Type: Application/json;charset=UTF-8',
        'Content-Length: ' . strlen($data),
      ];
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    $info = curl_getinfo($curl);
    curl_close($curl);

    // Check response.
    if (empty($info['http_code'])) {
      throw new GigaEmailSubscriptionAPINetworkError("Network error. Missing http_code in curl response info");
    }

    // Should we try to decode a json response?
    if ($result) {
      // We were sent json, or we require it.
      $result = json_decode($result, TRUE);
    }

    // Report errors now.
    if (substr($info['http_code'], 0, 1) != '2') {
      if (GIGA_EMAIL_SUBSCRIPTION_API_NO_THROW) {
        // Configured to not throw exceptions.
        if (!$result || !isset($result['error'])) {
          // The $result should be an array that contains an 'error' key.
          $result = ['error' => 'Unknown error ' . $info['http_code']];
        }
        return $result;
      }
      switch (substr($info['http_code'], 0, 1)) {
      case 5:
        throw new GigaEmailSubscriptionAPINetworkError($result['error']);
      case 4:
        throw new GigaEmailSubscriptionAPIRequestError($result['error']);
      default:
        throw new Exception("Unknown error.");
      }
    }

    // Return successful result.
    return $result;
  }
}

class GigaEmailSubscriptionAPINetworkError extends Exception {}
class GigaEmailSubscriptionAPIRequestError extends Exception {}
