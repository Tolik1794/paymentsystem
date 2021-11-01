<?php

namespace payment\PaymentSystem;

interface PaymentInitInterface
{
	public function setPaymentAuth(PaymentInterface $payment): void;
}