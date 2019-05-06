<?php
/**
 * Copyright (c) 2015 - 2019 Molkobain.
 *
 * This file is part of licensed extension.
 *
 * Use of this extension is bound by the license you purchased. A license grants you a non-exclusive and non-transferable right to use and incorporate the item in your personal or commercial projects. There are several licenses available (see https://www.molkobain.com/usage-licenses/ for more informations)
 */

namespace Molkobain\iTop\Extension\FontAwesome5\Portal\Extension;

use utils;
use AbstractPortalUIExtension;
use Silex\Application;

// Protection for iTop 2.3 and older
if(class_exists('AbstractPortalUIExtension'))
{
	/**
	 * Class PortalUIExtension
	 *
	 * @package Molkobain\iTop\Extension\FontAwesome5\Portal\Extension
	 */
	class PortalUIExtension extends AbstractPortalUIExtension
	{
		/**
		 * @inheritdoc
		 */
		public function GetCSSFiles(Application $oApp)
		{
			$aReturn = array();

			$aReturn[] = utils::GetAbsoluteUrlModulesRoot() . 'molkobain-fontawesome5-pack/fontawesome-free-5.7.2-web/css/all.min.css?v=' . utils::GetCompiledModuleVersion('molkobain-fontawesome5-pack');
			$aReturn[] = utils::GetAbsoluteUrlModulesRoot() . 'molkobain-fontawesome5-pack/common/css/fontawesome5-pack.css?v=' . utils::GetCompiledModuleVersion('molkobain-fontawesome5-pack');

			return $aReturn;
		}
	}
}
