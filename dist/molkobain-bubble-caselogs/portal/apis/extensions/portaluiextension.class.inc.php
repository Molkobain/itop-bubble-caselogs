<?php
/**
 * Copyright (c) 2015 - 2019 Molkobain.
 *
 * This file is part of licensed extension.
 *
 * Use of this extension is bound by the license you purchased. A license grants you a non-exclusive and non-transferable right to use and incorporate the item in your personal or commercial projects. There are several licenses available (see https://www.molkobain.com/usage-licenses/ for more informations)
 */

namespace Molkobain\iTop\Extension\BubbleCaselogs\Portal\Extension;

use utils;
use MetaModel;
use AbstractPortalUIExtension;
use Silex\Application;

/**
 * Class PortalUIExtension
 *
 * @package Molkobain\iTop\Extension\BubbleCaselogs\Portal\Extension
 */
class PortalUIExtension extends AbstractPortalUIExtension
{
    const MODULE_CODE = 'molkobain-bubble-caselogs';

    const DEFAULT_ENABLED = true;
    const DEFAULT_DEBUG = false;

    /**
     * @inheritdoc
     */
    public function GetCSSFiles(Application $oApp)
    {
        $sModuleVersion = utils::GetCompiledModuleVersion(static::MODULE_CODE);
        $sURLBase = utils::GetAbsoluteUrlModulesRoot() . '/' . static::MODULE_CODE . '/';

        $aReturn = array(
            $sURLBase . 'common/css/bubble-caselogs.css?v=' . $sModuleVersion,
        );

        return $aReturn;
    }

    /**
     * @inheritdoc
     */
    public function GetJSFiles(Application $oApp)
    {
        $sModuleVersion = utils::GetCompiledModuleVersion(static::MODULE_CODE);
        $sURLBase = utils::GetAbsoluteUrlModulesRoot() . '/' . static::MODULE_CODE . '/';

        $aJSFiles = array(
            $sURLBase . 'common/js/bubble-caselogs.js?v=' . $sModuleVersion,
        );

        return $aJSFiles;
    }

    /**
     * @inheritdoc
     */
    public function GetJSInline(Application $oApp)
    {
        // Check if enabled
        if(MetaModel::GetConfig()->GetModuleSetting(static::MODULE_CODE, 'enabled', static::DEFAULT_ENABLED) === false)
        {
            return '';
        }

        $bDebug = (MetaModel::GetConfig()->GetModuleSetting(static::MODULE_CODE, 'debug', static::DEFAULT_DEBUG) === true) ? 'true' : 'false';

        $sEndpointTemplate = utils::GetAbsoluteUrlModulesRoot() . static::MODULE_CODE . '/console/ajax.render.php?operation=format&class=-objectClass-&id=-objectId-';

        $sJSInline =
<<<EOF
// Make the endpoint url from the current object url as we don't have any way to know which object class/id we are manipulating...
function GetBubbleCaselogsEndpointUrl(sCurrentUrl)
{
    var sEndpoint = sCurrentUrl;
    var sEndpointTemplate = '{$sEndpointTemplate}';
    var aMatches = sCurrentUrl.match(/\/object\/(create|edit|view)\/([a-zA-Z0-9]+)\/([0-9+])/);
    
    if(aMatches.length === 4)
    {
        sEndpoint = sEndpointTemplate
            .replace(/-objectClass-/, aMatches[2])
            .replace(/-objectId-/, aMatches[3]);
    }
    
    return sEndpoint;
}

function InstanciateBubbleCaselogs(oElem)
{
    var sEndpoint = GetBubbleCaselogsEndpointUrl(oElem.closest('form').attr('action'));
    $.molkobain.bubble_caselogs(
        {
            debug: {$bDebug},
            endpoint: sEndpoint,
        },
        oElem
    );
}

// Instanciate widget on modals
$('body').on('loaded.bs.modal', function (oEvent) {
    var oForm = $(oEvent.target).find('.modal-content form');
    if(oForm.length > 0)
    {
        if(oForm.find('.field_set .form_field_control .caselog_field_entry:first').length > 0)
        {
            InstanciateBubbleCaselogs(oForm.find('.field_set .form_field_control .caselog_field_entry:first').parent());
        }
    }
    
});

// Instanciate widget on initial elements
$(document).ready(function(){
    $('.field_set .form_field_control .caselog_field_entry:first').each(function(){
        InstanciateBubbleCaselogs($(this).parent());
    });
});
EOF;

        return $sJSInline;
    }
}
