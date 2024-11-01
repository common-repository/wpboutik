<?php

namespace MoneticoDemoWebKit\Monetico\Request;

/**
 *  Represents additional informations that are specific to the COFIDIS partner.
 *  These values can be used only if your EPT has a COFIDIS payment mean activated (1euro, 3xCB, ...).
 *  You can use this to pre fill the request form on the COFIDIS website.
 */
class CofidisPaymentInformations {
	/**
	 * Civility of the customer
	 * @var ?string
	 */
	private $civiliteClient;

	/**
	 * Last name of the customer
	 * @var ?string
	 */
	private $nomClient;

	/**
	 * First name of the customer
	 * @var ?string
	 */
	private $prenomClient;

	/**
	 * Address of the customer
	 * @var ?string
	 */
	private $adresseClient;

	/**
	 * Additional address informations of the customer
	 * @var ?string
	 */
	private $complementAdresseClient;

	/**
	 * Zip code of the customer
	 * @var ?string
	 */
	private $codePostalClient;

	/**
	 * City of the customer
	 * @var ?string
	 */
	private $villeClient;

	/**
	 * Country of the customer
	 * @var ?string
	 */
	private $paysClient;

	/**
	 * Landline phone of the customer
	 * @var ?string
	 */
	private $telephoneFixeClient;

	/**
	 * Mobile phone of the customer
	 * @var ?string
	 */
	private $telephoneMobileClient;

	/**
	 * Customer’s geographic code of the entity of the country of birth
	 * @var ?string
	 */
	private $departementNaissanceClient;

	/**
	 * Birthdate of the customer
	 * @var ?\DateTime
	 */
	private $dateNaissanceClient;

	/**
	 * Cofidis pre-scoring
	 * @var ?int
	 */
	private $preScore;

	public function getFormFields() {
		$formFields = [];
		if ( ! is_null( $this->getCiviliteClient() ) ) {
			$formFields["civiliteclient"] = $this->getCiviliteClient();
		}

		if ( ! is_null( $this->getNomClient() ) ) {
			$formFields["nomclient"] = $this->getNomClient();
		}

		if ( ! is_null( $this->getPrenomClient() ) ) {
			$formFields["prenomclient"] = $this->getPrenomClient();
		}

		if ( ! is_null( $this->getAdresseClient() ) ) {
			$formFields["adresseclient"] = $this->getAdresseClient();
		}

		if ( ! is_null( $this->getComplementAdresseClient() ) ) {
			$formFields["complementadresseclient"] = $this->getComplementAdresseClient();
		}

		if ( ! is_null( $this->getCodePostalClient() ) ) {
			$formFields["codepostalclient"] = $this->getCodePostalClient();
		}

		if ( ! is_null( $this->getVilleClient() ) ) {
			$formFields["villeclient"] = $this->getVilleClient();
		}

		if ( ! is_null( $this->getPaysClient() ) ) {
			$formFields["paysclient"] = $this->getPaysClient();
		}

		if ( ! is_null( $this->getTelephoneFixeClient() ) ) {
			$formFields["telephonefixeclient"] = $this->getTelephoneFixeClient();
		}

		if ( ! is_null( $this->getTelephoneMobileClient() ) ) {
			$formFields["telephonemobileclient"] = $this->getTelephoneMobileClient();
		}

		if ( ! is_null( $this->getDepartementNaissanceClient() ) ) {
			$formFields["departementnaissanceclient"] = $this->getDepartementNaissanceClient();
		}

		if ( ! is_null( $this->getDateNaissanceClient() ) ) {
			$formFields["datenaissanceclient"] = $this->getDateNaissanceClient()->format( "Ymd" );
		}

		if ( ! is_null( $this->getPreScore() ) ) {
			$formFields["prescore"] = $this->getPreScore();
		}

		array_walk( $formFields, function ( &$value, $key ) {
			$value = bin2hex( $value );
		} );

		return $formFields;
	}

	/**
	 * @return string|null
	 */
	public function getCiviliteClient(): ?string {
		return $this->civiliteClient;
	}

