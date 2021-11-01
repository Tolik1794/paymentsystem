<?php

namespace payment\PaymentSystem;

interface PaymentReturnCashInterface
{
	/**
	 * @return string|null
	 */
	public function getError(): ?string;

	/**
	 * @return float|null
	 */
	public function getReturnedAmount(): ?float;

	/**
	 * @return array
	 */
	public function getResponse(): array;

	/**
	 * @return string
	 */
	public function getStatus(): string;

	/**
	 * @param string $prefix
	 * @return $this
	 */
	public function setErrorPrefix(string $prefix): PaymentReturnCashInterface;

	/**
	 * @param string $postfix
	 * @return $this
	 */
	public function setErrorPostfix(string $postfix): PaymentReturnCashInterface;

	/**
	 * @return float
	 */
	public function getMaxAmount(): float;

	/**
	 * @param float $maxAmount
	 */
	public function setMaxAmount(float $maxAmount): void;

	/**
	 * @return float
	 */
	public function getAmount(): float;

	/**
	 * @param float $amount
	 */
	public function setAmount(float $amount): void;

	/**
	 * @return string|null
	 */
	public function getMessage(): ?string;

	/**
	 * @return array
	 */
	public function toArray(): array;

	/**
	 * @param array $response
	 * @return PaymentReturnCashInterface
	 */
	public function initFromResponse(array $response): PaymentReturnCashInterface;
}