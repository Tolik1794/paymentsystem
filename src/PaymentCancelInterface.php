<?php

namespace payment\PaymentSystem;

interface PaymentCancelInterface
{
	/**
	 * @return string|null
	 */
	public function getError(): ?string;

	/**
	 * @return float|null
	 */
	public function getCanceledAmount(): ?float;

	/**
	 * @return Response
	 */
	public function getResponse(): Response;

	/**
	 * @return string
	 */
	public function getStatus(): string;

	/**
	 * @return array
	 */
	public function toArray(): array;

	/**
	 * @param string $prefix
	 * @return $this
	 */
	public function setErrorPrefix(string $prefix): PaymentCancelInterface;

	/**
	 * @param string $postfix
	 * @return $this
	 */
	public function setErrorPostfix(string $postfix): PaymentCancelInterface;

	public function initFromResponse(): void;
}