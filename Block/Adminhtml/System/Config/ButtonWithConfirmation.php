<?php

declare(strict_types=1);

namespace Freento\SqlLog\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ButtonWithConfirmation extends Field
{
    public const DEFAULT_CONFIRMATION_MESSAGE = 'Are you sure?';

    /**
     * @var string
     */
    protected $_template = 'system/config/button-with-confirmation.phtml';

    /**
     * {@inheritdoc}
     */
    public function render(AbstractElement $element): string
    {
        $element->unsScope();
        $fieldConfig = $element->getFieldConfig();
        if (!$fieldConfig || !isset($fieldConfig['button_url'])) {
            return '';
        }

        $this->setActionUrl($fieldConfig['button_url']);
        $this->setButtonLabel(__($fieldConfig['label'] ?? 'Send'));
        $originalData = $element->getOriginalData();
        $this->setConfirmationMessage(__($originalData['confirmation_message'] ?? self::DEFAULT_CONFIRMATION_MESSAGE));
        return parent::render($element);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    /**
     * Get action url
     *
     * @return string
     */
    public function getAjaxUrl(): string
    {
        $params = ['form_key' => $this->getFormKey()];
        if ($this->getRequest()->getParam('section')) {
            $params['section'] = $this->getRequest()->getParam('section');
        }

        if ($this->getRequest()->getParam('group')) {
            $params['group'] = $this->getRequest()->getParam('group');
        }

        if ($this->getRequest()->getParam('field')) {
            $params['field'] = $this->getRequest()->getParam('field');
        }

        return $this->_urlBuilder->getUrl(
            $this->getActionUrl(),
            $params
        );
    }
}
