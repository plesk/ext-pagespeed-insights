<?php
/**
 * Copyright 1999-2017. Parallels IP Holdings GmbH.
 */

/**
 * Class IndexController
 */
class IndexController extends pm_Controller_Action
{
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
            $this->view->error_access = $this->lmsg('error_only_admin');
        }

        $this->view->output_description = $this->lmsg('output_description');
        $this->addConfigLink();

        $this->view->list = new Modules_PagespeedInsights_List_Overview($this->view, $this->_request);
    }

    private function addConfigLink()
    {
        $this->view->output_configlink = '';

        if (pm_ProductInfo::isUnix()) {
            $this->view->output_configlink = $this->lmsg('output_configlink_installed');

            $pagespeed_status = Modules_PagespeedInsights_Helper::checkPagespeedStatus();

            if (empty($pagespeed_status)) {
                $this->view->output_configlink = $this->lmsg('output_configlink_installed_not');
            }
        }
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
