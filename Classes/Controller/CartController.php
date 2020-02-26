<?php

namespace RKW\RkwShop\Controller;

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

use RKW\RkwShop\Helper\DivUtility;
use RKW\RkwShop\Domain\Model\Order;
use RKW\RkwShop\Domain\Model\OrderItem;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Class CartController
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CartController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * productRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     * @inject
     */
    protected $productRepository = null;

    /**
     * Cart
     *
     * @var \RKW\RkwShop\Cart\Cart
     * @inject
     */
    protected $cart;

    /**
     * action update cart
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @param int                               $amount
     */
    public function updateAction(\RKW\RkwShop\Domain\Model\Product $product, $amount = 0)
    {

        $this->cart->initialize($product, $amount);

        $this->redirect('show', 'Cart', 'tx_rkwshop_cart', null, $this->settings['cartPid']);

    }

    /**
     * action show cart
     *
     * @return void
     */
    public function showAction()
    {

        $cart = $this->cart->get();

        $listItemsPerView = (int)$this->settings['itemsPerPage'] ? (int)$this->settings['itemsPerPage'] : 10;

//        $productList = DivUtility::prepareResultsList($queryResult, $listItemsPerView);

        $this->view->assignMultiple([
            'cartItems'   => $cart->getOrderItem(),
        ]);

    }
}