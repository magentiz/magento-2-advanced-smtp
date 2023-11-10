# Magentiz AdvancedSmtp by Open Techiz
Magentiz_AdvancedSmtp Extension, AdvancedSmtp for magento 2
> Magentiz AdvancedSmtp is built depending on module [Magento 2 SMTP](https://github.com/mageplaza/magento-2-smtp) and has additional integration with [AWS Ses](https://aws.amazon.com/ses/), [Magento 2 SMTP](https://github.com/mageplaza/magento-2-smtp) will be automatically installed when you use Composer. If you're installing using an archive, please install module [Magento 2 SMTP](https://github.com/mageplaza/magento-2-smtp) for the extension to work properly.

## Requirements
  * Magento Community Edition 2.3.x-2.4.x or Magento Enterprise Edition 2.3.x-2.4.x
  * Exec function needs to be enabled in PHP settings.

## Installation Method 1 - Installing via composer
  * Open command line
  * Using command "cd" navigate to your magento2 root directory
  * Run command: composer require magentiz/advanced-smtp

## Installation Method 2 - Installing using archive
  * Download [ZIP Archive](https://pm.opentechiz.com/vuong/magentiz-awsses/-/archive/master/magentiz-awsses-master.zip)
  * Extract files
  * In your Magento 2 root directory create folder app/code/Magentiz/AdvancedSmtp
  * Copy files and folders from archive to that folder
  * In command line, using "cd", navigate to your Magento 2 root directory
  * Run commands:
```
php bin/magento module:enable Magentiz_AdvancedSmtp
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## User guide

### 1. Configuration

Log into the Magento administration panel, go to ```Store > Configuration > Mageplaza Extension > SMTP > AWS SES config```.
Choose Yes to enable AWS SES. Enter AWS Ses api and secret key.

### 2. Email logs
This can be accessed at ```Store > SMTP > Email Logs```. From here you can see the emails sent from the server to customers.


## Support
If you have any issues, please [contact us](mailto:support@opentechiz.com)

## Need More Features?
Please contact us to get a quote
https://www.opentechiz.com/contact-us/

## License
The code is licensed under [Open Software License ("OSL") v. 3.0](http://opensource.org/licenses/osl-3.0.php).
