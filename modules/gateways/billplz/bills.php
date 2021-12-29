<?php

// https://classdocs.whmcs.com/8.1/index.html

use WHMCS\Authentication\CurrentUser;
use WHMCS\ClientArea;
use WHMCS\Database\Capsule;
use Billplz\Api;
use Billplz\Connect;

define('CLIENTAREA', true);

$base_dir = __DIR__ . '/../../../';
$includes_dir = $base_dir . 'includes/';
require $base_dir . 'init.php';
require $includes_dir . 'gatewayfunctions.php';
require $includes_dir . 'invoicefunctions.php';
require __DIR__ . '/api.php';
require __DIR__ . '/connect.php';

$ca = new ClientArea();

$ca->initPage();

$ca->requireLogin();

$currentUser = new CurrentUser();
$authUser = $currentUser->user();

// Check login status
if ($authUser) {

    $selectedClient = $currentUser->client();

    if ($selectedClient) {
        $invoice = $selectedClient->invoices()->find($_GET['invoiceid']);

        if (!$invoice) {
            exit('Invalid Invoice');
        }

        $invoice_currency = Capsule::table('mod_billplz_gateway_invoice_currency')
            ->where('invoiceid', $_GET['invoiceid'])
            ->take(1)
            ->first();

        if (!$invoice_currency || $invoice_currency->currency != "MYR") {
            exit('Unsupported Currency!');
        }

        $inv_attr = $invoice->getAttributes();

        if ($inv_attr['paymentmethod'] != 'billplz' || $inv_attr['status'] == 'Paid') {
            $CONFIG['SystemURL'] . "/viewinvoice.php?id={$_GET['invoiceid']}";
        }

        $gatewayParams = getGatewayVariables('billplz');

        $parameter = array(
            'collection_id' => $gatewayParams['collection_id'],
            'email' => $selectedClient->email,
            'mobile' => preg_replace('/\D/', '', $selectedClient->phonenumber),
            'name' => $selectedClient->fullname,
            'amount' => strval($inv_attr['total'] * 100),
            'callback_url' => $CONFIG['SystemURL'] . '/modules/gateways/callback/billplz.php',
            'description' => "Invoice #{$_GET['invoiceid']}"
        );

        $optional = array(
            'due_at' => $inv_attr['duedate'],
            'redirect_url' => $CONFIG['SystemURL'] . '/modules/gateways/billplz/return.php',
        );

        $connect = new Connect($gatewayParams['api_key']);
        $connect->setStaging($gatewayParams['is_sandbox'] == 'on');
        $billplz = new Api($connect);

        $data = Capsule::table('mod_billplz_gateway')
            ->where('invoiceid', $_GET['invoiceid'])
            ->take(1)
            ->first();

        $flagNeedCreateBill = true;
        $flagDeleteOldBill = false;
        if ($data) {
            $flagNeedCreateBill = false;
            foreach (['amount', 'name', 'email', 'mobile'] as $value) {
                if ($data->$value !=  $parameter[$value]) {
                    $flagNeedCreateBill = true;
                    $flagDeleteOldBill = true;
                    break;
                }
            }
        }

        if ($flagNeedCreateBill) {
            if ($flagDeleteOldBill) {
                $billplz->deleteBill($data->bill_slug);
                Capsule::table('mod_billplz_gateway')
                    ->where('invoiceid', $_GET['invoiceid'])
                    ->delete();
            }

            list($rheader, $rbody) = $billplz->toArray($billplz->createBill($parameter, $optional));

            if ($rheader !== 200) {
                error_log(print_r($rbody, true));
                throw new Exception(print_r($rbody, true));
            }

            Capsule::table('mod_billplz_gateway')->insert(
                [
                    'invoiceid' => $_GET['invoiceid'],
                    'bill_slug' => $rbody['id'],
                    'amount' => $parameter['amount'],
                    'name' => $parameter['name'],
                    'email' => $parameter['email'],
                    'mobile' => $parameter['mobile'],
                    'state' => 'due'
                ]
            );
        } else {
            list($rheader, $rbody) = $billplz->toArray($billplz->getBill($data->bill_slug));
        }

        header('Location: ' . $rbody['url']);
    } else {
        header('Location: ' . $CONFIG['SystemURL']);
    }
}
