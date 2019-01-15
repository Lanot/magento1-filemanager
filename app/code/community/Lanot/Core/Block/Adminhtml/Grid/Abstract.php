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
 * @package     Lanot_Core
 * @copyright   Copyright (c) 2012 Lanot
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Core Grid abstract
 *
 * @version 1.2.1
 * @author Lanot
 */
class Lanot_Core_Block_Adminhtml_Grid_Abstract
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_gridId = 'lanot_core_list_grid';
    protected $_entityIdField = 'entity_id';
    protected $_itemParam = 'entity_id';
    protected $_formFieldName = 'entity';
    protected $_columnPrefix = '';
    protected $_checkboxFieldName = 'in_selected';
    protected $_isTabGrid = false;

    /** @var Mage_Core_Model_Abstract */
    protected $_item = null;
    protected $_selectedLinks = null;
    protected $_eventPrefix = '';

    /**
     * Init Grid default properties
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId($this->_gridId);
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        if ($this->isReadonly()) {
            $this->setFilterVisibility(false);
        }
    }

    /**
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    protected function _getCollection()
    {
        return $this->_getItemModel()->getCollection();
    }

    /**
     * @return Lanot_Core_Block_Adminhtml_Grid_Abstract
     */
    protected function _prepareCollection()
    {
        $collection = $this->_getCollection();

        //filter colelction if it show in tab
        if ($this->_isTabGrid && $this->isReadonly()) {
            $valueIds = $this->_getSelectedLinks();
            if (empty($valueIds)) {
                $valueIds = array(0);
            }
            $collection->addFieldToFilter('main_table.'.$this->_entityIdField, array('in' => $valueIds));
        }

        Mage::dispatchEvent($this->_eventPrefix . 'lanot_grid_prepare_collection', array(
            'grid' => $this,
            'collection' => $collection)
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return (!$this->_isTabGrid) ? $this->getUrl('*/*/edit', array('id' => $row->getId())) : '#';
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return (!$this->_isTabGrid) ?
            $this->getUrl('*/*/grid', array('_current' => true, '_secure'=>true)) :
            $this->getUrl('*/*/ajaxgridonly', array('_current' => true, '_secure'=>true));
    }

    /**
     * Prepare Grid columns
     *
     * @return Lanot_Core_Block_Adminhtml_Grid_Abstract
     */
    protected function _prepareColumns()
    {
        Mage::dispatchEvent($this->_eventPrefix . 'lanot_grid_prepare_columns_before', array('grid' => $this));

        if ($this->_isTabGrid) {
            $this->addColumn('in_selected', array(
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_selected',
                'values' => $this->_getSelectedLinks(),
                'align' => 'center',
                'index' => $this->_entityIdField,
            ));
        }

        $this->addColumn($this->_entityIdField, array(
            'header' => $this->_getHelper()->__('ID'),
            'index'  => $this->_entityIdField,
            'type'   => 'number',
            'width'  => '50px',
        ));

        $this->addColumn('title', array(
            'header' => $this->_getHelper()->__('Title'),
            'index'  => 'title',
        ));

        $this->addColumn('is_active', array(
            'header'  => $this->_getHelper()->__('Active'),
            'index'   => 'is_active',
            'type'    => 'options',
            'options' => $this->_getItemModel()->getAvailableStatuses(),
            'width'  => '100px',
        ));

        $this->addColumn('updated_at', array(
            'header'  => $this->_getHelper()->__('Updated'),
            'index'   => 'updated_at',
            'width'   => '150px',
        ));

        if (!$this->_isTabGrid) {
            $this->addColumn('action',
                array(
                    'header'    => $this->_getHelper()->__('Action'),
                    'width'     => '70px',
                    'align'     => 'center',
                    'type'      => 'action',
                    'getter'    => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => $this->_getHelper()->__('Edit'),
                            'url'     => array('base' => '*/*/edit'),
                            'field'   => 'id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'banner',
            ));
        }

        Mage::dispatchEvent($this->_eventPrefix . 'lanot_grid_prepare_columns', array('grid' => $this));

        return parent::_prepareColumns();
    }


    /**
     * @return Lanot_Core_Block_Adminhtml_Grid_Abstract
     */
    protected function _prepareMassaction()
    {
        if (!$this->isMassActionAllowed()) {
            return $this;
        }

        $this->setMassactionIdField($this->_entityIdField);
        $this->getMassactionBlock()->setFormFieldName($this->_formFieldName);

        $this->getMassactionBlock()->addItem('active_enable', array(
            'label' => $this->_getHelper()->__('Enable'),
            'url'   => $this->getUrl('*/*/mass', array('type' => 'enable'))
        ));

        $this->getMassactionBlock()->addItem('active_disable', array(
            'label' => $this->_getHelper()->__('Disable'),
            'url'   => $this->getUrl('*/*/mass', array('type' => 'disable'))
        ));

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => $this->_getHelper()->__('Delete'),
            'confirm'  => $this->_getHelper()->__('Are you sure?'),
            'url'      => $this->getUrl('*/*/mass', array('type' => 'delete')),
        ));

        Mage::dispatchEvent($this->_eventPrefix . 'lanot_grid_prepare_massaction', array('grid' => $this));

        return $this;
    }

    /**
     * Retrieve selected values
     *
     * @return array
     */
    public function getSelectedLinks()
    {
        if (null === $this->_selectedLinks) {
            $this->_selectedLinks = $this->_getItem()->getSelectedLinks();
        }
        return $this->_selectedLinks;
    }

    /**
     * Retrieve selected values
     *
     * @return array
     */
    protected function _getSelectedLinks()
    {
        return $this->getSelectedLinks();
    }

    /**
     * Add column to grid
     *
     * @param   string $columnId
     * @param   array || Varien_Object $column
     * @return  Mage_Adminhtml_Block_Widget_Grid
     */
    public function addColumn($columnId, $column)
    {
        return parent::addColumn($this->_columnPrefix . $columnId, $column);
    }

    /**
     * Retrieve grid column by column id
     *
     * @param   string $columnId
     * @return  Varien_Object || false
     */
    public function getColumn($columnId)
    {
        if ($this->_columnPrefix && strpos($columnId, $this->_columnPrefix) !== 0) {
            $columnId = $this->_columnPrefix . $columnId;
        }
        return parent::getColumn($columnId);
    }

    /**
     * Remove existing column
     *
     * @param string $columnId
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    public function removeColumn($columnId)
    {
        if ($this->_columnPrefix && strpos($columnId, $this->_columnPrefix) !== 0) {
            $columnId = $this->_columnPrefix . $columnId;
        }
        return $this->_removeColumn($columnId);
    }

    /**
     * @return Lanot_Core_Block_Adminhtml_Grid_Abstract
     */
    protected function _getItem()
    {
        if ($this->_item !== null) {
            return $this->_item;
        }

        $itemId = $this->getRequest()->getParam($this->_itemParam);
        $this->_item = $this->_getItemModel();
        if ($itemId) {
            $this->_item->load($itemId);
        }
        return $this->_item;
    }

    /**
     * @param $column
     * @return Lanot_Core_Block_Adminhtml_Grid_Abstract
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter by selected values
        if ($this->_isTabGrid && $column->getId() == $this->_checkboxFieldName) {
            $valueIds = $this->_getSelectedLinks();
            if (empty($valueIds)) {
                $valueIds = 0;
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('main_table.'.$this->_entityIdField, array('in' => $valueIds));
            } else {
                if($valueIds) {
                    $this->getCollection()->addFieldToFilter('main_table.'.$this->_entityIdField, array('nin' => $valueIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @fix: Old versions
     * Remove existing column
     *
     * @param string $columnId
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _removeColumn($columnId)
    {
        if (isset($this->_columns[$columnId])) {
            unset($this->_columns[$columnId]);
            if ($this->_lastColumnId == $columnId) {
                $this->_lastColumnId = key($this->_columns);
            }
        }
        return $this;
    }

    //--------------------------- methods must be overwritten -----------------------------//
    /**
     * Checks when this block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return false;
    }

    /**
     * Checks when this block is not available
     *
     * @return boolean
     */
    public function isMassActionAllowed()
    {
        return !$this->_isTabGrid;
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _getItemModel()
    {
        return null;
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
    //--------------------------- methods must be overwritten-----------------------------//

    /**
     * @return string
     */
    public function getColumnPrefix()
    {
        return $this->_columnPrefix;
    }

    /**
     * @return string
     */
    public function getEntityIdField()
    {
        return $this->_entityIdField;
    }
}
