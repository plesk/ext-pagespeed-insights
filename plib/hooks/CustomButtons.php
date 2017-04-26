<?php
/**
 * Copyright 1999-2017. Parallels IP Holdings GmbH.
 */

/**
 * Class Modules_PagespeedInsights_CustomButtons
 */
class Modules_PagespeedInsights_CustomButtons extends pm_Hook_CustomButtons
{
    /**
     * Adds the custom PageSpeed Insights button
     *
     * @return array
     */
    public function getButtons()
    {
        $buttons = [
            [
                'place'              => self::PLACE_DOMAIN_PROPERTIES,
                'title'              => pm_Locale::lmsg('button_name'),
                'description'        => pm_Locale::lmsg('button_description'),
                'icon'               => pm_Context::getBaseUrl().'images/button_overview.png',
                'link'               => pm_Context::getActionUrl('index', 'result'),
                'additionalComments' => [
                    $this,
                    'getScore'
                ],
                'contextParams'      => true,
                'visibility'         => [
                    $this,
                    'getVisibility'
                ],
            ]
        ];

        return $buttons;
    }

    /**
     * Gets the score for additionalComments
     *
     * @param $options
     *
     * @return string
     */
    public function getScore($options)
    {
        if (empty($options['site_id'])) {
            return '';
        }

        try {
            pm_Context::init('pagespeed-insights');
            $score = pm_Settings::get('pagespeed_score_'.$options['site_id'], false);
            $score_output = '';

            if (!empty($score)) {
                $score_output = pm_Locale::lmsg('button_score', array('score' => $score));
            }

            return $score_output;
        }
        catch (Exception $e) {
            return '';
        }
    }

    /**
     * Gets the visibility state of a specific domain
     *
     * @param $options
     *
     * @return bool
     */
    public function getVisibility($options)
    {
        if (empty($options['site_id'])) {
            return false;
        }

        $domain = pm_Domain::getByDomainId($options['site_id']);
        $domain_available = Modules_PagespeedInsights_Helper::domainAvailable($domain, 'overview');

        if (empty($domain_available)) {
            return false;
        }

        if (!empty($options['alias_id'])) {

            return false;
        }

        return true;
    }
}
