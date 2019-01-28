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

class Lanot_FileManager_Block_Adminhtml_Tree
    extends Mage_Adminhtml_Block_Cms_Wysiwyg_Images_Tree
{
    /**
     * @return Lanot_FileManager_Helper_Media
     */
    protected function _getMediaHelper()
    {
        return Mage::helper('lanot_filemanager/media');
    }

    /**
     * @return Lanot_FileManager_Model_Storage
     */
    protected function _getStorage()
    {
        return Mage::registry('storage');
    }

    /**
     * Json tree builder
     *
     * @return string
     */
    public function getTreeJson()
    {
        $helper = $this->_getMediaHelper();//@todo: added by lanot
        $collection = $this->_getStorage()->getDirsCollection($helper->getCurrentPath());
        $jsonArray = array();
        foreach ($collection as $item) {
            $jsonArray[] = array(
                'text'  => $helper->getShortFilename($item->getBasename(), 20),
                'id'    => $helper->convertPathToId($item->getFilename()),
                'cls'   => 'folder'
            );
        }
        return Zend_Json::encode($jsonArray);
    }

    /**
     * Return tree node full path based on current path
     *
     * @return string
     */
    public function getTreeCurrentPath()
    {
        $treePath = '/root';
        if ($path = $this->_getStorage()->getSession()->getCurrentPath()) {
            $helper = $this->_getMediaHelper();//@todo: added by lanot
            $path = str_replace($helper->getStorageRoot(), '', $path);
            $relative = '';
            foreach (explode(DS, $path) as $dirName) {
                if ($dirName) {
                    $relative .= DS . $dirName;
                    $treePath .= '/' . $helper->idEncode($relative);
                }
            }
        }
        return $treePath;
    }
}
