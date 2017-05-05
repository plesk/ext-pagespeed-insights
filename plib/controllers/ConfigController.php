<?php
/**
 * Copyright 1999-2017. Parallels IP Holdings GmbH.
 */

/**
 * Class ConfigController
 */
class ConfigController extends pm_Controller_Action
{
    protected $_accessLevel = 'admin';

    public function init()
    {
        parent::init();

        $this->view->headLink()->appendStylesheet(pm_Context::getBaseUrl().'styles.css');

        // Set page title for all actions
        $this->view->pageTitle = $this->lmsg('page_title_apache');
    }

    /**
     * Forward index action to form action
     */
    public function indexAction()
    {
        if (!pm_ProductInfo::isUnix()) {
            $this->_redirect(pm_Context::getBaseUrl());
        }

        // Default action is formAction
        $this->_forward('form');
    }

    /**
     * Default action which creates the form in the settings and processes the requests
     */
    public function formAction()
    {
        $pagespeed_status = Modules_PagespeedInsights_Helper::checkPagespeedStatus();

        // Set the description text
        $this->view->output_description = $this->addSpanTranslation('output_description_apache', 'description-extension');

        // Init form here
        $form = new pm_Form_Simple();
        $this->installationType($form, $pagespeed_status);
        $this->addConfigTextarea($form, $pagespeed_status);
        $this->addButtons($form, $pagespeed_status);

        // Process the form - save the license key and run the installation scripts
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            try {
                $this->processPostRequest($form);
            }
            catch (Exception $e) {
                $this->_status->addMessage('error', $e->getMessage());
            }
        }

        $this->view->form = $form;
    }

    /**
     * Adds a span element with a CSS class to the form field and removes not provided language strings
     *
     * @param string $language_string
     * @param string $class_name
     *
     * @return string
     */
    private function addSpanTranslation($language_string, $class_name, $language_string_params = array())
    {
        $translated_string = $this->lmsg($language_string, $language_string_params);

        if ($translated_string == '[['.$language_string.']]') {
            $translated_string = '';
        }

        $span_element = '<span class="'.$class_name.'">'.$translated_string.'</span>';

        return $span_element;
    }

    /**
     * Adds elements to the pm_Form_Simple object depending on installation type
     *
     * @param      $form
     * @param bool $pagespeed_status
     */
    private function installationType(&$form, $pagespeed_status = false)
    {
        $form->addElement('description', 'type_apache_logo', [
            'description' => $this->addSpanTranslation('form_type_apache_logo', 'logo-product-apache'),
            'escape'      => false
        ]);

        if (empty($pagespeed_status)) {
            $form->addElement('description', 'apache_install', [
                'description' => $this->addSpanTranslation('form_type_apache_install', 'product-installed-apache'),
                'escape'      => false
            ]);
            $form->addElement('checkbox', 'apache', [
                'label'   => $this->lmsg('form_type_apache'),
                'value'   => pm_Settings::get('apache'),
                'checked' => true
            ]);
        } else {
            $form->addElement('description', 'apache_installed', [
                'description' => $this->addSpanTranslation('form_type_apache_installed', 'product-installed-apache'),
                'escape'      => false
            ]);
            $form->addElement('checkbox', 'apache', [
                'label'   => $this->lmsg('form_type_apache_reinstall'),
                'value'   => '',
                'checked' => false
            ]);
        }

        $form->addElement('description', 'type_apache_description_note', [
            'description' => $this->addSpanTranslation('form_type_apache_description_note', 'description-product'),
            'escape'      => false
        ]);

        return;
    }

    /**
     * Loads the configuration file and adds the textarea for the form
     *
     * @param $form
     */
    private function addConfigTextarea(&$form, $pagespeed_status = false)
    {
        $config = Modules_PagespeedInsights_Helper::loadConfigFile();

        if (!empty($config) AND !empty($pagespeed_status)) {
            $form->addElement('description', 'config_file', [
                'description' => $this->addSpanTranslation('form_type_config_file', 'logo-config-file'),
                'escape'      => false
            ]);

            $form->addElement('textarea', 'config', [
                'label'      => $this->lmsg('form_type_config'),
                'value'      => $config,
                'class'      => 'f-max-size',
                'rows'       => 10,
                'required'   => true,
                'validators' => [
                    [
                        'NotEmpty',
                        true
                    ],
                ],
            ]);
            $form->addElement('description', 'restore_description', [
                'description' => $this->addSpanTranslation('form_type_config_restore_description', 'description-restore'),
                'escape'      => false
            ]);
            $form->addElement('checkbox', 'restore', [
                'label'   => $this->lmsg('form_type_config_restore'),
                'value'   => '',
                'checked' => false
            ]);
        }
    }

    private function addButtons(&$form, $pagespeed_status = false)
    {
        $button_submit = $this->lmsg('form_button_save');

        if (empty($pagespeed_status)) {
            $button_submit = $this->lmsg('form_button_install');
        }

        $form->addControlButtons([
            'sendTitle'  => $button_submit,
            'cancelLink' => pm_Context::getActionUrl('index', 'index'),
        ]);
    }

    /**
     * Processes POST request - after form submission
     *
     * @param $form
     */
    private function processPostRequest($form)
    {
        if ($form->getValue('apache')) {
            if ($this->runInstallation('apache')) {
                Modules_PagespeedInsights_Helper::backupConfigFile();
                $this->_status->addMessage('info', $this->lmsg('message_success_apache'));
            }
        }

        if ($form->getValue('restore')) {
            if ($this->runRestoreConfig()) {
                $this->_status->addMessage('info', $this->lmsg('message_success_config_restord'));
            }
        } elseif ($form->getValue('config')) {
            if ($this->runSaveConfig($form->getValue('config'))) {
                $this->_status->addMessage('info', $this->lmsg('message_success_config'));
            }
            else {
                $this->_status->addMessage('error', $this->lmsg('message_error_config'));
            }
        }

        if (empty(pm_View_Status::getAllMessages(false))) {
            $this->_status->addMessage('warning', $this->lmsg('message_warning_installation'));
        }

        $this->_helper->json(['redirect' => pm_Context::getActionUrl('config', 'index')]);
    }

    /**
     * Starts the installation process of the service using shell scripts
     *
     * @param string $type
     *
     * @return bool
     * @throws pm_Exception
     */
    private function runInstallation($type)
    {
        $options = array();

        $result = pm_ApiCli::callSbin($type.'.sh', $options, pm_ApiCli::RESULT_FULL);

        if ($result['code']) {
            throw new pm_Exception("{$result['stdout']}\n{$result['stderr']}");
        }

        return true;
    }

    /**
     * Restores the configuration file with the default file that was stored after the installation
     *
     * @return bool
     */
    private function runRestoreConfig()
    {
        $config_restored = (bool) Modules_PagespeedInsights_Helper::restoreConfigFile();

        if (!empty($config_restored)) {
            return true;
        }

        return false;
    }

    /**
     * Saves the entered data to the PageSpeed configuration file
     *
     * @param $config
     *
     * @return bool
     */
    private function runSaveConfig($config)
    {
        $config_saved = (bool) Modules_PagespeedInsights_Helper::saveConfigFile($config);

        if (!empty($config_saved)) {
            return true;
        }

        return false;
    }

    /**
     * Checks state of the transferred option name which defines the state of the installation
     *
     * @param string $type
     *
     * @return null|string
     */
    private function getSettingsValue($type)
    {
        return pm_Settings::get($type, false);
    }
}
