<?php
/**
 * Copyright (c) 2015 - 2019 Molkobain.
 *
 * This file is part of licensed extension.
 *
 * Use of this extension is bound by the license you purchased. A license grants you a non-exclusive and non-transferable right to use and incorporate the item in your personal or commercial projects. There are several licenses available (see https://www.molkobain.com/usage-licenses/ for more informations)
 */

namespace Molkobain\iTop\Extension\FontAwesome5\Console\Extension;

use utils;
use iTopWebPage;
use iPageUIExtension;

/**
 * Class ConsoleUIExtension
 *
 * @package Molkobain\iTop\Extension\FontAwesome5\Console\Extension
 */
class PageUIExtension implements iPageUIExtension
{
    /**
     * @inheritdoc
     */
    public function GetNorthPaneHtml(iTopWebPage $oPage)
    {
        $sModuleVersion = utils::GetCompiledModuleVersion('molkobain-fontawesome5-pack');
        $oPage->add_linked_stylesheet(utils::GetAbsoluteUrlModulesRoot() . 'molkobain-fontawesome5-pack/fontawesome-free-5.7.2-web/css/all.min.css?v=' . $sModuleVersion);
        $oPage->add_saas('env-'.utils::GetCurrentEnvironment().'/molkobain-fontawesome5-pack/common/css/fontawesome5-pack.scss');
    }

    /**
     * @inheritdoc
     */
    public function GetSouthPaneHtml(iTopWebPage $oPage)
    {
        // Do nothing.
    }

    /**
     * @inheritdoc
     */
    public function GetBannerHtml(iTopWebPage $oPage)
    {
        // Do nothing.
    }
}
