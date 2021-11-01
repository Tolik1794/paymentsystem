<?php

namespace payment\PaymentSystem\LiqPayHandler;

use Exception;
use payment\PaymentSystem\LiqPayHandler\model\PaymentCancel;
use payment\PaymentSystem\LiqPayHandler\model\PaymentComplete;
use payment\PaymentSystem\LiqPayHandler\model\PaymentReturnCash;
use payment\PaymentSystem\PaymentCompleteInterface;
use payment\PaymentSystem\PaymentInterface;
use payment\PaymentSystem\LiqPayHandler\model\PaymentStatus;
use payment\PaymentSystem\PaymentCancelInterface;
use payment\PaymentSystem\PaymentReturnCashInterface;
use payment\PaymentSystem\PaymentStatusInterface;
use JetBrains\PhpStorm\Pure;
use payment\PaymentSystem\Response;

class LiqPayHandler implements PaymentInterface
{
	/**
	 * Decoding statuses array.
	 */
	const STATUSES = [
		'success' => 'Успешный платеж',
		'reversed' => 'Платеж возвращен',
		'hold_wait' => 'Сумма успешно заблокирована на счету отправителя',
		'wait_accept' => 'Деньги с клиента списаны, но магазин еще не прошел проверку',
		'error' => 'Возникла ошибка при получении данных. Попробуйте повторить запрос позже.',
		'failure' => 'Неуспешный платеж',
		'subscribed' => 'Подписка успешно оформлена',
		'unsubscribed' => 'Подписка успешно деактивирована',
		'try_again' => 'Оплата неуспешна. Клиент может повторить попытку еще раз',
		'wait_secure' => 'Платеж на проверке',
		'wait_reserve' => 'Средства по платежу зарезервированы для проведения возврата по ранее поданной заявке',
		'processing' => 'Платеж обрабатывается',
		'prepared' => 'Платеж создан, ожидается его завершение отправителем',
		'mp_verify' => 'Ожидается завершение платежа в кошельке MasterPass',
		'p24_verify' => 'Ожидается завершение платежа в Приват24',
		'wait_sender' => 'Ожидается подтверждение оплаты клиентом в приложении Privat24/SENDER',
		'wait_qr' => 'Ожидается сканирование QR-кода клиентом',
		'senderapp_verify' => 'Ожидается подтверждение в приложении SENDER',
		'sender_verify' => 'Требуется ввод данных отправителя. Для завершения платежа, требуется выполнить sender_verify',
		'receiver_verify' => 'Требуется ввод данных получателя. Для завершения платежа, требуется выполнить receiver_verify',
		'pin_verify' => 'Ожидается подтверждение pin-code',
		'phone_verify' => 'Ожидается ввод телефона клиентом. Для завершения платежа, требуется выполнить phone_verify',
		'password_verify' => 'Ожидается подтверждение пароля приложения Приват24',
		'otp_verify' => 'Требуется OTP подтверждение клиента. OTP пароль отправлен на номер телефона Клиента. Для завершения платежа, требуется выполнить otp_verify',
		'ivr_verify' => 'Ожидается подтверждение звонком ivr',
		'cvv_verify' => 'Требуется ввод CVV карты отправителя. Для завершения платежа, требуется выполнить cvv_verify',
		'captcha_verify' => 'Ожидается подтверждение captcha',
		'3ds_verify' => 'Требуется 3DS верификация. Для завершения платежа, требуется выполнить 3ds_verify',
	];


