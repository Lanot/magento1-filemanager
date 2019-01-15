<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Lanot
 * @package     Lanot_Core
 * @copyright   Copyright (c) 2012 Lanot
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Model's interface abstract
 *
 * @version 1.0.0
 * @author Lanot
 */
interface Lanot_Core_Model_Item_Interface
{
    /**
     * @return array
     */
    public function getAvailableStatuses();

    /**
     * @return int
     */
    public function getStatusDisabled();

    /**
     * @return int
     */
    public function getStatusEnabled();
}