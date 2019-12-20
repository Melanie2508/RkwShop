<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
    {

        //=================================================================
        // Configure Plugin
        //=================================================================
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'ItemList',
            array(
                'Order' => 'newInit, newAjax, new, create, optIn',
            ),
            // non-cacheable actions
            array(
                'Order' => 'newAjax, new, create, optIn',
            )
        );

        //=================================================================
        // Register CommandController
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'RKW\\RkwShop\\Controller\\OrderCommandController';


        //=================================================================
        // Register DataMapper
        //=================================================================
        /** @var \TYPO3\CMS\Extbase\Object\Container\Container $extbaseObjectContainer */
        $extbaseObjectContainer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class);
        $extbaseObjectContainer->registerImplementation(
            \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::class,
            \RKW\RkwShop\Persistence\Generic\Mapper\DataMapper::class
        );

        //=================================================================
        // Register SignalSlots
        //=================================================================
        /**
         * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
         */
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Tools\\Registration',
            \RKW\RkwRegistration\Tools\Registration::SIGNAL_AFTER_CREATING_OPTIN_EXISTING_USER  . 'RkwShop',
            'RKW\\RkwShop\\Service\\RkwMailService',
            'handleOptInRequestEvent'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Tools\\Registration',
            \RKW\RkwRegistration\Tools\Registration::SIGNAL_AFTER_CREATING_OPTIN_USER  . 'RkwShop',
            'RKW\\RkwShop\\Service\\RkwMailService',
            'handleOptInRequestEvent'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Tools\\Registration',
            \RKW\RkwRegistration\Tools\Registration::SIGNAL_AFTER_USER_REGISTER_GRANT . 'RkwShop',
            'RKW\RkwShop\Orders\OrderManager',
            'saveOrderSignalSlot'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwShop\\Orders\\OrderManager',
            \RKW\RkwShop\Orders\OrderManager::SIGNAL_AFTER_ORDER_CREATED_USER,
            'RKW\\RkwShop\\Service\\RkwMailService',
            'confirmationOrderUser'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwShop\\Orders\\OrderManager',
            \RKW\RkwShop\Orders\OrderManager::SIGNAL_AFTER_ORDER_CREATED_ADMIN,
            'RKW\\RkwShop\\Service\\RkwMailService',
            'confirmationOrderAdmin'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Tools\\Registration',
            \RKW\RkwRegistration\Tools\Registration::SIGNAL_AFTER_DELETING_USER,
            'RKW\RkwShop\Orders\OrderManager',
            'removeAllOrdersOfFrontendUserSignalSlot'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwShop\\Orders\\OrderManager',
            \RKW\RkwShop\Orders\OrderManager::SIGNAL_AFTER_ORDER_DELETED_USER,
            'RKW\\RkwShop\\Service\\RkwMailService',
            'deleteOrderUser'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwShop\\Orders\\OrderManager',
            \RKW\RkwShop\Orders\OrderManager::SIGNAL_AFTER_ORDER_DELETED_ADMIN,
            'RKW\\RkwShop\\Service\\RkwMailService',
            'deleteOrderAdmin'
        );

        //=================================================================
        // Register Logger
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['RKW']['RkwShop']['writerConfiguration'] = array(

            // configuration for WARNING severity, including all
            // levels with higher severity (ERROR, CRITICAL, EMERGENCY)
            \TYPO3\CMS\Core\Log\LogLevel::DEBUG => array(
                // add a FileWriter
                'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
                    // configuration for the writer
                    'logFile' => 'typo3temp/logs/tx_rkwshop.log'
                )
            ),
        );
    },
    $_EXTKEY
);

