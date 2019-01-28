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

class Lanot_FileManager_Model_Storage_Collection
    extends Mage_Cms_Model_Wysiwyg_Images_Storage_Collection
{
    /**
     * Directory names regex pre-filter
     *
     * @var string
     */
    protected $_allowedDirsMask  = null;

    /**
     * Filenames regex pre-filter
     *
     * @var string
     */
    protected $_allowedFilesMask = null;
}
