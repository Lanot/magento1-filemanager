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

class Lanot_Core_Block_Adminhtml_System_Extension
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_fieldRenderer;
    protected $_feedData = array();
    protected $_activeModules = array();

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_feedData = $this->_getUpgrades();//@todo:

        $html = $this->_getHeaderHtml($element);

        //#1. Populate by installed modules
        foreach ($this->_getAllModules() as $moduleName) {
            if ((strpos($moduleName, 'Lanot_') === 0)
                && ($moduleName != 'Lanot_Core')
                && $this->_getEnabled($moduleName)
            ) {
                $this->_activeModules[$moduleName] = true;
                $html.= $this->_getFieldHtml($element, $moduleName);
            }
        }

        //#2. Populate by new modules
        $isFirst = true;
        if (!empty($this->_feedData)) {
            foreach($this->_feedData as $moduleName => $moduleData) {
                if (!isset($this->_activeModules[$moduleName])) {
                    if ($isFirst) {
                        $title = $this->_getHelper()->__('New Modules');
                        $html.= '<tr><td colspan="4"><br /><b>' . $title . '</b></td></tr>';
                    }

                    $html.= $this->_getFieldHtml($element, $moduleName);
                    $isFirst = false;
                }
            }
        }

        $html.= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * @return array
     */
    protected function _getAllModules()
    {
        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        sort($modules);
        return $modules;
    }

    /**
     * @return object
     */
    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('lanot_core/adminhtml_system_config_form_field');
        }
        return $this->_fieldRenderer;
    }

    /**
     * @param $fieldset
     * @param $moduleName
     * @return string
     */
    protected function _getFieldHtml($fieldset, $moduleName)
    {
        $field = $fieldset->addField($moduleName, 'label',
            array(
                'name'          => 'LanotInstalledExtensions_'.$moduleName,
                'label'         => $moduleName,
                'value'         => $this->_getVersion($moduleName),
                'additional_columns' => $this->_getAdditionalColData($moduleName),
            ))->setRenderer($this->_getFieldRenderer());

        return $field->toHtml();
    }

    /**
     * @return string
     */
    protected function _getButtonHtml()
    {
        $url = $this->getUrl('*/*/lanotupgrades', array('_current' => true, '_secure' => true));
        $field = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
            'label' => Mage::helper('lanot_core')->__('Check Upgrades and Offers'),
            'onclick' => "window.location.href='" . $url . "'",
            'class' => 'task'
        ));

        return $field->toHtml();
    }

    /**
     * @param $moduleName
     * @return string
     */
    public function _getVersion($moduleName) {
        $node = Mage::getConfig()->getNode()->modules->{$moduleName};
        if ($node && $node->version) {
            return (string) $node->version;
        }
        return 'N/A';
    }

    /**
     * @param $moduleName
     * @return string
     */
    public function _getEnabled($moduleName) {
        $node = Mage::getConfig()->getNode()->modules->{$moduleName};
        if ($node && $node->active && ($node->active == 'true')) {
            return (string) $node->version;
        }
        return false;
    }

    /**
     * Return header html for fieldset
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderHtml($element)
    {
        $html = parent::_getHeaderHtml($element);
        if (empty($this->_feedData)) {
            $html .= sprintf(
                '<tr><th>%s</th><th>%s</th></tr>',
                $this->_getHelper()->__('Module'),
                $this->_getHelper()->__('Installed Version')
            );
        } else {
            $html .= sprintf(
                '<tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th></tr>',
                $this->_getHelper()->__('Module'),
                $this->_getHelper()->__('Installed Version'),
                $this->_getHelper()->__('Last Version'),
                $this->_getHelper()->__('Upgrade & Offers')
            );
        }
        return $html;
    }

    /**
     * Return footer html for fieldset
     * Add extra tooltip comments to elements
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getFooterHtml($element)
    {
        $tooltipsExist = false;
        $html = '</tbody></table>';

        $html.= $this->_getButtonHtml();

        foreach ($element->getSortedElements() as $field) {
            if ($field->getTooltip()) {
                $tooltipsExist = true;
                $html .= sprintf('<div id="row_%s_comment" class="system-tooltip-box" style="display:none;">%s</div>',
                    $field->getId(), $field->getTooltip()
                );
            }
        }

        $html .= '</fieldset>' . $this->_getExtraJs($element, $tooltipsExist);
        return $html;
    }

    /**
     * @return array
     */
    protected function _getUpgrades()
    {
        return Mage::registry('lanot_upgrades_and_offers');
    }

    /**
     * @return Lanot_Core_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('lanot_core');
    }

    /**
     * @param $moduleName
     * @return array
     */
    protected function _getAdditionalColData($moduleName)
    {
        $data = array();
        if (!empty($this->_feedData) && isset($this->_feedData[$moduleName])) {
            $item = $this->_feedData[$moduleName];
            $notes = $item['notes'];
            if (empty($notes) && isset($item['link'])) {
                $notes .= sprintf("<a href='%s' target='_blank'>%s</a> <br />",
                    $item['link'],
                    $item['title']
                );
            }
            $data['version'] = !empty($item['version']) ? $item['version'] : 'N/A';
            $data['notes'] = !empty($notes) ? $notes : 'N/A';
        } elseif (!empty($this->_feedData)) {
            $data['version'] = 'N/A';
            $data['notes'] = 'N/A';
        }
        return $data;
    }
}
