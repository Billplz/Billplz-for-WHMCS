<?php
/*
 * Last Update: August 2017
 */

function billplzPay_config()
{
    $configarray = array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Billplz Payment Gateway'
        ),
        'billplz_api_key' => array(
            'FriendlyName' => 'API Secret Key',
            'Type' => 'text',
            'Size' => '20',
        ),
        'billplz_collection_id' => array(
            'FriendlyName' => 'Collection ID',
            'Type' => 'text',
            'Size' => '20',
            'Description' => 'Optional. If you unsure, leave blank.'
        ),
        'billplz_x_signature_key' => array(
            'FriendlyName' => 'X Signature Key',
            'Type' => 'text',
            'Size' => '20'
        ),
        'instructions' => array(
            'FriendlyName' => 'Payment Information',
            'Type' => 'textarea',
            'Rows' => '5',
            'Description' => 'This information will be displayed on the receipt page.'
        ),
        'billplz_deliver' => array(
            'FriendlyName' => 'Deliver Email & SMS',
            'Type' => 'dropdown',
            'Options' => array(
                '0' => 'No Notification',
                '1' => 'Email Notification',
                '2' => 'SMS Notification',
                '3' => 'Email & SMS Notification'
            ),
            'Description' => 'Note: Charge RM0.15 for every SMS notification sent',
        ),
        'billplz_success_path' => array(
            'FriendlyName' => 'Successful Payment',
            'Type' => 'dropdown',
            'Options' => array(
                'viewinvoice' => 'Specific Invoice',
                'listinvoice' => 'List Invoice',
                'clientarea' => 'Client Area',
            ),
            'Description' => 'Choose page to redirect the user after completed payment',
        ),
        'billplz_failed_path' => array(
            'FriendlyName' => 'Failed Payment',
            'Type' => 'dropdown',
            'Options' => array(
                'listinvoice' => 'List Invoice',
                'viewinvoice' => 'Specific Invoice',
                'clientarea' => 'Client Area',
            ),
            'Description' => 'Choose page to redirect the user after failed payment',
        ),
    );
    return $configarray;
}

//use Illuminate\Database\Capsule\Manager as Capsule;

function billplzPay_link($params)
{

    // Gateway Configuration Parameters
    $api_key = $params['billplz_api_key'];
    $collection_id = $params['billplz_collection_id'];
    $x_signature = $params['billplz_x_signature_key'];
    $instructions = $params['instructions'];

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params['description'];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $name = $firstname . ' ' . $lastname;
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    $raw_string = $amount .$invoiceId;
    $filtered_string = preg_replace("/[^a-zA-Z0-9]+/", "", $raw_string);
    $hash = hash_hmac('sha256', $filtered_string, $x_signature);
    
    #query
    //$sql = Capsule::select("SELECT description FROM tblinvoiceitems WHERE invoiceid='$invoiceId'");
    //$bill_desc = $sql[0]->description;
    # Enter your code submit to the gateway..
    
    if (substr($systemUrl, -1) === '/'){
        $action_url = $systemUrl . 'modules/gateways/billplzPay/billplzBills.php';
    }else {
        $action_url = $systemUrl . '/modules/gateways/billplzPay/billplzBills.php';
    }
    
    $sendData = '<form name="paymentfrm" method="post" action="' . $action_url . '">
    <input type="hidden" name="email" value = "' . $email . '">
    <input type="hidden" name="mobile" value = "' . $phone . '">
    <input type="hidden" name="name" value = "' . $name . '">
    <input type="hidden" name="amount" value = "' . $amount . '">
    <input type="hidden" name="invoiceid" value = "' . $invoiceId . '">
    <input type="hidden" name="description" value = "' . $description . '">
    <input type="hidden" name="hash" value = "' . $hash . '">
    <input src="' . $systemUrl . '/modules/gateways/billplzPay/btn-pay.png" name="submit" type="image">
    </form><p>' . $instructions . '</p>';
    return $sendData;
}