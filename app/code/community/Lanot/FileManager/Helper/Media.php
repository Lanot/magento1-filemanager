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
class Lanot_FileManager_Helper_Media extends Mage_Cms_Helper_Wysiwyg_Images
{
    protected $_isCurrentPathWritable = true;

    /**
     *
     */
    const THUMB_PATH = 'images/lanot/filemanager';
    const XML_NODE_PATH_ROOT_DIR = 'lanot_filemanager/view/root_directory';

    /**
     * Images Storage root directory
     *
     * @return string
     */
    public function getStorageRoot()
    {
        $root = Mage::getBaseDir();
        if ($path = $this->_getSubStorageRoot()) {
            $root .=  DS . $path;
        }
        return $root;
    }

    /**
     * Images Storage base URL
     *
     * @return string
     */
    public function getBaseUrl()
    {
        $url = $this->_sanitizeUrl(Mage::getBaseUrl()) . '/';
        if ($path = $this->_getSubStorageRoot()) {
            $path = trim($path, DS);
            $url .= $this->convertPathToUrl($path) . '/';
        }
        return $url;
    }


    /**
     * Return URL based on current selected directory or root directory for startup
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        if (!$this->_currentUrl) {
            $path = str_replace($this->getStorageRoot(), '', $this->getCurrentPath());
            $path = trim($path, DS);
            $this->_currentUrl = $this->getBaseUrl() . $this->convertPathToUrl($path) . '/';
        }
        return $this->_currentUrl;
    }

    /**
     * Return path of the current selected directory or root directory for startup
     * Try to create target directory if it doesn't exist
     *
     * @throws Mage_Core_Exception
     * @return string
     */
    public function getCurrentPath()
    {
        if (!$this->_currentPath) {
            $currentPath = $this->getStorageRoot();
            $path = $this->_getRequest()->getParam($this->getTreeNodeName());
            if ($path) {
                $path = $this->convertIdToPath($path);
                if (is_dir($path)) {
                    $currentPath = $path;
                }
            }

            $io = new Varien_Io_File();
            if (!$io->isWriteable($currentPath) && !$io->mkdir($currentPath)) {
                $this->_isCurrentPathWritable = false;
                //$message = Mage::helper('cms')->__('The directory %s is not writable by server.',$currentPath);
                //Mage::throwException($message);
            }
            $this->_currentPath = $currentPath;
        }
        return $this->_currentPath;
    }

    /**
     * @return string
     */
    public function getThumbPath()
    {
        return self::THUMB_PATH;
    }

    /**
     * @return string
     */
    public function getSkinPath()
    {
        return Mage::getBaseDir('skin') . DS .
            Mage::getDesign()->getArea() . DS .
            Mage_Core_Model_Design_Package::DEFAULT_PACKAGE . DS .
            Mage_Core_Model_Design_Package::DEFAULT_THEME;
    }

    /**
     * @return bool
     */
    public function getIsCurrentPathWritable()
    {
        return $this->_isCurrentPathWritable;
    }

    /**
     * @return string|bool
     */
    protected function _getSubStorageRoot()
    {
        $subDir = (string) Mage::app()->getStore()->getConfig(self::XML_NODE_PATH_ROOT_DIR);
        if (!empty($subDir)) {
            return $subDir;
        }
        return false;
    }

    /**
     * @return string
     */
    protected function _sanitizeUrl($url)
    {
        $url = str_replace('/index.php', '/', $url);
        $url = str_replace('//', '/', $url);//@fix
        return $url;
    }
}