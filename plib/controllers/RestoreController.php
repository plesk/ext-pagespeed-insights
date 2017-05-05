<?php
/**
 * Copyright 1999-2017. Parallels IP Holdings GmbH.
 */

/**
 * Class RestoreController
 */
class RestoreController extends pm_Controller_Action
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

        $this->view->pagespeed_status = Modules_PagespeedInsights_Helper::checkPagespeedStatus();

        if (empty($this->view->pagespeed_status)) {
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
        // Set the description text
        $this->view->output_description = Modules_PagespeedInsights_Helper::addSpanTranslation('output_description_restore', 'description-extension');

        $form = new pm_Form_Simple();
        $this->addConfigTextarea($form);
        $this->addButtons($form);

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
     * Loads the configuration file and adds the textarea for the form
     *
     * @param $form
     */
    private function addConfigTextarea(&$form)
    {
        $config = Modules_PagespeedInsights_Helper::loadConfigFile();

        if (!empty($config) AND !empty($this->view->pagespeed_status)) {
            $form->addElement('checkbox', 'restore', [
                'label'   => $this->lmsg('form_type_config_restore'),
                'value'   => '',
                'checked' => false
            ]);
        }
    }

    private function addButtons(&$form)
    {
        $button_submit = $this->lmsg('form_button_restore');

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
        if ($form->getValue('restore')) {
            if ($this->runRestoreConfig()) {
                $this->_status->addMessage('info', $this->lmsg('message_success_config_restord'));
            } else {
                $this->_status->addMessage('error', $this->lmsg('message_error_config_restord'));
            }
        }

        if (empty(pm_View_Status::getAllMessages(false))) {
            $this->_status->addMessage('warning', $this->lmsg('message_warning_restore'));
        }

        $this->_helper->json(['redirect' => pm_Context::getActionUrl('restore', 'index')]);
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
}
