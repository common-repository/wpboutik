<?php

namespace MoneticoDemoWebKit\Monetico\Request;

/**
 * Represents a split payment request to the Monetico Payment page.
 * This object let you initialize a new pre-authorized payment request and allows you to retrieve
 * all the HTML form fields to be included through the method <see cref="GetFormFields"/>.
 * This class must only be used if your EPT is using split payment mode. In other cases,
 * use @see PaymentRequest for classic payment
 * or @see PreAuthorizedPaymentRequest for pre authorized payment
 */
class SplitPaymentRequest extends PaymentRequest {
	/**
	 * @var ?int
	 * Numbers of instalments (2, 3 or 4) this payment will be divided into
	 */
	private $nbrEch;

	/**
	 * Date of the first instalment
	 * @var ?\DateTime
	 * The first instalment MUST be set to today's date
	 */
	private $dateEch1;

	/**
	 * @var ?\DateTime
	 * Date of the second instalment
	 */
	private $dateEch2;

	/**
	 * @var ?\DateTime
	 * Date of the third instalment
	 */
	private $dateEch3;

	/**
	 * @var ?\DateTime
	 * Date of the fourth instalment
	 */
	private $dateEch4;

	/**
	 * @var ?float
	 * Amount of the first instalment
	 */
	private $montantEch1;

	/**
	 * @var ?float
	 * Amount of the second instalment
	 */
	private $montantEch2;

	/**
	 * @var ?float
	 * Amount of the third instalment
	 */
	private $montantEch3;

	/**
	 * @var ?float
	 * Amount of the fourth instalment
	 */
	private $montantEch4;

	public function getFormFieldsWithoutMac() {
		$formFields = parent::getFormFieldsWithoutMac();

		if ( ! is_null( $this->getNbrEch() ) ) {
			$formFields["nbrech"] = $this->getNbrEch();
		}

		if ( ! is_null( $this->getDateEch1() ) ) {
			$formFields["dateech1"] = $this->getDateEch1()->format( "d/m/Y" );
		}

		if ( ! is_null( $this->getDateEch2() ) ) {
			$formFields["dateech2"] = $this->getDateEch2()->format( "d/m/Y" );
		}

		if ( ! is_null( $this->getDateEch3() ) ) {
			$formFields["dateech3"] = $this->getDateEch3()->format( "d/m/Y" );
		}

		if ( ! is_null( $this->getDateEch4() ) ) {
			$formFields["dateech4"] = $this->getDateEch4()->format( "d/m/Y" );
		}

		if ( ! is_null( $this->getMontantEch1() ) ) {
			$formFields["montantech1"] = $this->formatAmount( $this->getMontantEch1(), $this->getDevise() );
		}

		if ( ! is_null( $this->getMontantEch2() ) ) {
			$formFields["montantech2"] = $this->formatAmount( $this->getMontantEch2(), $this->getDevise() );
		}

		if ( ! is_null( $this->getMontantEch3() ) ) {
			$formFields["montantech3"] = $this->formatAmount( $this->getMontantEch3(), $this->getDevise() );
		}

		if ( ! is_null( $this->getMontantEch4() ) ) {
			$formFields["montantech4"] = $this->formatAmount( $this->getMontantEch4(), $this->getDevise() );
		}

		return $formFields;
	}

	/**
	 * @return int|null
	 */
	public function getNbrEch(): ?int {
		return $this->nbrEch;
	}

	/**
	 * @param int|null $nbrEch
	 */
	public function setNbrEch( ?int $nbrEch ): void {
		$this->nbrEch = $nbrEch;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getDateEch1(): ?\DateTime {
		return $this->dateEch1;
	}

	/**
	 * @param \DateTime|null $dateEch1
	 */
	public function setDateEch1( ?\DateTime $dateEch1 ): void {
		$this->dateEch1 = $dateEch1;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getDateEch2(): ?\DateTime {
		return $this->dateEch2;
	}

	/**
	 * @param \DateTime|null $dateEch2
	 */
	public function setDateEch2( ?\DateTime $dateEch2 ): void {
		$this->dateEch2 = $dateEch2;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getDateEch3(): ?\DateTime {
		return $this->dateEch3;
	}

	/**
	 * @param \DateTime|null $dateEch3
	 */
	public function setDateEch3( ?\DateTime $dateEch3 ): void {
		$this->dateEch3 = $dateEch3;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getDateEch4(): ?\DateTime {
		return $this->dateEch4;
	}

	/**
	 * @param \DateTime|null $dateEch4
	 */
	public function setDateEch4( ?\DateTime $dateEch4 ): void {
		$this->dateEch4 = $dateEch4;
	}

	/**
	 * @return float|null
	 */
	public function getMontantEch1(): ?float {
		return $this->montantEch1;
	}

	/**
	 * @param float|null $montantEch1
	 */
	public function setMontantEch1( ?float $montantEch1 ): void {
		$this->montantEch1 = $montantEch1;
	}

	/**
	 * @return float|null
	 */
	public function getMontantEch2(): ?float {
		return $this->montantEch2;
	}

	/**
	 * @param float|null $montantEch2
	 */
	public function setMontantEch2( ?float $montantEch2 ): void {
		$this->montantEch2 = $montantEch2;
	}

	/**
	 * @return float|null
	 */
	public function getMontantEch3(): ?float {
		return $this->montantEch3;
	}

	/**
	 * @param float|null $montantEch3
	 */
	public function setMontantEch3( ?float $montantEch3 ): void {
		$this->montantEch3 = $montantEch3;
	}

	/**
	 * @return float|null
	 */
	public function getMontantEch4(): ?float {
		return $this->montantEch4;
	}

	/**
	 * @param float|null $montantEch4
	 */
	public function setMontantEch4( ?float $montantEch4 ): void {
		$this->montantEch4 = $montantEch4;
	}
}