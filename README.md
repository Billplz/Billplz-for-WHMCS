# Billplz for WHMCS

Accept payment using Billplz.

## Installation

* Download: https://github.com/Billplz/billplz-for-whmcs/archive/master.zip
* Extract the dowloaded zip files
* Upload folder **modules** to your WHMCS installation directory

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