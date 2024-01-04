# Mage2 Module Cpm UseMyTerm

    ``cpm/module-usemyterm``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
Use My Terms Payment Method

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Cpm`
 - Enable the module by running `php bin/magento module:enable Cpm_UseMyTerm`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require cpm/module-usemyterm`
 - enable the module by running `php bin/magento module:enable Cpm_UseMyTerm`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration

 - UseMyTerms - payment/usemyterms/*


## Specifications

 - Observer
	- sales_order_place_after > Cpm\UseMyTerm\Observer\Sales\OrderPlaceAfter

 - Payment Method
	- UseMyTerms


## Attributes

 - Customer - Use My Terms (use_my_terms)

 - Customer - Use Your Terms Title (use_your_terms_title)

 - Sales - User Terms Title (usertermstitle)

