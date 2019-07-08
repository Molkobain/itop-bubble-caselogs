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
use AbstractPortalUIExtension;
use Silex\Application;
use Molkobain\iTop\Extension\BubbleCaselogs\Common\Helper\ConfigHelper;

/**
 * Class PortalUIExtension
 *
 * @package Molkobain\iTop\Extension\BubbleCaselogs\Portal\Extension
 */
class PortalUIExtension extends AbstractPortalUIExtension
{
    /**
     * @inheritdoc
     */
    public function GetCSSFiles(Application $oApp)
    {
        $sModuleVersion = utils::GetCompiledModuleVersion(ConfigHelper::GetModuleCode());
        $sURLBase = utils::GetAbsoluteUrlModulesRoot() . '/' . ConfigHelper::GetModuleCode() . '/';

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
        $sModuleVersion = utils::GetCompiledModuleVersion(ConfigHelper::GetModuleCode());
        $sURLBase = utils::GetAbsoluteUrlModulesRoot() . '/' . ConfigHelper::GetModuleCode() . '/';

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
        if(ConfigHelper::IsEnabled() === false)
        {
            return '';
        }

        $bDebug = (ConfigHelper::GetSetting('debug') === true) ? 'true' : 'false';

        $sEndpointTemplate = utils::GetAbsoluteUrlModulesRoot() . ConfigHelper::GetModuleCode() . '/console/ajax.render.php?operation=format&class=-objectClass-&id=-objectId-';

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
            gui: 'portal',
        },
        oElem
    );
}

// Instanciate widget on modals
$('body').on('loaded.bs.modal', function (oEvent) {
    var oForm = $(oEvent.target).find('.modal-content form');
    if(oForm.length > 0)
    {
        // Have to put a timeout in order to wait for the JS form widget to build the actual form
        setTimeout(function(){
            if(oForm.find('.field_set .form_field_control .caselog_field_entry:first').length > 0)
	        {
	            InstanciateBubbleCaselogs(oForm.find('.field_set .form_field_control .caselog_field_entry:first').parent());
	        }
        }, 300);
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
