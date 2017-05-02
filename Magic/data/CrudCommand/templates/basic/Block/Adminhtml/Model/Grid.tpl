<?php
class {{block_admin_grid_class_name}}
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Init the grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('{{module_alias}}_{{model}}_grid');
        $this->setDefaultSort('{{primarykey}}');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare grid collection
     *
     * @return {{block_admin_grid_class_name}}
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('{{module_alias}}/{{model}}')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Retrieve grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * Retrieve URL for Row click
     *
     * @param Varien_Object $row Row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'id'    => $row->getId()
        ));
    }

    /**
     * Define grid columns
     *
     * @return {{block_admin_grid_class_name}}
     */
    protected function _prepareColumns()
    {
        $helper = $this->helper('{{module_alias}}');
        $this->addColumn('{{primarykey}}', array(
            'header' => $helper->__('ID'),
            'index'  => '{{primarykey}}',
            'type'   => 'text',
            'width'  => 20,
        ));

        $this->addColumn('email', array(
            'header' => $helper->__('Email'),
            'index'  => 'email',
            'type'   => 'text',
        ));

        $this->addColumn('created_at', array(
            'header' => $helper->__('Date Created'),
            'index'  => 'created_at',
            'type'   => 'date',
        ));

        $this->addColumn('action',
            array(
                'header'  => $helper->__('Action'),
                'width'   => '100px',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => $helper->__('Edit'),
                        'url'     => array('base' => '*/*/edit'),
                        'field'   => 'id',
                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
        ));

        return $this;

    }

    /**
     * run actions after collection loaded
     *
     * @return {{block_admin_grid_class_name}}
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        return parent::_afterLoadCollection();
    }
}
