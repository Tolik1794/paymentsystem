<?php

namespace payment\PaymentSystem;

abstract class PaymentStatusAbstract
{
	protected Response $response;
	protected ?string $statusDescription = null;
	protected ?string $status = null;
	protected ?\DateTime $date = null;
	protected ?float $amount = null;
	protected ?float $refundAmount = null;
	protected ?string $error = null;

	public function __construct(Response $response)
	{
		$this->response = $response;
		$this->initFromResponse();
	}

	/**
	 * @return float|null
	 */
	public function getAmount(): ?float
	{
		return $this->amount;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getDate(): ?\DateTime
	{
		return $this->date;
	}

	/**
	 * @return string|null
	 */
	public function getStatus(): ?string
	{
		return $this->status;
	}

	/**
	 * @return string|null
	 */
	public function getStatusDescription(): ?string
	{
		return $this->statusDescription;
	}

	/**
	 * @return string|null
	 */
	public function getError(): ?string
	{
		return $this->error;
	}

	/**
	 * @return Response
	 */
	public function getResponse(): Response
	{
		return $this->response;
	}

	/**
	 * @return float|null
	 */
	public function getRefundAmount(): ?float
	{
		return $this->refundAmount;
	}

	/**
	 * @return float|null
	 */
	public function getAmountInOrder(): ?float
	{
		return $this->amount - $this->refundAmount;
	}
}