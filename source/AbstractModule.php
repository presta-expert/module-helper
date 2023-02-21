<?php
/**
 * Namespace declaration
 */
namespace PrestaExpert\Helper;

/**
 * Checking if module is executed in prestashop context
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Used namespaces
 */
use Db;
use Tab;
use Shop;
use Module;
use Configuration;

/**
 * An abstract class which makes creating modules
 * faster and without repeating the code
 *
 * Your module class should extend this class to
 * get additional features
 *
 * IMPORTANT NOTE:
 * Keep in mind to execute parent::hookDisplayHeader
 * and parent::hookDisplayBackOfficeHeader if your
 * module uses it to keep getFrontOfficeMedia() and
 * getBackOfficeMedia() working.
 *
 * @author      Presta.Expert Team <support@presta.expert>
 * @copyright   Presta.Expert Team <support@presta.expert>
 * @version     1.0.5
 */
abstract class AbstractModule extends Module
{
    /**
     * Module internal name
     *
     * @var string
     */
    public $name;

    /**
     * Module name which gonna be displayed
     *
     * @var string
     */
    public $displayName;

    /**
     * Module description which gonna be displayed
     *
     * @var string
     */
    public $description;

    /**
     * Module version
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * Module author
     *
     * @var string
     */
    public $author = 'support@presta.expert';

    /**
     * Module tab / category
     *
     * administration
     * advertising_marketing
     * analytics_stats
     * billing_invoicing
     * checkout
     * content_management
     * dashboard
     * emailing
     * export
     * front_office_features
     * i18n_localization
     * market_place
     * merchandizing
     * migration_tools
     * mobile
     * others
     * payments_gateways
     * payment_security
     * pricing_promotion
     * quick_bulk_update
     * search_filter
     * seo
     * shipping_logistics
     * slideshows
     * smart_shopping
     * social_networks
     *
     * @var string
     */
    public $tab;

    /**
     * Should we create an instance of module on module list?
     *
     * @var integer
     */
    public $need_instance = 0;

    /**
     * Should our module use twitter bootstrap?
     *
     * @var integer
     */
    public $bootstrap = 1;

    /**
     * Supported versions of prestashop
     *
     * @var array
     */
    public $ps_versions_compliancy = array(
        'min' => '1.5',
        'max' => '1.7'
    );

    /**
     * Returns an array of configuration keys
     *
     * Returned keys will be automatically added
     * and removed on installing / uninstalling
     *
     * @return  array
     */
    public function getConfiguration()
    {
        return array();
    }

    /**
     * Returns an array of hooks to register
     *
     * Returned hooks will be automatically
     * added on installing our module
     *
     * @return  array
     */
    public function getHooks()
    {
        return array();
    }

    /**
     * Returns an array of additional sql queries
     * which will be executed on install
     *
     * You can place for example CREATE TABLE
     * queries here
     *
     * @return  array
     */
    public function getInstallSql()
    {
        return array();
    }

    /**
     * Returns an array of additional sql queries
     * which will be executed on uninstall
     *
     * You can place for example DROP TABLE here
     *
     * @return  array
     */
    public function getUninstallSql()
    {
        return array();
    }

    /**
     * Returns an array of media (css, js) which
     * should be added in back office
     *
     * IMPORTANT NOTE:
     * Keep in mind to execute parent::hookDisplayHeader
     * and parent::hookDisplayBackOfficeHeader if your
     * module uses it to keep getFrontOfficeMedia() and
     * getBackOfficeMedia() working.
     *
     * @return  array
     */
    public function getBackOfficeMedia()
    {
        return array();
    }

    /**
     * Returns an array of media (css, js) which
     * should be added in front office
     *
     * IMPORTANT NOTE:
     * Keep in mind to execute parent::hookDisplayHeader
     * and parent::hookDisplayBackOfficeHeader if your
     * module uses it to keep getFrontOfficeMedia() and
     * getBackOfficeMedia() working.
     *
     * @return  array
     */
    public function getFrontOfficeMedia()
    {
        return array();
    }

    /**
     * Returns an array of tabs which module should
     * add on installation
     *
     * return array(
     *     array(
     *         'parent' => 'AdminParentOrders',
     *         'class'  => 'AdminModule',
     *         'name'   => 'Doing something',
     *     ),
     * );
     *
     * @return  array
     */
    public function getTabs()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function install()
    {
        // Setting context for multi-store purposes
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        // Calling parent::install();
        if (!parent::install()) {
            return false;
        }

        // Loop over each of configuration value
        foreach ($this->getConfiguration() as $key => $defaultValue) {

            // Trying to create configuration value
            if (!Configuration::updateValue($key, $defaultValue)) {
                return false;
            }
        }

        // Loop over each of hook
        foreach ($this->getHooks() as $hook) {

            // Trying to register hook
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        // Loop over each of sql
        foreach ($this->getInstallSql() as $sql) {

            // Trying to execute SQL query
            if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql)) {
                return false;
            }
        }

