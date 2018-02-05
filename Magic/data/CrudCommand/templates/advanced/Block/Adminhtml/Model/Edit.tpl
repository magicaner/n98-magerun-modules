<?php

class  {{block_admin_edit_class_name}}
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Init the form container
     *
     * @return void
     */
    public function __construct()
    {
        $this->_objectId   = 'id';
        $this->_mode       = 'edit';
        $this->_controller = '{{admin_controller}}';
        $this->_blockGroup = '{{module_alias}}';

        parent::__construct();
        $this->_updateButton('save', 'label', $this->helper('{{module_alias}}')->__('Save'));
        $this->_updateButton('delete', 'label', $this->helper('{{module_alias}}')->__('Delete'));

        /* @var $model {{model_class_name}} */

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);


        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getModel() && $this->getModel()->getId()) {
            return $this->helper('{{module_alias}}')
                ->__("Edit {{model}} '%s'", $this->htmlEscape($this->getModel()->getName()));
        } else {
            return $this->helper('{{module_alias}}')->__('New {{model}}');
        }
    }
}
