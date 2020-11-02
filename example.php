<?php
require_once("./cedsonhub_lib.php");

/**
 * CedsonHubApi class
 * use this class to make api calls to CedsonHub
 *
 * Endpoint call will always return an associative array with following keys
 * 'success' - boolen value indication if API call was successful or not
 * 'result' - associative array which contains the response body or data sent back via CedsonHub, if success is false this will be an emtpy array
 * 'error' - string contains error message that needs to be shown to user, empty string if success is true
 *
 * link to api docs: https://cedsonhub.site/api-docs
 *
 * if you found any error or bug please contact CedsonHub team via discord
 * discord invite link: https://discord.gg/498nWwa
 */

function format_response(array $result) {
    echo "<pre>";
    echo "<code>";
    var_dump($result);
    echo "</code>";
    echo "</pre>";
    echo "<br>";
}

// create CedsonHubApi instance
$cedsonhub_api = new CedsonHubApi("if_you_are_using_dummy_api_key_ensure_it_has_50_chars");

// get balance
$balance = $cedsonhub_api->call_get_balance();

if ($balance["success"]) {
    // do your stuff with balance
    format_response($balance["result"]);
} else {
    // handle error
    echo $balance["error"];
}

// get list of supported coins
$supported_coins = $cedsonhub_api->call_get_supported_coins();

if ($supported_coins["success"]) {
    // do your stuff with supported coins
    format_response($supported_coins["result"]);
} else {
    // handle error
    echo $supported_coins["error"];
}


// check username
$is_username = $cedsonhub_api->call_check_username("john_doe");

if ($is_username["success"]) {
    // do your stuff with username
    format_response($is_username["result"]);
} else {
    // handle error
    echo $is_username["error"];
}


// payout
// total_payout = amount_in_coins * 100000000 + amount_in_satoshis
// either amount_in_coins or amount_in_satoshis could be zero but not less than zero
// both amount_in_coins and amount_in_satoshis must be integer

// $payout = $cedsonhub_api->call_payout("to", "currency", "amount_in_coins", "amount_in_satoshis", "is_referral", "ip_address");
// this would payout 1.00000001 BTC
$payout = $cedsonhub_api->call_payout("john_doe", "BTC", "1", "1", true, "99.98.97.96");

if ($payout["success"]) {
    // do your stuff with payout response
    format_response($payout["result"]);
} else {
    // handle error
    echo $payout["error"];
}

// get recent payouts
$recent_payouts = $cedsonhub_api->call_get_recent_payouts();

if ($recent_payouts["success"]) {
    // do your stuff with recent payouts
    format_response($recent_payouts["result"]);
} else {
    // handle error
    echo $recent_payouts["error"];
}


// get sites list
$sites_list = $cedsonhub_api->call_get_sites_list();

if ($sites_list["success"]) {
    // do your stuff with sites list
    format_response($sites_list["result"]);
} else {
    // handle error
    echo $sites_list["error"];
}

// create invoice to accept payment from CedsonHub users
// invoice total amount = amount_in_coins * 100000000 + amount_in_satoshis
// this would create invoice for 100 BTC satoshis, 0.00000100 BTC
$invoice = $cedsonhub_api->call_create_invoice("BTC", "0", "100");

if ($invoice["success"]) {
    // make sure to save invoice details in your data storage
    format_response($invoice["result"]);
    // and redirect user to redirect_url
    // header(sprintf("Location: %s", $invoice["result"]["redirect_url"]) )
} else {
    // handle error
    echo $sites_list["error"];
}

// verify invoice status
// pass payment_id which you got from create invoice response
$invoice_status = $cedsonhub_api->call_verify_invoice(1);

if ($invoice_status["success"]) {
    format_response($invoice_status["result"]);

    // $invoice_status["status"] might have 3 values 'Pending', 'Paid', 'Canceled' for more details please read our docs.

    // invoice is not verified or canceled by user
    if ($invoice_status["result"]["status"] == "Pending") {
        // show user the link with redirect url provided by create_invoice api endpoint
        echo "Please verify invoice";
    }
    // invoice is canceled, please do not check its status again, it is complete
    elseif ($invoice_status["result"]["status"] == "Canceled") {
        echo "This invoice is canceled";
    }
    // invoice is paid, please dont check its status again, it is complete
    elseif ($invoice_status["result"]["status"] == "Paid") {
        // invoice is paid, here do your stuff with sent funds
        echo "You are granted a premium access on our project.";
    }

} else {
    // handle error
    echo $sites_list["error"];
}

?>
