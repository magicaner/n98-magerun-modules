<?php
class {{block_admin_edit_form_class_name}}_Tabs_General
    extends Mage_Adminhtml_Block_Catalog_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * get helper
     *
     * @return {{helper_data_class_name}}
     */
    protected function _getHelper()
    {
        return Mage::helper('{{module_alias}}');
    }

    /**
     * get model
     *
     * @return Varien_Object
     */
    protected function _getModel()
    {
        if (!$this->hasData('model')) {
            if ((!$model = Mage::registry('{{model}}'))) {
                $model = Mage::getModel('{{module_alias}}/{{model}}');
            }
            $this->setData('model', $model);
        }
        return $this->getData('model');
    }
    /**
     * prepare form
     *
     * @return void
     */
    protected function _prepareForm()
    {
        $helper = Mage::helper('{{module_alias}}');
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('fields', [
            'legend' => $helper->__('General')
        ]);

        $fieldset->addField('example_field', 'text', array(
            'name'      => 'example_field',
            'title'     => $helper->__('Example Field'),
            'label'     => $helper->__('Example Field'),
            'required'  => true,
        ));

        $form->setDataObject($this->getModel());
        $form->setFieldNameSuffix('{{model}}');
        $this->setForm($form);
    }

    /**
     * init form values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $this->getForm()->addValues($this->_getModel()->getData());
        return $this;
    }

    /**
     * get label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('{{module_alias}}')->__('General');
    }

    /**
     * get tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('{{module_alias}}')->__('General');
    }

    /**
     * can show tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
