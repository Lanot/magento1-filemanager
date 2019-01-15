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

class Lanot_FileManager_Block_Adminhtml_Content
    extends Mage_Adminhtml_Block_Cms_Wysiwyg_Images_Content
{
    /**
     * Block construction
     */
    public function __construct()
    {
        parent::__construct();

        $this->_removeButton('insert_files');
        $this->_removeButton('delete_folder');
        $this->_removeButton('delete_files');
        $this->_removeButton('newfolder');

        $this->_addButton('newfolder', array(
            'class'   => 'save',
            'label'   => $this->helper('cms')->__('Create Folder...'),
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.newFolder();',
            'id'      => 'button_new_folder',
        ));

        $this->_addButton('copy_selected', array(
            'class'   => 'no-display',
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.copySelected(false);',
            'id'      => 'button_copy_selected',
            'label'   => $this->_getHelper()->__('Copy'),
        ));

        $this->_addButton('cut_selected', array(
            'class'   => 'no-display',
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.copySelected(true);',
            'id'      => 'button_cut_selected',
            'label'   => $this->_getHelper()->__('Cut'),
        ));

        $this->_addButton('paste_selected', array(
            'class'   => 'no-display',
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.pasteSelected();',
            'id'      => 'button_paste_selected',
            'label'   => $this->_getHelper()->__('Paste'),
        ));

        $this->_addButton('cancel_selected', array(
            'class'   => 'no-display',
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.cancelSelected();',
            'id'      => 'button_cancel_selected',
            'label'   => $this->_getHelper()->__('Cancel'),
        ));

        $this->_addButton('rename_selected', array(
            'class'   => 'no-display',
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.renameSelected();',
            'id'      => 'button_rename_selected',
            'label'   => $this->_getHelper()->__('Rename'),
        ));

        $this->_addButton('delete_selected', array(
            'class'   => 'delete no-display',
            'type'    => 'button',
            'onclick' => 'MediabrowserInstance.deleteSelected();',
            'id'      => 'button_delete_selected',
            'label'   => $this->_getHelper()->__('Delete'),
        ));


        $this->addData(array(
            'url_copy_selected' => $this->getUrl('*/*/copySelected'),
            'url_rename_selected' => $this->getUrl('*/*/renameSelected'),

            'rename_selected_error_message' => $this->_getHelper()->__('Could not rename few files/folders at the one time. Please, select one.'),
            'rename_message' => $this->_getHelper()->__('New Folder/File Name:'),
            'delete_confirmation_message' => $this->_getHelper()->__('Are you sure you want to delete selected folders or files?'),
            'same_folder_message' => $this->_getHelper()->__('Could not perform operation into same folder. Please, select another target folder.'),
            'paste_button_message' => $this->_getHelper()->__('Paste %d item(s)'),
        ));
        return $this;
    }

    protected function _getHelper()
    {
        return $this->helper('lanot_filemanager');
    }
}
