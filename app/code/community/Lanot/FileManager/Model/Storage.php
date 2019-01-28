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

class Lanot_FileManager_Model_Storage extends Mage_Cms_Model_Wysiwyg_Images_Storage
{
    const THUMB_PLACEHOLDER_PATH_SUFFIX = 'images/lanot/filemanager/text.png';
    protected $_canUseFileStorage = null;

     /**
      * Config object getter
      *
      * @return Mage_Core_Model_Config_Element
      */
     public function getConfig()
     {
         if (!$this->_config) {
             $this->_config = Mage::getConfig()->getNode('lanot_filemanager/browser', 'adminhtml');
         }
         return $this->_config;
     }

     /**
      * Media Storage Helper getter
      * @return Lanot_FileManager_Helper_Media
      */
     public function getHelper()
     {
         return Mage::helper('lanot_filemanager/media');
     }

     /**
      * Storage collection
      *
      * @param string $path Path to the directory
      * @return Varien_Data_Collection_Filesystem
      */
     public function getCollection($path = null)
     {
         $collection = Mage::getModel('lanot_filemanager/storage_collection');
         if ($path !== null) {
             $collection->addTargetDir($path);
         }
         return $collection;
     }

    /**
     * Return files
     *
     * @param string $path Parent directory path
     * @param string $type Type of storage, e.g. image, media etc.
     * @return Varien_Data_Collection_Filesystem
     */
    public function getFilesCollection($path, $type = null)
    {
        $collection = parent::getFilesCollection($path, $type);

        $helper = $this->getHelper();

        // prepare items. prepare new file url
        foreach ($collection as $item) {
            $item->setDownloadUrl($this->getUrl('lanot_filemanager/adminhtml_media/download',
                array('id' => $helper->idEncode($item->getFilename()))
            ));
        }

        return $collection;
    }

     /**
      * Create thumbnail for image and save it to thumbnails directory
      *
      * @param string $source Image path to be resized
      * @param bool $keepRation Keep aspect ratio or not
      * @return bool|string Resized filepath or false if errors were occurred
      */
     public function resizeFile($source, $keepRation = true)
     {
         if (!$this->isImage($source)) {
             return false;
         }
         return parent::resizeFile($source, $keepRation);
     }

     /**
      * Prepare allowed_extensions config settings
      *
      * @param string $type Type of storage, e.g. image, media etc.
      * @return array Array of allowed file extensions
      */
     public function getAllowedExtensions($type = null)
     {
         $extensions = $this->getConfigData('extensions');

         if (is_string($type) && array_key_exists("{$type}_allowed", $extensions)) {
             $allowed = $extensions["{$type}_allowed"];
         } else {
             $allowed = $extensions['allowed'];
         }

         if (!$allowed) {
            return false;
         }

         return array_keys(array_filter($allowed));
     }

     /**
      * Thumbnail root directory getter
      *
      * @return string
      */
     public function getThumbnailRoot()
     {
         return Mage::getConfig()->getOptions()->getMediaDir() . DS . self::THUMBS_DIRECTORY_NAME;
     }

     /**
      * @param $oldname
      * @param $newName
      * @return bool
      */
     public function rename($oldname, $newName)
     {
         if (!is_file($oldname) && !is_dir($oldname)) {
             Mage::throwException(
                 Mage::helper('lanot_filemanager')->__('A file or directory "%s" does not exists', $oldname)
             );
         }

         $dir = dirname($oldname);
         $newname = $dir . DS . $newName;
         if (is_file($newname)) {
             Mage::throwException(
                 Mage::helper('lanot_filemanager')->__('A file with the same name already exists. Please try another file name.')
             );
         }
         if (is_dir($newname) || is_link($newname)) {
             Mage::throwException(
                 Mage::helper('lanot_filemanager')->__('A directory with the same name already exists. Please try another folder name.')
             );
         }

         return rename($oldname, $newname);
     }

