<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'TYPO3.CMS.' . $_EXTKEY,
	'Pi1',
	array(
		'Wsdl' => 'show'
	),
		// non-cacheable actions
	array(

	)
);
?>