<?php
/**
 * Copyright (c) 2015 - 2019 Molkobain.
 *
 * This file is part of licensed extension.
 *
 * Use of this extension is bound by the license you purchased. A license grants you a non-exclusive and non-transferable right to use and incorporate the item in your personal or commercial projects. There are several licenses available (see https://www.molkobain.com/usage-licenses/ for more informations)
 */

namespace Molkobain\iTop\Extension\BubbleCaselogs\Common\Helper;

use Dict;
use MetaModel;
use UserRights;
use AttributeDate;
use AttributeDateTime;
use AttributeText;
use InlineImage;
use utils;

/**
 * Class CaselogHelper
 *
 * @package Molkobain\iTop\Extension\BubbleCaselogs\Common\Helper
 */
class CaselogHelper
{
	/** @var string ENUM_UI_CONSOLE */
	const ENUM_UI_CONSOLE = 'console';
	/** @var string ENUM_UI_PORTAL */
	const ENUM_UI_PORTAL = 'portal';

	/** @var string DEFAULT_UI */
	const DEFAULT_UI = self::ENUM_UI_CONSOLE;

    /**
     * @param array $aEntries
	 * @param string $sGUI
     *
     * @return string
     *
	 * @throws \ArchivedObjectException
	 * @throws \CoreException
	 * @throws \Exception
     */
    public static function FormatEntries($aEntries, $sGUI = self::DEFAULT_UI)
    {
        $sHtml = '';
        $iNbEntries = count($aEntries);

        if($iNbEntries > 0)
        {
            $bReverseOrder = (ConfigHelper::GetSetting('reverse_order') === true);
            $bAllEntriesOpened = (ConfigHelper::GetSetting('all_entries_opened') === true);

            // Dict entries
            /** @var string $sOpenAllEntriesLabel */
            $sOpenAllEntriesLabel = Dict::S('Molkobain:BubbleCaselogs:Entries:OpenAll');
            /** @var string $sCloseAllEntriesLabel */
            $sCloseAllEntriesLabel = Dict::S('Molkobain:BubbleCaselogs:Entries:CloseAll');
            /** @var string $sCloseEntryLabel */
            $sCloseEntryLabel = Dict::S('Molkobain:BubbleCaselogs:Entry:Close');

	        $sExpandCssClasses = ConfigHelper::GetSetting('open_all_icon');
	        $sCollapseCssClasses = ConfigHelper::GetSetting('close_all_icon');
	        $sIconsSeparator = ConfigHelper::GetSetting('icons_separator');

            // First pass to retrieve number of users
            $aUserIds = array();
            for($i = 0; $i < $iNbEntries; $i++)
            {
                $iEntryUserId = $aEntries[$i]['user_id'];
                if(!in_array($iEntryUserId, $aUserIds))
                {
                    $aUserIds[] = $iEntryUserId;
                }
            }
            $iNbUsers = count($aUserIds);

            // Opening thread
            $sHtml .= '<div class="mbc-thread">';
            // - Header
            $sHtml .=
<<<EOF
    <div class="mbc-thread-header">
        <span class="mbc-th-togglers">
            <span class="mbc-tht-toggler mbc-tht-openall" title="{$sOpenAllEntriesLabel}" data-toggle="tooltip"><span class="{$sExpandCssClasses}"></span></span>
            <span class="mbc-tht-separator">{$sIconsSeparator}</span>
            <span class="mbc-tht-toggler mbc-tht-closeall" title="{$sCloseAllEntriesLabel}" data-toggle="tooltip"><span class="{$sCollapseCssClasses}"></span></span>
        </span>
        <span class="mbc-th-info pull-right">
	        <span class="mbc-thi-peerscount">{$iNbUsers}<span class="fa fa-users"></span></span>
	        <span class="mbc-thi-messagescount">{$iNbEntries}<span class="fas fa-comment fa-flip-horizontal"></span></span>
		</span>
    </div>
EOF;
            // - Content
            $sHtml .= '<div class="mbc-thread-content">';

            $sLastDate = null;
            $sLastUserId = null;
            $iLastLoopIndex = $iNbEntries - 1;
            $sUniqueId = uniqid();
            // Caching profile picture url as it is resource consuming
            $aEntryContactPicturesCache = array();
            $aEntryPeerColorClassCache = array();
            // Current user
            $iCurrentUserId = UserRights::GetUserId();

            if($bReverseOrder)
            {
                $aEntries = array_reverse($aEntries);
            }

            for($i = 0; $i < $iNbEntries; $i++)
            {
                $sEntryDatetime = AttributeDateTime::GetFormat()->Format($aEntries[$i]['date']);
                $sEntryDate = AttributeDate::GetFormat()->Format($aEntries[$i]['date']);

                $sEntryUser = $aEntries[$i]['user_login'];
                $iEntryUserId = $aEntries[$i]['user_id'];

                // Retrieving profile picture if available (standard datamodel)
                $sEntryContactPictureUrl = null;
                // - Trying to retrieving it from database
                if(MetaModel::IsValidAttCode('Person', 'picture'))
                {
                    $oEntryUser = MetaModel::GetObject('User', $iEntryUserId, false, true);
                    if($oEntryUser !== null)
                    {
                        $sEntryContactClass = 'Person';
                        $sEntryPictureAttCode = 'picture';
                        $iEntryContactId = $oEntryUser->Get('contactid');
                        $oEntryContact = MetaModel::GetObject($sEntryContactClass, $iEntryContactId, false, true);

                        // Protection against users without contact and DM without standard picture attribute
                        if(($oEntryContact !== null) && (MetaModel::IsValidAttCode($sEntryContactClass, $sEntryPictureAttCode)))
                        {
	                        /** @var \ormDocument $oEntryPicture */
	                        $oEntryPicture = $oEntryContact->Get($sEntryPictureAttCode);
	                        if(!$oEntryPicture->IsEmpty())
	                        {
		                        if($sGUI === static::ENUM_UI_PORTAL)
		                        {
			                        $sEntryContactPictureUrl = utils::GetAbsoluteUrlAppRoot() . 'pages/exec.php/object/document/display/' . $sEntryContactClass . '/' . $iEntryContactId . '/' . $sEntryPictureAttCode . '?exec_module=itop-portal&exec_page=index.php';
		                        }
		                        else
		                        {
			                        $sEntryContactPictureUrl = $oEntryPicture->GetDisplayURL($sEntryContactClass, $iEntryContactId, $sEntryPictureAttCode);
		                        }
	                        }
                        }
                    }
                }
                // - Caching URL
                $aEntryContactPicturesCache[$iEntryUserId] = $sEntryContactPictureUrl;

                // Opening user block if previous user was different or if previous date was different
                if(($iEntryUserId !== $sLastUserId) || ($sEntryDate !== $sLastDate))
                {
                    if($sEntryDate !== $sLastDate)
                    {
                        $sHtml .= '<div class="mbc-tc-date">' . $sEntryDate . '</div>';
                    }

                    // Opening block
                    if($iEntryUserId === $iCurrentUserId)
                    {
                        $sEntryBlockClass = 'mbc-tc-block-me';
                    }
                    else
                    {
                        if(!array_key_exists($iEntryUserId, $aEntryPeerColorClassCache))
                        {
                            $iPeerClassNumber = (count($aEntryPeerColorClassCache) % 5) + 1;
                            $aEntryPeerColorClassCache[$iEntryUserId] = 'mbc-tc-block-color-' . $iPeerClassNumber;
                        }
                        $sEntryBlockClass = $aEntryPeerColorClassCache[$iEntryUserId];
                    }
                    $sHtml .= '<div class="mbc-tc-block ' . $sEntryBlockClass . '">';

                    // Opening medallion from profile picture or first name letter
                    $sEntryMedallionStyle = ($sEntryContactPictureUrl !== null) ? ' background-image: url(\'' . $sEntryContactPictureUrl . '\');' : '';
                    $sEntryMedallionContent = ($sEntryContactPictureUrl !== null) ? '' : substr($sEntryUser, 0, 1);
                    // - Entry tooltip
                    $sEntryMedallionTooltip = $sEntryUser;
                    $sEntryMedallionTooltipPlacement = ($iEntryUserId === $iCurrentUserId) ? 'left' : 'right';
                    $sHtml .=
<<<EOF
    <div class="mbc-tcb-medallion" style="{$sEntryMedallionStyle}" data-toggle="tooltip" data-placement="{$sEntryMedallionTooltipPlacement}" title="{$sEntryMedallionTooltip}">
        $sEntryMedallionContent
    </div>
    <div class="mbc-tcb-user">{$sEntryUser}</div>
EOF;

                    // Opening entries
                    $sHtml .= '<div class="mbc-tcb-entries">';
                }

                // Preparing entry content
                $sEntryId = 'mbc-tcb-entry-' . $sUniqueId . '-' . $i;
                $sEntryHtml = AttributeText::RenderWikiHtml($aEntries[$i]['message_html'], true /* wiki only */);
                $sEntryHtml = InlineImage::FixUrls($sEntryHtml);

                // Adding entry
                $sEntryClass = '';
                if(!$bAllEntriesOpened)
                {
                    if($bReverseOrder && ($i < $iNbEntries-CASELOG_VISIBLE_ITEMS))
                    {
                        $sEntryClass = 'closed';
                    }
                    elseif(!$bReverseOrder && ($i >= CASELOG_VISIBLE_ITEMS))
                    {
                        $sEntryClass = 'closed';
                    }

                }
                $sHtml .=
<<<EOF
    <div class="mbc-tcb-entry {$sEntryClass}" id="{$sEntryId}">
        <div class="mbc-tcbe-content">{$sEntryHtml}</div>
        <div class="mbc-tcbe-date">{$sEntryDatetime}</div>
        <div class="mbc-tcbe-toggler"><span class="fa fa-caret-up" title="{$sCloseEntryLabel}"></span></div>
    </div>
EOF;

                // Closing user block if next user is different or if last entry or if next entry is for another date
                if(($i === $iLastLoopIndex)
                    || ($i < $iLastLoopIndex && $iEntryUserId !== $aEntries[$i + 1]['user_id'])
                    || ($i < $iLastLoopIndex && $sEntryDate !== AttributeDate::GetFormat()->Format($aEntries[$i + 1]['date'])))
                {
                    // Closing entries
                    $sHtml .= '</div>';

                    // Closing block
                    $sHtml .= '</div>';
                }

                // Updating current loop informations
                $sLastDate = $sEntryDate;
                $sLastUserId = $iEntryUserId;
            }

            // Closing thread content and thread
            $sHtml .= '</div>';
            $sHtml .= '</div>';
        }

        return $sHtml;
    }
}