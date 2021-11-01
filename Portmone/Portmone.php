<?php

namespace payment\PaymentSystem\Portmone;

use payment\PaymentSystem\PaymentAbstract;
use payment\PaymentSystem\PaymentInterface;
use payment\PaymentSystem\Portmone\entities\Bill;
use payment\PaymentSystem\Portmone\entities\PayOrder;
use payment\PaymentSystem\Portmone\entities\PortmoneOrder;
use payment\PaymentSystem\Portmone\entities\Result;
use payment\PaymentSystem\Portmone\exceptions\PortmoneException;
use payment\PaymentSystem\Portmone\libs\PortmoneHelper;
use payment\PaymentSystem\Portmone\libs\Request;
use payment\PaymentSystem\entity\PaymentReturnCashInterface;
use payment\PaymentSystem\entity\PaymentStatusInterface;
use App\Entity\TblOrders;

class Portmone extends PaymentAbstract implements PaymentInterface
{
    const LANG_RU = 'ru';
    const LANG_UK = 'uk';
    const LANG_EN = 'en';
    const DEFAULT_LANG = 'ru';

    const ORDER_STATUS_PAYED = 'PAYED';
    const ORDER_STATUS_PREAUTH = 'PREAUTH';
    const ORDER_STATUS_CREATED = 'CREATED';
    const ORDER_STATUS_REJECTED = 'REJECTED';
    const ORDER_STATUS_RETURN = 'RETURN';

    const STATUSES = [
        'PAYED' => 'Оплачен',
        'PREAUTH' => 'Сумма успешно заблокирована на счету отправителя',
        'CREATED' => 'Создан',
        'REJECTED' => 'Отклонен',
        'RETURN' => 'Возвращен',
    ];

    public $languages = [self::LANG_RU, self::LANG_UK, self::LANG_EN];

    protected $payee_id;
    protected $login;
    protected $password;

    /**
     * Checkout action
     * @param $shop_order_number
     * @param $bill_amount
     * @param string $description
     * @param string $success_url
     * @param string $failure_url
     * @param string $lang
     * @return Checkout
     */
    public function checkout(
        $shop_order_number,
        $bill_amount,
        $description = '',
        $success_url = '',
        $failure_url = '',
        $lang = ''
    ) {
        $checkout = new Checkout($this);
        $checkout->setShopOrderNumber($shop_order_number);
        $checkout->setBillAmount($bill_amount);
        $checkout->setDescription($description);
        $checkout->setSuccessUrl($success_url);
        $checkout->setFailureUrl($failure_url);
        $checkout->setLang($lang);
        return $checkout;
    }

    /**
     * Check order result
     * @param $shop_order_number
     * @return PortmoneOrder
     * @throws PortmoneException
     */
    public function getResult($shop_order_number)
    {
        $result = new Results($this, $this->login, $this->password);
        return $result->checkOrder($shop_order_number);
    }

    /**
     * Check POST data for containing Bills xml structure
     * @param null $post
     * @return bool
     */
    public function isBills($post = null)
    {
        // custom specified or default POST data
        if (null === $post && isset($_POST)) {
            $post = $_POST;
        }
        // simple check that this is, probably, Bills xml structure
        if (!empty($post['data'])
            && false !== strpos($post['data'], '<BILLS>')
            // but Pay Order contain <BIILS> too, so check this in NOT Pay Orders request
            && false === strpos($post['data'], '<PAY_ORDERS>')
        ) {
            return true;
        }
        return false;
    }

    /**
     * Bill request processing
     * @param null $post
     * @return Bill
     * @throws PortmoneException
     */
    public function getBill($post = null)
    {
        // custom specified or default POST data
        if (null === $post && isset($_POST)) {
            $post = $_POST;
        }
        if (!empty($post['data'])) {
            $data = PortmoneHelper::parseXml($post['data']);
            if (isset($data->BILL)) {
                return new Bill($data->BILL);
            } else {
                throw new PortmoneException('Invalid bill format', PortmoneException::PARSE_ERROR);
            }
        } else {
            throw new PortmoneException('Data param not found', PortmoneException::PARAMS_ERROR);
        }
    }

