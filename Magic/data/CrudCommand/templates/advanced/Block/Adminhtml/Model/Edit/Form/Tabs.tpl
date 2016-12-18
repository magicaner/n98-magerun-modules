<?php

class {{block_admin_edit_form_class_name}}_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('{{model}}_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('{{module_alias}}')->__('{{model:ucfirst}}'));
    }

    /**
     * before html
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $this->addTab('general', [
            'label' => Mage::helper('{{module_alias}}')->__('General'),
            'title' => Mage::helper('{{module_alias}}')->__('General'),
            'content' => $this->getLayout()->createBlock('{{module_alias}}/adminhtml_{{model}}_edit_form_tabs_general',
                            'tab_{{model}}_general')->toHtml(),
        ]);

        return parent::_beforeToHtml();
    }
}
