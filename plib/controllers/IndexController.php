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
        $this->view->list = new Modules_PagespeedInsights_List_Overview($this->view, $this->_request);
        $this->view->installstatus = Modules_PagespeedInsights_Helper::checkPagespeedStatus();
        $this->view->config_default = Modules_PagespeedInsights_Helper::compareConfigFiles();
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
