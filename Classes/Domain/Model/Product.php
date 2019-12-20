<?php

namespace RKW\RkwShop\Domain\Model;

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
 * Class Product
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Product extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var string
     */
    protected $recordType;


    /**
     * @var integer
     */
    protected $crdate;


    /**
     * @var integer
     */
    protected $tstamp;


    /**
     * @var integer
     */
    protected $hidden;


    /**
     * @var integer
     */
    protected $deleted;


    /**
     * title
     *
     * @var string
     */
    protected $title;

    /**
     * subtitle
     *
     * @var string
     */
    protected $subtitle;

    
    /**
     * publishingDate
     *
     * @var integer
     */
    protected $publishingDate;

    
    /**
     * author
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwShop\Domain\Model\Author>
     */
    protected $author = null;
    
    
    /**
     * page
     *
     * @var \RKW\RkwShop\Domain\Model\Pages
     */
    protected $page;

    
    /**
     * image
     *
     * @var \RKW\RkwBasics\Domain\Model\FileReference
     */
    protected $image = null;
    

    /**
     * download
     *
     * @var \RKW\RkwBasics\Domain\Model\FileReference
     */
    protected $download = null;


    /**
     * productBundle
     *
     * @var \RKW\RkwShop\Domain\Model\ProductBundle
     */
    protected $productBundle;


    /**
     * allowSingleOrder
     *
     * @var boolean
     */
    protected $allowSingleOrder = true;

    /**
     * stock
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwShop\Domain\Model\Stock>
     * @cascade remove
     */
    protected $stock;


    /**
     * orderedExternal
     *
     * @var int
     */
    protected $orderedExternal;

    /**
     * backendUser
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwShop\Domain\Model\BackendUser>
     */
    protected $backendUser;


    /**
     * adminEmail
     *
     * @var string
     */
    protected $adminEmail;


    
    /**
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->backendUser = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->author = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

    }

    /**
     * Returns the recordType value
     *
     * @return string
     * @api
     */
    public function getRecordType()
    {
        return $this->recordType;
    }


    /**
     * Sets the recordType value
     *
     * @param $recordType
     * @return string
     * @api
     */
    public function setRecordType($recordType)
    {
        return $this->recordType = $recordType;
    }


    /**
     * Returns the crdate value
     *
     * @return integer
     * @api
     */
    public function getCrdate()
    {

        return $this->crdate;
    }


    /**
     * Returns the tstamp value
     *
     * @return integer
     * @api
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Sets the hidden value
     *
     * @param integer $hidden
     * @api
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }


    /**
     * Returns the hidden value
     *
     * @return integer
     * @api
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Sets the deleted value
     *
     * @param integer $deleted
     * @api
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }


    /**
     * Returns the deleted value
     *
     * @return integer
     * @api
     */
    public function getDeleted()
    {
        return $this->deleted;
    }


    /**
     * Returns the title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the subtitle
     *
     * @return string $subtitle
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * Sets the subtitle
     *
     * @param string $subtitle
     * @return void
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }


    /**
     * Sets the publishingDate value
     *
     * @param integer $publishingDate
     * @api
     */
    public function setPublishingDate($publishingDate)
    {
        $this->publishingDate = $publishingDate;
    }



    /**
     * Adds a author
     *
     * @param \RKW\RkwShop\Domain\Model\Author $author
     * @return void
     */
    public function addAuthor(\RKW\RkwShop\Domain\Model\Author $author)
    {
        $this->author->attach($author);
    }

    /**
     * Removes a author
     *
     * @param \RKW\RkwShop\Domain\Model\Author $author
     * @return void
     */
    public function removeAuthor(\RKW\RkwShop\Domain\Model\Author $author)
    {
        $this->author->detach($author);
    }

    /**
     * Returns the author
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwShop\Domain\Model\Author> $author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Sets the author
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwShop\Domain\Model\Author> $author
     * @return void
     */
    public function setAuthor(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $author)
    {
        $this->author = $author;
    }
    

    /**
     * Returns the publishingDate value
     *
     * @return integer
     * @api
     */
    public function getPublishingDate()
    {
        return $this->publishingDate;
    }

    /**
     * Returns the productBundle
     *
     * @return \RKW\RkwShop\Domain\Model\ProductBundle $productBundle
     */
    public function getProductBundle()
    {
        return $this->productBundle;
    }

    /**
     * Sets the productBundle
     *
     * @param \RKW\RkwShop\Domain\Model\ProductBundle $productBundle
     * @return void
     */
    public function setProductBundle(\RKW\RkwShop\Domain\Model\ProductBundle $productBundle)
    {
        $this->productBundle = $productBundle;
    }

    /**
     * Returns the allowSingleOrder
     *
     * @return boolean $bundleOnly
     */
    public function getAllowSingleOrder()
    {
        if (
            ($this->getRecordType() != '\RKW\RkwShop\Domain\Model\ProductBundle')
            && ($this->getRecordType() != '\RKW\RkwShop\Domain\Model\ProductSubscription')
        ){
            return 99;
        }
        return $this->allowSingleOrder;
    }

    /**
     * Sets the allowSingleOrder
     *
     * @param boolean $allowSingleOrder
     * @return void
     */
    public function setAllowSingleOrder($allowSingleOrder)
    {
        $this->allowSingleOrder = $allowSingleOrder;
    }


    /**
     * Returns the page
     *
     * @return \RKW\RkwShop\Domain\Model\Pages $page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Sets the page
     *
     * @param \RKW\RkwShop\Domain\Model\Pages $page
     * @return void
     */
    public function setPage(\RKW\RkwShop\Domain\Model\Pages $page)
    {
        $this->page = $page;
    }


    /**
     * Returns the image
     *
     * @return \RKW\RkwBasics\Domain\Model\FileReference $image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Sets the image
     *
     * @param \RKW\RkwBasics\Domain\Model\FileReference $image
     * @return void
     */
    public function setImage(\RKW\RkwBasics\Domain\Model\FileReference $image)
    {
        $this->image = $image;
    }


    /**
     * Returns the download
     *
     * @return \RKW\RkwBasics\Domain\Model\FileReference $download
     */
    public function getDownload()
    {
        return $this->download;
    }

    /**
     * Sets the download
     *
     * @param \RKW\RkwBasics\Domain\Model\FileReference $download
     * @return void
     */
    public function setDownload(\RKW\RkwBasics\Domain\Model\FileReference $download)
    {
        $this->download = $download;
    }    


    /**
     * Adds a stock
     *
     * @param \RKW\RkwShop\Domain\Model\Stock $stock
     * @return void
     */
    public function addStock(\RKW\RkwShop\Domain\Model\Stock $stock)
    {
        $this->stock->attach($stock);
    }

    /**
     * Removes a stock
     *
     * @param \RKW\RkwShop\Domain\Model\Stock $stock
     * @return void
     */
    public function removeStock(\RKW\RkwShop\Domain\Model\Stock $stock)
    {
        $this->stock->detach($stock);
    }

    /**
     * Returns the stock
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwShop\Domain\Model\Stock> $stock
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Sets the stock
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwShop\Domain\Model\Stock> $stock
     * @return void
     */
    public function setStock(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $stock)
    {
        $this->stock = $stock;
    }


    /**
     * Returns the orderedExternal
     *
     * @return int $orderedExternal
     */
    public function getOrderedExternal()
    {
        return $this->orderedExternal;
    }


    /**
     * Sets the orderedExternal
     *
     * @param int $orderedExternal
     * @return void
     */
    public function setOrderedExternal($orderedExternal)
    {
        $this->orderedExternal = $orderedExternal;
    }


    /**
     * Adds a backendUser
     *
     * @param \RKW\RkwShop\Domain\Model\BackendUser $backendUser
     * @return void
     */
    public function addBackendUser(\RKW\RkwShop\Domain\Model\BackendUser $backendUser)
    {
        $this->backendUser->attach($backendUser);
    }

    /**
     * Removes a backendUser
     *
     * @param \RKW\RkwShop\Domain\Model\BackendUser $backendUser
     * @return void
     */
    public function removeBackendUser(\RKW\RkwShop\Domain\Model\BackendUser $backendUser)
    {
        $this->backendUser->detach($backendUser);
    }

    /**
     * Returns the EventWorkshop
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwShop\Domain\Model\BackendUser> $backendUser
     */
    public function getBackendUser()
    {
        return $this->backendUser;
    }

    /**
     * Sets the EventWorkshop
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwShop\Domain\Model\BackendUser> $backendUser
     * @return void
     */
    public function setBackendUser(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $backendUser)
    {
        $this->backendUser = $backendUser;
    }

    /**
     * Returns the adminEmail
     *
     * @return string $adminEmail
     */
    public function getAdminEmail()
    {
        return $this->adminEmail;
    }

    /**
     * Sets the adminEmail
     *
     * @param string $adminEmail
     * @return void
     */
    public function setAdminEmail($adminEmail)
    {
        $this->adminEmail = $adminEmail;
    }

}