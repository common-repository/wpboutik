<?php

namespace MoneticoDemoWebKit\Monetico\Request;

/**
 * Represents the "contexte_commande" field content.
 * See technical documentation for a full explanation of each field and the format to use
 */
class OrderContext implements \JsonSerializable {
	/**
	 * @var OrderContextBilling
	 */
	private $orderContextBilling;

	/**
	 * @var ?OrderContextClient
	 */
	private $orderContextClient;

	/**
	 * @var ?OrderContextShipping
	 */
	private $orderContextShipping;

	/**
	 * @var ?OrderContextShoppingCart
	 */
	private $orderContextShoppingCart;

	/**
	 * OrderContext constructor.
	 *
	 * @param ?OrderContextBilling $billing
	 */
	public function __construct( $billing ) {
		$this->setOrderContextBilling( $billing );
	}

	public function jsonSerialize(): mixed {
		return array_filter( [
			'billing'      => $this->getOrderContextBilling(),
			'client'       => $this->getOrderContextClient(),
			'shipping'     => $this->getOrderContextShipping(),
			'shoppingCart' => $this->getOrderContextShoppingCart()
		], function ( $value ) {
			return ! is_null( $value );
		} );
	}

	/**
	 * @return \MoneticoDemoWebKit\Monetico\Request\OrderContextBilling
	 */
	public function getOrderContextBilling(): \MoneticoDemoWebKit\Monetico\Request\OrderContextBilling {
		return $this->orderContextBilling;
	}

	/**
	 * @param \MoneticoDemoWebKit\Monetico\Request\OrderContextBilling $orderContextBilling
	 */
	public function setOrderContextBilling( \MoneticoDemoWebKit\Monetico\Request\OrderContextBilling $orderContextBilling ): void {
		$this->orderContextBilling = $orderContextBilling;
	}

	/**
	 * @return \MoneticoDemoWebKit\Monetico\Request\OrderContextClient|null
	 */
	public function getOrderContextClient(): ?\MoneticoDemoWebKit\Monetico\Request\OrderContextClient {
		return $this->orderContextClient;
	}

	/**
	 * @param \MoneticoDemoWebKit\Monetico\Request\OrderContextClient|null $orderContextClient
	 */
	public function setOrderContextClient( ?\MoneticoDemoWebKit\Monetico\Request\OrderContextClient $orderContextClient ): void {
		$this->orderContextClient = $orderContextClient;
	}

	/**
	 * @return \MoneticoDemoWebKit\Monetico\Request\OrderContextShipping|null
	 */
	public function getOrderContextShipping(): ?\MoneticoDemoWebKit\Monetico\Request\OrderContextShipping {
		return $this->orderContextShipping;
	}

	/**
	 * @param \MoneticoDemoWebKit\Monetico\Request\OrderContextShipping|null $orderContextShipping
	 */
	public function setOrderContextShipping( ?\MoneticoDemoWebKit\Monetico\Request\OrderContextShipping $orderContextShipping ): void {
		$this->orderContextShipping = $orderContextShipping;
	}

	/**
	 * @return \MoneticoDemoWebKit\Monetico\Request\OrderContextShoppingCart|null
	 */
	public function getOrderContextShoppingCart(): ?\MoneticoDemoWebKit\Monetico\Request\OrderContextShoppingCart {
		return $this->orderContextShoppingCart;
	}

	/**
	 * @param \MoneticoDemoWebKit\Monetico\Request\OrderContextShoppingCart|null $orderContextShoppingCart
	 */
	public function setOrderContextShoppingCart( ?\MoneticoDemoWebKit\Monetico\Request\OrderContextShoppingCart $orderContextShoppingCart ): void {
		$this->orderContextShoppingCart = $orderContextShoppingCart;
	}
}

?>