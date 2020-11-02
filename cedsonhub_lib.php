<?php
// get dependencies
require_once("./library/Requests.php");

// load requests library
// this library is used to make hppt requests easy
// and compitable with servers where cURL is not availble
Requests::register_autoloader();


/**
 * Currencies class
 * handles supported currencies in CedsonHub
 */
class CurrenciesCedsonHub
{
    // constants of currency values used in CedsonHub
    protected const CURRENCY_BTC = "BTC";
    protected const CURRENCY_LTC = "LTC";
    protected const CURRENCY_DOGE = "DOGE";

    // list of all supported currencies in CedsonHub
    protected const CURRENCY_LIST = array(
        'BTC' => self::CURRENCY_BTC,
        'LTC' => self::CURRENCY_LTC,
        'DOGE' => self::CURRENCY_DOGE
    );

    // validates currency
    protected function is_currency(string $currency) {
        foreach (self::CURRENCY_LIST as $key => $value) {
            if ($value == $currency) {
                return true;
            }
        }

        return false;
    }
}


/**
 * API endpoints class
 * stores all api endpoint urls
 */
class UrlsCedsonHub extends CurrenciesCedsonHub
{
    private $base_url = "https://cedsonhub.site/api/v1";
    private $get_balance_url = "/get-balance";
    private $get_coins_list_url = "/currencies";
    private $check_user_url = "/check-user";
    private $payout_url = "/payout";
    private $recent_payouts_url = "/recent-payouts";
    private $project_categories_url = "/project-categories";
    private $sites_list_url = "/sites-list";
    private $accept_payment_create_url = "/accept-payment/create";
    private $accept_payment_verify_url = "/accept-payment/verify";

    // methods to get endpoint urls
    protected function get_balance_url() {
        return sprintf("%s%s", $this->base_url, $this->get_balance_url);
    }

    protected function get_currencies_url() {
        return sprintf("%s%s", $this->base_url, $this->get_coins_list_url);
    }

    protected function get_check_user_url() {
        return sprintf("%s%s", $this->base_url, $this->check_user_url);
    }

    protected function get_payout_url() {
        return sprintf("%s%s", $this->base_url, $this->payout_url);
    }

    protected function get_recent_payouts_url() {
        return sprintf("%s%s", $this->base_url, $this->recent_payouts_url);
    }

    protected function get_project_categories_url() {
        return sprintf("%s%s", $this->base_url, $this->project_categories_url);
    }

    protected function get_sites_list_url() {
        return sprintf("%s%s", $this->base_url, $this->sites_list_url);
    }

    protected function get_accept_payment_create_url() {
        return sprintf("%s%s", $this->base_url, $this->accept_payment_create_url);
    }

    protected function get_accept_payment_verify_url() {
        return sprintf("%s%s", $this->base_url, $this->accept_payment_verify_url);
    }
}



/**
 * Base class to start api requests to CedsonHub
 */
class BaseCedsonHub extends UrlsCedsonHub
{
    // store api_key
    private $api_key;

    // validate api_key before setting
    private function set_api_key(string $api_key) {
        if (strlen($api_key) < 50) {
            throw new Exception("Invalid api key");
        }
        $this->api_key = $api_key;
    }

    // get api_key, used only in inherited classes
    protected function get_api_key() {
        return $this->api_key;
    }

    // set api_key on class creation
    function __construct(string $api_key) {
        $this->set_api_key($api_key);
    }

    // return error response
    protected function get_error_response($response, $json) {
        // error message
        $error_message = "";

        // error in sent data
        if ($response->status_code == 400) {
            // add error for api_key
            if (array_key_exists("api_key", $json)) {
                $error_message .= sprintf("api_key: %s\n", $json["api_key"][0]);
            }

            // add error for username
            if (array_key_exists("username", $json)) {
                $error_message .= sprintf("username: %s\n", $json["username"][0]);
            }

            // add error for currency
            if (array_key_exists("currency", $json)) {
                $error_message .= sprintf("currency: %s\n", $json["currency"][0]);
            }

            // add error for amount_in_coins
            if (array_key_exists("amount_in_coins", $json)) {
                $error_message .= sprintf("amount_in_coins: %s\n", $json["amount_in_coins"][0]);
            }

            // add error for amount_in_satoshis
            if (array_key_exists("amount_in_satoshis", $json)) {
                $error_message .= sprintf("amount_in_satoshis: %s\n", $json["amount_in_satoshis"][0]);
            }

            // add error for to
            if (array_key_exists("to", $json)) {
                $error_message .= sprintf("to: %s\n", $json["to"][0]);
            }

            // add error for ip_address
            if (array_key_exists("ip_address", $json)) {
                $error_message .= sprintf("ip_address: %s\n", $json["ip_address"][0]);
            }

            // add error for is_referral
            if (array_key_exists("is_referral", $json)) {
                $error_message .= sprintf("is_referral: %s\n", $json["is_referral"][0]);
            }

            // add error for payment_id
            if (array_key_exists("payment_id", $json)) {
                $error_message .= sprintf("payment_id: %s\n", $json["payment_id"][0]);
            }

            if (strlen($error_message) <= 0) {
                $error_message = "Unknown error";
            }

            return $this->map_response(false, array(), $error_message);
        }
        // error in user/owner/site
        elseif ($response->status_code > 400 && $response->status_code < 500) {
            return $this->map_response(false, array(), $json["detail"]);
        }
        // CedsonHub server error
        elseif ($response->status_code >= 500 && $response->status_code <= 599) {
            return $this->map_response(false, array(), "Something went wrong on CedsonHub's server");
        }
        // unknown error
        else {
            return $this->map_response(false, array(), "Unknown error");
        }
    }