	/**
	 * Decoding error array.
	 */
	const ERROR_DESCRIPTION = [
		"limit" => "Превышен лимит на сумму или количество платежей клиента",
		"frod" => "Транзакция определена как нетипичная/рисковая согласно Anti-Fraud правилам Банка",
		"decline" => "Транзакция определена как нетипичная/рисковая согласно Anti-Fraud системы Банка",
		"err_auth" => "Требуется авторизация",
		"err_cache" => "Истекло время хранения данных для данной операции",
		"user_not_found" => "Пользователь не найден",
		"err_sms_send" => "Не удалось отправить смс",
		"err_sms_otp" => "Неверно указан пароль из смс",
		"shop_blocked" => "Магазин заблокирован",
		"shop_not_active" => "Магазин не активный",
		"invalid_signature" => "Неверная подпись запроса",
		"order_id_empty" => "Передан пустой order_id",
		"err_shop_not_agent" => "Вы не являетесь агентом для указанного магазина",
		"err_card_def_notfound" => "Карта для приема платежей не найдена в кошельке",
		"err_no_card_token" => "У пользователя нет карты с таким card_token",
		"err_card_liqpay_def" => "Укажите другую карту",
		"err_card_type" => "Неверный тип карты",
		"err_card_country" => "Укажите другую карту",
		"err_limit_amount" => "Сумма перевода меньше или больше заданного лимита",
		"err_payment_amount_limit" => "Сумма перевода меньше или больше заданного лимита",
		"amount_limit" => "Превышен лимит суммы",
		"payment_err_sender_card" => "Укажите другую карту отправителя",
		"payment_processing" => "Платеж обрабатывается",
		"err_payment_discount" => "Не найдена скидка для данного платежа",
		"err_wallet" => "Не удалось загрузить кошелек",
		"err_get_verify_code" => "Требуется верифицировать карту",
		"err_verify_code" => "Неверный код верификации",
		"wait_info" => "Ожидается дополнительная информация, попробуйте позже",
		"err_path" => "Неверный адрес запроса",
		"err_payment_cash_acq" => "Платеж не может быть проведен в этом магазине",
		"err_split_amount" => "Сумма платежей ращепления не совпадает с суммой платежа",
		"err_card_receiver_def" => "У получателя не установлена карта для приема платежей",
		"payment_err_status" => "Неверный статус платежа",
		"public_key_not_found" => "Не найден public_key",
		"payment_not_found" => "Платеж не найден",
		"payment_not_subscribed" => "Платеж не является регулярным",
		"wrong_amount_currency" => "Валюта платежа не совпадает с валютой debit",
		"err_amount_hold" => "Сумма не может быть больше суммы платежа",
		"err_access" => "Ошибка доступа",
		"order_id_duplicate" => "Такой order_id уже есть",
		"err_blocked" => "Доступ в аккаунт закрыт",
		"err_empty" => "Параметр не заполнен",
		"err_empty_phone" => "Параметр phone не заполнен",
		"err_missing" => "Не передан параметр",
		"err_wrong" => "Неверно указан параметр",
		"err_wrong_currency" => "Неверно указана валюта. Используйте (USD, UAH, RUB, EUR)",
		"err_phone" => "Указан неверный номер телефона",
		"err_card" => "Неверно указан номер карты",
		"err_card_bin" => "Бин карты не найден",
		"err_terminal_notfound" => "Терминал не найден",
		"err_commission_notfound" => "Комиссия не найдена",
		"err_payment_create" => "Не удалось создать платеж",
		"err_mpi" => "Не удалось проверить карту",
		"err_currency_is_not_allowed" => "Валюта запрещена",
		"err_look" => "Не удалось завершить операцию",
		"err_mods_empty" => "Не удалось завершить операцию",
		"payment_err_type" => "Неверный тип платежа",
		"err_payment_currency" => "Валюта карты или перевода запрещены",
		"err_payment_exchangerates" => "Не найден подходящий курс валют",
		"err_signature" => "Неверная подпись запроса",
		"err_api_action" => "Не передан параметр action",
		"err_api_callback" => "Не передан параметр callback",
		"err_api_ip" => "В этом мерчанте запрещен вызов API с этого IP",
		"expired_phone" => "Истек срок подтверждения платежа вводом номера телефона",
		"expired_3ds" => "Истек срок 3DS верификации клиента",
		"expired_otp" => "Истек срок подтверждения платежа OTP паролем",
		"expired_cvv" => "Истек срок подтверждения платежа вводом CVV кода",
		"expired_p24" => "Истек срок выбора карты в Приват24",
		"expired_sender" => "Истек срок получения данных об отправителе",
		"expired_pin" => "Истек срок подтверждения платежа pin-кодом карты",
		"expired_ivr" => "Истек срок подтверждения платежа звонком IVR",
		"expired_captcha" => "Истек срок подтверждения платежа с помощью captcha",
		"expired_password" => "Истек срок подтверждения платежа паролем Приват24",
		"expired_senderapp" => "Истек срок подтверждения платежа формой в Приват24",
		"expired_prepared" => "Истек срок завершения созданного платежа",
		"expired_mp" => "Истек срок завершения платежа в кошельке MasterPass",
		"expired_qr" => "Истек срок подтверждения платежа сканированием QR кода",
		"5" => "Карта не поддерживает 3DSecure",
		"90" => "Общая ошибка во время обработки",
		"101" => "Токен создан не этим мерчантом",
		"102" => "Присланый токен не активен",
		"103" => "Достингута максимальная сумма покупок по токену",
		"104" => "Лимит транзакций по токену исчерпан",
		"105" => "Карта не поддерживается",
		"106" => "Мерчанту не разрешена предавторизация",
		"107" => "Экваер не поддерживает 3ds",
		"108" => "Такой токен не существует",
		"109" => "Превышен лимит попыток по данному IP",
		"110" => "Сессия истекла",
		"111" => "Бранч карты заблокирован",
		"112" => "Достигнут лимит по дневному лимиту карты по бранчу",
		"113" => "Временно закрыта возможность проведения P2P-платежей с карт ПБ на карты зарубежных банков",
		"114" => "Достигнут лимит по комплитам",
		"115" => "Неверное имя получателя",
		"2903" => "Достигнут дневной лимит использования карты",
		"2915" => "Такой order_id уже есть",
		"3914" => "Платежи для данной страны запрещены",
		"9851" => "Карта просрочена",
		"9852" => "Неверный номер карты",
		"9854" => "Платеж отклонен. Попробуйте позже",
		"9855" => "Карта не поддерживает данный вид транзакции",
		"9857" => "Карта не поддерживает данный вид транзакции",
		"9859" => "Недостаточно средств",
		"9860" => "Превышен лимит операций по карте",
		"9861" => "Превышен лимит на оплату в интернете",
		"9863" => "На карте установлено ограничение. Обратитесь в поддержку банка",
		"9867" => "Неверно указанна сумма транзакции",
		"9868" => "Платеж отклонен. Банк не подтвердил операцию. Обратитесь в банк",
		"9872" => "Банк не подтвердил операцию. Обратитесь в банк",
		"9882" => "Неверно переданы параметры или транзакция с такимим условиями не разрешена",
		"9886" => "Мерчанту не разрешены рекурентные платежи",
		"9961" => "Платеж отклонен. Обратитесь в поддержку банка",
		"9989" => "Платеж отклонен. Проверьте правильность введеных реквизитов карты"
	];

