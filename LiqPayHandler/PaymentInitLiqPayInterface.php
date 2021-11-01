<?php

namespace payment\PaymentSystem\LiqPayHandler;

use payment\PaymentSystem\PaymentInitInterface;

interface PaymentInitLiqPayInterface
{
	public function getLiqpayPrivateKey(): ?string;

	public function getLiqpayPublicKey(): ?string;
}