    /**
     * Check POST data for containing Pay Orders xml structure
     * @param null $post
     * @return bool
     */
    public function isPayOrders($post = null)
    {
        // custom specified or default POST data
        if (null === $post && isset($_POST)) {
            $post = $_POST;
        }
        // simple check that this is, probably, Pay Orders xml structure
        if (!empty($post['data']) && strpos($post['data'], '<PAY_ORDERS>')) {
            return true;
        }
        return false;
    }

    /**
     * Pay order request processing
     * @param null $post
     * @return PayOrder
     * @throws PortmoneException
     */
    public function getPayOrder($post = null)
    {
        // custom specified or default POST data
        if (null === $post && isset($_POST)) {
            $post = $_POST;
        }
        if (!empty($post['data'])) {
            $data = PortmoneHelper::parseXml($post['data']);
            if (isset($data->PAY_ORDER)) {
                return new PayOrder($data->PAY_ORDER);
            } else {
                throw new PortmoneException('Invalid pay order format', PortmoneException::PARSE_ERROR);
            }
        } else {
            throw new PortmoneException('Data param not found', PortmoneException::PARAMS_ERROR);
        }
    }

    /**
     * Do postAuth confirm action
     * @param $bill_id
     * @param $amount
     * @param string $lang
     * @return Result
     * @throws PortmoneException
     */
    public function postAuthConfirm($bill_id, $amount, $lang = self::DEFAULT_LANG)
    {
        $result = new Results($this, $this->login, $this->password);
        return $result->postAuth($bill_id, Results::POST_AUTH_ACTION_SET_PAID, $amount, $lang);
    }

    /**
     * Do postAuth reject action
     * @param $bill_id
     * @param string $lang
     * @return Result
     * @throws PortmoneException
     */
    public function postAuthReject($bill_id, $lang = self::DEFAULT_LANG)
    {
        $result = new Results($this, $this->login, $this->password);
        return $result->postAuth($bill_id, Results::POST_AUTH_ACTION_REJECT, null, $lang);
    }

    /**
     * Perform return payment action
     * @param $bill_id
     * @param $returnAmount
     * @param string $lang
     * @return Result
     * @throws PortmoneException
     */
    public function returnPayment($bill_id, $returnAmount, $lang = self::DEFAULT_LANG)
    {
        $result = new Results($this, $this->login, $this->password);
        return $result->returnPayment($bill_id, $returnAmount, $lang);
    }

    /**
     * Success response to bill and pay order requests
     * @param bool $output
     * @param bool $header
     * @param bool $exit
     * @return string
     */
    public function sendSuccess($output = true, $header = true, $exit = false)
    {
        return $this->response(0, 'OK', $output, $header, $exit);
    }

    /**
     * Error response to bill and pay order requests
     * @param $code
     * @param $reason
     * @param bool $output
     * @param bool $header
     * @param bool $exit
     * @return string
     */
    public function sendError($code, $reason, $output = true, $header = true, $exit = false)
    {
        return $this->response($code, $reason, $output, $header, $exit);
    }

    //******************************** Internal functions *********************************************************//
    public function getPayeeId()
    {
        return $this->payee_id;
    }

    public function request()
    {
        return new Request();
    }

    protected function response($code, $reason, $output = true, $header = true, $exit = false)
    {
        $msg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . "<RESULT>\n"
            . "\t<ERROR_CODE>" . intval($code) . "</ERROR_CODE>\n"
            . "\t<REASON>" . PortmoneHelper::encode($reason) . "</REASON>\n"
            . "</RESULT>\n";
        if ($output) {
            if ($header) {
                header("Content-type: text/xml; charset=utf-8");
            }
            echo $msg;
            if ($exit) {
                exit();
            }
        }
        return $msg;
    }

	/**
	 * Принятие платежа
	 * @param string $order_id
	 * @return array|mixed
	 * @throws PortmoneException
	 */
    public function complete(string $order_id)
    {
        $orderStatus = $this->getResult($order_id);

        $response_data = [];

        if (empty($orderStatus)){
            $response_data['error'] = 'Заказ не найден';

            return $response_data;
        }
        $amount = floatval($orderStatus->getBillAmount());

        if ($amount == 0 || empty($amount)){
            $response_data['error'] = 'Не указана сумма, невозможно принять деньги.';

            return $response_data;
        }

        $result = $this->postAuthConfirm($orderStatus->getBillId(),$amount);

        $payedOrder = $result->getOrder();

        if (!empty($payedOrder->getErrorMessage())){
            $response_data['error'] = $payedOrder->getErrorMessage();

            return $response_data;
        }

        $response_data['payed'] = $amount = $payedOrder->getBillAmount();

        return $response_data;
    }

