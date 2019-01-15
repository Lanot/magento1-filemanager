<?php
/**
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