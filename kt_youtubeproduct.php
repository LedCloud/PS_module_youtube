<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to License,
* that is bundled with this package in the file LICENSE.txt.
* If you did not receive a copy of the license, please send an email
* to connie@diacalc.org so we can send you a copy immediately.
*
* Do not edit or add to this file.
* @author    Konstantin Toporov
* @copyright © 2019 Konstantin Toporov
* @license   LICENSE.txt
* @category  Front Office Features
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once 'YTModel.php';

class Kt_YoutubeProduct extends Module
{
    const PREFIX = 'KT_YOUTUBEPRODUCT_';
    
    const HOOKS = array(
        'EXTRA' => 'displayProductExtraContent', //1.7
        'THUMBS' => 'displayAfterProductThumbs', //1.7.1
        'BUTTONS' => 'displayProductButtons', //1.6
        'ADDINFO' => 'displayProductAdditionalInfo', //the same since 1.7.1
    );

    public function __construct()
    {
        $this->name = 'kt_youtubeproduct';
        $this->tab = 'front_office_features';
        $this->version = '0.2.0';
        $this->author = 'Konstantin Toporov';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Youtube video on product page');
        $this->description = $this->l('Embed youtube video on the product page');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $hookName = self::HOOKS['EXTRA'];
        } else {
            $hookName = self::HOOKS['BUTTONS'];
        }
        
