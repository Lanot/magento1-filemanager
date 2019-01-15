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
class Lanot_FileManager_Block_Adminhtml_Content_Files
    extends Mage_Adminhtml_Block_Cms_Wysiwyg_Images_Content_Files
{
    const DEFAULT_THUMB = 'text.png';
    const FOLDER_THUMB = 'folder.png';

    /**
     * @var Varien_Data_Collection_Filesystem
     */
    protected $_dirsCollection = null;

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
        return Mage::getSingleton('lanot_filemanager/storage');
    }

    /**
     * Prepared Files collection for current directory
     *
     * @return Varien_Data_Collection_Filesystem
     */
    public function getFiles()
    {
        if (! $this->_filesCollection) {
            $this->_filesCollection = $this->_getStorage()->getFilesCollection(
                $this->_getMediaHelper()->getCurrentPath(), $this->_getMediaType()
            );
        }
        return $this->_filesCollection;
    }

    /**
     * Prepared sub Dirs collection for current directory
     *
     * @return Varien_Data_Collection_Filesystem
     */
    public function getDirs()
    {
        if (! $this->_dirsCollection) {
            $this->_dirsCollection= $this->_getStorage()->getDirsCollection($this->_getMediaHelper()->getCurrentPath());
        }
        return $this->_dirsCollection;
    }

    /**
     * Dirs collection count getter
     *
     * @return int
     */
    public function getDirsCount()
    {
        return $this->getDirs()->count();
    }

    /**
     * Default folder thumbnail
     *
     * @return string
     */
    public function getFolderThumbnail()
    {
        return $this->_getSkinUrl() . $this->_getMediaHelper()->getThumbPath() . '/' . self::FOLDER_THUMB;
    }

    /**
     * Dir name getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getDirName(Varien_Object $file)
    {
        return $file->getBasename();
    }

    /**
     * Dir name getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileShortName(Varien_Object $file)
    {
        return $this->_getMediaHelper()->getShortFilename($file->getBasename(), 15);
    }

    /**
     * File name URL getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getDirId(Varien_Object $file)
    {
        return $this->_getMediaHelper()->convertPathToId($file->getFilename());
    }

    /**
     * File thumb URL getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileThumbUrl(Varien_Object $file)
    {
        if (!$this->_getStorage()->isImage($file->getBasename()) &&
            ($type = $this->_getFileType($file->getFilename()))
        ) {
            $thumbUrl = $this->_getSkinUrl() . $this->_getMediaHelper()->getThumbPath() . '/' . $type . '.png';
            $file->setThumbUrl($thumbUrl);
        } elseif (!is_dir($file->getFilename()) && !$this->_getStorage()->isImage($file->getBasename())) {
            $thumbUrl = $this->_getSkinUrl() . $this->_getMediaHelper()->getThumbPath() . '/' . self::DEFAULT_THUMB;
            $file->setThumbUrl($thumbUrl);
        }
        return  $file->getThumbUrl();
    }

    /**
     * @param $filename
     * @return bool
     */
    protected function _getFileType($filename)
    {
        $info = pathinfo($filename);
        if (!is_array($info) || !isset($info['extension'])) {
            return false;
        }
        $file = $this->_getMediaHelper()->getSkinPath(). DS . $this->_getMediaHelper()->getThumbPath() . DS . $info['extension'] . '.png';
        if (file_exists($file)) {
            return $info['extension'];
        }
        return false;
    }

    /**
     * @return string
     */
    public function _getSkinUrl()
    {
        return Mage::getDesign()->getSkinBaseUrl(array(
            '_package' => Mage_Core_Model_Design_Package::DEFAULT_PACKAGE,
            '_theme' => Mage_Core_Model_Design_Package::DEFAULT_THEME,
        ));
    }
}