	/**
	 * Public key to LiqPay API
	 *
	 * @var string
	 */
	private string $public_key;

	/**
	 * Private key to LiqPay API
	 *
	 * @var string
	 */
	private string $private_key;

	/**
	 * @param string $public_key
	 */
	public function setPublicKey(string $public_key): void
	{
		$this->public_key = $public_key;
	}

	/**
	 * @param string $private_key
	 */
	public function setPrivateKey(string $private_key): void
	{
		$this->private_key = $private_key;
	}

	/**
	 * Принятие платежа
	 * @param string $order_id
	 * @return PaymentCompleteInterface
	 * @throws Exception
	 */
	public function complete(string $order_id): PaymentCompleteInterface
	{
		$result = $this->request('hold_completion', ['order_id' => $order_id,], __FUNCTION__);

		return new PaymentComplete($result);
	}

	/**
	 * Возврат платежа
	 * @param string $order_id
	 * @return PaymentCancelInterface
	 * @throws Exception
	 */
	public function cancel(string $order_id): PaymentCancelInterface
	{
		return new PaymentCancel($this->request('refund', ['order_id' => $order_id], __FUNCTION__));
	}

	/**
	 * @param string $order_id
	 * @param float  $amount
	 * @return Response
	 * @throws Exception
	 */
	public function return(string $order_id, float $amount): Response
	{
		$action = 'refund';
		return $this->request($action, ['order_id' => $order_id, 'amount' => $amount], __FUNCTION__);
	}

	/**
	 * Проверка статуса
	 *
	 * @param string $order_id
	 * @return PaymentStatusInterface
	 * @throws Exception
	 */
	public function status(string $order_id): PaymentStatusInterface
	{
		$result = $this->request('status', ['order_id' => $order_id], __FUNCTION__);

		return new PaymentStatus($result);
	}

	/**
	 * Устанавливаем данные авторизации
	 * @param PaymentInitLiqPayInterface $paymentInit
	 */
	public function setAuth(PaymentInitLiqPayInterface $paymentInit): void
	{
		$this->private_key = $paymentInit->getLiqpayPrivateKey();
		$this->public_key = $paymentInit->getLiqpayPublicKey();
	}

	/**
	 * @return PaymentReturnCashInterface|null
	 */
	#[Pure]
	public function initReturnCashModel(): ?PaymentReturnCashInterface
	{
		return new PaymentReturnCash();
	}

	/**
	 * Request to LiqPay API by action and params.
	 *
	 * @param string $action
	 * @param array  $params
	 * @param string $type
	 * @return Response
	 * @throws Exception
	 */
	public function request(string $action, array $params, string $type): Response
	{
		if (!$this->public_key || !$this->private_key) {
			return new Response([], $type, 'error', 'Не указан LiqPay аккаунт для выбранного магазина');
		}

		try {
			$liq_pay = new LiqPay($this->public_key, $this->private_key);
			$response = (array)$liq_pay->api("request", $params + ['action' => $action, 'version' => 3]);

			if ($response['result'] === 'ok' && $response['status'] != 'error' || !isset($response['err_code'])) {
				return new Response($response, $type, 'success',
					self::STATUSES[$response['status']] ?? $response['status']);
			} else {
				return new Response([], $type, 'error', self::ERROR_DESCRIPTION[$response['err_code']] ?? '');
			}
		} catch (Exception $e) {
			return new Response([], $type, 'error',
				sprintf('Ошибка при отправке запроса на API LiqPay: %s', $e->getMessage()));
		}
	}
}

