# Billplz for WHMCS

Accept payment using Billplz.

## Installation

* Download: https://github.com/Billplz/billplz-for-whmcs/archive/master.zip
* Extract the dowloaded zip files
* Upload folder **modules** to your WHMCS installation directory

## Updating from version prior to 3.0.0

* Remove directory `modules/gateways/billplzPay`
* Remove files:
  * `modules/gateways/billplzPay.php`
  * `modules/gateways/callback/billplzCallback.php`
* Drop table `mod_billplz_gateway`:
  * ```DROP TABLE mod_billplz_gateway```
* Download: https://github.com/Billplz/billplz-for-whmcs/archive/master.zip
* Extract the dowloaded zip files
* Upload folder **modules** to your WHMCS installation directory
* Navigate to System Settings >> Payment Gateways
* Save changes

## Compatibility

Tested up WHMCS 8.2.x

## Configuration

1. Login to WHMCS Administration
1. Navigate to System Settings >> Payment Gateways
1. Set up API Secret Key, Collection ID and X Signature Key
1. Set **Convert to Processing** to **MYR**
1. Save changes

### Custom Bill Description

You may customize the Bill Description by modifying `$parameter['description']` in **modules/gateways/billplz/bills.php**

# Other

Facebook: [Billplz Dev Jam](https://www.facebook.com/groups/billplzdevjam/)
