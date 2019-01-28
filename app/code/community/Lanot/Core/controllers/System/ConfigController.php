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
require_once('Mage/Adminhtml/controllers/System/ConfigController.php');

class Lanot_Core_System_ConfigController extends Mage_Adminhtml_System_ConfigController
{
    public function lanotupgradesAction()
    {
        $email = Mage::getStoreConfig('lanot_core/account/email');
        $data = $this->_getExtensionModel()->getFeedData($email);
        Mage::register('lanot_upgrades_and_offers', $data);

        return $this->_forward('edit');
    }

    /**
     * @return Lanot_Core_Model_Extension
     */
    protected function _getExtensionModel()
    {
        return Mage::getModel('lanot_core/extension');
    }
}
