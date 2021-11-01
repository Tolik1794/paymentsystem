<?php

namespace payment\PaymentSystem\Portmone\entities;

class PortmoneOrder extends BaseEntity
{

    public function getOrderNumber()
    {
        if ($this->getProperty('shop_order_number')) {
            return $this->getProperty('shop_order_number');
        } elseif ($this->getProperty('shopordernumber')) {
            return $this->getProperty('shopordernumber'); // alternative syntax
        } else {
            return false;
        }
    }

    public function getBillId()
    {
        return $this->getProperty('shop_bill_id');
    }

    /**
     * Order status
     * @return string|bool
     */
    public function getStatus()
    {
        return $this->getProperty('status');
    }

    public function getBillDate()
    {
        return $this->getProperty('bill_date');
    }

    public function getPayDate()
    {
        return $this->getProperty('pay_date');
    }

    public function getBillAmount()
    {
        return $this->getProperty('bill_amount');
    }

    public function getAuthCode()
    {
        return $this->getProperty('auth_code');
    }

    public function getErrorCode()
    {
        return $this->getProperty('error_code');
    }

    public function getErrorMessage()
    {
        return $this->getProperty('error_message');
    }
}