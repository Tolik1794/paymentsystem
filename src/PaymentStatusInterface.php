<?php

namespace payment\PaymentSystem;

interface PaymentStatusInterface
{
	/**
	 * @return float|null
	 */
	public function getAmount(): ?float;

	/**
	 * @return \DateTime|null
	 */
	public function getDate(): ?\DateTime;

	/**
	 * @return string|null
	 */
	public function getStatus(): ?string;

	/**
	 * @return string|null
	 */
	public function getStatusDescription(): ?string;

	/**
	 * @return string|null
	 */
	public function getError(): ?string;

	/**
	 * @return Response
	 */
	public function getResponse(): Response;

	/**
	 * @return float|null
	 */
	public function getRefundAmount(): ?float;

	/**
	 * @return float|null
	 */
	public function getAmountInOrder(): ?float;

	public function initFromResponse(): void;
}