<?php

namespace payment\PaymentSystem\Portmone\entities;

use payment\PaymentSystem\Portmone\exceptions\PortmoneException;

class Bill extends BaseEntity
{

    public function __construct(\stdClass $data)
    {
        parent::__construct($data);
        // check bill format (just few fields)
        if (false === $this->getBillId() || false === $this->getOrderNumber()) {
            throw new PortmoneException('Invalid bill format', PortmoneException::PARSE_ERROR);
        }
    }

    /**
     * Alias to getBillNumber()
     * @return bool|string
     */
    public function getOrderNumber()
    {
        return $this->getBillNumber();
    }

    public function getOrderDescription()
    {
        return $this->getProperty('PAYER->CONTRACT_NUMBER');
    }

    public function getBillId()
    {
        return $this->getProperty('BILL_ID');
    }

    public function getBillNumber()
    {
        return $this->getProperty('BILL_NUMBER');
    }

    public function getBillDate()
    {
        return $this->getProperty('BILL_DATE');
    }

    public function getBillPeriod()
    {
        return $this->getProperty('BILL_PERIOD');
    }

    public function getPayDate()
    {
        return $this->getProperty('PAY_DATE');
    }

    public function getPayedAmount()
    {
        return $this->getProperty('PAYED_AMOUNT');
    }

    public function getPayedCommission()
    {
        return $this->getProperty('PAYED_COMMISSION');
    }

    public function getPayedDebt()
    {
        return $this->getProperty('PAYED_DEBT');
    }

    public function getAuthCode()
    {
        return $this->getProperty('AUTH_CODE');
    }

    public function getPayeeName()
    {
        return $this->getProperty('PAYEE->NAME');
    }

    public function getPayeeCode()
    {
        return $this->getProperty('PAYEE->CODE');
    }

    public function getBankName()
    {
        return $this->getProperty('BANK->NAME');
    }

    public function getBankCode()
    {
        return $this->getProperty('BANK->CODE');
    }

    public function getBankAccount()
    {
        return $this->getProperty('BANK->ACCOUNT');
    }

    public function getAttribute1()
    {
        return $this->getProperty('PAYER->ATTRIBUTE1');
    }

    public function getAttribute2()
    {
        return $this->getProperty('PAYER->ATTRIBUTE2');
    }

    public function getAttribute3()
    {
        return $this->getProperty('PAYER->ATTRIBUTE3');
    }

    public function getAttribute4()
    {
        return $this->getProperty('PAYER->ATTRIBUTE4');
    }

}