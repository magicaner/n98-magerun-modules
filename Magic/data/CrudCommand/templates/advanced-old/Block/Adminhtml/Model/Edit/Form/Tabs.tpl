<?php

class {{block_admin_edit_form_class_name}}_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('{{model}}_form_tabs');
        $this->setDestElementId('{{model}}_form');
        //$this->setTitle(Mage::helper('{{module_alias}}')->__('Tabs'));
    }
}
