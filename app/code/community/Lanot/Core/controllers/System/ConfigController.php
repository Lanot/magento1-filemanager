<?php
/**
 * @category    Lanot
 * @package     Lanot_Core
 * @copyright   Copyright (c) 2012 Lanot
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
