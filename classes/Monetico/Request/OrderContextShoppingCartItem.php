<?php

namespace MoneticoDemoWebKit\Monetico\Request;

class OrderContextShoppingCartItem implements \JsonSerializable {

	/**
	 * @var ?string
	 */
	private $name;

	/**
	 * @var ?string
	 */
	private $description;

	/**
	 * @var ?string
	 */
	private $productCode;

	/**
	 * @var ?string
	 */
	private $imageURL;

	/**
	 * @var ?int
	 */
	private $unitPrice;

	/**
	 * @var ?int
	 */
	private $quantity;

	/**
	 * @var ?string
	 */
	private $productSKU;

	/**
	 * @var ?string
	 */
	private $productRisk;

	public function jsonSerialize(): mixed {
		return array_filter( [
			"name"        => $this->getName(),
			"description" => $this->getDescription(),
			"productCode" => $this->getProductCode(),
			"imageURL"    => $this->getImageURL(),
			"unitPrice"   => $this->getUnitPrice(),
			"quantity"    => $this->getQuantity(),
			"productSKU"  => $this->getProductSKU(),
			"productRisk" => $this->getProductRisk()
		], function ( $value ) {
			return ! is_null( $value );
		} );
	}

	/**
	 * @return string|null
	 */
	public function getName(): ?string {
		return $this->name;
	}

	/**
	 * @param string|null $name
	 */
	public function setName( ?string $name ): void {
		$this->name = $name;
	}

	/**
	 * @return string|null
	 */
	public function getDescription(): ?string {
		return $this->description;
	}

	/**
	 * @param string|null $description
	 */
	public function setDescription( ?string $description ): void {
		$this->description = $description;
	}

	/**
	 * @return string|null
	 */
	public function getProductCode(): ?string {
		return $this->productCode;
	}

	/**
	 * @param string|null $productCode
	 */
	public function setProductCode( ?string $productCode ): void {
		$this->productCode = $productCode;
	}

	/**
	 * @return string|null
	 */
	public function getImageURL(): ?string {
		return $this->imageURL;
	}

	/**
	 * @param string|null $imageURL
	 */
	public function setImageURL( ?string $imageURL ): void {
		$this->imageURL = $imageURL;
	}

	/**
	 * @return int|null
	 */
	public function getUnitPrice(): ?int {
		return $this->unitPrice;
	}

	/**
	 * @param int|null $unitPrice
	 */
	public function setUnitPrice( ?int $unitPrice ): void {
		$this->unitPrice = $unitPrice;
	}

	/**
	 * @return int|null
	 */
	public function getQuantity(): ?int {
		return $this->quantity;
	}

	/**
	 * @param int|null $quantity
	 */
	public function setQuantity( ?int $quantity ): void {
		$this->quantity = $quantity;
	}

	/**
	 * @return string|null
	 */
	public function getProductSKU(): ?string {
		return $this->productSKU;
	}

	/**
	 * @param string|null $productSKU
	 */
	public function setProductSKU( ?string $productSKU ): void {
		$this->productSKU = $productSKU;
	}

	/**
	 * @return string|null
	 */
	public function getProductRisk(): ?string {
		return $this->productRisk;
	}

	/**
	 * @param string|null $productRisk
	 */
	public function setProductRisk( ?string $productRisk ): void {
		$this->productRisk = $productRisk;
	}
}