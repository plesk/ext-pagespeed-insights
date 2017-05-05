<?php
/**
 * Copyright 1999-2017. Parallels IP Holdings GmbH.
 */

/**
 * Class InstallController
 */
class InstallController extends pm_Controller_Action
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
        $this->view->output_description = Modules_PagespeedInsights_Helper::addSpanTranslation('output_description_apache', 'description-extension');

        $form = new pm_Form_Simple();
        $this->installationType($form, $pagespeed_status);
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
     * Adds elements to the pm_Form_Simple object depending on installation type
     *
     * @param      $form
     * @param bool $pagespeed_status
     */
    private function installationType(&$form, $pagespeed_status = false)
    {
        $form->addElement('description', 'type_apache_logo', [
            'description' => Modules_PagespeedInsights_Helper::addSpanTranslation('form_type_apache_logo', 'logo-product-apache'),
            'escape'      => false
        ]);

        if (empty($pagespeed_status)) {
            $form->addElement('description', 'apache_install', [
                'description' => Modules_PagespeedInsights_Helper::addSpanTranslation('form_type_apache_install', 'product-installed-apache'),
                'escape'      => false
            ]);
            $form->addElement('checkbox', 'apache', [
                'label'   => $this->lmsg('form_type_apache'),
                'value'   => pm_Settings::get('apache'),
                'checked' => true
            ]);
        } else {
            $form->addElement('description', 'apache_installed', [
                'description' => Modules_PagespeedInsights_Helper::addSpanTranslation('form_type_apache_installed', 'product-installed-apache'),
                'escape'      => false
            ]);
            $form->addElement('checkbox', 'apache', [
                'label'   => $this->lmsg('form_type_apache_reinstall'),
                'value'   => '',
                'checked' => false
            ]);
        }

        $form->addElement('description', 'type_apache_description_note', [
            'description' => Modules_PagespeedInsights_Helper::addSpanTranslation('form_type_apache_description_note', 'description-product'),
            'escape'      => false
        ]);

        return;
    }

    private function addButtons(&$form, $pagespeed_status = false)
    {
        $button_submit = $this->lmsg('form_button_reinstall');

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

        if (empty(pm_View_Status::getAllMessages(false))) {
            $this->_status->addMessage('warning', $this->lmsg('message_warning_installation'));
        }

        $this->_helper->json(['redirect' => pm_Context::getActionUrl('install', 'index')]);
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
}
