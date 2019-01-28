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

require_once('Mage/Adminhtml/controllers/Cms/Wysiwyg/ImagesController.php');

class Lanot_FileManager_Adminhtml_MediaController
	extends Mage_Adminhtml_Cms_Wysiwyg_ImagesController
{

    public function indexAction()
    {
        $isError = false;
        $storeId = (int) $this->getRequest()->getParam('store');
        try {
            $this->_getMediaHelper()->getCurrentPath();
            $root = $this->_getMediaHelper()->getStorageRoot();
            if (!is_dir($root)) {
                Mage::throwException($this->_getDataHelper()->__('Root folder "%s" doesn\'t exist.', $root));
                $isError = true;
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_initAction()->loadLayout();//'overlay_popup'
        if (!$isError) {
            $block = $this->getLayout()->getBlock('wysiwyg_images.js');
            if ($block) {
                $block->setStoreId($storeId);
            }
        } else {
            $this->getLayout()->getBlock('left')->unsetChildren();
            $this->getLayout()->getBlock('content')->unsetChildren();
        }

        $this->renderLayout();
    }

    public function treeJsonAction()
    {
        try {
            $this->_initAction();
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('lanot_filemanager/adminhtml_tree')->getTreeJson());
        } catch (Exception $e) {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array()));
        }
    }

    /**
     * Delete file from media storage
     *
     * @return void
     */
    public function deleteFilesAction()
    {
        try {
            if (!$this->getRequest()->isPost()) {
                throw new Exception ('Wrong request.');
            }
            $files = Mage::helper('core')->jsonDecode($this->getRequest()->getParam('files'));
            if (!$files || !is_array($files)) {
                return ;
            }

            foreach ($files as $file) {
                $this->getStorage()->delete($this->_getRealFilename($file));
            }
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function renameSelectedAction()
    {
        try {
            if (!$this->getRequest()->isPost()) {
                throw new Exception ('Wrong request.');
            }

            $file = trim($this->getRequest()->getParam('file'));
            $name = trim($this->getRequest()->getParam('name'));
            if (empty($name) || empty($file)) {
                Mage::throwException($this->__('Passed empty name'));
            }
            $file = $this->_getRealFilename($file);
            if (is_file($file) || is_dir($file)) {
                if(!$this->getStorage()->rename($file, $name)) {
                    Mage::throwException($this->__('Could not rename file/folder.'));
                }
            } else {
                Mage::throwException($this->__('Unsupported file/folder type'));
            }
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function copySelectedAction()
    {
        try {
            if (!$this->getRequest()->isPost()) {
                throw new Exception ('Wrong request.');
            }

            $oldfolder = trim($this->getRequest()->getParam('oldfolder'));
            $newfolder = trim($this->getRequest()->getParam('newfolder'));
            $oldfolder = $this->_getRealFilename($oldfolder);
            $newfolder = $this->_getRealFilename($newfolder);

            $cut = (bool) $this->getRequest()->getParam('cut');
            $files = Mage::helper('core')->jsonDecode($this->getRequest()->getParam('items'));
            if (!$files || !is_array($files)) {
                return ;
            }

            //check files before paste operation - protect copy self into self
            $_files = array();
            foreach ($files as $file) {
                $file = $this->_getRealFilename($file, $oldfolder);
                if (!$file) {
                    continue;
                } elseif ($file && is_dir($file) && (strpos($newfolder, $file) === 0)) {
                    Mage::throwException(
                        $this->__('Could not perform paste operation. Please, select another target folder.')
                    );
                }
                $_files[] = $file;
            }

            //perform copy/cut/paste operation
            foreach ($_files as $file) {
                if (!$this->getStorage()->copy($file, $newfolder, $cut)) {
                    Mage::throwException($this->__('Could not perform paste operation.'));
                }
            }
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function downloadAction()
    {
        $filename = false;
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            $filename = $this->_getMediaHelper()->idDecode($id);
            if (!file_exists($filename)) {
                $filename = false;
            }
        }

        if (!$filename) {
            $this->getResponse()->setBody($this->_getDataHelper()->__('File with ID %s does not exist', $id));
        } else {
            return $this->_prepareDownloadResponse(basename($filename), file_get_contents($filename));
        }
    }

    /**
     * Register storage model and return it
     *
     * @return Lanot_FileManager_Model_Storage
     */
    public function getStorage()
    {
        if (!Mage::registry('storage')) {
            $storage = Mage::getModel('lanot_filemanager/storage');
            Mage::register('storage', $storage);
        }
        return Mage::registry('storage');
    }

    /**
     * @param $file
     * @return bool|string
     */
    protected function _getRealFilename($file, $folder = null)
    {
        /** @var $helper Lanot_FileManager_Helper_Media */
        $helper = $this->_getMediaHelper();
        if ($file == 'root') {
            return $helper->getStorageRoot();
        }
        $file = $helper->idDecode($file);
        if ((strpos($file, '/') === false) && (strpos($file, '\\') === false)) {
            $path = $folder ? $folder : $this->getStorage()->getSession()->getCurrentPath();//for files
        } else {
            $path = $helper->getStorageRoot();//for folders
        }

        $_file = realpath($path . DS . $file);
        if (strpos($_file, realpath($path)) === 0 && strpos($_file, realpath($helper->getStorageRoot())) === 0) {
            return $_file;
        }
        return false;
    }

    /**
     * Save current path in session
     *
     * @return Mage_Adminhtml_Cms_Page_Wysiwyg_ImagesController
     */
    protected function _saveSessionCurrentPath()
    {
        $this->getStorage()
            ->getSession()
            ->setCurrentPath($this->_getMediaHelper()->getCurrentPath());
        return $this;
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('lanot/lanot_filemanager');
    }

    /**
     * @return Lanot_FileManager_Helper_Media
     */
    protected function _getMediaHelper()
    {
        return Mage::helper('lanot_filemanager/media');
    }

    /**
     * @return Lanot_FileManager_Helper_Data
     */
    protected function _getDataHelper()
    {
        return Mage::helper('lanot_filemanager');
    }

    //Forse stub
    public function onInsertAction() {}
    public function deleteFolderAction(){}
}
