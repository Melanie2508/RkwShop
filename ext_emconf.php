<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "rkw_shop"
 *
 * Auto generated by Extension Builder 2016-05-04
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
	'title' => 'RKW Shop',
	'description' => '',
	'category' => 'plugin',
    'author' => 'Steffen Kroggel',
    'author_email' => 'developer@steffenkroggel.de',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '8.7.4',
	'constraints' => [
		'depends' => [
            'typo3' => '7.6.0-8.7.99',
            'rkw_basics' => '8.7.7-8.7.99',
            'rkw_mailer' => '8.7.20-8.7.99',
            'rkw_registration' => '8.7.5-8.7.99'
		],
		'conflicts' => [
            'rkw_authors' => '0.0.0-7.6.13',
            'rkw_basics' => '0.0.0-8.7.6',
            'rkw_order' => '',
            'rkw_soap' => '0.0.0-7.6.5',
            'rkw_registration' => '0.0.0-8.7.4'
		],
		'suggests' => [
		],
	],
];