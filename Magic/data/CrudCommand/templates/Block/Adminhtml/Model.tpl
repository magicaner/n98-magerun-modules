<?php

class {{block_admin_grid_container_class_name}}
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->_blockGroup = '{{module}}';
        $this->_controller = '{{admin_controller}}';
        $this->_headerText = Mage::helper('{{module}}')->__('Manage {{model}}');
        parent::__construct();
    }
}