    // return success response
    protected function get_success_response($response, $json) {
        return $this->map_response(true, $json, "");
    }

    private function map_response(bool $success, array $result, string $error) {
        return array(
            "success" => $success,
            "result" => $result,
            "error" => $error
        );
    }
}


/**
 * GetBalanceAPI class
 * implements get_balance_url api endpoint
 */
final class GetBalanceAPI extends BaseCedsonHub
{
    final public function get_balance() {
        // url
        $url = sprintf("%s?api_key=%s", $this->get_balance_url(), $this->get_api_key());

        // make a request using Requests lib
        $response = Requests::get($url);
        $json = json_decode($response->body, true);

        // response is success
        if ($response->status_code == 200) {
            return $this->get_success_response($response, $json);
        }
        // handle error response
        else {
            return $this->get_error_response($response, $json);
        }
    }
}


/**
 * GetSupportedCoinsAPI class
 * implements get_supported_coins api endpoint
 */
final class GetSupportedCoinsAPI extends BaseCedsonHub
{
    final public function get_supported_coins() {
        // url
        $url = sprintf("%s?api_key=%s", $this->get_currencies_url(), $this->get_api_key());

        // make a request using Requests lib
        $response = Requests::get($url);
        $json = json_decode($response->body, true);

        // response is success
        if ($response->status_code == 200) {
            return $this->get_success_response($response, $json);
        }
        // handle error response
        else {
            return $this->get_error_response($response, $json);
        }
    }
}


/**
 * CheckUserAPI class
 * implements check check_username endpoint
 */
final class CheckUserAPI extends BaseCedsonHub
{
    final public function check_username(string $username) {
        // url
        $url = sprintf(
            "%s?api_key=%s&username=%s",
            $this->get_check_user_url(),
            $this->get_api_key(),
            $username
        );

        // make a request using Requests lib
        $response = Requests::get($url);
        $json = json_decode($response->body, true);

        // response is success
        if ($response->status_code == 200) {
            return $this->get_success_response($response, $json);
        }
        // handle error response
        else {
            return $this->get_error_response($response, $json);
        }
    }
}


/**
 * PayoutAPI class
 * implements payout api endpoint
 */
final class PayoutAPI extends BaseCedsonHub
{
    final public function payout(string $to, string $currency, int $amount_in_coins, int $amount_in_satoshis, bool $is_referral = false, string $ip_address = null) {
        // validate values before making api call
        if (!$this->validate_amount($amount_in_coins)) {
            return array(
                "success" => false,
                "result" => array(),
                "error" => "Invalid amount_in_coins"
            );
        }

        if (!$this->validate_amount($amount_in_satoshis)) {
            return array(
                "success" => false,
                "result" => array(),
                "error" => "Invalid amount_in_satoshis"
            );
        }

        if (!$this->is_currency($currency)) {
            return array(
                "success" => false,
                "result" => array(),
                "error" => "Invalid currency"
            );
        }

        // url
        $url = $this->get_payout_url();

        // data
        $data = array(
            "api_key" => $this->get_api_key(),
            "to" => $to,
            "currency" => $currency,
            "amount_in_coins" => $amount_in_coins,
            "amount_in_satoshis" => $amount_in_satoshis,
            "is_referral" => $is_referral,
            "ip_address" => $ip_address
        );

        // make a request using Requests lib
        $response = Requests::post($url, array(), $data);
        $json = json_decode($response->body, true);

        // response is success
        if ($response->status_code == 200) {
            return $this->get_success_response($response, $json);
        }
        // handle error response
        else {
            return $this->get_error_response($response, $json);
        }
    }

    private function validate_amount($amount) {
        return $amount >= 0;
    }

}


/**
 * GetRecentPayoutsAPI class
 * implements get_recent_payouts api endpoint
 */
final class GetRecentPayoutsAPI extends BaseCedsonHub
{
    final public function get_recent_payouts() {
        // url
        $url = sprintf(
            "%s?api_key=%s",
            $this->get_recent_payouts_url(),
            $this->get_api_key()
        );

        // make a request using Requests lib
        $response = Requests::get($url);
        $json = json_decode($response->body, true);

        // response is success
        if ($response->status_code == 200) {
            return $this->get_success_response($response, $json);
        }
        // handle error response
        else {
            return $this->get_error_response($response, $json);
        }
    }
}


