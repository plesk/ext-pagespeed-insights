<?php
// Copyright 1999-2017. Parallels IP Holdings GmbH.

class Modules_PagespeedInsights_List_Overview extends pm_View_List_Simple
{
    public function __construct(Zend_View $view, Zend_Controller_Request_Abstract $request)
    {
        parent::__construct($view, $request);

        $data = $this->getData();
        $this->setData($data);
        $this->setColumns(
            array(
                'column-1' => array(
                    'title'      => $this->lmsg('table_domain'),
                    'noEscape'   => true,
                    'searchable' => true,
                    'sortable'   => true,
                ),
                'column-2' => array(
                    'title'    => $this->lmsg('table_domain_score'),
                    'noEscape' => true,
                    'sortable' => true,
                ),
                'column-3' => array(
                    'title'    => '',
                    'noEscape' => true,
                    'sortable' => false,
                )
            )
        );

        $this->setDataUrl(['action' => 'index-data']);
    }

    /**
     * Gets the domains array for the list
     *
     * @return array
     */
    private function getData()
    {
        $data = array();
        $domains_all = pm_Domain::getAllDomains();

        foreach ($domains_all as $domain) {
            if ($domain->isActive() AND $domain->hasHosting()) {
                $site_id = $domain->getId();

                $class = 'list_subdomain';
                $dom_id = $domain->getProperty('parentDomainId');

                if ($dom_id == 0) {
                    $dom_id = $site_id;
                    $class = 'list_domain';
                }

                $domain_score = $this->getDomainScore($site_id);
                $action_link_string = $this->lmsg('table_details');

                if (empty($domain_score)) {
                    $domain_score = '<span class="score-no">'.$this->lmsg('score_not_available').'</span>';
                    $action_link_string = $this->lmsg('table_analyze');
                }

                $resolving = pm_Settings::get('pagespeed_resolving_'.$site_id, true);
                $domain_available = Modules_PagespeedInsights_Helper::domainAvailable($domain, 'index');

                if (empty($resolving) OR empty($domain_available)) {

                    $domain_score = $this->lmsg('error_domain_not_available_list');
                    $action_link_string = $this->lmsg('table_recheck');
                }

                $action_link = '<a href="'.pm_Context::getActionUrl('index', 'result').'?site_id='.$site_id.'">'.$action_link_string.'</a>';

                $data[] = array(
                    'column-1' => '<span class="domainid'.$dom_id.str_pad($site_id, 3, '0', STR_PAD_LEFT).' '.$class.'">'.$domain->getDisplayName().'</span>',
                    'column-2' => $domain_score,
                    'column-3' => $action_link,
                );
            }
        }

        return $data;
    }

    /**
     * Gets the domain score from the database and adds CSS style if available else normal status message
     *
     * @param $id
     *
     * @return bool|string
     */
    private function getDomainScore($id)
    {
        $score = (int) pm_Settings::get('pagespeed_score_'.$id, false);

        if (!empty($score)) {
            $score_class = 'result_green';

            if ($score < 85 AND $score >= 65) {
                $score_class = 'result_orange';
            } elseif ($score < 65) {
                $score_class = 'result_red';
            }

            $score_span = '<span class="score'.str_pad($score, 3, '0', STR_PAD_LEFT).' '.$score_class.'">'.$score.'</span>';

            return $this->lmsg('result_overall_score', ['score' => $score_span]);
        }

        return false;
    }
}
