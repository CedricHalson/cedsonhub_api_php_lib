# CedsonHub API php library
We are happy to see you here. This script is developed and supported by [CedsonHub](https://cedsonhub.site) team.

### Requirements
* PHP 7 or higher
* Requests (inside library folder)

## Installation
Move library folder and cedsonhub_lib.php file into your project directory

## Info
This php library makes usage of CedsonHub api much easier. It provides a class with defined methods for each API endpoint.
Api endpoint calls always return an associative array in format below.

```php
array(
    "success": true, // boolen value indication if API call was successful or not
    "result": array(), // associative array which contains the response body or data sent back via CedsonHub, if success is false this will be an emtpy array
)   "error": "" // string contains error message that needs to be shown to user, empty string if success is true
```

More details about each endpoint is described in [api docs page](https://cedsonhub.site/api-docs).

## Usage

```php
<?php
require_once("./cedsonhub_lib.php");

// pass your api_key
$cedsonhub_api = new CedsonHubApi("if_you_are_using_dummy_api_key_ensure_it_has_50_chars");

// get project balance
$balance = $cedsonhub_api->call_get_balance();

if ($balance["success"]) {
    // do your stuff with balance
    var_dump($balance["result"]);
} else {
    // handle error
    echo $balance["error"];
}
```

## List of available methods

### Get balance
    call_get_balance()
### Get supported coins
    call_get_supported_coins()
### Check username
    call_check_username(string $username)
### Payout
    call_payout(string $to, string $currency, int $amount_in_coins, int $amount_in_satoshis, $is_referral, $ip_address)
### Get recent payouts
    call_get_recent_payouts()
### Get sites list
    call_get_sites_list()
### Accept payment: create invoice
    call_create_invoice(string $currency, int $amount_in_coins, int $amount_in_satoshis)
### Accept payment: verify invoice
    call_verify_invoice(int $payment_id)

## License
[MIT](https://github.com/CedricHalson/cedsonhub_api_php_lib/blob/main/LICENSE)
