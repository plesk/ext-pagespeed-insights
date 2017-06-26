<?php
/**
 * Copyright 1999-2017. Parallels IP Holdings GmbH.
 */

/**
 * Class Modules_PagespeedInsights_Helper
 */
class Modules_PagespeedInsights_Helper
{
    /**
     * Makes a cURL request to the Google PageSpeed Insights API
     *
     * @param $url
     *
     * @return mixed|string
     */
    public static function getPageSpeedApi($url)
    {
        if (!empty($url)) {
            $pagespeed_api_url = 'https://www.googleapis.com/pagespeedonline/v2/runPagespeed';

            // Set checked URL
            $pagespeed_api_url .= '?url='.rawurlencode($url);

            // Add screenshot
            $pagespeed_api_url .= '&screenshot=true';

            // Set language code
            $pagespeed_api_url .= '&locale='.substr(pm_Locale::getCode(), 0, 2);

            $client = new Zend_Http_Client($pagespeed_api_url);

            try {
                $pagespeed_result = $client->request(Zend_Http_Client::GET);
            }
            catch (Exception $e) {
                return $e->getMessage();
            }

            if ($pagespeed_result->isError()) {
                return $pagespeed_result->getMessage()."\n\n".$pagespeed_result->getRawBody();
            }

            $pagespeed_result = json_decode($pagespeed_result->getBody());

            if ($pagespeed_result->responseCode != 200) {
                return 'error_http_not_200';
            }

            return $pagespeed_result;
        }
    }

    /**
     * Removes square brackets from not provided language strings, needed for status response from API call
     *
     * @param string $language_string
     * @param array  $language_string_params
     *
     * @return string
     */
    public static function translateString($language_string, $language_string_params = array())
    {
        $translated_string = pm_Locale::lmsg($language_string, $language_string_params);

        if ($translated_string == '[['.$language_string.']]') {
            $translated_string = $language_string;
        }

        return $translated_string;
    }

    /**
     * Adds a span element with a CSS class to the form field and removes not provided language strings
     *
     * @param string $language_string
     * @param string $class_name
     *
     * @return string
     */
    public static function addSpanTranslation($language_string, $class_name, $language_string_params = array())
    {
        $translated_string = pm_Locale::lmsg($language_string, $language_string_params);

        if ($translated_string == '[['.$language_string.']]') {
            $translated_string = '';
        }

        $span_element = '<span class="'.$class_name.'">'.$translated_string.'</span>';

        return $span_element;
    }

    /**
     * Creates the output object from the PageSpeed Insights response data
     *
     * @param object    $pagespeed_data
     * @param string    $name
     * @param pm_Domain $domain_object
     *
     * @return stdClass
     */
    public static function createOutputObject($pagespeed_data, $name, $domain_object)
    {
        $result = new stdClass();
        $result->id = $pagespeed_data->id;
        $result->name = $domain_object->getDisplayName();
        $result->site_id = $domain_object->getId();
        $result->domain_id = self::getDomainId($domain_object);

        if (!empty($pagespeed_data->screenshot)) {
            $result->screenshot = $pagespeed_data->screenshot;
        }

        if (!empty($pagespeed_data->title)) {
            $result->title = $pagespeed_data->title;
        }

        $result->score = $pagespeed_data->ruleGroups->SPEED->score;
        self::getPageStats($pagespeed_data->pageStats, $result);
        $result->compressed_files = self::getCompressedFiles($name);
        self::getRuleResults($pagespeed_data->formattedResults->ruleResults, $result);

        return $result;
    }

    /**
     * Gets the main domain ID (= Webspace ID)
     *
     * @param pm_Domain $domain_object
     *
     * @return mixed
     */
    private static function getDomainId($domain_object)
    {
        $domain_id = (int) $domain_object->getProperty('parentDomainId');

        if ($domain_id == 0) {
            return $domain_object->getId();
        }

        return $domain_id;
    }

    /**
     * Loads all relevant page stats from the JSON response
     *
     * @param $stats
     * @param $result
     */
    private static function getPageStats($stats, &$result)
    {
        $result->stats = new stdClass();
        $total_size = 0;
        $totel_size_keys = array(
            'htmlResponseBytes',
            'cssResponseBytes',
            'imageResponseBytes',
            'javascriptResponseBytes',
            'otherResponseBytes'
        );

        foreach ($stats as $key => $value) {
            if (in_array($key, $totel_size_keys)) {
                $total_size += $value;
            }

            if (stripos($key, 'bytes') !== false) {
                $value = self::formatBytes($value);
            }

            $result->stats->$key = $value;
        }

        $result->stats->{'total_response'} = self::formatBytes($total_size);
    }

    /**
     * Helper function to format bytes
     *
     * @param $bytes
     *
     * @return string
     */
    private static function formatBytes($bytes)
    {
        $base = log($bytes, 1024);
        $suffixes = array(
            'B',
            'KiB',
            'MiB',
            'GiB',
            'TiB'
        );

        return round(pow(1024, $base - floor($base)), 2).' '.$suffixes[(int) (floor($base))];
    }

