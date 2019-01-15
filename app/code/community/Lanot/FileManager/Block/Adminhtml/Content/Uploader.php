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
 * @package     Lanot_FileManager
 * @copyright   Copyright (c) 2012 Lanot
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Lanot_FileManager_Block_Adminhtml_Content_Uploader
    extends Mage_Adminhtml_Block_Cms_Wysiwyg_Images_Content_Uploader
{
    /**
     * Lanot_FileManager_Block_Adminhtml_Content_Uploader constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->getButtonConfig()
            ->setAttributes(array(
                'accept' => $this->getButtonConfig()->getMimeTypesByExtensions(
                    $this->getAllowedExtensions()
                ),
            ));
    }

    /**
     * @return array
     */
    public function getAllowedExtensions()
    {
        $type = $this->_getMediaType();
        $allowed = Mage::getSingleton('lanot_filemanager/storage')->getAllowedExtensions($type);

        if (!$allowed) {
            $allowed = array_keys($this->_getHelper()->getMimeTypes());
        }
        return $allowed;
    }
}