     /**
      * @param $oldname
      * @param $newfolder
      * @param bool $move
      * @return bool
      */
     public function copy($oldname, $newfolder, $move = false)
     {
         $info = pathinfo($oldname);
         $name = $info["basename"];
         $newname = $newfolder . DS . $name;
         if ($move) {
             return rename($oldname, $newname);
         } else {
             return copy($oldname, $newname);
         }
     }

     /**
      * @param $file
      * @return bool|Mage_Cms_Model_Wysiwyg_Images_Storage|void
      */
     public function delete($file)
     {
         if (is_file($file)) {
             return $this->deleteFile($file);
         } elseif (is_dir($file)) {
             return $this->deleteDirectory($file);
         }
         return false;
     }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route='', $params=array())
    {
        return Mage::helper('adminhtml')->getUrl($route, $params);
    }

    /**
     * Return thumbnails directory path for file/current directory
     *
     * @param string $filePath Path to the file
     * @return string
     */
    public function getThumbsPath($filePath = false)
    {
        $mediaRootDir = Mage::getConfig()->getOptions()->getMediaDir();
        $thumbnailDir = $this->getThumbnailRoot();

        if ($filePath && strpos($filePath, $mediaRootDir) === 0) {
            $thumbnailDir .= DS . dirname(substr($filePath, strlen($mediaRootDir)));
        } elseif ($filePath && strpos($filePath, $mediaRootDir) === false) {
            $baseDir = Mage::getBaseDir();
            $thumbnailDir .= DS . 'root' . DS . str_replace($baseDir, '', $filePath);
        }

        return $thumbnailDir;
    }

    /**
     * @fix: for old Magento (CE 1.5)
     * Return one-level child directories for specified path
     *
     * @param string $path Parent directory path
     * @return Varien_Data_Collection_Filesystem
     */
    public function getDirsCollection($path)
    {
        if ($this->_canUseFileStorage() && //@fix: for CE 1.4
            Mage::helper('core/file_storage_database')->checkDbUsage()) {
            $subDirectories = Mage::getModel('core/file_storage_directory_database')->getSubdirectories($path);
            foreach ($subDirectories as $directory) {
                $fullPath = rtrim($path, DS) . DS . $directory['name'];
                if (!file_exists($fullPath)) {
                    mkdir($fullPath, 0777, true);
                }
            }
        }

        $conditions = array('reg_exp' => array(), 'plain' => array());

        foreach ($this->getConfig()->dirs->exclude->children() as $dir) {
            $conditions[$dir->getAttribute('regexp') ? 'reg_exp' : 'plain'][(string) $dir] = true;
        }
        // "include" section takes precedence and can revoke directory exclusion
        foreach ($this->getConfig()->dirs->include->children() as $dir) {
            unset($conditions['regexp'][(string) $dir], $conditions['plain'][(string) $dir]);
        }

        $regExp = $conditions['reg_exp'] ? ('~' . implode('|', array_keys($conditions['reg_exp'])) . '~i') : null;
        $collection = $this->getCollection($path)
            ->setCollectDirs(true)
            ->setCollectFiles(false)
            ->setCollectRecursively(false);
        $storageRootLength = strlen($this->getHelper()->getStorageRoot());

        foreach ($collection as $key => $value) {
            $rootChildParts = explode(DIRECTORY_SEPARATOR, substr($value->getFilename(), $storageRootLength));

            if (array_key_exists($rootChildParts[0], $conditions['plain'])
                || ($regExp && preg_match($regExp, $value->getFilename()))) {
                $collection->removeItemByKey($key);
            }
        }

        return $collection;
    }

    /**
     * @return bool
     */
    protected function _canUseFileStorage()
    {
        if (null === $this->_canUseFileStorage) {
            $file = Mage::getBaseDir('code');
            $file.= '/core/Mage/Core/Helper/File/Storage/Database.php';
            $this->_canUseFileStorage = file_exists($file);
        }
        return $this->_canUseFileStorage;
    }
}
