<?php

class {{block_admin_edit_form_class_name}} extends Mage_Adminhtml_Block_Widget
{

    protected function _prepareLayout()
    {
        $helper = Mage::helper('{{module_alias}}');
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => $helper->__('Back'),
                    'onclick'   => 'setLocation(\''.$this->getUrl('*/*/index', array('store'=>$this->getRequest()->getParam('store', 0))).'\')',
                    'class' => 'back'
                ))
        );

        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => $helper->__('Delete'),
                    'onclick'   => 'if (confirm(\'' . $helper->__('Are you sure?') . '\')) {setLocation(\'' .
                        $this->getUrl('*/*/delete', array('id'=>$this->getRequest()->getParam('id', 0))) . '\'); }',
                    'class' => 'delete'
                ))
        );

        $this->setChild('reset_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => $helper->__('Reset'),
                    'onclick'   => 'setLocation(\''.$this->getUrl('*/*/*', array('_current'=>true)).'\')'
                ))
        );

        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => $helper->__('Save'),
                    'onclick'   => '$(\'{{model}}_form\').submit()',
                    'class'     => 'save'
                ))
        );
    }

    /**
     * get helper
     *
     * @return unknown
     */
    public function getProducts()
    {
        return $this->_getHelper()->getProducts();
    }

    /**
     * Retrieve block attributes update helper
     *
     * @return {{helper_data_class_name}}
     */
    protected function _getHelper()
    {
        return $this->helper('{{module_alias}}');
    }

    /**
     * Retrieve back button html code
     *
     * @return string
     */
    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    /**
     * Retrieve cancel button html code
     *
     * @return string
     */
    public function getCancelButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    /**
     * Retrieve save button html code
     *
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * Get save url
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', ['store' => Mage::app()->getRequest()->getParam('store', '0')]);
    }

    /**
     * Get validation url
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', ['_current' => true]);
    }

    /**
     * to html
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = <<<HTML
        <div class="content-header">
            <table cellspacing="0">
                <tr>
                    <td><h3>{$this->_getHelper()->__('{{model|ucfirst|uc_words: }}')}</h3></td>
                    <td class="form-buttons">
                    {$this->getBackButtonHtml()}
                    {$this->getCancelButtonHtml()}
                    {$this->getSaveButtonHtml()}
                    </td>
                </tr>
            </table>
        </div>
        <form action="{$this->getSaveUrl()}" method="post" id="{{model}}_form" enctype="multipart/form-data">
            {$this->getBlockHtml('formkey')}
        </form>
        <script type="text/javascript">
        var {{model}}Form = new varienForm('{{model}}_form');
        {{model}}Form._processValidationResult = function(transport) {
            var response = transport.responseText.evalJSON();

            if (response.error){
                if (response.attribute && $(response.attribute)) {
                    $(response.attribute).setHasError(true, attributesForm);
                    Validation.ajaxError($(response.attribute), response.message);
                    if (!Prototype.Browser.IE){
                        $(response.attribute).focus();
                    }
                } else if ($('messages')) {
                    $('messages').innerHTML = '<ul class="messages"><li class="error-msg"><ul><li>' + response.message + '</li></ul></li></ul>';
                }
            } else {
                attributesForm._submit();
            }
        };
        </script>
HTML;
        return $html;
    }
}
