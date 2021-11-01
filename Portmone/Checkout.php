<?php

namespace payment\PaymentSystem\Portmone;

use payment\PaymentSystem\Portmone\libs\PortmoneHelper;

class Checkout
{
    const DEFAULT_ENCODING = 'UTF-8';

    private $parent;
    private $checkout_url = 'https://www.portmone.com.ua/gateway/';

    protected $payee_id;

    protected $checkout_from_id;
    protected $shop_order_number;
    protected $bill_amount = 0.0;
    protected $description = '';

    protected $success_url = '';
    protected $failure_url = '';

    protected $encoding = self::DEFAULT_ENCODING;

    protected $lang = Portmone::DEFAULT_LANG;

    protected $preauth_flag;

    public function __construct(Portmone $parent)
    {
        $this->parent = $parent;
        $this->payee_id = $parent->getPayeeId();
        $this->checkout_from_id = 'portmone_checkout_form_' . mt_rand(1000, 9999);
    }

    /**
     * Set order number for checkout form
     * @param $value
     * @return $this
     */
    public function setShopOrderNumber($value)
    {
        $this->shop_order_number = mb_substr($value, 0, 1024, $this->encoding);
        return $this;
    }

    /**
     * Set total amount to be payed
     * @param $value
     * @return $this
     */
    public function setBillAmount($value)
    {
        $this->bill_amount = PortmoneHelper::toFloat($value);
        return $this;
    }

    /**
     * Add some order details for customer. This description will be showed on payment page
     * @param $value
     * @return $this
     */
    public function setDescription($value)
    {
        $this->description = mb_substr($value, 0, 2048, $this->encoding);
        return $this;
    }

    /**
     * Define full URL where user will be redirected after successful payment
     * @param $value
     * @return $this
     */
    public function setSuccessUrl($value)
    {
        $value = filter_var($value, FILTER_VALIDATE_URL);
        if ('' !== $value) {
            $this->success_url = $value;
        }
        return $this;
    }

    /**
     * Define full URL where user will be redirected when payment will failed for some reason
     * @param $value
     * @return $this
     */
    public function setFailureUrl($value)
    {
        $value = filter_var($value, FILTER_VALIDATE_URL);
        if ('' !== $value) {
            $this->failure_url = $value;
        }
        return $this;
    }

    /**
     * Define one of available languages for payment page (use Portmone::LANG_* constants)
     * @param $value
     * @return $this
     */
    public function setLang($value)
    {
        $value = strtolower($value);
        if (in_array($value, $this->parent->languages)) {
            $this->lang = $value;
        } else {
            $this->lang = Portmone::DEFAULT_LANG;
        }
        return $this;
    }

    /**
     * Specify encoding of your checkout form data (e.g. description)
     * This is not necessary if encoding is UTF-8
     * @param $value
     * @return $this
     */
    public function setEncoding($value)
    {
        $this->encoding = $value;
        return $this;
    }

    /**
     * Call this method to turn checkout form into the "pre auth" mode.
     * @param string $value
     * @return $this
     */
    public function preAuth($value = 'Y')
    {
        $this->preauth_flag = $value === 'Y' ? 'Y' : 'N';
        return $this;
    }

    /**
     * Final method to get compiled checkout form HTML code
     * @param bool $autoSubmit add JS code to submit form automatically, after page will be loaded
     * @param array $formOptions name=value pairs for <form> tag attributes
     * @param array $inputOptions name=value pairs for <input> tags attributes
     * @param array $submitOptions  name=value pairs for submit tag attributes
     * @return string HTML code of checkout form
     */
    public function getForm($autoSubmit = true, $formOptions = [], $inputOptions = [], $submitOptions = [])
    {
        // build inputs html
        $inputsHtml = $this->renderInput('payee_id', $inputOptions);
        $inputsHtml .= $this->renderInput('shop_order_number', $inputOptions);
        $inputsHtml .= $this->renderInput('bill_amount', $inputOptions);
        $inputsHtml .= $this->renderInput('description', $inputOptions);
        $inputsHtml .= $this->renderInput('success_url', $inputOptions);
        $inputsHtml .= $this->renderInput('failure_url', $inputOptions);
        $inputsHtml .= $this->renderInput('lang', $inputOptions);
        if (self::DEFAULT_ENCODING !== $this->encoding) {
            $inputsHtml .= $this->renderInput('encoding', $inputOptions);
        }
        if ('Y' == $this->preauth_flag) {
            $inputsHtml .= $this->renderInput('preauth_flag', $inputOptions);
        }
        if (!$autoSubmit) {
            $inputsHtml .= $this->renderSubmit($submitOptions);
        }
        // wrap inputs to form tag
        $formHtml = $this->renderCheckoutForm($inputsHtml, $formOptions);
        if ($autoSubmit) {
            $formHtml .= $this->renderAutoSubmitJS();
        }
        return $formHtml;
    }

    /**
     * @param $innerHtml
     * @param array $formOptions
     * @return string
     */
    protected function renderCheckoutForm($innerHtml, $formOptions = [])
    {
        $formOptions['action'] = $this->checkout_url;
        $formOptions['method'] = 'POST';
        if (!empty($formOptions['id'])) {
            $this->checkout_from_id = $formOptions['id'];
        }
        $formOptions['id'] = $this->checkout_from_id;
        // build html
        $html = '<form';
        foreach ($formOptions as $optionName => $optionValue) {
            $html .= ' ' . $optionName . '="' . PortmoneHelper::encode($optionValue, $this->encoding) . '"';
        }
        $html .= ">\n";
        $html .= $innerHtml;
        $html .= "</form>\n";
        return $html;
    }

    /**
     * @param $name
     * @param array $options
     * @return bool|string
     */
    protected function renderInput($name, $options = [])
    {
        if (!isset($this->$name)) {
            return false;
        }
        $options['name'] = $name;
        $options['value'] = $this->$name;
        $options['type'] = 'hidden';
        $html = '   <input';
        foreach ($options as $optionName => $optionValue) {
            $html .= ' ' . $optionName . '="' . PortmoneHelper::encode($optionValue, $this->encoding) . '"';
        }
        $html .= ">\n";
        return $html;
    }

    /**
     * @param array $options
     * @return string
     */
    protected function renderSubmit($options = [])
    {
        $options['type'] = 'submit';
        $html = '   <input';
        foreach ($options as $optionName => $optionValue) {
            $html .= ' ' . $optionName . '="' . PortmoneHelper::encode($optionValue, $this->encoding) . '"';
        }
        $html .= ">\n";
        return $html;
    }

    /**
     * @return string
     */
    protected function renderAutoSubmitJS()
    {
        return "
<script>
    var portmone_checkout_from_submit = function (event) {
        var checkout_form = document.getElementById('{$this->checkout_from_id}');
        if (checkout_form instanceof HTMLFormElement) {
            checkout_form.submit();
        }
    };
    if (window.addEventListener) {
        window.addEventListener('load', portmone_checkout_from_submit, false);
    } else if (window.attachEvent) {
        window.attachEvent('onload', portmone_checkout_from_submit);
    }
</script>
        ";
    }
}
?>