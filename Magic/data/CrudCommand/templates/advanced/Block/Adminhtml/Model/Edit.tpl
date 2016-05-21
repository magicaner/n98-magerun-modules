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

        if ($this->getRequest()->getParam($this->_objectId)) {
            $model = Mage::getModel('{{module_alias}}/{{model}}');
            $model->load($this->getRequest()->getParam($this->_objectId));
            $this->setModel($model);
        }


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
