<?php
/**
 * Private Entrepreneur Anatolii Lehkyi (aka Lanot)
 *
 * @category    Lanot
 * @package     Lanot_Core
 * @copyright   Copyright (c) 2010 Anatolii Lehkyi
 * @license     http://opensource.org/licenses/osl-3.0.php
 * @link        http://www.lanot.biz/
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