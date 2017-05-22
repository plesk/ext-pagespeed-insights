<?php
/**
 * Copyright 1999-2017. Parallels IP Holdings GmbH.
 */

$messages = [
    'button_description'                   => 'Run PageSpeed Insights now!',
    'button_name'                          => 'Google PageSpeed Insights',
    'button_score'                         => 'Score: %%score%% / 100',
    'error_domain_not_available'           => 'Domain is inactive. Please select an active domain!',
    'error_domain_not_available_list'      => 'Domain is inactive.',
    'error_http_not_200'                   => 'Domain does not answer with HTTP status code 200. Please check manually!',
    'error_only_admin'                     => 'Only administrators are allowed to see this page!',
    'error_select_domain_overview'         => 'Please select a domain from the domain overview in <strong>Websites & Domains</strong>',
    'error_wrong_domain_selected'          => 'Please select a domain that belongs to you in <strong>Websites & Domains</strong>!',
    'form_button_install'                  => 'Install',
    'form_button_reinstall'                => 'Re-Install',
    'form_button_restore'                  => 'Restore',
    'form_button_save'                     => 'Save',
    'form_type_apache'                     => 'Install PageSpeed Apache Module',
    'form_type_apache_description_note'    => 'Note: Static files should be processed by Apache to get the best out of this module. Please deactivate the option "Apache & nginx Settings" &rarr; "nginx settings" &rarr; "Smart static files processing" for all used domains!',
    'form_type_apache_installed'           => '<img src="/modules/pagespeed-insights/images/check.png" alt="PageSpeed Apache is installed" /> PageSpeed Apache Module is installed!',
    'form_type_apache_logo'                => '<img src="/modules/pagespeed-insights/images/pagespeed-apache.png" alt="PageSpeed - Apache module" />',
    'form_type_apache_reinstall'           => 'Re-Install PageSpeed Apache Module',
    'form_type_config'                     => 'Configuration file',
    'form_type_config_restore'             => 'Yes, restore default configuration file',
    'form_type_config_restore_description' => 'Don\'t worry, you can always restore to the initial default configuration file that was created during the first installation process! Select the checkbox below to restore the file.',
    'hint_ip_does_not_resolve'             => 'Using a proxy? Domain does not resolve to the Plesk server\'s IP address.',
    'message_error_config'                 => 'An error occurred! Please check the syntax or restore from the default configuration file.',
    'message_error_config_restord'         => 'An error occurred! Configuration file could not be restored successfully!',
    'message_success'                      => 'Data has been saved successfully!',
    'message_success_apache'               => 'PageSpeed Apache Module has been installed successfully!',
    'message_success_config'               => 'Configuration file has been saved successfully!',
    'message_success_config_restord'       => 'Configuration file has been restored successfully!',
    'message_warning_installation'         => 'Installation was not executed. Please select the corresponding check box!',
    'message_warning_restore'              => 'Restoration was not executed. Please select the corresponding check box!',
    'output_configlink_installed'          => 'The <strong>Google Pagespeed Apache Module</strong> is installed. It helps you to improve the performance increasingly! <a href="/modules/pagespeed-insights/index.php/config">Go to the configuration page</a>.',
    'output_configlink_installed_not'      => 'The <strong>Google Pagespeed Apache Module</strong> is not installed. It can help you to improve the performance increasingly! <a href="/modules/pagespeed-insights/index.php/config">Go to the installation page</a>.',
    'output_description'                   => 'With PageSpeed Insights you can identify ways to make your site faster and more mobile-friendly. Read more about PageSpeed Insights and how to improve the website performance: <a href="https://www.plesk.com/product-technology/google-pagespeed-insights-optimize-your-site" target="_blank" title=" Google PageSpeed Insights – How to optimize your site to rank higher">Google PageSpeed Insights – How to optimize your site to rank higher</a>',
    'output_description_apache'            => 'The Apache module <strong>mod_pagespeed</strong> is an open-source module created by Google to help <strong>Make the Web Faster</strong> by rewriting web pages to reduce latency and bandwidth. mod_pagespeed automatically applies web performance best practices to pages, and associated assets (CSS, JavaScript, images) without requiring that you modify your existing content or workflow.<br />Learn more about the <a href="https://developers.google.com/speed/pagespeed/module/" target="_blank">PageSpeed Module</a>.',
    'output_description_config'            => 'You can adjust the Apache module configuration file for your needs. If you get an error message although your input was saved, then the Apache server could not be restarted due to a syntax error. In such a case, you should correct your input or restore the default configuration file.<br /><br /><strong>Attention: Modify the file only if you know what you are doing!</strong>',
    'output_description_index'             => 'Please select a domain from the domain overview in <strong>Websites & Domains</strong> to start the check process!',
    'output_description_restore'           => 'You can restore the default configuration file from the Apache module. This backup file was created during the first installation process.<br /><br /><strong>Attention: All possible changes will be overwritten!</strong>',
    'output_select_domain'                 => 'Domain Overview',
    'page_title'                           => 'Google PageSpeed Insights',
    'page_title_apache'                    => 'PageSpeed - Apache Module by Google',
    'page_title_config'                    => 'PageSpeed - Apache Module - Edit configuration file',
    'result_cssResponseBytes'              => 'CSS response size:',
    'result_download_compressed_files'     => 'Download optimized <a href="%%domain%%">image, JavaScript, and CSS resources</a> for this page.',
    'result_htmlResponseBytes'             => 'HTML response size:',
    'result_id'                            => 'PageSpeed Insights result for',
    'result_imageResponseBytes'            => 'Image response size:',
    'result_impact_consider_fixing'        => 'Consider Fixing',
    'result_impact_label'                  => 'Impact:',
    'result_impact_passed'                 => 'Passed',
    'result_impact_should_fix'             => 'Should Fix',
    'result_internal_apache_nginx'         => 'Apache &amp; nginx Settings',
    'result_internal_hosting_settings'     => 'Hosting Settings',
    'result_javascriptResponseBytes'       => 'JavaScript response size:',
    'result_numberCssResources'            => 'Number of CSS resources:',
    'result_numberHosts'                   => 'Number of hosts:',
    'result_numberJsResources'             => 'Number of JS resources:',
    'result_numberResources'               => 'Total number of resources:',
    'result_numberStaticResources'         => 'Number of static resources:',
    'result_otherResponseBytes'            => 'Other Response Size:',
    'result_overall_score'                 => '%%score%% / 100',
    'result_overall_score_lable'           => 'Result:',
    'result_page_stats'                    => 'Page Statistics',
    'result_rules'                         => 'Suggestions Summary',
    'result_title_lable'                   => 'Title:',
    'result_total_response'                => 'Total response size:',
    'result_totalRequestBytes'             => 'Total request size:',
    'score_not_available'                  => 'No score yet',
    'table_analyze'                        => 'Analyze',
    'table_details'                        => 'Details',
    'table_domain'                         => 'Domain',
    'table_domain_score'                   => 'PageSpeed Score',
    'table_recheck'                        => 'Re-Check',
    'tool_apache_info'                     => 'This will bring you to the installation page.',
    'tool_apache_install'                  => 'Install PageSpeed Apache Module',
    'tool_apache_reinstall'                => 'Re-Install PageSpeed Apache Module',
    'tool_config_edit'                     => 'Edit configuration file',
    'tool_config_edit_description'         => 'Edit the configuration file of the Apache module as you need.',
    'tool_config_file_custom'              => 'Custom configuration file is active.',
    'tool_config_file_default'             => 'Default configuration file is active.',
    'tool_config_restore'                  => 'Restore configuration file',
    'tool_config_restore_description'      => 'Restore the default configuration file of the Apache module.',
];
