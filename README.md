# Magentiz AWSSes by Open Techiz
Magentiz_AWSSes Extension, awsses for magento 2
> Magentiz AWSSes is built depending on module [Magento 2 SMTP](https://github.com/mageplaza/magento-2-smtp) and has additional integration with [AWS Ses](https://aws.amazon.com/ses/), [Magento 2 SMTP](https://github.com/mageplaza/magento-2-smtp) will be automatically installed when you use Composer. If you're using an archive, please install module [Magento 2 SMTP](https://github.com/mageplaza/magento-2-smtp) for the extension to work properly.

## Requirements
  * Magento Community Edition 2.3.x-2.4.x or Magento Enterprise Edition 2.3.x-2.4.x
  * Exec function needs to be enabled in PHP settings.

## Installation Method 1 - Installing via composer
  * Open command line
  * Using command "cd" navigate to your magento2 root directory
  * Run command: composer require magentiz/awsses

## Installation Method 2 - Installing using archive
  * Download [ZIP Archive](link)
  * Extract files
  * In your Magento 2 root directory create folder app/code/Magentiz/AWSSes
  * Copy files and folders from archive to that folder
  * In command line, using "cd", navigate to your Magento 2 root directory
  * Run commands:
```
php bin/magento module:enable Magentiz_AWSSes
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## Support
If you have any issues, please [contact us](mailto:support@opentechiz.com)

## Need More Features?
Please contact us to get a quote
https://www.opentechiz.com/contact-us/

## License
The code is licensed under [Open Software License ("OSL") v. 3.0](http://opensource.org/licenses/osl-3.0.php).
