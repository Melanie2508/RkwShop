<?php

namespace RKW\RkwShop\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

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
 * Class ProductRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ProductRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{


    /**
     * Get all products including hidden and deleted
     *
     * @param \RKW\RkwShop\Domain\Model\ProductBundle $productBundle
     * @return array|null|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByProductBundleOrderedByPublishingDate (\RKW\RkwShop\Domain\Model\ProductBundle $productBundle)
    {

        $query = $this->createQuery();
        $query->equals('productBundle', $productBundle);
        $query->setOrderings(array('publishingDate' => QueryInterface::ORDER_ASCENDING));

        return $query->execute();
    }



    /**
     * Find all products by a list of uids
     *
     * @param string $uidList
     * @return array
     */
    public function findByUidList($uidList)
    {

        $query = $this->createQuery();
        $uidArray = explode(',', $uidList);
        $result = [];

        // 1. Get all products by uid
        $constraints = [];
        foreach ($uidArray as $key => $value) {
            $constraints[] =  $query->equals('uid', $value);
        }

        // we have to keep the order given by the comma-list
        $query->setOrderings($this->orderByKey('uid', $uidArray));

        $products = $query->matching(
            $query->logicalOr(
                $constraints
            )
        )->execute();


        // 2. Check for parentProduct and its settings
        $uidList = [];

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        foreach ($products as $product) {

            /*
            // check for getAllowSingleOrder = false
            // --> add productBundle instead of given product in some cases

            if (
                ($product->getProductBundle())
                && (! $product->getProductBundle()->getAllowSingleOrder())
                && ($parentId = $product->getProductBundle()->getUid())
            ) {

                if (! in_array($parentId, $uidList)) {

                    $query = $this->createQuery();
                    $query->matching(
                        $query->equals('uid', $parentId)
                    );

                    $result[] = $query->execute()->getFirst();
                    $uidList[] = $parentId;
                }

            } else if (! in_array($product->getUid(), $uidList)) {

                $result[] = $product;
                $uidList[] = $product->getUid();
            }
            */

            if (! in_array($product->getUid(), $uidList)) {

                if ($product->getRecordType() != '\RKW\RkwShop\Domain\Model\ProductDownload') {
                    $result[] = $product;
                    $uidList[] = $product->getUid();
                }
            }
        }

        return $result;
        //===
    }


    /**
     * Get all products including hidden and deleted
     *
     * @api Used by RKW Soap
     * @return array|null|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findAllSoap()
    {

        $query = $this->createQuery();
        // $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        return $query->execute();
    }

    /**
     * Finds an object matching the given identifier.
     *
     * @param int $uid The identifier of the object to find
     * @return object The matching object if found, otherwise NULL
     * @api used by RKW Soap
     */
    public function findByUidSoap($uid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $query->matching(
            $query->equals('uid', $uid)
        );

        $query->setLimit(1);

        return $query->execute()->getFirst();
    }


    /**
     * @param $key
     * @param array $uidArray
     * @return array
     */
    protected function orderByKey($key, $uidArray)
    {
        $order = array();
        foreach ($uidArray as $uid) {
            $order["$key={$uid}"] = \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING;
        }
        return $order;
        //===
    }


}