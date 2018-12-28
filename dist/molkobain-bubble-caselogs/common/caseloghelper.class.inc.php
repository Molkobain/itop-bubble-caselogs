<?php
/**
 * Copyright (c) 2015 - 2019 Molkobain.
 *
 * This file is part of licensed extension.
 *
 * Use of this extension is bound by the license you purchased. A license grants you a non-exclusive and non-transferable right to use and incorporate the item in your personal or commercial projects. There are several licenses available (see https://www.molkobain.com/usage-licenses/ for more informations)
 */

namespace Molkobain\iTop\Extension\BubbleCaselogs\Helper;

use Dict;
use MetaModel;
use UserRights;
use AttributeDate;
use AttributeDateTime;
use AttributeText;
use InlineImage;

/**
 * Class CaselogHelper
 *
 * @package Molkobain\iTop\Extension\BubbleCaselogs\Helper
 */
class CaselogHelper
{
    const MODULE_CODE = 'molkobain-bubble-caselogs';

    const DEFAULT_REVERSE_ORDER = false;
    const DEFAULT_ALL_ENTRIES_OPENED = false;

    /**
     * @param array $aEntries
     *
     * @return string
     *
     * @throws \Exception;
     */
    public static function FormatEntries($aEntries)
    {
        $sHtml = '';
        $iNbEntries = count($aEntries);

        if($iNbEntries > 0)
        {
            $bReverseOrder = (MetaModel::GetConfig()->GetModuleSetting(static::MODULE_CODE, 'reverse_order', static::DEFAULT_REVERSE_ORDER) === true);
            $bAllEntriesOpened = (MetaModel::GetConfig()->GetModuleSetting(static::MODULE_CODE, 'all_entries_opened', static::DEFAULT_ALL_ENTRIES_OPENED) === true);

            // Dict entries
            /** @var string $sOpenAllEntriesLabel */
            $sOpenAllEntriesLabel = Dict::S('Molkobain:BubbleCaselogs:Entries:OpenAll');
            /** @var string $sCloseAllEntriesLabel */
            $sCloseAllEntriesLabel = Dict::S('Molkobain:BubbleCaselogs:Entries:CloseAll');
            /** @var string $sCloseEntryLabel */
            $sCloseEntryLabel = Dict::S('Molkobain:BubbleCaselogs:Entry:Close');

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
            <span class="mbc-tht-openall" title="{$sOpenAllEntriesLabel}" data-toggle="tooltip"><span class="fa fa-envelope-open-o"></span></span><span class="mbc-tht-closeall" title="{$sCloseAllEntriesLabel}" data-toggle="tooltip"><span class="fa fa-envelope-o"></span></span>
        </span>
        <span class="mbc-th-peerscount pull-right">{$iNbUsers}<span class="fa fa-users"></span></span>
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
                    $oEntryUser = MetaModel::GetObject('User', $iEntryUserId, false);
                    if($oEntryUser !== null)
                    {
                        $sEntryContactClass = 'Person';
                        $iEntryContactId = $oEntryUser->Get('contactid');
                        $oEntryContact = MetaModel::GetObject($sEntryContactClass, $iEntryContactId, false);

                        /** @var \ormDocument $oEntryPicture */
                        $oEntryPicture = $oEntryContact->Get('picture');
                        if(!$oEntryPicture->IsEmpty())
                        {
                            $sEntryContactPictureUrl = $oEntryPicture->GetDisplayURL($sEntryContactClass, $iEntryContactId, 'picture');
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