<?php

namespace MoneticoDemoWebKit\Monetico\Request;

class OrderContextShoppingCart implements \JsonSerializable {
	/**
	 * @var ?int
	 */
	private $giftCardAmount;

	/**
	 * @var ?int
	 */
	private $giftCardCount;

	/**
	 * @var ?string
	 */
	private $giftCardCurrency;

	/**
	 * @var ?\DateTime?
	 */
	private $preOrderDate;

	/**
	 * @var ?bool
	 */
	private $preorderIndicator;

	/**
	 * @var ?bool
	 */
	private $reorderIndicator;

	/**
	 * @var ?OrderContextShoppingCartItem[]
	 */
	private $shoppingCartItems;

	public function jsonSerialize(): mixed {
		return array_filter( [
			"giftCardAmount"    => $this->getGiftCardAmount(),
			"giftCardCount"     => $this->getGiftCardCount(),
			"giftCardCurrency"  => $this->getGiftCardCurrency(),
			"preOrderDate"      => $this->getFormatedPreOrderDate(),
			"preorderIndicator" => $this->getPreorderIndicator(),
			"reorderIndicator"  => $this->getReorderIndicator(),
			"shoppingCartItems" => $this->getShoppingCartItems()
		], function ( $value ) {
			return ! is_null( $value );
		} );
	}

	/**
	 * @return int|null
	 */
	public function getGiftCardAmount(): ?int {
		return $this->giftCardAmount;
	}

	/**
	 * @param int|null $giftCardAmount
	 */
	public function setGiftCardAmount( ?int $giftCardAmount ): void {
		$this->giftCardAmount = $giftCardAmount;
	}

	/**
	 * @return int|null
	 */
	public function getGiftCardCount(): ?int {
		return $this->giftCardCount;
	}

	/**
	 * @param int|null $giftCardCount
	 */
	public function setGiftCardCount( ?int $giftCardCount ): void {
		$this->giftCardCount = $giftCardCount;
	}

	/**
	 * @return string|null
	 */
	public function getGiftCardCurrency(): ?string {
		return $this->giftCardCurrency;
	}

	/**
	 * @param string|null $giftCardCurrency
	 */
	public function setGiftCardCurrency( ?string $giftCardCurrency ): void {
		$this->giftCardCurrency = $giftCardCurrency;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getPreOrderDate(): ?\DateTime {
		return $this->preOrderDate;
	}

	/**
	 * @return string|null
	 */
	public function getFormatedPreOrderDate(): ?string {
		if ( $this->getPreOrderDate() instanceof \DateTime ) {
			return $this->getPreOrderDate()->format( "Y-m-d" );
		}

		return null;
	}

	/**
	 * @param \DateTime|null $preOrderDate
	 */
	public function setPreOrderDate( ?\DateTime $preOrderDate ): void {
		$this->preOrderDate = $preOrderDate;
	}

	/**
	 * @return bool|null
	 */
	public function getPreorderIndicator(): ?bool {
		return $this->preorderIndicator;
	}

	/**
	 * @param bool|null $preorderIndicator
	 */
	public function setPreorderIndicator( ?bool $preorderIndicator ): void {
		$this->preorderIndicator = $preorderIndicator;
	}

	/**
	 * @return bool|null
	 */
	public function getReorderIndicator(): ?bool {
		return $this->reorderIndicator;
	}

	/**
	 * @param bool|null $reorderIndicator
	 */
	public function setReorderIndicator( ?bool $reorderIndicator ): void {
		$this->reorderIndicator = $reorderIndicator;
	}

	/**
	 * @return \MoneticoDemoWebKit\Monetico\Request\OrderContextShoppingCartItem[]|null
	 */
	public function getShoppingCartItems(): ?array {
		return $this->shoppingCartItems;
	}

	/**
	 * @param \MoneticoDemoWebKit\Monetico\Request\OrderContextShoppingCartItem[]|null $shoppingCartItems
	 */
	public function setShoppingCartItems( ?array $shoppingCartItems ): void {
		$this->shoppingCartItems = $shoppingCartItems;
	}


}