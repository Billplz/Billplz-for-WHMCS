<?php

use WHMCS\Database\Capsule;

function billplz_config()
{
    billplz_create_table();

    $configarray = array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Billplz'
        ),
        'is_sandbox' => array(
            'FriendlyName' => 'Is Sandbox',
            'Type' => 'yesno',
            'Description' => 'Tick for Billplz Sandbox'
        ),
        'api_key' => array(
            'FriendlyName' => 'API Secret Key',
            'Type' => 'text',
            'Size' => '20',
            'Description' => 'Billplz > Settings > API Secret Key'
        ),
        'collection_id' => array(
            'FriendlyName' => 'Collection ID',
            'Type' => 'text',
            'Size' => '20',
            'Description' => 'Billplz > Billing > Collection ID'
        ),
        'x_signature_key' => array(
            'FriendlyName' => 'X Signature Key',
            'Type' => 'text',
            'Size' => '20',
            'Description' => 'Billplz > Settings > X Signature Key'
        ),
        'instructions' => array(
            'FriendlyName' => 'Payment Information',
            'Type' => 'textarea',
            'Rows' => '5',
            'Description' => 'This information will be displayed on the receipt page.'
        ),
        'bill_logo' => array(
            'FriendlyName' => 'Bill Logo',
            'Type' => 'dropdown',
            'Options' => array(
                'logo-fpx.png' => 'Logo FPX',
                'logo-all.png' => 'Logo All',
                'logo-old.png' => 'Old Logo',
            ),
            'Description' => 'Choose page to redirect the user after completed payment',
        ),
        'success_path' => array(
            'FriendlyName' => 'Successful Payment',
            'Type' => 'dropdown',
            'Options' => array(
                'viewinvoice' => 'Specific Invoice',
                'listinvoice' => 'List Invoice',
                'clientarea' => 'Client Area',
            ),
            'Description' => 'Choose page to redirect the user after completed payment',
        ),
        'failed_path' => array(
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

function billplz_create_table()
{
    // If the table is not exist, create one
    if (!Capsule::schema()->hasTable('mod_billplz_gateway')) {
        Capsule::schema()->create(
                'mod_billplz_gateway',
        function ($table) {
            $table->engine = 'InnoDB';

            $table->bigIncrements('id')->primary();
            $table->integer('invoiceid');
            $table->string('bill_slug');
            $table->string('amount');
            $table->string('name');
            $table->string('email');
            $table->string('mobile');
            $table->string('state');

            $table->index('invoiceid');
            $table->index('bill_slug');
        }
    );
    }
}

function billplz_link($params)
{

    $system_url = rtrim($params['systemurl'], '/');
    $logo = $params['bill_logo'];

    //'/modules/gateways/billplz/bills.php';

    $code = '<p>'
        . nl2br($params['instructions'])
        . '<br />'
        . '<a href="'.$system_url.'/modules/gateways/billplz/bills.php?invoiceid='.$params['invoiceid'].'">'
        . '<img src="'.$system_url.'/modules/gateways/billplz/'.$logo.'" title="'.Lang::trans('Pay with Billplz').'">'
        . '</a>'
        . '<br />'
        . Lang::trans('invoicerefnum')
        . ': '
        . $params['invoicenum']
        . '</p>';

    return $code;
}