/**
 * SitesListAPI class
 * implements get_sites_list api endpoint
 */
final class SitesListAPI extends BaseCedsonHub
{
    final public function get_sites_list() {
        // url
        $url = sprintf(
            "%s?api_key=%s",
            $this->get_sites_list_url(),
            $this->get_api_key()
        );

        // make a request using Requests lib
        $response = Requests::get($url);
        $json = json_decode($response->body, true);

        // response is success
        if ($response->status_code == 200) {
            return $this->get_success_response($response, $json);
        }
        // handle error response
        else {
            return $this->get_error_response($response, $json);
        }
    }
}


/**
 * AcceptPaymentCreate class
 * implements accept_payment_create_url api endpoint
 */
final class AcceptPaymentCreate extends BaseCedsonHub
{
    final public function create_invoice(string $currency, int $amount_in_coins, int $amount_in_satoshis) {
        // url
        $url = $this->get_accept_payment_create_url();

        // data
        $data = array(
            "api_key" => $this->get_api_key(),
            "currency" => $currency,
            "amount_in_coins" => $amount_in_coins,
            "amount_in_satoshis" => $amount_in_satoshis
        );

        // make a request using Requests lib
        $response = Requests::post($url, array(), $data);
        $json = json_decode($response->body, true);

        // response is success
        if ($response->status_code == 200) {
            return $this->get_success_response($response, $json);
        }
        // handle error response
        else {
            return $this->get_error_response($response, $json);
        }
    }
}


/**
 * AcceptPaymentVerify class
 * implements accept_payment_verify_url api endpoint
 */
final class AcceptPaymentVerify extends BaseCedsonHub
{
    final public function verify_invoice(int $payment_id) {
        // url
        $url = sprintf(
            "%s?api_key=%s&payment_id=%u",
            $this->get_accept_payment_verify_url(),
            $this->get_api_key(),
            $payment_id
        );

        // make a request using Requests lib
        $response = Requests::get($url);
        $json = json_decode($response->body, true);

        // response is success
        if ($response->status_code == 200) {
            return $this->get_success_response($response, $json);
        }
        // handle error response
        else {
            return $this->get_error_response($response, $json);
        }
    }
}



/**
 * CedsonHubApi class
 * use this class to make api calls to CedsonHub
 */
final class CedsonHubApi extends BaseCedsonHub
{
    private $server_is_not_respoding = array(
        "success" => false,
        "result" => array(),
        "error" => "CedsonHub server is not responding"
    );

    final public function call_get_balance() {
        try {
            $api = new GetBalanceAPI($this->get_api_key());
            return $api->get_balance();
        } catch (\Throwable $th) {
            return $this->server_is_not_respoding;
        }

    }

    final public function call_get_supported_coins() {
        try {
            $api = new GetSupportedCoinsAPI($this->get_api_key());
            return $api->get_supported_coins();
        } catch (\Throwable $th) {
            return $this->server_is_not_respoding;
        }
    }

    final public function call_check_username(string $username) {
        try {
            $api = new CheckUserAPI($this->get_api_key());
            return $api->check_username($username);
        } catch (\Throwable $th) {
            return $this->server_is_not_respoding;
        }
    }

    final public function call_payout(string $to, string $currency, int $amount_in_coins, int $amount_in_satoshis, bool $is_referral = false, string $ip_address = null) {
        try {
            $api = new PayoutAPI($this->get_api_key());
            return $api->payout($to, $currency, $amount_in_coins, $amount_in_satoshis, $is_referral, $ip_address);
        } catch (\Throwable $th) {
            return $this->server_is_not_respoding;
        }
    }

    final public function call_get_recent_payouts() {
        try {
            $api = new GetRecentPayoutsAPI($this->get_api_key());
            return $api->get_recent_payouts();
        } catch (\Throwable $th) {
            return $this->server_is_not_respoding;
        }
    }

    final public function call_get_sites_list() {
        try {
            $api = new SitesListAPI($this->get_api_key());
            return $api->get_sites_list();
        } catch (\Throwable $th) {
            return $this->server_is_not_respoding;
        }
    }

    final public function call_create_invoice(string $currency, int $amount_in_coins, int $amount_in_satoshis) {
        try {
            $api = new AcceptPaymentCreate($this->get_api_key());
            return $api->create_invoice($currency, $amount_in_coins, $amount_in_satoshis);
        } catch (\Throwable $th) {
            return $this->server_is_not_respoding;
        }
    }

    final public function call_verify_invoice(int $payment_id) {
        try {
            $api = new AcceptPaymentVerify($this->get_api_key());
            return $api->verify_invoice($payment_id);
        } catch (\Throwable $th) {
            return $this->server_is_not_respoding;
        }
    }
}


?>
