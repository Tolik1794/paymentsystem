<?php

namespace payment\PaymentSystem;

use Exception;
use payment\PaymentSystem\PaymentReturnCashInterface;
use payment\PaymentSystem\PaymentStatusInterface;

interface PaymentInterface
{
	/**
	 * Списание заблокированных средств с карты
	 * @param string $order_id
	 * @return mixed
	 */
    public function complete(string $order_id);

	/**
	 * Возврат средств
	 * @param string $order_id
	 * @param float  $amount
	 * @return mixed
	 */
    public function return(string $order_id, float $amount);

	/**
	 * Возврат средств
	 * @param string $order_id
	 * @return mixed
	 */
	public function cancel(string $order_id);

	/**
	 * Проверка текущего состояния платежа
	 * @param string $order_id
	 * @return mixed
	 */
    public function status(string $order_id): PaymentStatusInterface;

	/**
	 * @return PaymentReturnCashInterface|null
	 */
	public function initReturnCashModel(): ?PaymentReturnCashInterface;

	/**
	 * Request to LiqPay API by action and params.
	 *
	 * @param string $action
	 * @param array  $params
	 * @param string $type
	 * @return Response
	 * @throws Exception
	 */
	function request(string $action, array $params, string $type): Response;
}