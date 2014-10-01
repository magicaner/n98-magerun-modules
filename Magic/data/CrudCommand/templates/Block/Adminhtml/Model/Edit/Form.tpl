<?php
class {{block_admin_edit_form_class_name}}
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * init form structure
     *
     * @return {{block_admin_edit_form_class_name}}
     */
    protected function _prepareForm()
    {
        /* @var $helper Ecommeleon_Sponsor_Helper_Data */

        $form = new Varien_Data_Form();

        $helper = Mage::helper('{{module_alias}}');

        $fieldset = $form->addFieldset('new', array('legend' => $helper->__('{{model}} details')));

        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'title'     => $helper->__('Customer name'),
            'label'     => $helper->__('Customer name'),
            'note'      => $helper->__('Will be used in email notification'),
            'required'  => false,
        ));


        if ($model = $this->getModel()) {

            $fieldset->addField('entity_id', 'hidden', [
             'name' => 'entity_id',
             'value' => $model->getId()
            ]);

            $form->addValues($model->getData());
            $form->setAction($this->getUrl('*/*/save', ['id' => $model->getId()]));

        } else {

            $form->setAction($this->getUrl('*/*/post'));
            $form->addValues([

            ]);
        }

        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setEnctype('multipart/form-data');

        $this->setForm($form);

        return $this;
    }

    /**
     * get current model
     *
     * @return {{model_class_name}}
     */
    protected function getModel()
    {
        return $this->getParentBlock()->getModel();
    }
}
