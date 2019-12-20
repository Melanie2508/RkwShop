<?php
namespace RKW\RkwShop\Tests\Integration\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwShop\Domain\Repository\OrderRepository;
use RKW\RkwShop\Domain\Repository\FrontendUserRepository;

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
 * OrderRepositoryTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderRepositoryTest extends FunctionalTestCase
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
     * @var \RKW\RkwShop\Domain\Repository\OrderRepository
     */
    private $subject = null;

    /**
     * @var \RKW\RkwShop\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository;

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
        $this->importDataSet(__DIR__ . '/OrderRepositoryTest/Fixtures/Database/Global.xml');

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
        $this->subject = $this->objectManager->get(OrderRepository::class);

        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findByFrontendUserIncludesOrdersWithDifferentStoragePid()
    {

        /**
         * Scenario:
         *
         * Given a user has placed two orders
         * Given one order has a different storage pid
         * When I fetch the orders of the user
         * Then both orders are returned
         */
        $this->importDataSet(__DIR__ . '/OrderRepositoryTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        $result = $this->subject->findByFrontendUser($frontendUser)->toArray();
        static::assertEquals(2, count($result));
        self::assertEquals('1', $result[0]->getUid());
        self::assertEquals('2', $result[1]->getUid());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findByFrontendUserIgnoresDeletedOrders()
    {
        /**
         * Scenario:
         *
         * Given a user has placed two orders
         * Given one of the orders is deleted
         * When I fetch the orders of the user
         * Then only one order is returned
         */
        $this->importDataSet(__DIR__ . '/OrderRepositoryTest/Fixtures/Database/Check20.xml');


        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        $result = $this->subject->findByFrontendUser($frontendUser)->toArray();
        static::assertEquals(1, count($result));
        self::assertEquals('1', $result[0]->getUid());
    }



    //===================================================================

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findByTimestampSoapIncludesDeletedOrders()
    {

        /**
         * Scenario:
         *
         * Given there are two orders
         * Given one of the orders is deleted
         * When I fetch the orders by timestamp zero
         * Then two orders are returned
         */
        $this->importDataSet(__DIR__ . '/OrderRepositoryTest/Fixtures/Database/Check30.xml');

        $result = $this->subject->findByTimestampSoap(0);
        static::assertEquals(2, count($result));

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findByTimestampSoapIncludesHiddenOrders()
    {

        /**
         * Scenario:
         *
         * Given there are two orders
         * Given one of the orders is hidden
         * When I fetch the orders by timestamp zero
         * Then two orders are returned
         */
        $this->importDataSet(__DIR__ . '/OrderRepositoryTest/Fixtures/Database/Check40.xml');

        $result = $this->subject->findByTimestampSoap(0);
        static::assertEquals(2, count($result));

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findByTimestampSoapRespectsStoragePid ()
    {

        /**
         * Scenario:
         *
         * Given there are two orders
         * Given one of the orders has a different storagePid
         * When I fetch the orders by timestamp zero
         * Then only one order is returned
         */
        $this->importDataSet(__DIR__ . '/OrderRepositoryTest/Fixtures/Database/Check50.xml');

        $result = $this->subject->findByTimestampSoap(0);
        static::assertEquals(1, count($result));

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findByTimestampSoapReturnsOnlyOrdersWithTimestampEqualOrGreaterThanGiven()
    {

        /**
         * Scenario:
         *
         * When I fetch the orders by timestamp greater than zero
         * Then orders with tstamp greater or equal than the given timestamp are returned
         */
        $this->importDataSet(__DIR__ . '/OrderRepositoryTest/Fixtures/Database/Check60.xml');

        $result = $this->subject->findByTimestampSoap(4000);
        static::assertEquals(2, count($result));

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findByTimestampSoapIncludesReferencesToDeletedAndHiddenFeUsers ()
    {

        /**
         * Scenario:
         *
         * Given there are three orders
         * Given that each order belongs to a different frontend user
         * Given that one of the frontend users is deleted
         * Given that one of the frontend users is hidden
         * When I fetch the orders by timestamp zero
         * Then the three orders are returned
         * Then the relation to the deleted frontend user is kept
         * Then the relation to the hidden frontend user is kept
         */
        $this->importDataSet(__DIR__ . '/OrderRepositoryTest/Fixtures/Database/Check70.xml');

        $result = $this->subject->findByTimestampSoap(0)->toArray();
        static::assertEquals(3, count($result));

        static::assertGreaterThan(0, $result[0]->getFrontendUser());
        static::assertGreaterThan(0, $result[1]->getFrontendUser());
        static::assertGreaterThan(0, $result[2]->getFrontendUser());


    }



    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findByTimestampSoapIncludesReferencesToDeletedShippingAddresses ()
    {

        /**
         * Scenario:
         *
         * Given there are three orders
         * Given that each order belongs to a different shipping address
         * Given that one of the shipping address is deleted
         * Given that one of the shipping address is hidden
         * When I fetch the orders by timestamp zero
         * Then the three orders are returned
         * Then the relation to the deleted shipping address is kept
         * Then the relation to the hidden shipping address is kept
         */
        $this->importDataSet(__DIR__ . '/OrderRepositoryTest/Fixtures/Database/Check80.xml');

        $result = $this->subject->findByTimestampSoap(0)->toArray();
        static::assertEquals(3, count($result));

        static::assertGreaterThan(0, $result[0]->getShippingAddress());
        static::assertGreaterThan(0, $result[1]->getShippingAddress());
        static::assertGreaterThan(0, $result[2]->getShippingAddress());


    }



    //===================================================================

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findByUidSoapIncludesDeletedOrders()
    {

        /**
         * Scenario:
         *
         * Given there is an order
         * Given the order is deleted
         * When I fetch the order by uid
         * Then the order is returned
         */
        $this->importDataSet(__DIR__ . '/OrderRepositoryTest/Fixtures/Database/Check90.xml');

        $result = $this->subject->findByUidSoap(1);
        static::assertInstanceOf(\RKW\RkwShop\Domain\Model\Order::class, $result);

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function findByUidSoapIncludesHiddenOrders()
    {

        /**
         * Scenario:
         *
         * Given there is an order
         * Given the order is hidden
         * When I fetch the order by uid
         * Then the order is returned
         */
        $this->importDataSet(__DIR__ . '/OrderRepositoryTest/Fixtures/Database/Check100.xml');

        $result = $this->subject->findByUidSoap(1);
        static::assertInstanceOf(\RKW\RkwShop\Domain\Model\Order::class, $result);

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
         * Given there is an order
         * Given the order has a different storage pid
         * When I fetch the order by uid
         * Then the order is not returned
         */
        $this->importDataSet(__DIR__ . '/OrderRepositoryTest/Fixtures/Database/Check110.xml');

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