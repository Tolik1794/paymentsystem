<?php

namespace payment\PaymentSystem\Portmone\entities;

use payment\PaymentSystem\Portmone\exceptions\PortmoneException;

class Result extends BaseEntity
{
    protected $request;
    /** @var  PortmoneOrder[] */
    protected $orders = [];

    /**
     * Parse request result structure
     * Result constructor.
     * @param \stdClass $data
     * @throws PortmoneException
     */
    public function __construct(\stdClass $data)
    {
        parent::__construct($data);
        // request section
        if (isset($data->request) && is_object($data->request)) {
            $this->request = $data->request;
        } else {
            throw new PortmoneException('Request section missed in portmone result', PortmoneException::RESULT_ERROR);
        }
        // orders/order section
        if (isset($data->orders->order) && (is_array($data->orders->order) || is_object($data->orders->order))) {
            if (is_array($data->orders->order)) {
                // few orders
                foreach ($data->orders->order as $order) {
                    if (is_object($order)) {
                        $this->orders[] = new PortmoneOrder($order);
                    } else {
                        throw new PortmoneException(
                            'Invalid order section in portmone result',
                            PortmoneException::RESULT_ERROR
                        );
                    }
                }
            } else {
                // one order
                $this->orders[] = new PortmoneOrder($data->orders->order);
            }
        } elseif (isset($data->order) && is_object($data->order)) {
            // one order without list (postauth response)
            $this->orders[] = new PortmoneOrder($data->order);
        } else {
            return null;
            /*throw new PortmoneException(
                'Orders/order section missed in portmone result',
                PortmoneException::RESULT_ERROR
            );*/
        }
    }

    /**
     * Get single or specified (by bill ID or shop order number) order from result
     * @param null $bill_id
     * @param null $shop_order_number
     * @return PortmoneOrder
     * @throws PortmoneException
     */
    public function getOrder($bill_id = null, $shop_order_number = null)
    {
        if (empty($this->orders)) {
            return null;
            /*throw new PortmoneException(
                'No one order in portmone result',
                PortmoneException::RESULT_ERROR
            );*/
        }
        if (1 == count($this->orders)) {
            if (null !== $bill_id) {
                // additional check by bill ID
                if ($this->orders[0]->getBillId() == $bill_id) {
                    return $this->orders[0];
                } else {
                    throw new PortmoneException('Order not found', PortmoneException::NOT_FOUND);
                }
            } elseif (null !== $shop_order_number) {
                // additional check by order number
                if ($this->orders[0]->getOrderNumber() == $shop_order_number) {
                    return $this->orders[0];
                } else {
                    throw new PortmoneException('Order not found', PortmoneException::NOT_FOUND);
                }
            } else {
                return $this->orders[0];
            }
        } elseif (null !== $bill_id) {
            foreach ($this->orders as $order) {
                if ($order->getBillId() == $bill_id) {
                    return $order;
                }
            }
            throw new PortmoneException('Order not found', PortmoneException::NOT_FOUND);
        } elseif (null !== $shop_order_number) {
            foreach ($this->orders as $order) {
                if ($order->getOrderNumber() == $shop_order_number) {
                    return $order;
                }
            }
            throw new PortmoneException('Order not found', PortmoneException::NOT_FOUND);
        } else {
            throw new PortmoneException(
                'More than one orders found, please specify order number',
                PortmoneException::PARAMS_ERROR
            );
        }
    }

    public function getRequest()
    {
        return $this->request; // simple object, as-is, without class
    }
}