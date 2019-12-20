<?php
namespace RKW\RkwShop\Tests\Integration\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwShop\Domain\Repository\OrderItemRepository;
use RKW\RkwShop\Domain\Repository\ProductRepository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
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
 * OrderItemRepositoryTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderItemRepositoryTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
        'typo3conf/ext/rkw_shop',

    ];
    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [];

    /**
     * @var \RKW\RkwShop\Domain\Repository\OrderItemRepository
     */
    private $subject = null;


    /**
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     */
    private $productRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    private $persistenceManager = null;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager = null;

    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/OrderItemRepositoryTest/Fixtures/Database/Global.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Tests/Functional/Domain/Repository/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $this->objectManager->get(OrderItemRepository::class);

        $this->productRepository = $this->objectManager->get(ProductRepository::class);

    }


    //=============================================
    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \Exception
     */
    public function getOrderedSumByProductAndPreOrderReturnsSumOfOrderAmountsWithoutDeleted()
    {

        /**
         * Scenario:
         *
         * Given there are five order items for a product
         * Given one of the order items is deleted
         * Given one of the order items is a pre-order
         * Given one of the order items is a deleted pre-order
         * When I fetch the amount of orders
         * Then deleted order items are excluded from calculation
         * Then pre-ordered items are excluded from calculation
         */
        $this->importDataSet(__DIR__ . '/OrderItemRepositoryTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        $result = $this->subject->getOrderedSumByProductAndPreOrder($product);
        self::assertEquals(9, $result);

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \Exception
     */
    public function getOrderedSumByProductAndPreOrderReturnsSumOfPreOrderAmountsWithoutDeleted()
    {

        /**
         * Scenario:
         *
         * Given there are five order items for a product
         * Given one of the order items is deleted
         * Given one of the order items is a pre-order
         * Given one of the order items is a deleted pre-order
         * When I fetch the amount of orders
         * Then deleted order items are excluded from calculation
         * Then normal ordered items are excluded from calculation
         */
        $this->importDataSet(__DIR__ . '/OrderItemRepositoryTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        $result = $this->subject->getOrderedSumByProductAndPreOrder($product, true);
        self::assertEquals(5, $result);

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function findByOrderUidSoapIncludesDeletedOrders ()
    {

        /**
         * Scenario:
         *
         * Given a order has three order items
         * Given one of the order items is a pre-order
         * Given one of the order items is deleted
         * When I fetch the order-items for the order
         * Then three order items are returned
         */
        $this->importDataSet(__DIR__ . '/OrderItemRepositoryTest/Fixtures/Database/Check20.xml');

        $result = $this->subject->findByOrderUidSoap(1);
        self::assertCount(3, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findByOrderUidSoapRespectsStoragePid ()
    {
        /**
         * Scenario:
         *
         * Given a order has two order items
         * Given one of the order items has a different storage pid
         * When I fetch the order-items for the order
         * Then two order items are returned
         */
        $this->importDataSet(__DIR__ . '/OrderItemRepositoryTest/Fixtures/Database/Check30.xml');

        $result = $this->subject->findByOrderUidSoap(1);
        self::assertCount(1, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findByOrderUidSoapIncludesOrderItemsWithReferencesToDeletedAndHiddenProducts()
    {
        /**
         * Scenario:
         *
         * Given there is a order with three order items
         * Given that each order-item refers to a product
         * Given that one of the products has been deleted
         * Given that one of the products has been hidden
         * When I fetch the order-items for the order
         * Then the three order-items are returned
         * Then the relation to the deleted product is kept
         * Then the relation to the hidden product is kept
         */
        $this->importDataSet(__DIR__ . '/OrderItemRepositoryTest/Fixtures/Database/Check40.xml');
        $result = $this->subject->findByOrderUidSoap(1)->toArray();

        self::assertCount(3, $result);
        self::assertEquals(1, $result[0]->getProduct()->getUid());
        self::assertEquals(2, $result[1]->getProduct()->getUid());
        self::assertEquals(3, $result[2]->getProduct()->getUid());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findByOrderUidSoapIncludesOrderItemsWithReferencesToDeletedOrders()
    {
        /**
         * Scenario:
         *
         * Given there is a order with one order items
         * Given that the order-item refers to a order
         * Given that the order has been deleted
         * When I fetch the order-items for the order
         * Then the order-item is returned
         * Then the relation to the deleted order-item is kept
         */
        $this->importDataSet(__DIR__ . '/OrderItemRepositoryTest/Fixtures/Database/Check41.xml');
        $result = $this->subject->findByOrderUidSoap(1)->toArray();

        self::assertCount(1, $result);
        self::assertEquals(1, $result[0]->getOrder()->getUid());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findByOrderUidSoapIncludesOrderItemsWithReferencesToHiddenOrders()
    {
        /**
         * Scenario:
         *
         * Given there is a order with one order items
         * Given that the order-item refers to a order
         * Given that the order has been hidden
         * When I fetch the order-items for the order
         * Then the order-item is returned
         * Then the relation to the hidden order-item is kept
         */
        $this->importDataSet(__DIR__ . '/OrderItemRepositoryTest/Fixtures/Database/Check42.xml');
        $result = $this->subject->findByOrderUidSoap(1)->toArray();

        self::assertCount(1, $result);
        self::assertEquals(1, $result[0]->getOrder()->getUid());

    }

    //===================================================================

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findByUidSoapIncludesDeletedOrderItems()
    {

        /**
         * Scenario:
         *
         * Given there is an order item
         * Given the order item is deleted
         * When I fetch the order item by uid
         * Then the order item is returned
         */
        $this->importDataSet(__DIR__ . '/OrderItemRepositoryTest/Fixtures/Database/Check50.xml');

        $result = $this->subject->findByUidSoap(1);
        static::assertInstanceOf(\RKW\RkwShop\Domain\Model\OrderItem::class, $result);

    }


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findByUidSoapRespectsStoragePid ()
    {

        /**
         * Scenario:
         *
         * Given there is an order item
         * Given the order item has a different storage pid
         * When I fetch the order item by uid
         * Then the order item is not returned
         */
        $this->importDataSet(__DIR__ . '/OrderItemRepositoryTest/Fixtures/Database/Check60.xml');

        $result = $this->subject->findByUidSoap(1);
        static::assertNull($result);

    }
    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
