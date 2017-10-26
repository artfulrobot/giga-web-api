<?php
/**
 * Custom API client for GIGA email subscriptions.
 *
 * @author Rich Lott / Artful Robot for Systopia.
 *
 * Note:
 * Pre-shared key must be defined before the API is called, e.g. in
 * your settings config file. Important that the file it is defined in is
 * never checked into a public repository like github!
 *
 * eg. define('GIGA_EMAIL_SUBSCRIPTION_API_PSK', 'insert-some-hash-here');
 */
if (!defined('GIGA_EMAIL_SUBSCRIPTION_API_ENDPOINT')) {
  define('GIGA_EMAIL_SUBSCRIPTION_API_ENDPOINT', 'https://crm.giga-hamburg.de/civicrm/giga-subscriptions-api');
}
/**
 * If set TRUE, then no exceptions will be thrown (by this code) and errors
 * will need to be checked for in $output['error']
 */
if (!defined('GIGA_EMAIL_SUBSCRIPTION_API_NO_THROW')) {
  define('GIGA_EMAIL_SUBSCRIPTION_API_NO_THROW', FALSE);
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
          $result = ['error' => 'Unknown error'];
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
