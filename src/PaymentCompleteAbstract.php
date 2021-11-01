<?php

namespace payment\PaymentSystem;

abstract class PaymentCompleteAbstract
{
	protected Response $response;
	protected ?string $error = null;
	protected ?float $acceptedAmount = null;
	protected string $status;
	protected ?string $errorPrefix = null;
	protected ?string $errorPostfix = null;

	public function __construct(Response $response)
	{
		$this->response = $response;
		$this->initFromResponse();
	}

	/**
	 * @return string|null
	 */
	public function getError(): ?string
	{
		return $this->error;
	}

	/**
	 * @return float|null
	 */
	public function getAcceptedAmount(): ?float
	{
		return $this->acceptedAmount;
	}

	/**
	 * @return Response
	 */
	public function getResponse(): Response
	{
		return $this->response;
	}

	/**
	 * @return string
	 */
	public function getStatus(): string
	{
		return $this->status;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return get_object_vars($this);
	}

	/**
	 * @param string $prefix
	 * @return $this
	 */
	public function setErrorPrefix(string $prefix): PaymentCompleteInterface
	{
		$this->errorPrefix = $prefix;

		return $this;
	}

	/**
	 * @param string $postfix
	 * @return $this
	 */
	public function setErrorPostfix(string $postfix): PaymentCompleteInterface
	{
		$this->errorPostfix = $postfix;

		return $this;
	}
}