<?php
/**
 * Private Entrepreneur Anatolii Lehkyi (aka Lanot)
 *
 * @category    Lanot
 * @package     Lanot_Core
 * @copyright   Copyright (c) 2010 Anatolii Lehkyi
 * @license     http://opensource.org/licenses/osl-3.0.php
 * @link        http://www.lanot.biz/
 */

/**
 * Core Controller Abstract
 * @version 1.0.0
 * @author Lanot
 */
class Lanot_Core_Controller_Adminhtml_AbstractController
    extends Mage_Adminhtml_Controller_Action
{
    protected $_msgTitle = 'Items';
    protected $_msgHeader = 'Items';
    protected $_msgItemDoesNotExist = 'Item does not exist.';
    protected $_msgItemNotFound = 'Unable to find an item. #%s';
    protected $_msgItemEdit = 'Edit Item';
    protected $_msgItemNew = 'New Item';
    protected $_msgItemSaved = 'The item has been saved.';
    protected $_msgItemDeleted = 'The item has been deleted.';
    protected $_msgError = 'An error occurred while edit the item.';
    protected $_msgErrorItems = 'An error occurred while edit the items. %s';
    protected $_msgItems = 'The items %s has been';

    protected $_menuActive = null;
    protected $_aclSection = null;

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'new':
            case 'edit':
            case 'save':
            case 'mass':
                return $this->_getAclHelper()->isActionAllowed($this->_aclSection . '/save');
                break;
            case 'delete':
                return $this->_getAclHelper()->isActionAllowed($this->_aclSection . '/delete');
                break;
            default:
                return $this->_getAclHelper()->isActionAllowed($this->_aclSection);
                break;
        }
    }

    /**
     * Init actions
     *
     * @return Lanot_Core_Controller_Adminhtml_AbstractController
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu($this->_menuActive)
            ->_addBreadcrumb(
                $this->_getHelper()->__($this->_msgTitle),
                $this->_getHelper()->__($this->_msgTitle)
            )
            ->_addBreadcrumb(
                $this->_getHelper()->__($this->_msgHeader),
                $this->_getHelper()->__($this->_msgHeader)
            );

        return $this;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_title($this->__($this->_msgTitle))
            ->_title($this->__($this->_msgHeader))
            ->_initAction()
            ->renderLayout();
    }

    /**
     * Grid action
     */
    public function gridAction()
    {
        $this->_loadLayouts();
    }

    /**
     * Create new Item
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit item
     */
    public function editAction()
    {
        $this->_title($this->__($this->_msgTitle))
            ->_title($this->__($this->_msgHeader));

        // 1. instance model
        $model = $this->_getItemModel();

        // 2. if exists id, check it and load data
        $itemId = $this->getRequest()->getParam('id');
        if ($itemId) {
            $model->load($itemId);
            if (!$model->getId()) {
                $this->_getSession()->addError($this->_getHelper()->__($this->_msgItemNotFound, $itemId));
                return $this->_redirect('*/*/');
            }
            // prepare title
            $this->_title($model->getTitle());
            $breadCrumb = $this->_getHelper()->__($this->_msgItemEdit);
        } else {
            $this->_title($this->_getHelper()->__($this->_msgItemNew));
            $breadCrumb = $this->_getHelper()->__($this->_msgItemNew);
        }
        // 3. Set entered data if was error when we do save
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        // 4. Register model to use later in blocks
        $this->_registerItem($model);

        // Init breadcrumbs
        $this->_initAction()->_addBreadcrumb($breadCrumb, $breadCrumb);

        // 5. render layout
        $this->renderLayout();
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        $redirectPath = '*/*';
        $redirectParams = array();
        $hasError = false;
        // check if data sent
        $data = $this->getRequest()->getPost();
        if ($data) {
            $data = $this->_preparePostData($data);

            //1. instance model
            $model = $this->_getItemModel();
            //2. if exists id, try to load data
            $itemId = $this->getRequest()->getParam('id');
            if ($itemId) {
                $model->load($itemId);
            }
            $model->addData($data);

            try {
                //3. save the data
                $model->save();
                //4. display success message
                $msg = $this->_getHelper()->__($this->_msgItemSaved);
                $this->_getSession()->addSuccess($msg);
                //5. check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $redirectPath = '*/*/edit';
                    $redirectParams = array('id' => $model->getId());
                }
            } catch (Mage_Core_Exception $e) {
                $hasError = true;
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $hasError = true;
                $msg = $this->_getHelper()->__($this->_msgError);
                $this->_getSession()->addException($e, $msg);
            }
            //6. check if errors happened
            if ($hasError) {
                $this->_getSession()->setFormData($data);
                $redirectPath = '*/*/edit';
                $redirectParams = array('id' => $this->getRequest()->getParam('id'));
            }
        }

        if ($this->getRequest()->getParam('store')) {
            $redirectParams['store'] = $this->getRequest()->getParam('store');
        }
        //7. go to grid or edit form
        $this->_redirect($redirectPath, $redirectParams);
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        $itemId = $this->getRequest()->getParam('id');
        if ($itemId) {
            try {
                // 1. instance model
                $model = $this->_getItemModel();
                // 2. if exists id, load data
                $model->load($itemId);
                // 3. check if elements exists
                if (!$model->getId()) {
                    $msg = $this->_getHelper()->__($this->_msgItemDoesNotExist);
                    Mage::throwException($msg);
                }
                // 4. delete item
                $model->delete();
                // display success message
                $msg = $this->_getHelper()->__($this->_msgItemDeleted);
                $this->_getSession()->addSuccess($msg);
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $msg = $this->_getHelper()->__($this->_msgError, $e->getMessage());
                $this->_getSession()->addException($e, $msg);
            }
        }
        // 5. go to grid
        $this->_redirect('*/*/');
    }

    /**
     * Mass Update/Delete Action
     */
    public function massAction()
    {
        $msg = '';
        $key = $this->getRequest()->getParam('massaction_prepare_key');
        $itemIds = $this->getRequest()->getParam($key);
        $type = $this->getRequest()->getParam('type');
        $active = $this->_getItemModel()->getStatusDisabled();

        if (is_array($itemIds) && count($itemIds)) {
            try {
                foreach ($itemIds as $itemId) {
                    // 1. instance banner model
                    $model = $this->_getItemModel();
                    // 2. if exists id, load data
                    $model->load($itemId);
                    // 3. check if elements exists
                    if (!$model->getId()) {
                        Mage::throwException($this->_getHelper()->__($this->_msgItemNotFound));
                    }
                    // 4. main logic
                    switch ($type) {
                        case 'enable':
                            $active = $this->_getItemModel()->getStatusEnabled();
                        case 'disable':
                            $model->setIsActive($active);
                            $model->save();
                            $msg = $this->_msgItems .
                                (($active == $this->_getItemModel()->getStatusDisabled()) ? ' disabled' : ' enabled') . '.';
                            break;
                        case 'delete':
                            $model->delete();
                            $msg = $this->_msgItems . ' deleted.';
                            break;
                    }
                }
                // display success message
                $this->_getSession()->addSuccess($this->_getHelper()->__($msg, implode(', ', $itemIds)));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e, $this->_getHelper()->__($this->_msgErrorItems, $e->getMessage()));
            }
        }
        // 5. go to grid
        $this->_redirect('*/*/');
    }

    /**
     * @param array $data
     * @return array
     */
    protected function _preparePostData($data)
    {
        return $data;
    }

    /**
     * @return Mage_Core_Helper_Abstract
     */
    protected function _getHelper()
    {
        return null;
    }

    /**
     * @return Mage_Core_Helper_Abstract
     */
    protected function _getAclHelper()
    {
        return null;
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _getItemModel()
    {
        return null;
    }

    /**
     * @param Mage_Core_Model_Abstract $model
     * @return Lanot_Core_Controller_Adminhtml_AbstractController
     */
    protected function _registerItem(Mage_Core_Model_Abstract $model)
    {
        return $this;
    }

    /**
     * @return Lanot_Core_Controller_Adminhtml_AbstractController
     */
    protected function _loadLayouts()
    {
        $this->loadLayout();
        $this->renderLayout();
        return $this;
    }
}
