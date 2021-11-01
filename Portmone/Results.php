<?php

namespace payment\PaymentSystem\Portmone;

use payment\PaymentSystem\Portmone\entities\PortmoneOrder;
use payment\PaymentSystem\Portmone\entities\Result;
use payment\PaymentSystem\Portmone\exceptions\PortmoneException;
use payment\PaymentSystem\Portmone\libs\Request;

class Results
{
    const POST_AUTH_ACTION_SET_PAID = 'set_paid';
    const POST_AUTH_ACTION_REJECT = 'reject';

    private $parent;
    private $result_url = 'https://www.portmone.com.ua/gateway/';
    private $return_url = 'https://www.portmone.com.ua/r3/secure/gate/return';

    protected $payee_id;

    protected $login;
    protected $password;

    protected $billData;

    public function __construct(Portmone $parent, $login, $password)
    {
        $this->parent = $parent;
        $this->payee_id = $parent->getPayeeId();
        $this->login = $login;
        $this->password = $password;
        // check login and password
        if (null === $this->login || null === $this->password) {
            throw new PortmoneException('Login and Password required', PortmoneException::PARAMS_ERROR);
        }
    }

    /**
     * @param $shop_order_number
     * @return PortmoneOrder
     * @throws PortmoneException
     */
    public function checkOrder($shop_order_number)
    {
        // send request
        $response = $this->request()->post($this->result_url, [
            'method' => 'result',
            'payee_id' => $this->payee_id,
            'login' => $this->login,
            'password' => $this->password,
            'shop_order_number' => $shop_order_number,
        ]);
        if ($response) {
            $result = new Result($response);
            if ($result->getRequest()->shop_order_number == $shop_order_number) { //
                return $result->getOrder(null, $shop_order_number);
            }
        }
        throw new PortmoneException('Check order request fail', PortmoneException::REQUEST_ERROR);
    }

    /**
     * Perform post auth action - confirm or reject
     * @param $bill_id
     * @param $action
     * @param null $amount
     * @param string $lang
     * @return Result
     * @throws PortmoneException
     */
    public function postAuth($bill_id, $action, $amount = null, $lang = Portmone::DEFAULT_LANG)
    {
        if (self::POST_AUTH_ACTION_SET_PAID !== $action && self::POST_AUTH_ACTION_REJECT !== $action) {
            throw new PortmoneException('Invalid postAuth action', PortmoneException::PARAMS_ERROR);
        }
        // send request
        $params = [
            'method' => 'preauth',
            'action' => $action,
            //'payee_id' => $this->payee_id, // ???
            'login' => $this->login,
            'password' => $this->password,
            'shop_bill_id' => $bill_id,
            'encoding' => 'utf-8',
            'lang' => in_array($lang, $this->parent->languages) ? $lang : Portmone::DEFAULT_LANG,
        ];
        if (self::POST_AUTH_ACTION_SET_PAID === $action) {
            if (is_float($amount)) {
                $params['postauth_amount'] = floatval($amount);
            } else {
                throw new PortmoneException('Amount value required for set_paid action', PortmoneException::PARAMS_ERROR);
            }
        }
        $response = $this->request()->post($this->result_url, $params);
        if ($response) {
            return new Result($response);
        }
        throw new PortmoneException('Post auth request fail', PortmoneException::REQUEST_ERROR);
    }

    /**
     * Perform return payment action
     * @param $bill_id
     * @param $returnAmount
     * @param string $lang
     * @return Result
     * @throws PortmoneException
     */
    public function returnPayment($bill_id, $returnAmount, $lang = Portmone::DEFAULT_LANG)
    {
        // send request
        $params = [
            'method' => 'return',
            //'payee_id' => $this->payee_id, // ???
            'login' => $this->login,
            'password' => $this->password,
            'shop_bill_id' => $bill_id,
            'return_amount' => floatval($returnAmount),
            'encoding' => 'utf-8',
            'lang' => in_array($lang, $this->parent->languages) ? $lang : Portmone::DEFAULT_LANG,
        ];
        $response = $this->request()->post($this->return_url, $params);
        if ($response) {
            return new Result($response);
        }
        throw new PortmoneException('Return request fail', PortmoneException::REQUEST_ERROR);
    }

    /**
     * @return Request
     */
    protected function request()
    {
        return $this->parent->request();
    }
}

?>