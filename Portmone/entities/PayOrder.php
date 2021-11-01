<?php

namespace payment\PaymentSystem\Portmone\entities;

use payment\PaymentSystem\Portmone\exceptions\PortmoneException;

class PayOrder extends BaseEntity
{
    /** @var Bill[] */
    protected $bills = [];

    public function __construct(\stdClass $data)
    {
        parent::__construct($data);
        // check pay order format (just few fields)
        if (false === $this->getPayOrderId() || false === $this->getPayOrderAmount()) {
            throw new PortmoneException('Invalid pay order format', PortmoneException::PARSE_ERROR);
        }
        // parse bills section
        if (isset($this->data->BILLS->BILL)) {
            if (is_array($this->data->BILLS->BILL)) {
                foreach ($this->data->BILLS->BILL as $bill) {
                    if (is_object($bill)) {
                        $this->bills[] = new Bill($bill);
                    } else {
                        throw new PortmoneException(
                            'Invalid bills section in pay order',
                            PortmoneException::RESULT_ERROR
                        );
                    }
                }
            } elseif (is_object($this->data->BILLS->BILL)){
               $this->bills[] = new Bill($this->data->BILLS->BILL);
            } else {
                throw new PortmoneException(
                    'Invalid bills section in pay order',
                    PortmoneException::RESULT_ERROR
                );
            }
        } else {
            throw new PortmoneException(
                'Bills section missed in pay order',
                PortmoneException::RESULT_ERROR
            );
        }
    }

    /**
     * Get all bills
     * @return Bill[]
     */
    public function getBills()
    {
        return $this->bills;
    }

    /**
     * Get specified bill by bill ID or by order number
     * @param $bill_id
     * @param null $shop_order_number
     * @return Bill
     * @throws PortmoneException
     */
    public function getBill($bill_id, $shop_order_number = null)
    {
        foreach ($this->bills as $bill) {
            // get by bill id
            if (!empty($bill_id) && $bill->getBillId() == $bill_id) {
                return $bill;
            }
            // or get by bill order number
            if (!empty($shop_order_number) && $bill->getOrderNumber() == $shop_order_number) {
                return $bill;
            }
        }
        throw new PortmoneException('Bill not found', PortmoneException::NOT_FOUND);
    }

    public function getPayOrderId()
    {
        return $this->getProperty('PAY_ORDER_ID');
    }

    public function getPayOrderDate()
    {
        return $this->getProperty('PAY_ORDER_DATE');
    }

    public function getPayOrderNumber()
    {
        return $this->getProperty('PAY_ORDER_NUMBER');
    }

    public function getPayOrderAmount()
    {
        return $this->getProperty('PAY_ORDER_AMOUNT');
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
}