	/**
	 * @param string|null $civiliteClient
	 */
	public function setCiviliteClient( ?string $civiliteClient ): void {
		$this->civiliteClient = $civiliteClient;
	}

	/**
	 * @return string|null
	 */
	public function getNomClient(): ?string {
		return $this->nomClient;
	}

	/**
	 * @param string|null $nomClient
	 */
	public function setNomClient( ?string $nomClient ): void {
		$this->nomClient = $nomClient;
	}

	/**
	 * @return string|null
	 */
	public function getPrenomClient(): ?string {
		return $this->prenomClient;
	}

	/**
	 * @param string|null $prenomClient
	 */
	public function setPrenomClient( ?string $prenomClient ): void {
		$this->prenomClient = $prenomClient;
	}

	/**
	 * @return string|null
	 */
	public function getAdresseClient(): ?string {
		return $this->adresseClient;
	}

	/**
	 * @param string|null $adresseClient
	 */
	public function setAdresseClient( ?string $adresseClient ): void {
		$this->adresseClient = $adresseClient;
	}

	/**
	 * @return string|null
	 */
	public function getComplementAdresseClient(): ?string {
		return $this->complementAdresseClient;
	}

	/**
	 * @param string|null $complementAdresseClient
	 */
	public function setComplementAdresseClient( ?string $complementAdresseClient ): void {
		$this->complementAdresseClient = $complementAdresseClient;
	}

	/**
	 * @return string|null
	 */
	public function getCodePostalClient(): ?string {
		return $this->codePostalClient;
	}

	/**
	 * @param string|null $codePostalClient
	 */
	public function setCodePostalClient( ?string $codePostalClient ): void {
		$this->codePostalClient = $codePostalClient;
	}

	/**
	 * @return string|null
	 */
	public function getVilleClient(): ?string {
		return $this->villeClient;
	}

	/**
	 * @param string|null $villeClient
	 */
	public function setVilleClient( ?string $villeClient ): void {
		$this->villeClient = $villeClient;
	}

	/**
	 * @return string|null
	 */
	public function getPaysClient(): ?string {
		return $this->paysClient;
	}

	/**
	 * @param string|null $paysClient
	 */
	public function setPaysClient( ?string $paysClient ): void {
		$this->paysClient = $paysClient;
	}

	/**
	 * @return string|null
	 */
	public function getTelephoneFixeClient(): ?string {
		return $this->telephoneFixeClient;
	}

	/**
	 * @param string|null $telephoneFixeClient
	 */
	public function setTelephoneFixeClient( ?string $telephoneFixeClient ): void {
		$this->telephoneFixeClient = $telephoneFixeClient;
	}

	/**
	 * @return string|null
	 */
	public function getTelephoneMobileClient(): ?string {
		return $this->telephoneMobileClient;
	}

	/**
	 * @param string|null $telephoneMobileClient
	 */
	public function setTelephoneMobileClient( ?string $telephoneMobileClient ): void {
		$this->telephoneMobileClient = $telephoneMobileClient;
	}

	/**
	 * @return string|null
	 */
	public function getDepartementNaissanceClient(): ?string {
		return $this->departementNaissanceClient;
	}

	/**
	 * @param string|null $departementNaissanceClient
	 */
	public function setDepartementNaissanceClient( ?string $departementNaissanceClient ): void {
		$this->departementNaissanceClient = $departementNaissanceClient;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getDateNaissanceClient(): ?\DateTime {
		return $this->dateNaissanceClient;
	}

	/**
	 * @param \DateTime|null $dateNaissanceClient
	 */
	public function setDateNaissanceClient( ?\DateTime $dateNaissanceClient ): void {
		$this->dateNaissanceClient = $dateNaissanceClient;
	}

	/**
	 * @return int|null
	 */
	public function getPreScore(): ?int {
		return $this->preScore;
	}

	/**
	 * @param int|null $preScore
	 */
	public function setPreScore( ?int $preScore ): void {
		$this->preScore = $preScore;
	}
}

?>