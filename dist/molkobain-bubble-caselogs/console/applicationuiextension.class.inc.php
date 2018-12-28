<?php
/**
 * Copyright (c) 2015 - 2019 Molkobain.
 *
 * This file is part of licensed extension.
 *
 * Use of this extension is bound by the license you purchased. A license grants you a non-exclusive and non-transferable right to use and incorporate the item in your personal or commercial projects. There are several licenses available (see https://www.molkobain.com/usage-licenses/ for more informations)
 */

namespace Molkobain\iTop\Extension\BubbleCaselogs\Console\Extension;

use utils;
use MetaModel;
use DBObjectSet;
use WebPage;
use iApplicationUIExtension;

/**
 * Class ApplicationUIExtension
 *
 * @package Molkobain\iTop\Extension\BubbleCaselogs\Console\Extension
 */
class ApplicationUIExtension implements iApplicationUIExtension
{
    const MODULE_CODE = 'molkobain-bubble-caselogs';

    const DEFAULT_ENABLED = true;
    const DEFAULT_DEBUG = false;

    /**
     * @inheritdoc
     */
    public function OnDisplayProperties($oObject, WebPage $oPage, $bEditMode = false)
    {
        // Check if enabled
        if(MetaModel::GetConfig()->GetModuleSetting(static::MODULE_CODE, 'enabled', static::DEFAULT_ENABLED) === false)
        {
            return;
        }

        $sModuleVersion = utils::GetCompiledModuleVersion(static::MODULE_CODE);
        $bDebug = (MetaModel::GetConfig()->GetModuleSetting(static::MODULE_CODE, 'debug', static::DEFAULT_DEBUG) === true) ? 'true' : 'false';

        $sEndpoint = utils::GetAbsoluteUrlModulesRoot() . static::MODULE_CODE . '/console/ajax.render.php?operation=format&class=' . get_class($oObject) . '&id=' . $oObject->GetKey();

        // Add js files
        $oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot() . static::MODULE_CODE . '/common/js/bubble-caselogs.js?v=' . $sModuleVersion);

        // Add css files
        $oPage->add_saas('env-' . utils::GetCurrentEnvironment() . '/' . static::MODULE_CODE . '/common/css/bubble-caselogs.scss');

        // Instanciate widget on object's caselogs
        $oPage->add_ready_script(
<<<EOF
    // Molkobain bubble caselogs
    $(document).ready(function(){
        // Initializing widget
        $('.caselog > table').each(function(){
            $.molkobain.bubble_caselogs(
                {
                    debug: {$bDebug},
                    endpoint: '{$sEndpoint}',
                },
                $(this)
            );
        });
    });
EOF

        );

        return;
    }

    /**
     * @inheritdoc
     */
    public function OnDisplayRelations($oObject, WebPage $oPage, $bEditMode = false)
    {
        // Do nothing
    }

    /**
     * @inheritdoc
     */
    public function OnFormSubmit($oObject, $sFormPrefix = '')
    {
        // Do nothing
    }

    /**
     * @inheritdoc
     */
    public function OnFormCancel($sTempId)
    {
        // Do nothing
    }

    /**
     * @inheritdoc
     */
    public function EnumUsedAttributes($oObject)
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function GetIcon($oObject)
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function GetHilightClass($oObject)
    {
        // Possible return values are:
        // HILIGHT_CLASS_CRITICAL, HILIGHT_CLASS_WARNING, HILIGHT_CLASS_OK, HILIGHT_CLASS_NONE
        return HILIGHT_CLASS_NONE;
    }

    /**
     * @inheritdoc
     */
    public function EnumAllowedActions(DBObjectSet $oSet)
    {
        // No action
        return array();
    }
}