    /**
     * Creates the compressed files API link with the correct domain ID
     *
     * @param $domain
     *
     * @return string
     */
    private static function getCompressedFiles($domain)
    {
        $compressed_file_api = 'https://developers.google.com/speed/pagespeed/insights/optimizeContents?url='.rawurlencode($domain).'&strategy=desktop';

        return $compressed_file_api;
    }

    /**
     * Entry function to get the complete rule result
     *
     * @param $rules
     * @param $result
     */
    private static function getRuleResults($rules, &$result)
    {
        $result->rules = new stdClass();

        foreach ($rules as $key => $value) {
            $result->rules->$key = new stdClass();
            $result->rules->$key->name = $value->localizedRuleName;
            $result->rules->$key->impact = $value->ruleImpact;
            $result->rules->$key->summary = self::getImpactSummary($value->summary);
            $result->rules->$key->url_blocks = new stdClass();
            self::getImpactUrlBlocks($value->urlBlocks, $result->rules->$key->url_blocks);
        }
    }

    /**
     * Gets the impact summary
     *
     * @param $summary
     *
     * @return string
     */
    private static function getImpactSummary($summary)
    {
        $summary_output = '';

        if (!empty($summary)) {
            $summary_output .= self::getImpactFormat($summary);
        }

        return $summary_output;
    }

    /**
     * Gets the impact format output with all possible replacements
     *
     * @param $format
     *
     * @return mixed|string
     */
    private static function getImpactFormat($format)
    {
        $format_output = '';

        if (!empty($format->format)) {
            $format_output = $format->format;

            if (!empty($format->args)) {
                foreach ($format->args as $key => $value) {
                    if ($value->type == 'HYPERLINK') {
                        $format_output = preg_replace('@{{BEGIN_LINK}}(.*){{END_LINK}}@', '<a href="'.$value->value.'" target="_blank">$1</a>', $format_output);
                    } elseif ($value->type == 'SNAPSHOT_RECT') {
                        $format_output = str_replace('{{'.$value->key.'}}', '('.ucfirst(strtolower($value->key)).')', $format_output);
                    } elseif (in_array($value->type, array(
                        'INT_LITERAL',
                        'BYTES',
                        'PERCENTAGE',
                        'DURATION',
                        'URL'
                    ))) {
                        $format_output = str_replace('{{'.$value->key.'}}', $value->value, $format_output);
                    }
                }
            }
        }

        return $format_output;
    }

    /**
     * Gets the impact URL blocks
     *
     * @param $url_blocks
     * @param $result
     */
    private static function getImpactUrlBlocks($url_blocks, &$result)
    {
        if (!empty($url_blocks)) {
            foreach ($url_blocks as $key => $url_block) {
                $result->$key = new stdClass();
                if (!empty($url_block->header)) {
                    $result->$key->header = self::getImpactFormat($url_block->header);
                }

                if (!empty($url_block->urls)) {
                    $result->$key->urls = new stdClass();
                    self::getImpactUrlBlocksUrls($url_block->urls, $result->$key->urls);
                }
            }
        }
    }

    /**
     * Gets all URLs within a URL block
     *
     * @param $urls
     * @param $result
     */
    private static function getImpactUrlBlocksUrls($urls, &$result)
    {
        foreach ($urls as $key => $url) {
            if (!empty($url->result)) {
                $result->$key = new stdClass();
                $result->$key = self::getImpactFormat($url->result);
            }
        }
    }

    /**
     * Checks the availability of the requested domain
     *
     * @param pm_Domain $domain_object
     * @param string    $action
     *
     * @return bool
     */
    public static function domainAvailable($domain_object, $action = 'result')
    {
        if (!$domain_object->isActive() OR !$domain_object->hasHosting()) {
            return false;
        }

        if ($domain_object->isSuspended() OR $domain_object->isDisabled()) {
            return false;
        }

        if ($action == 'result') {
            self::isResolvingToPlesk($domain_object);
        }

        return true;
    }

    /**
     * Checks whether domain name is resolving to Plesk server directly - if not then the domain name is pointing to another
     * IP address what happens if the user is using a proxy server or the domain is not mapping the Plesk server at all
     *
     * @param pm_Domain $domain_object
     *
     * @return bool
     */
    private static function isResolvingToPlesk($domain_object)
    {
        $ip_resolving = false;

        try {
            $records = @dns_get_record($domain_object->getName(), DNS_A | DNS_AAAA);
        }
        catch (Exception $e) {

            return $ip_resolving;
        }

        if (empty($records)) {
            return $ip_resolving;
        }

        $domain_ip_addresses = $domain_object->getIpAddresses();

        foreach ($records as $record) {
            $ip_address = '';

            if (isset($record['ip'])) {
                $ip_address = $record['ip'];
            } elseif (isset($record['ipv6'])) {
                $ip_address = $record['ipv6'];
            }

            foreach ($domain_ip_addresses as $domain_ip) {
                if ($ip_address === $domain_ip) {
                    $ip_resolving = true;
                }
            }
        }

        pm_Settings::set('pagespeed_resolving_'.$domain_object->getId(), $ip_resolving);

        return $ip_resolving;
    }

