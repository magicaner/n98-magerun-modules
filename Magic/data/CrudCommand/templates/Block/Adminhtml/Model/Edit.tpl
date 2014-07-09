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
        $this->_blockGroup = '{{module}}';

        parent::__construct();
        $this->_updateButton('save', 'label', $this->helper('{{module}}')->__('Save'));
        $this->_updateButton('delete', 'label', $this->helper('{{module}}')->__('Delete'));

        /* @var $model Ecommeleon_Sponsor_Model_RecurringCredit */

        if ($this->getRequest()->getParam($this->_objectId)) {
            $model = Mage::getModel('{{module}}/{{model}}');
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
            return $this->helper('{{module}}')
                ->__("Edit {{model}} '%s'", $this->htmlEscape($this->getModel()->getName()));
        } else {
            return $this->helper('{{module}}')->__('New {{model}}');
        }
    }
}
