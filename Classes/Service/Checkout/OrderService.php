<?php

namespace RKW\RkwShop\Service\Checkout;

use RKW\RkwShop\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use RKW\RkwShop\Domain\Model\ShippingAddress;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Class OrderService
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderService implements \TYPO3\CMS\Core\SingletonInterface
{


    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_ORDER_CREATED_ADMIN = 'afterOrderCreatedAdmin';

    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_ORDER_CREATED_USER = 'afterOrderCreatedUser';

    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_ORDER_DELETED_ADMIN = 'afterOrderDeletedAdmin';

    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_ORDER_DELETED_USER = 'afterOrderDeletedUser';


    /**
     * orderRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\OrderRepository
     * @inject
     */
    protected $orderRepository;


    /**
     * orderItemRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\OrderItemRepository
     * @inject
     */
    protected $orderItemRepository;

    /**
     * productRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     * @inject
     */
    protected $productRepository;

    /**
     * stockRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\StockRepository
     * @inject
     */
    protected $stockRepository;

    /**
     * BackendUserRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\BackendUserRepository
     * @inject
     */
    protected $backendUserRepository;

    /**
     * @var \RKW\RkwShop\Service\Checkout\CartService
     * @inject
     */
    protected $cartService;

    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;

    /**
     * Persistence Manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;


    /**
     * configurationManager
     *
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;


    /**
     * @var  \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * Create Order
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @param \TYPO3\CMS\Extbase\Mvc\Request|null $request
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser|null $frontendUser
     * @return string
     * @throws \RKW\RkwShop\Exception
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     */
    public function createOrder (\RKW\RkwShop\Domain\Model\Order $order, \TYPO3\CMS\Extbase\Mvc\Request $request = null, \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser = null)
    {
        // check for shippingAddress
        if (
            (! $order->getShippingAddress())
            || (! $order->getShippingAddress()->getAddress())
            || (! $order->getShippingAddress()->getZip())
            || (! $order->getShippingAddress()->getCity())
        ){
            throw new Exception('orderService.error.noShippingAddress');
        }


        // cleanup & check orderItem
        $this->cleanUpOrderItemList($order);
        if (! count($order->getOrderItem()->toArray())) {
            throw new Exception('orderService.error.noOrderItem');
        }

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        foreach($order->getOrderItem() as $orderItem) {

            if (
                (! $orderItem->getProduct() instanceof \RKW\RkwShop\Domain\Model\ProductSubscription)
                && ($orderItem->getProduct()->getRecordType() !== '\RKW\RkwShop\Domain\Model\ProductSubscription')
            ){
                $stock = $this->getRemainingStockOfProduct($orderItem->getProduct());
                $stockPreOrder = $this->getPreOrderStockOfProduct($orderItem->getProduct());

                if ($orderItem->getAmount() > ($stock + $stockPreOrder)) {
                    throw new Exception('orderService.error.outOfStock');
                }
            }
        }

        // handling for existing and logged in users
        if (
            ($frontendUser)
            && (! $frontendUser->_isNew())
        ) {

            $this->persistOrder($order, $frontendUser);

            $this->cartService->deleteCart($this->cartService->getCart());

            return 'orderService.message.created';
        }

    }

    /**
     * cancelOrder
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     */
    public function cancelOrder(\RKW\RkwShop\Domain\Model\Order $order) {

        $order->setStatus(60);
        $order->setCanceledAt(time());

        $this->orderRepository->update($order);

    }

    /**
     * persistOrder
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser|null $frontendUser
     * @return bool
     * @throws \RKW\RkwShop\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function persistOrder(\RKW\RkwShop\Domain\Model\Order $order, \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser)
    {
        //  @todo: Check state
        // check order
//        if ($order->getStatus() > 0) {
//            throw new Exception('orderService.error.orderAlreadyPersisted');
//        }

//        // check frontendUser
//        if ($frontendUser->_isNew()) {
//            throw new Exception('orderService.error.frontendUserNotPersisted');
//        }

        $order->setStatus(50);
        $order->setOrderNumber($this->createOrderNumber());

        // save it
        $this->orderRepository->add($order);
        $this->persistenceManager->persistAll();

        //  @todo: $order->getFrontendUser() === $this->getFrontendUser()

        // send final confirmation mail to user
        $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_ORDER_CREATED_USER, [$frontendUser, $order]);

        // send mail to admins
        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $backendUsersList = [];
        $backendUsersForProductMap = [];
        foreach ($order->getOrderItem() as $orderItem) {

            $backendUsersForProduct = $this->getBackendUsersForAdminMails($orderItem->getProduct());
            $backendUsersList = array_merge($backendUsersList, $backendUsersForProduct);
            $tempBackendUserForProductMap = [];
            /** @var \RKW\RkwShop\Domain\Model\BackendUser $backendUser */
            foreach ($backendUsersForProduct as $backendUser) {
                if ($backendUser->getRealName()) {
                    $tempBackendUserForProductMap[] = $backendUser->getRealName();
                } else if ($backendUser->getEmail()) {
                    $tempBackendUserForProductMap[] = $backendUser->getEmail();
                }
            }
            $backendUsersForProductMap[$orderItem->getProduct()->getUid()] = implode(', ', $tempBackendUserForProductMap);
        }

        $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_ORDER_CREATED_ADMIN, [array_unique($backendUsersList), $order, $backendUsersForProductMap]);

        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Saved order with uid %s of user with uid %s via signal-slot.', $order->getUid(), $frontendUser->getUid()));

        return true;

    }

    /**
     * @param int $newOrderNumber
     * @return string
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    protected function createOrderNumber($newOrderNumber = 1)
    {
        /** @var \RKW\RkwShop\Domain\Model\Order $latestOrder */
        $latestOrder = $this->orderRepository->findLatestOrder()->getFirst();

        //  @todo: create order number and set to registry - see https://github.com/extcode/cart/blob/9292a3806cbd5c1e9e88bb73d182567586ba5b91/Classes/Utility/OrderUtility.php#L814

        if ($latestOrder) {
            $newOrderNumber = (int)$latestOrder->getOrderNumber() + 1;
        }

        return $this->buildOrderNumber($newOrderNumber);
    }

    /**
     * @param int $newOrderNumber
     * @return string
     */
    public function buildOrderNumber($newOrderNumber = 1)
    {
        return str_pad($newOrderNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Intermediate function for saving of orders - used by SignalSlot
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\Registration $registration
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function saveOrderSignalSlot(\RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser, \RKW\RkwRegistration\Domain\Model\Registration $registration)
    {
        // get order from registration
        if (
            ($order = $registration->getData())
            && ($order instanceof \RKW\RkwShop\Domain\Model\Order)
        ) {

            try {
                $this->saveOrder($order, $frontendUser);

            } catch (\RKW\RkwShop\Exception $exception) {
                // do nothing
            }
        }
    }



    /**
     * Removes all open orders of a FE-User - used by SignalSlot
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function removeAllOrdersOfFrontendUserSignalSlot(\RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser)
    {

        //  @todo: Wird diese Funktion überhaupt benötigt?
        $orders = $this->orderRepository->findByFrontendUser($frontendUser);
        if ($orders) {

            /** @var \RKW\RkwShop\Domain\Model\Order $order */
            foreach ($orders as $order) {

                // delete order
                $this->orderRepository->remove($order);
                $this->persistenceManager->persistAll();

                // send final confirmation mail to user
                $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_ORDER_DELETED_USER, [$frontendUser, $order]);

                // send mail to admins
                /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
                $backendUsersList = [];
                $backendUsersForProductMap = [];
                foreach ($order->getOrderItem() as $orderItem) {
                    $backendUsersForProduct = $this->getBackendUsersForAdminMails($orderItem->getProduct());
                    $backendUsersList = array_merge($backendUsersList, $backendUsersForProduct);

                    $tempBackendUserForProductMap = [];
                    /** @var \RKW\RkwShop\Domain\Model\BackendUser $backendUser */
                    foreach ($backendUsersForProduct as $backendUser) {
                        if ($backendUser->getRealName()) {
                            $tempBackendUserForProductMap[] = $backendUser->getRealName();
                        } else if ($backendUser->getEmail()) {
                            $tempBackendUserForProductMap[] = $backendUser->getEmail();
                        }
                    }
                    $backendUsersForProductMap[$orderItem->getProduct()->getUid()] = implode(', ', $tempBackendUserForProductMap);
                }
                $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_ORDER_DELETED_ADMIN, array(array_unique($backendUsersList), $frontendUser, $order, $backendUsersForProductMap));
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Deleted order with uid %s of user with uid %s via signal-slot.', $order->getUid(), $frontendUser->getUid()));

            }
        }
    }

    public function checkShippingAddress(\RKW\RkwShop\Domain\Model\Order $order)
    {

        if ($order->getShippingAddressSameAsBillingAddress() === 1) {

            $shippingAddress = $this->makeShippingAddress($order);

            $order->setShippingAddress($shippingAddress);

        }

        return $order;
     }

    /**
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @return \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAdress
     */
    public function makeShippingAddress(\RKW\RkwShop\Domain\Model\Order $order)
    {
        $frontendUser = $order->getFrontendUser();

        /** @var \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = GeneralUtility::makeInstance(ShippingAddress::class);

        $shippingAddress->setFrontendUser($frontendUser);
        $shippingAddress->setGender($frontendUser->getTxRkwregistrationGender());
        $shippingAddress->setFirstName($frontendUser->getFirstName());
        $shippingAddress->setLastName($frontendUser->getLastName());
        $shippingAddress->setCompany($frontendUser->getCompany());
        $shippingAddress->setAddress($frontendUser->getAddress());
        $shippingAddress->setZip($frontendUser->getZip());
        $shippingAddress->setCity($frontendUser->getCity());

        return $shippingAddress;

    }


    /**
     * Get remaining stock of product
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @return int
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     */
    public function getRemainingStockOfProduct (\RKW\RkwShop\Domain\Model\Product $product)
    {

        //  @todo: If a order is created, the availableStock of all contained products must be updated by decreasing the amount according to the order amount of the product or its parent product collection.
        //  @todo: If a order is updated, the availableStock of all contained products must be updated by decreasing the amount according to the order amount of the product or its parent product collection.
        //  @todo: If an order is cancelled, the availableStock of all contained products must be updated by increasing the amount according to the order amount of the product or its parent product collection.
        //  @todo: If an order is completed, the availableStock and stock itself have to be updated.
        //  @todo: Before completing an order there has to be a last check, that everything is available, because there could be new data from synchronizing by SOAP.
        //  @todo: SOAP must update the stock whenever it synchronizes. What happens to availableStock?
        //  @todo: All orders, even external ones, have to reflect the change of stock on the contained items.
        //  @todo: External orders have to be removed completely as these are not necessary anymore, when stock itself is updated by synchronizing. If somebody does not use a WaWi and wants to get items from stock for internal use, he preferably has to place an order himself.

        //  @todo: remove external_order, Stock entity, allowSingleOrder?

        //  @todo: After initially passing a new product to AVS including an initial stock, then AVS should stay responsible for increasing or decreasing stock except of course all regular orders on the website.

        //  @todo: If a product could be sold independently from a collection, should be decided on the product itself.

        //  @todo: If a product collection is ordered, only the product collection should be an orderItem. BUT: In the background the contained products and their stock should be updated. QUESTION: What happens to the stock of these contained items on behalf of AVS? Does their stock reflect the order as part of a collection?

        //  @todo: Is a pre-order flag necessary? If there is a product out of stock, I cannot order it, but if it would be stocked up, how could I know? Is this the reason for the model "Stock"? Is PreOrder a real action? If a product is out of stock, nobody can buy it, but if a product is out of stock and there is an information, when the next charge will be available, one can order it. So I think, different stocks should be possible (if it could happen, that a new charge could be sold out in one pre-order, maybe there should be a restriction to maximum x pre-orders?), but "pre-order" is not necessary. Shouldn't be AVS responsible for providing new charges?
        //  @todo: Solution => "Stock" remains in place to provide pre-order, but "Stock" is a property of notCollection-Items.
        //  @todo: availability and thereby preordering of a collectionItem must be determined by checking its children. BUT: bewware of ProductSeries as it is an open concept.

        if (
            ($product->getProductBundle())
            && (! $product->getProductBundle()->getAllowSingleOrder())
        ){
            $product = $product->getProductBundle();
        }

        var_dump($product->getProductType()->getTitle());

        if (
            ($product instanceof \RKW\RkwShop\Domain\Model\ProductBundle)
            && ($product->getRecordType() === '\RKW\RkwShop\Domain\Model\ProductBundle')
        ){

            $children = $product->getChildProducts();
            $availableChildren = [];

            foreach ($children as $childProduct) {

                $orderedSum = $this->orderItemRepository->getOrderedSumByProductAndPreOrder($childProduct);
                $stockSum = $this->stockRepository->getStockSumByProductAndPreOrder($childProduct);
                $availableChildren[] = intval($stockSum) - (intval($orderedSum) + intval($childProduct->getOrderedExternal()));

            }

            $remainingStock = min($availableChildren);

        } else {

            $orderedSum = $this->orderItemRepository->getOrderedSumByProductAndPreOrder($product);
            $stockSum = $this->stockRepository->getStockSumByProductAndPreOrder($product);

            $remainingStock = intval($stockSum) - (intval($orderedSum) + intval($product->getOrderedExternal()));

        }

        return (($remainingStock > 0) ? $remainingStock : 0);
    }


    /**
     * Get pre-order stock of product
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @return int
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     */
    public function getPreOrderStockOfProduct (\RKW\RkwShop\Domain\Model\Product $product)
    {
        if (
            ($product->getProductBundle())
            && (! $product->getProductBundle()->getAllowSingleOrder())
        ){
            $product = $product->getProductBundle();
        }

        $orderedSum = $this->orderItemRepository->getOrderedSumByProductAndPreOrder($product, true);
        $stockSum = $this->stockRepository->getStockSumByProductAndPreOrder($product, true);

        $preOrderStock = intval($stockSum) - intval($orderedSum);
        return (($preOrderStock > 0) ? $preOrderStock : 0);
    }



    /**
     * Clean up order product list
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @return void
     */
    public function cleanUpOrderItemList (\RKW\RkwShop\Domain\Model\Order $order)
    {

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        foreach ($order->getOrderItem()->toArray() as $orderItem) {
            if (! $orderItem->getAmount()) {
                $order->removeOrderItem($orderItem);
            }
        }
    }


    /**
     * Get all BackendUsers for sending admin mails
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @return array <\RKW\RkwShop\Domain\Model\BackendUser> $backendUsers
     */
    public function getBackendUsersForAdminMails (\RKW\RkwShop\Domain\Model\Product $product)
    {

        $backendUsers = [];
        $settings = $this->getSettings();
        if (! $settings['disableAdminMails']) {

            $productTemp = $product;
            if ($product->getProductBundle()) {
                $productTemp  = $product->getProductBundle();
            }

            // go through ObjectStorage
            foreach ($productTemp->getBackendUser() as $backendUser) {
                if ((\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($backendUser->getEmail()))) {
                    $backendUsers[] = $backendUser;
                }
            }

            // get field for alternative e-emails
            if ($email = $productTemp->getAdminEmail()) {

                /** @var \RKW\RkwShop\Domain\Model\BackendUser $backendUser */
                $backendUser = $this->backendUserRepository->findOneByEmail($email);
                if (
                    ($backendUser)
                    && (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($backendUser->getEmail()))
                ) {
                    $backendUsers[] = $backendUser;
                }
            }

            // fallback-handling
            if (
                (count($backendUsers) < 1)
                && ($fallbackBeUser = $settings['fallbackBackendUserForAdminMails'])
            ) {

                /** @var \RKW\RkwShop\Domain\Model\BackendUser $beUser */
                $backendUser = $this->backendUserRepository->findOneByUsername($fallbackBeUser);
                if (
                    ($backendUser)
                    && (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($backendUser->getEmail()))
                ) {
                    $backendUsers[] = $backendUser;
                }
            }
        }

        return $backendUsers;
    }



    /**
     * Returns TYPO3 settings
     *
     * @return array
     */
    protected function getSettings()
    {
        $settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
            'Rkwshop'
        );

        return $settings['plugin.']['tx_rkwshop.']['settings.'];
    }



    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
    }


}