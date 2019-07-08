<?php
/**
 * Copyright (c) 2015 - 2019 Molkobain.
 *
 * This file is part of licensed extension.
 *
 * Use of this extension is bound by the license you purchased. A license grants you a non-exclusive and non-transferable right to use and incorporate the item in your personal or commercial projects. There are several licenses available (see https://www.molkobain.com/usage-licenses/ for more informations)
 */

$aApprootLevelsToTry = array(
	'/../../',
	'/../../../',
	'/../../../../',
	'/../../../../../',
);
foreach($aApprootLevelsToTry as $sApprootLevelToTry)
{
	$sApprootPath = __DIR__ . $sApprootLevelToTry . 'approot.inc.php';
	if(file_exists($sApprootPath))
	{
		require_once $sApprootPath;
		break;
	}
}
require_once(APPROOT . '/application/application.inc.php');
require_once(APPROOT . '/application/webpage.class.inc.php');
require_once(APPROOT . '/application/ajaxwebpage.class.inc.php');

try
{
    require_once(APPROOT . '/application/startup.inc.php');
    require_once(APPROOT . '/application/user.preferences.class.inc.php');

    require_once(APPROOT . '/application/loginwebpage.class.inc.php');
    LoginWebPage::DoLoginEx(null /* any portal */, false);

    require_once(APPROOT . 'env-' . utils::GetCurrentEnvironment() . '/' . \Molkobain\iTop\Extension\BubbleCaselogs\Common\Helper\ConfigHelper::GetModuleCode() . '/common/caseloghelper.class.inc.php');

    $oPage = new ajax_page("");
    $oPage->no_cache();

    $operation = utils::ReadParam('operation', '');

    switch($operation)
    {
        case 'format':
            $oPage->SetContentType('text/html');
            $sClass = utils::ReadParam('class', '');
            $iKey = (int) utils::ReadParam('id', 0);
            $sAttCode = utils::ReadPostedParam('att_code', '');

            if(($sClass === '') || ($iKey === 0) || ($sAttCode === ''))
            {
                throw new Exception('Invalid parameters');
            }

            $oFilter = DBSearch::FromOQL('SELECT ' . $sClass . ' WHERE id = :id');
            $oSet = new DBObjectSet($oFilter, array(), array('id' => $iKey));

            if($oObject = $oSet->Fetch())
            {
                /** @var \ormCaseLog $oCaseLog */
                $oCaseLog = $oObject->Get($sAttCode);
                $sHtml = \Molkobain\iTop\Extension\BubbleCaselogs\Common\Helper\CaselogHelper::FormatEntries($oCaseLog->GetAsArray());
                $oPage->add($sHtml);
                $oPage->add_ready_script(InlineImage::FixImagesWidth());
            }
            else
            {
                throw new Exception('Object not found');
            }
            break;

        default:
            $oPage->p("Invalid query.");
    }

    $oPage->output();
}
catch(Exception $e)
{
    // note: transform to cope with XSS attacks
    echo htmlentities($e->GetMessage(), ENT_QUOTES, 'utf-8');
    IssueLog::Error($e->getMessage() . "\nDebug trace:\n" . $e->getTraceAsString());
}