        return parent::install() &&
            $this->registerHook($hookName) &&
            $this->registerHook('actionProductUpdate') &&
            $this->registerHook('displayAdminProductsMainStepLeftColumnBottom');
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall() &&
                $this->unregisterHook('actionProductUpdate') &&
                $this->unregisterHook('displayAdminProductsMainStepLeftColumnBottom');
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        if (((bool)Tools::isSubmit('submitKt_youtubeproductModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitKt_youtubeproductModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Hooks'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => self::HOOKS['BUTTONS'],
                        'name' => self::PREFIX.'BUTTONS',
                        'is_bool' => true,
                        'desc' => $this->l('The video will be under the buttons over the description.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => self::HOOKS['THUMBS'],
                        'name' => self::PREFIX.'THUMBS',
                        'is_bool' => true,
                        'desc' => $this->l('The video will be under the thumb pictures.'),
                        'disabled' => version_compare(_PS_VERSION_, '1.7.1', '<'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => self::HOOKS['EXTRA'],
                        'name' => self::PREFIX.'EXTRA',
                        'is_bool' => true,
                        'desc' => $this->l('The video will inside the description panel on a separate tab.'),
                        'disabled' => version_compare(_PS_VERSION_, '1.7', '<'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
    * Set values for the inputs.
    */
    protected function getConfigFormValues()
    {
        $hookExtra = version_compare(_PS_VERSION_, '1.7', '>=') &&
                $this->isRegisteredInHook(self::HOOKS['EXTRA']);
        
        $hookThumbs = version_compare(_PS_VERSION_, '1.7.1', '>=') &&
                $this->isRegisteredInHook(self::HOOKS['THUMBS']);
        
        
        if (version_compare(_PS_VERSION_, '1.7.1', '>=')) {
            $hookButtonsName = self::HOOKS['ADDINFO'];
        } else {
            $hookButtonsName = self::HOOKS['BUTTONS'];
        }
        $hookButtons = $this->isRegisteredInHook($hookButtonsName);
        
        return array(
            self::PREFIX.'EXTRA' => $hookExtra,
            self::PREFIX.'THUMBS' => $hookThumbs,
            self::PREFIX.'BUTTONS' => $hookButtons,
        );
    }
    
    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $hookButtons = Tools::getValue(self::PREFIX.'BUTTONS');
        if (version_compare(_PS_VERSION_, '1.7.1', '>=')) {
            $hookButtonsName = self::HOOKS['ADDINFO'];
        } else {
            $hookButtonsName = self::HOOKS['BUTTONS'];
        }
        if ($hookButtons && !$this->isRegisteredInHook($hookButtonsName)) {
            //Регистрируем
            $this->registerHook($hookButtonsName);
        } elseif (!$hookButtons && $this->isRegisteredInHook($hookButtonsName)) {
            $this->unregisterHook($hookButtonsName);
        }
        
        $hookThumbs = version_compare(_PS_VERSION_, '1.7.1', '>=') &&
                Tools::getValue(self::PREFIX.'THUMBS');
        if ($hookThumbs && !$this->isRegisteredInHook(self::HOOKS['THUMBS'])) {
            //Регистрируем
            $this->registerHook(self::HOOKS['THUMBS']);
        } elseif (!$hookThumbs && $this->isRegisteredInHook(self::HOOKS['THUMBS'])) {
            $this->unregisterHook(self::HOOKS['THUMBS']);
        }
        
        $hookExtra = version_compare(_PS_VERSION_, '1.7', '>=') &&
                Tools::getValue(self::PREFIX.'EXTRA');
        if ($hookExtra && !$this->isRegisteredInHook(self::HOOKS['EXTRA'])) {
            //Регистрируем
            $this->registerHook(self::HOOKS['EXTRA']);
        } elseif (!$hookExtra && $this->isRegisteredInHook(self::HOOKS['EXTRA'])) {
            $this->unregisterHook(self::HOOKS['EXTRA']);
        }
    }

    public function hookDisplayProductExtraContent($params)
    {
        // > 1.7.0
        $id_product = (int)Tools::getValue('id_product');
        $content = $this->getTemplate($id_product);
        if ($content) {
            return array(
                (new PrestaShop\PrestaShop\Core\Product\ProductExtraContent())
                ->setTitle($this->l('Video'))
                ->setContent($content)
                    );
        }
    }
    
    //public function hookDisplayProductAdditionalInfo()
    public function hookDisplayProductButtons()
    {
        $id_product = (int)Tools::getValue('id_product');
        
        return $this->getTemplate($id_product);
    }
    
    public function hookDisplayAfterProductThumbs()
    {
        //Под thumbs продукта > 1.7.1
        $id_product = (int)Tools::getValue('id_product');
        
        return $this->getTemplate($id_product);
    }
    
    //Для нестандартного шаблона - д.б. две колонки
    public function hookDisplayLeftColumnProduct()
    {
        //Оставить для совместимости
        $id_product = (int)Tools::getValue('id_product');
        
        return $this->getTemplate($id_product);
    }
    
    //Для нестандартного шаблона - д.б. две колонки
    public function hookDisplayRightColumnProduct()
    {
        //Оставить для совместимости
        $id_product = (int)Tools::getValue('id_product');
        
        return $this->getTemplate($id_product);
    }
    
    protected function getTemplate($id_product)
    {
        if ($p = YTModel::getData($id_product)) {
            $this->context->smarty->assign(array(
                'kt_reference' => $p['reference'],
                'kt_params' => $p['params'],
                'kt_origin' => Tools::getCurrentUrlProtocolPrefix() .
                            Tools::getHttpHost(),
            ));
            
            return $this->context->smarty->fetch($this->local_path.'views/templates/hooks/youtubeframe.tpl');
        }
    }


    public function hookDisplayAdminProductsMainStepLeftColumnBottom($params)
    {
        $id_product = $params['id_product'];
        $p = YTModel::getData($id_product);
        
        if (!$p || empty($p)) {
            $p = array(
                'reference' => '',
                'params' => '',
            );
        }
        
        $this->context->smarty->assign(array(
                'kt_reference' => $p['reference'],
                'kt_params' => $p['params'],
            ));
        
        return $this->context->smarty->fetch($this->local_path.'views/templates/hooks/product_yt.tpl');
    }
    
    public function hookUpdateProduct($params)
    {
        require_once 'YTModel.php';
        
        $id_product = $params['id_product'];
        $reference = Tools::getValue('kt_yt_reference');
        $p = Tools::getValue('kt_yt_params');
        
        if (!empty($reference)) {
            YTModel::saveData($id_product, array(
                'reference' => $reference,
                'params' => $p,
            ));
        } else {
            YTModel::deleteData($id_product);
        }
    }
}
