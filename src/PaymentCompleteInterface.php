<?php

namespace payment\PaymentSystem;

interface PaymentCompleteInterface
{
	/**
	 * @return float|null
	 */
	public function getAcceptedAmount(): ?float;

	/**
	 * @return string
	 */
	public function getStatus(): string;

	/**
	 * @return string|null
	 */
	public function getError(): ?string;

	/**
	 * @return Response
	 */
	public function getResponse(): Response;

	/**
	 * @param string $prefix
	 * @return $this
	 */
	public function setErrorPrefix(string $prefix): self;

	/**
	 * @param string $postfix
	 * @return $this
	 */
	public function setErrorPostfix(string $postfix): self;

	public function initFromResponse(): void;

	/**
	 * @return array
	 */
	public function toArray(): array;
}