	/**
	 * Возврат платежа
	 * @param string $order_id
	 * @return array|mixed|PortmoneOrder|string[]|null
	 * @throws PortmoneException
	 */
    public function cancel(string $order_id)
    {
        $orderStatus = $this->getResult($order_id);

        if (empty($orderStatus)){
            return [
                'error' => 'Заказ не найден'
            ];
        }

        if (!in_array($orderStatus->getStatus(),[self::ORDER_STATUS_PREAUTH,self::ORDER_STATUS_PAYED])
            || ($orderStatus->getStatus() == self::ORDER_STATUS_PAYED && myUser::getInstance()->isSalesManager())){
            return [
                'error' => 'Неверный статус платежа! Невозможно отменить платеж.'
            ];
        }

        $amount = floatval($orderStatus->getBillAmount());

        if ($amount == 0 || empty($amount)){
            return [
                'error' => 'Не указана сумма, невозможно вернуть деньги.'
            ];
        }

        $result = $this->returnPayment($orderStatus->getBillId(),$amount);

        $payedOrder = $result->getOrder();

        if (!empty($payedOrder->getErrorMessage())){
            return [
                'error' => $payedOrder->getErrorMessage()
            ];
        }

        if ($payedOrder->getStatus() == Portmone::ORDER_STATUS_RETURN){
            return [
                'returned' => true
            ];
        } else{
            return [
                'error' => 'Платеж не найден'
            ];
        }
    }

	/**
	 * Проверка статуса
	 * @param string $number
	 * @return PaymentStatusInterface
	 * @throws PortmoneException
	 */
    public function status(string $number): PaymentStatusInterface
    {
        $result = $this->getResult($number);

        if (empty($result)){
            return [
                'error' => 'Платеж не найден'
            ];
        }

        return $this->formatResponseStatus($result);
    }

    /**
     * Устанавливаем данные авторизации
     * @param $params
     */
    public function setAuth($params) : void
    {
        $store = $params['store'];
        $this->payee_id = $store->getPortmonePayeeId();
        $this->login = $store->getPortmoneLogin();
        $this->password = $store->getPortmonePassword();
    }

    /**
     * Форматирование ответа проверки статуса платежа
     * @param $response
     * @return array
     */
    public function formatResponseStatus($response): array
    {
        $result['status'] = Portmone::STATUSES[$response->getStatus()];
        $result['date'] = $response->getPayDate();
        $result['amount'] = $response->getBillAmount() . ' UAH';

        return $result;
    }

    /**
     * URL для приема платежа
     * @param array $params
     * @return string
     */
    public function getCompleteUrl(array $params = []) : string
    {
        $base_url = PaymentAbstract::DEFAULT_COMPLETE_URL;

        $url = $this->addUrlParams($base_url,$params);

        return $url;
    }

    /**
     * URL для возврата платежа
     * @param array $params
     * @return string
     */
    public function getCancelUrl(array $params = []) : string
    {
        $base_url = PaymentService::DEFAULT_CANCEL_URL;

        $url = $this->addUrlParams($base_url,$params);

        return $url;
    }

    /**
     * URL для проверки статуса платежа
     * @param array $params
     * @return string
     */
    public function getStatusUrl(array $params = []) : string
    {
        $base_url = PaymentService::DEFAULT_STATUS_URL;

        $url = $this->addUrlParams($base_url,$params);

        return $url;
    }

	/**
	 * @param array $params
	 * @return string
	 */
	public function getReturnUrl(array $params = []): string
	{
		return '';
	}

	/**
	 * @param string $order_id
	 * @param float  $amount
	 * @return array
	 */
	public function return(string $order_id, float $amount): array
	{
		return [];
	}

	public function initReturnCashModel(): ?PaymentReturnCashInterface
	{
		// TODO: Implement initReturnCashModel() method.
	}
}