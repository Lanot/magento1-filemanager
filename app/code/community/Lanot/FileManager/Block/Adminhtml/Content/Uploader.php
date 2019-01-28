<?php

/**
 * Private Entrepreneur Anatolii Lehkyi (aka Lanot)
 *
 * @category    Lanot
 * @package     Lanot_FileManager
 * @copyright   Copyright (c) 2010 Anatolii Lehkyi
 * @license     http://opensource.org/licenses/osl-3.0.php
 * @link        http://www.lanot.biz/
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