        // Loop over each of tab
        foreach ($this->getTabs() as $tabArray) {

            // Creating tab
            $tab = new Tab();
            $tab->id_parent = (int)Tab::getIdFromClassName($tabArray['parent']);
            $tab->class_name = $tabArray['class'];
            $tab->active = 1;
            $tab->module = $this->name;

            // Creating hidden tab
            if ($tabArray['parent'] < 0) {
                $tab->id_parent = -1;
            }

            // Creating translations
            foreach ($this->context->controller->getLanguages() as $lang) {

                // Adding translation for each lang
                $tab->name[(int)$lang['id_lang']] = $this->l($tabArray['name']);
            }

            // Trying to add new tab
            if (!$tab->add()) {
                return false;
            }
        }

        // Registering displayHeader hook if we have at least one media defined
        // No matter if user registered displayHeader earlier it will work anyways (tested)
        // All you have to remember it to execute parent::hookDisplayHeader in your module
        if ($this->getFrontOfficeMedia()) {

            // Registering displayHeader hook
            $this->registerHook('displayHeader');
        }

        // Registering displayBackOfficeHeader hook if we have at least one media defined
        // No matter if user registered displayBackOfficeHeader earlier it will work anyways (tested)
        // All you have to remember it to execute parent::hookDisplayBackOfficeHeader in your module
        if ($this->getBackOfficeMedia()) {

            // Registering displayBackOfficeHeader hook
            $this->registerHook('displayBackOfficeHeader');
        }

        // Returning boolean true
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall()
    {
        // Calling parent::uninstall();
        if (!parent::uninstall()) {
            return false;
        }

        // Loop over each of configuration value
        foreach (array_keys($this->getConfiguration()) as $key) {

            // Trying to remove configuration value
            if (!Configuration::deleteByName($key)) {
                return false;
            }
        }

        // Loop over each of sql
        foreach ($this->getUninstallSql() as $sql) {

            // Trying to execute SQL query
            if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql)) {
                return false;
            }
        }

        // Loop over each of tab
        foreach ($this->getTabs() as $tabArray) {

            // Creating tab
            $tab = new Tab((int)Tab::getIdFromClassName($tabArray['class']));

            // Trying to remove tab
            if (!$tab->delete()) {
                return false;
            }
        }

        // Returning true
        return true;
    }

    /**
     * Adds custom media (css & js) in back office
     * using displayBackOfficeHeader hook
     *
     * IMPORTANT NOTE:
     * Keep in mind to execute parent::hookDisplayHeader
     * and parent::hookDisplayBackOfficeHeader if your
     * module uses it to keep getFrontOfficeMedia() and
     * getBackOfficeMedia() working.
     *
     * @return  void
     */
    public function hookDisplayBackOfficeHeader()
    {
        return $this->addMedia($this->getBackOfficeMedia());
    }

    /**
     * Adds custom media (css & js) in front office
     * using displayHeader hook
     *
     * IMPORTANT NOTE:
     * Keep in mind to execute parent::hookDisplayHeader
     * and parent::hookDisplayBackOfficeHeader if your
     * module uses it to keep getFrontOfficeMedia() and
     * getBackOfficeMedia() working.
     *
     * @return  void
     */
    public function hookDisplayHeader()
    {
        return $this->addMedia($this->getFrontOfficeMedia());
    }

    /**
     * Adds media using controller addCSS or addJS
     * method based on extension
     *
     * It adds CSS in media type = all however you
     * can change it by extending this method
     *
     * IMPORTANT NOTE:
     * Keep in mind to execute parent::hookDisplayHeader
     * and parent::hookDisplayBackOfficeHeader when your
     * module uses it to keep getFrontOfficeMedia() and
     * getBackOfficeMedia() working.
     *
     * @param   array   $mediaArray
     * @return  void
     */
    protected function addMedia(array $mediaArray)
    {
        // Loop over each of element in array
        foreach ($mediaArray as $media) {

            // Exploding string using dot
            $explode = explode('.', $media);

            // Getting file extension
            $extension = mb_strtolower(end($explode));

            // Adding correct media depends on extension
            // I'm not sure if sass, scss, less works correctly but i've implemented them
            if (in_array($extension, ['css', 'sass', 'scss', 'less'])) {
                $this->context->controller->addCSS($media);
            }
            elseif ($extension == 'js') {
                $this->context->controller->addJS($media);
            }
        }
    }
}