    /**
     * Runs shell script in the postInstallCheck trigger
     */
    public static function postInstallCheck()
    {
        pm_ApiCli::callSbin('postinstallcheck.sh');
    }

    /**
     * Runs shell script in the preUninstallCheck trigger
     */
    public static function preUninstallCheck()
    {
        pm_ApiCli::callSbin('preuninstallcheck.sh');
    }

    /**
     * Helper function to backup the configuration file
     */
    public static function backupConfigFile()
    {
        $file_manager = new pm_ServerFileManager();

        // First create a backup of the initial config file - only once in the first saving process
        if (!$file_manager->fileExists('/usr/local/psa/var/modules/pagespeed-insights/pagespeed.conf')) {
            $config_data_ori = self::loadConfigFile();

            if (!empty($config_data_ori)) {
                $file_manager->filePutContents('/usr/local/psa/var/modules/pagespeed-insights/pagespeed.conf', $config_data_ori);
            }
        }
    }

    /**
     * Helper function to load the configuration file data
     *
     * @return bool|string
     */
    public static function loadConfigFile()
    {
        $file_manager = new pm_ServerFileManager();
        $config_path = self::getConfigPath();

        if (!empty($config_path)) {
            $config = $file_manager->fileGetContents($config_path);

            if (!empty($config)) {
                return $config;
            }
        }

        return false;
    }

    /**
     * Gets the configuration path depending on the operation system
     *
     * @return bool|string
     */
    private static function getConfigPath()
    {
        $file_manager = new pm_ServerFileManager();

        if ($file_manager->fileExists('/etc/httpd/conf.d/pagespeed.conf')) {
            // CentOS
            return '/etc/httpd/conf.d/pagespeed.conf';
        } elseif ($file_manager->fileExists('/etc/apache2/mods-available/pagespeed.conf')) {
            // Ubuntu
            return '/etc/apache2/mods-available/pagespeed.conf';
        }

        return false;
    }

    /**
     * Helper function to save the transmitted data to the configuration file
     *
     * @param $config_data
     *
     * @return bool
     */
    public static function saveConfigFile($config_data)
    {
        if (!empty($config_data)) {
            $file_manager = new pm_ServerFileManager();
            $config_path = self::getConfigPath();

            if (!empty($config_path)) {
                $file_manager->filePutContents($config_path, $config_data);

                // Restart web server to activate changes
                $result = pm_ApiCli::callSbin('restart_server.sh', array(), pm_ApiCli::RESULT_FULL);

                if ($result['code']) {
                    return false;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Helper function to restore the previously stored configuration file
     *
     * @return bool
     */
    public static function restoreConfigFile()
    {
        $file_manager = new pm_ServerFileManager();

        // Check for the backup file and restore from it if exists
        if ($file_manager->fileExists('/usr/local/psa/var/modules/pagespeed-insights/pagespeed.conf')) {
            $config_data_backup = $file_manager->fileGetContents('/usr/local/psa/var/modules/pagespeed-insights/pagespeed.conf');
            $config_path = self::getConfigPath();

            if (!empty($config_data_backup) and !empty($config_path)) {
                $file_manager->filePutContents(self::getConfigPath(), $config_data_backup);

                // Restart web server to activate changes
                $result = pm_ApiCli::callSbin('restart_server.sh', array(), pm_ApiCli::RESULT_FULL);

                if ($result['code']) {
                    return false;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Compares default file with current configuration file
     *
     * @return bool
     */
    public static function compareConfigFiles()
    {
        $file_manager = new pm_ServerFileManager();

        // Compare content of the backup file with the content of the current configuration file
        if ($file_manager->fileExists('/usr/local/psa/var/modules/pagespeed-insights/pagespeed.conf')) {
            $config_data_backup = $file_manager->fileGetContents('/usr/local/psa/var/modules/pagespeed-insights/pagespeed.conf');
            $config_data_current = $file_manager->fileGetContents(self::getConfigPath());

            if ($config_data_backup == $config_data_current) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks the status of the Pagespeed Apache module installation
     *
     * @return bool
     */
    public static function checkPagespeedStatus()
    {
        if (!pm_ProductInfo::isUnix()) {
            return false;
        }

        // Restart web server to activate changes
        $result = pm_ApiCli::callSbin('pagespeed_installed.sh', array(), pm_ApiCli::RESULT_FULL);

        if (!empty($result['code']) OR empty(trim($result['stdout']))) {
            return false;
        }

        return true;
    }
}
