<?php

namespace MoneticoDemoWebKit\Monetico\Request;

/**
 * Represents a pre-authorized payment request to the Monetico Payment page.
 * This object let you initialize a new pre-authorized payment request and allows you to retrieve
 * all the HTML form fields to be included through the method @see getFormFields().
 * This class must be used only if your EPT is using pre-authorization payment mode. In other cases,
 * use @see PaymentRequest for classic payment
 * or @see SplitPaymentRequest for split payment.
 */
class PreAuthorizedPayment extends PaymentRequest {
	/**
	 * Preauthorization dossier number
	 * @var ?string
	 */
	private $numeroDossier;

	public function __construct( string $reference, float $montant, string $devise, string $language, OrderContext $contexteCommande, string $numeroDossier ) {
		parent::__construct( $reference, $montant, $devise, $language, $contexteCommande );
		$this->setNumeroDossier( $numeroDossier );
	}

	public function getFormFieldsWithoutMac() {
		$formFields = parent::getFormFieldsWithoutMac();

		if ( ! is_null( $this->getNumeroDossier() ) ) {
			$formFields["numero_dossier"] = $this->getNumeroDossier();
		}

		return $formFields;
	}

	/**
	 * @return string|null
	 */
	public function getNumeroDossier(): ?string {
		return $this->numeroDossier;
	}

	/**
	 * @param string|null $numeroDossier
	 */
	public function setNumeroDossier( ?string $numeroDossier ): void {
		$this->numeroDossier = $numeroDossier;
	}
}