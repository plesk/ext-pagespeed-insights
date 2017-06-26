<?php
/**
 * Copyright 1999-2017. Parallels IP Holdings GmbH.
 */

if (pm_ProductInfo::isUnix()) {
    pm_Loader::registerAutoload();
    pm_Context::init('pagespeed-insights');

    Modules_PagespeedInsights_Helper::preUninstallCheck();
}
