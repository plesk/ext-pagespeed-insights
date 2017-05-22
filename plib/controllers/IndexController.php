<?php
/**
 * Copyright 1999-2017. Parallels IP Holdings GmbH.
 */

/**
 * Class IndexController
 */
class IndexController extends pm_Controller_Action
{
    protected $installation;

    public function init()
    {
        parent::init();

        $this->view->headLink()->appendStylesheet(pm_Context::getBaseUrl().'styles.css');

        // Init title for all actions
        $this->view->pageTitle = $this->lmsg('page_title');
    }

    public function indexAction()
    {
        if (!pm_Session::getClient()->isAdmin()) {
            throw new Exception($this->lmsg('error_only_admin'));
        }

        $this->view->output_description = $this->lmsg('output_description');
        $this->view->installstatus = Modules_PagespeedInsights_Helper::checkPagespeedStatus();
        $this->view->config_default = Modules_PagespeedInsights_Helper::compareConfigFiles();
        $this->view->tools = $this->addToolsButtons($this->view->installstatus, $this->view->config_default);
        $this->view->list = new Modules_PagespeedInsights_List_Overview($this->view, $this->_request);
    }

    /**
     * Adds buttons for the Apache Module integration (install, config and restore)
     *
     * @param bool $installation_status
     * @param bool $config_default
     *
     * @return array
     */
    private function addToolsButtons($installation_status = false, $config_default = true)
    {
        $buttons = array();

        $buttons[] = array(
            'icon'        => pm_Context::getBaseUrl().'/images/tool-apache-install.png',
            'title'       => $installation_status ? $this->lmsg('tool_apache_reinstall') : $this->lmsg('tool_apache_install'),
            'description' => $this->lmsg('tool_apache_info'),
            'controller'  => 'install',
            'action'      => 'index'
        );

        if (!empty($installation_status)) {
            $config_file = $this->lmsg('tool_config_file_default');

            if (empty($config_default)) {
                $config_file = $this->lmsg('tool_config_file_custom');
            }

            $config_description = $this->lmsg('tool_config_edit_description').' '.$config_file;

            $buttons[] = array(
                'icon'        => pm_Context::getBaseUrl().'/images/tool-config-edit.png',
                'title'       => $this->lmsg('tool_config_edit'),
                'description' => $config_description,
                'controller'  => 'config',
                'action'      => 'index'
            );

            if (empty($config_default)) {
                $buttons[] = array(
                    'icon'        => pm_Context::getBaseUrl().'/images/tool-config-restore.png',
                    'title'       => $this->lmsg('tool_config_restore'),
                    'description' => $this->lmsg('tool_config_restore_description'),
                    'controller'  => 'restore',
                    'action'      => 'index'
                );
            }
        }

        return $buttons;
    }

    public function indexDataAction()
    {
        $list = new Modules_PagespeedInsights_List_Overview($this->view, $this->_request);
        $this->_helper->json($list->fetchData());
    }

    public function resultAction()
    {
        $get_global = $this->getRequest()->getQuery();

        if (empty($get_global['site_id'])) {
            $this->view->result = $this->lmsg('error_select_domain_overview');

            return;
        }

        if (!pm_Session::getClient()->hasAccessToDomain($get_global['site_id'])) {
            $this->view->result = $this->lmsg('error_wrong_domain_selected');

            return;
        }

        $domain_object = pm_Domain::getByDomainId($get_global['site_id']);

        if (Modules_PagespeedInsights_Helper::domainAvailable($domain_object) == false) {
            $this->view->result = $this->lmsg('error_domain_not_available');

            return;
        }

        $domain_name = $domain_object->getName();

        if (stripos($domain_name, 'http://') === false) {
            $domain_name = 'http://'.$domain_name;
        }

        $pagespeed_result = Modules_PagespeedInsights_Helper::getPageSpeedApi($domain_name);

        if (is_string($pagespeed_result)) {
            $this->view->result = Modules_PagespeedInsights_Helper::translateString($pagespeed_result);

            return;
        }

        if (!empty($pagespeed_result->ruleGroups->SPEED->score)) {
            pm_Settings::set('pagespeed_score_'.$domain_object->getId(), $pagespeed_result->ruleGroups->SPEED->score);
        }

        $this->view->result = Modules_PagespeedInsights_Helper::createOutputObject($pagespeed_result, $domain_name, $domain_object);
    }
}
