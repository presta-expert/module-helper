# README

An abstract class which you may use when creating your custom modules instead of using standard prestashop Module class.

It implements some features that allows you to create modules in a more schematic way without unnecessarily repeating the code.

I plan to add more options in the future including simplification for creating controllers, forms, tables etc.

## Requirements

- PHP >= 5.2.4
- Prestashop 1.5 - 1.7

## Installation

### Composer (recommended)

```bash
$ composer require presta-expert/module-helper
```

## Basic usage

```php
<?php
/**
 * Checking if module is executed in prestashop context
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Loading composer autoload
 */
require __DIR__ . '/vendor/autoload.php';

/**
 * Your module description
 */
class MyCustomModule extends \PrestaExpert\Helper\AbstractModule
{
    /**
     * Setting your module details
     */
    public function __construct()
    {
        $this->name    = 'mycustommodule';
        $this->version = '1.5.2';
        $this->author  = 'Johnny Sins';
        $this->tab     = 'analytics_stats';

        parent::__construct();

        $this->displayName = $this->l('My custom module');
        $this->description = $this->l('My custom module full description');
    }

    /**
     * {@inheritdoc}
     * 
     * Below configuration values will be automatically 
     * added on module installation
     *
     * Also below values will be automatically deleted
     * on module uninstallation
     */
    public function getConfiguration()
    {
        return array(
            'MY_CUSTOM_CONFIG_1' => 'Lorem',
            'MY_CUSTOM_CONFIG_2' => 'Lorem ipsum',
        );
    }

    /**
     * {@inheritdoc}
     * 
     * Below hooks will be automatically registered on 
     * module installation without manually implementing 
     * registerHook
     * 
     * All you have to do is implement your hook methods
     * public function hookDisplayHeader(array $params)
     */
    public function getHooks()
    {
        return array(
            'displayHeader',
            'displayBackOfficeHeader',
        );
    }

    /**
     * {@inheritdoc}
     * 
     * Below queries will be automatically executed on 
     * module installation
     */
    public function getInstallSql()
    {
        return array(
            'CREATE TABLE `custom_table` (
                 id int,
                 value1 varchar(255),
                 value2 varchar(255)
             );',
        );
    }

    /**
     * {@inheritdoc}
     * 
     * Below queries will be automatically executed on 
     * module uninstallation
     */
    public function getUninstallSql()
    {
        return array(
            'DROP TABLE `custom_table`',
        );
    }

    /**
     * {@inheritdoc}
     * 
     * Below tabs will be automatically added on module
     * installation
     * 
     * Also below tabs will be automatically deleted
     * on module uninstall
     *
     * Keep in mind to keep the proper structure of 
     * the array
     */
    public function getTabs()
    {
        return array(
            array(
                // Parent tab name (or null if your tab should be parent)
                'parent' => 'AdminParentOrders', 
    
                // Your tab class name
                'class' => 'AdminModule',
    
                // Display name of tab (will be automatically used in translation $this->l())
                'name' => 'Doing something',
            ),
        );
    }

    /**
     * {@inheritdoc}
     * 
     * Below media will be automatically added in 
     * back office using displayBackOfficeHeader
     */
    public function getBackOfficeMedia()
    {
        return array(
            '/modules/mycustommodule/views/js/backoffice.js',
            '/modules/mycustommodule/views/css/backoffice.css',
        );
    }

    /**
     * {@inheritdoc}
     * 
     * Below media will be automatically added in 
     * front office using displayHeader
     */
    public function getFrontOfficeMedia()
    {
        return array(
            '/modules/mycustommodule/views/js/frontoffice.js',
            '/modules/mycustommodule/views/css/frontoffice.css',
        );
    }

    /**
     * {@inheritdoc}
     * 
     * IMPORTANT NOTE:
     * Keep in mind to execute parent::hookDisplayHeader
     * and parent::hookDisplayBackOfficeHeader when your
     * module uses it to keep getFrontOfficeMedia() and
     * getBackOfficeMedia() working.
     * 
     * @return  void
     */
    public function hookDisplayBackOfficeHeader()
    {
        parent::hookDisplayBackOfficeHeader();
    
        // Your hook placeholder to keep getBackOfficeMedia() working
    }

    /**
     * {@inheritdoc}
     * 
     * IMPORTANT NOTE:
     * Keep in mind to execute parent::hookDisplayHeader
     * and parent::hookDisplayBackOfficeHeader when your
     * module uses it to keep getFrontOfficeMedia() and
     * getBackOfficeMedia() working.
     *
     * @return  void
     */
    public function hookDisplayHeader()
    {
        parent::hookDisplayHeader();
        
        // Your hook placeholder to keep getFrontOfficeMedia() working
    }

}
```

## Authors

- [Presta.Expert](https://presta.expert) Team

## License

The files in this archive are released under the [MIT LICENSE](LICENSE).
