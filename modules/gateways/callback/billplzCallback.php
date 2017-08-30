<?php
// Reference: http://docs.whmcs.com/Using_Models
// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
require_once __DIR__ . '/../billplzPay/billplz-api.php';

// Get variable for WHMCS configuration
global $CONFIG;

// Module name.
$gatewayModuleName = 'billplzPay';

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);
// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

// Get payment gateway details
$api_key = $gatewayParams['billplz_api_key'];
$x_signature = $gatewayParams['billplz_x_signature_key'];

// Retrieve data returned in payment gateway return

try {
    $data = Billplz::getCallbackData($x_signature);
} catch (\Exception $e) {
    exit($e->getMessage());
}

$billplz = new Billplz($api_key);

// Validate the status from ID
$moreData = $billplz->check_bill($data['id']);

// Collect data
$success = $data['paid'];
$invoiceId = $moreData['reference_1'];
$transactionId = $data['id'];
$paymentAmount = number_format(($moreData['amount'] / 100), 2);
$paymentFee = 0;
$hash = Billplz::getSignature();

$transactionState = $data['state'];

if ($success) {
    /**
     * Validate Callback Invoice ID.
     *
     * Checks invoice ID is a valid invoice number. Note it will count an
     * invoice in any status as valid.
     *
     * Performs a die upon encountering an invalid Invoice ID.
     *
     * Returns a normalized invoice ID.
     */
    $invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);

    checkCbTransID($transactionId);

    $transactionStatus = 'Callback: ' . $transactionState;

    /**
     * Add Invoice Payment.
     *
     * Applies a payment transaction entry to the given invoice ID.
     *
     * @param int $invoiceId         Invoice ID
     * @param string $transactionId  Transaction ID
     * @param float $paymentAmount   Amount paid (defaults to full balance)
     * @param float $paymentFee      Payment fee (optional)
     * @param string $gatewayModule  Gateway module name
     */
    addInvoicePayment($invoiceId, $transactionId, $paymentAmount, $paymentFee, $gatewayModuleName);

    // Log Transaction
    logTransaction($gatewayParams['name'], $_POST, $transactionStatus);
} else {
    // Nothing to do
}
echo 'ALL IS WELL';
