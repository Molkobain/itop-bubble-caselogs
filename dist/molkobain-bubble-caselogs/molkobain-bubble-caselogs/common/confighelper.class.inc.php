<?php
/**
 * Copyright (c) 2015 - 2019 Molkobain.
 *
 * This file is part of licensed extension.
 *
 * Use of this extension is bound by the license you purchased. A license grants you a non-exclusive and non-transferable right to use and incorporate the item in your personal or commercial projects. There are several licenses available (see https://www.molkobain.com/usage-licenses/ for more informations)
 */

namespace Molkobain\iTop\Extension\BubbleCaselogs\Common\Helper;

use Molkobain\iTop\Extension\HandyFramework\Common\Helper\ConfigHelper as BaseConfigHelper;

/**
 * Class ConfigHelper
 *
 * @package Molkobain\iTop\Extension\BubbleCaselogs\Common\Helper
 */
class ConfigHelper extends BaseConfigHelper
{
    const MODULE_NAME = 'molkobain-bubble-caselogs';
    const SETTING_CONST_FQCN = 'Molkobain\\iTop\\Extension\\BubbleCaselogs\\Common\\Helper\\ConfigHelper';

    const DEFAULT_SETTING_DEBUG = false;
	const DEFAULT_SETTING_REVERSE_ORDER = false;
	const DEFAULT_SETTING_ALL_ENTRIES_OPENED = false;
	const DEFAULT_SETTING_DISABLED_IN_BACKOFFICE = false;
	const DEFAULT_SETTING_DISABLED_IN_PORTALS = false;
    const DEFAULT_SETTING_OPEN_ALL_ICON = 'fas fa-book-open';
    const DEFAULT_SETTING_CLOSE_ALL_ICON = 'fas fa-book';
    const DEFAULT_SETTING_ICONS_SEPARATOR = '-';
}
