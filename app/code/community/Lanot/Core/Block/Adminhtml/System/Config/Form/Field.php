<?php
/**
 * @category    Lanot
 * @package     Lanot_Core
 * @copyright   Copyright (c) 2012 Lanot
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Lanot_Core_Block_Adminhtml_System_Config_Form_Field
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $cols = $element->getData('additional_columns');

        $id = $element->getHtmlId();

        $useContainerId = $element->getData('use_container_id');
        $html = '<tr id="row_' . $id . '">'
            . '<td class="label"><label for="'.$id.'">'.$element->getLabel().'</label></td>';

        $html.= '<td class="value">';
        $html.= $this->_getElementHtml($element);
        $html.= '</td>';

        if (!empty($cols) && is_array($cols)) {
            foreach($cols as $key => $val) {
                $html.= '<td class="value class-' . $key . '">';
                $html.= $val;
                $html.= '</td>';
            }
        }
        $html.= '</tr>';
        return $html;
    }
}
