<?php

namespace MoneticoDemoWebKit\Monetico\Request;

use MoneticoDemoWebKit\Monetico\Collections\Currency;
use MoneticoDemoWebKit\Monetico\Collections\Language;
use MoneticoDemoWebKit\Monetico\Collections\ThreeDSecureChallengeValue;
use MoneticoDemoWebKit\Monetico\Collections\PaymentProtocol;
use MoneticoDemoWebKit\Monetico\HmacComputer;

/**
 *  Represents a payment request to the Monetico Payment page.
 *  This object let you initialize a new payment request and allows you to retrieve all the HTML form
 *  fields to be included through the @see getFormFields().
 *  If you are using an EPT with split payment mode, use @see SplitPaymentRequest instead
 *  or @see PreAuthorizedPaymentRequest for pre authorized payment.
 */
class PaymentRequest {
	/**
	 * @var string
	 * Reference of the payment
	 */
	private $reference;

	/**
	 * @var \DateTime
	 * Date of the payment
	 */
	private $date;

	/**
	 * @var float
	 * Amount of the payment
	 */
	private $montant;

	/**
	 * @var string
	 * Currency of the payment
	 * Must be a string from the Currency class
	 */
	private $devise;

	/**
	 * @var string
	 * Display language of the payment page
	 * Must be a string from the Language class
	 */
	private $langue;

	/**
	 * @var OrderContext
	 * Order context
	 */
	private $contexteCommande;

	/**
	 * @var ?string
	 * Free text associated with this payment.
	 * Free text allows you to associate any text that you wish to see on your dashboard when viewing this payment.
	 *
	 * Careful : The content of this field may be blocked for security reasons.
	 * Therefore, it is recommended to avoid special characters and line feeds.
	 */
	private $texteLibre;

	/**
	 * @var ?string
	 * Client e-mail address
	 */
	private $mail;

	/**
	 * @var ?string
	 * URL of the page that will be used by Monetico payment page to redirect the client if the payment has been done successfully
	 */
	private $urlRetourOk;

	/**
	 * @var ?string
	 * URL of the page that will be used by Monetico payment page to redirect the client if the payment has failed
	 */
	private $urlRetourErreur;

	/**
	 * @var ?bool
	 * Indicates if the merchant wish not to authenticate the client (optional parameter)
	 */
	private $threeDSecureDebrayable;

	/**
	 * @var ?string
	 * Indicates the merchant wish about client authentication in 3DSecure V2 protocol (optional parameter)
	 *
	 * Must be a string from the ThreeDSecureChallengeValue class
	 */
	private $threeDSecureChallenge;

	/**
	 * @var ?string
	 * Label that will appear on the card holder's bank statement (optional parameter)
	 */
	private $libelleMonetique;

	/**
	 * @var ?string
	 * Location/city that will appear on the card holder's bank statement (optional parameter)
	 */
	private $libelleMoneticoLocalite;

	/**
	 * @var ?string[]
	 * Indicates the alternate payment mode that the payment page must not display for this payment (optional parameter)
	 *
	 * Must contain string from the PaymentProtocol class
	 */
	private $desactiveMoyenPaiement;

	/**
	 * @var ?string
	 * Alias of the client bank card to use (optional parameter) (requires subscription to the "express payment" option)
	 */
	private $aliasCb;

	/**
	 * @var ?bool
	 * Forces the client to input a bank card (optional parameter) (requires subscription to the "express payment" option)
	 */
	private $forceSaisieCb;

	/**
	 * @var ?string
	 * Indicates to the payment page that this payment has to be proceed using selected protocol.
	 * The client will be immediately redirected to the partner protocol page and will not see the Monetico payment page itself.
	 * (optional parameter) (requires subscription to a partner payment protocol)
	 *
	 * Must contain string from the PaymentProtocol class
	 */
	private $protocole;

	/**
	 * @var ?CofidisPaymentInformations
	 * Fields specific to Cofidis allowing to pre fill the request form on Cofidis site
	 */
	private $cofidis;

	public function __construct( string $reference, float $montant, string $devise, string $language, OrderContext $contexteCommande ) {
		$this->setReference( $reference );
		$this->setMontant( $montant );
		$this->setLangue( $language );
		$this->setDevise( $devise );
		$this->setContexteCommande( $contexteCommande );
		$this->setDate( new \DateTime( 'now' ) );
	}

	public function getFormFields() {
		$options           = get_option( 'wpboutik_options_params' );
		$formFields        = $this->getFormFieldsWithoutMac();
		$hmacComputer      = new HmacComputer();
		$seal              = $hmacComputer->sealFields( $formFields, $options['monetico_cle_mac'] );
		$formFields['MAC'] = $seal;

		return $formFields;
	}

	public function getFormFieldsWithoutMac() {
		$options = get_option( 'wpboutik_options_params' );

		$formFields = [];
		// Payment terminal
		$formFields["TPE"]     = $options['monetico_tpe'];
		$formFields["societe"] = $options['monetico_code_societe'];
		$formFields["lgue"]    = $this->getLangue();
		$formFields["version"] = "3.0";

		// Payment informations
		$formFields["reference"]         = $this->getReference();
		$formFields["date"]              = $this->getDate()->format( "d/m/Y:H:i:s" );
		$formFields["montant"]           = $this->formatAmount( $this->getMontant(), $this->getDevise() );
		$formFields["contexte_commande"] = base64_encode( utf8_encode( json_encode( $this->getContexteCommande() ) ) );

		// Optional parameters
		if ( is_string( $this->getTexteLibre() ) ) {
			$formFields["texte-libre"] = $this->getTexteLibre();
		}

		if ( is_string( $this->getMail() ) ) {
			$formFields["mail"] = $this->getMail();
		}

		if ( is_string( $this->getUrlRetourOk() ) ) {
			$formFields["url_retour_ok"] = $this->getUrlRetourOk();
		}

		if ( is_string( $this->getUrlRetourErreur() ) ) {
			$formFields["url_retour_err"] = $this->getUrlRetourErreur();
		}

		if ( ! is_null( $this->getThreeDSecureDebrayable() ) ) {
			$formFields["3dsdebrayable"] = $this->getThreeDSecureDebrayable() ? "1" : "0";
		}

		if ( is_string( $this->getThreeDSecureChallenge() ) ) {
			$formFields["ThreeDSecureChallenge"] = $this->getThreeDSecureChallenge();
		}

		if ( is_string( $this->getLibelleMonetique() ) ) {
			$formFields["libelleMonetique"] = $this->getLibelleMonetique();
		}

		if ( is_string( $this->getLibelleMoneticoLocalite() ) ) {
			$formFields["libelleMonetiqueLocalite"] = $this->getLibelleMoneticoLocalite();
		}

		if ( $this->getDesactiveMoyenPaiement() != null && ! empty( $this->getDesactiveMoyenPaiement() ) ) {
			$formFields["desactivemoyenpaiement"] = implode( ',', $this->getDesactiveMoyenPaiement() );
		}

		if ( is_string( $this->getAliasCb() ) ) {
			$formFields["aliascb"] = $this->getAliasCb();
		}

		if ( ! is_null( $this->getForceSaisieCb() ) ) {
			$formFields["forcesaisiecb"] = $this->getForceSaisieCb() ? "1" : "0";
		}

		if ( is_string( $this->getProtocole() ) ) {
			$formFields["protocole"] = $this->getProtocole();
		}

		// Cofidis partner optional fields
		if ( ! is_null( $this->getCofidis() ) ) {
			$cofidisFields = $this->getCofidis()->getFormFields();
			if ( ! empty( $cofidisFields ) ) {
				foreach ( $cofidisFields as $key => $value ) {
					$formFields[ $key ] = $value;
				}
			}
		}

		return $formFields;
	}

	public function formatAmount( $amount, $devise ) {
		return $amount . $devise;
	}

	/**
	 * @return string
	 */
	public function getReference(): string {
		return $this->reference;
	}

	/**
	 * @param string $reference
	 */
	public function setReference( string $reference ): void {
		$this->reference = $reference;
	}

	/**
	 * @return \DateTime
	 */
	public function getDate(): \DateTime {
		return $this->date;
	}

	/**
	 * @param \DateTime $date
	 */
	public function setDate( \DateTime $date ): void {
		$this->date = $date;
	}

	/**
	 * @return float
	 */
	public function getMontant(): float {
		return $this->montant;
	}

	/**
	 * @param float $montant
	 */
	public function setMontant( float $montant ): void {
		$this->montant = $montant;
	}

	/**
	 * @return string
	 */
	public function getDevise(): string {
		return $this->devise;
	}

	/**
	 * @param string $devise
	 */
	public function setDevise( string $devise ): void {
		$this->devise = $devise;
	}

	/**
	 * @return string
	 */
	public function getLangue(): string {
		return $this->langue;
	}

	/**
	 * @param string $langue
	 */
	public function setLangue( string $langue ): void {
		$this->langue = $langue;
	}

	/**
	 * @return \MoneticoDemoWebKit\Monetico\Request\OrderContext
	 */
	public function getContexteCommande(): \MoneticoDemoWebKit\Monetico\Request\OrderContext {
		return $this->contexteCommande;
	}

	/**
	 * @param \MoneticoDemoWebKit\Monetico\Request\OrderContext $contexteCommande
	 */
	public function setContexteCommande( \MoneticoDemoWebKit\Monetico\Request\OrderContext $contexteCommande ): void {
		$this->contexteCommande = $contexteCommande;
	}

	/**
	 * @return string|null
	 */
	public function getTexteLibre(): ?string {
		return $this->texteLibre;
	}

	/**
	 * @param string|null $texteLibre
	 */
	public function setTexteLibre( ?string $texteLibre ): void {
		$this->texteLibre = $texteLibre;
	}

	/**
	 * @return string|null
	 */
	public function getMail(): ?string {
		return $this->mail;
	}

	/**
	 * @param string|null $mail
	 */
	public function setMail( ?string $mail ): void {
		$this->mail = $mail;
	}

	/**
	 * @return string|null
	 */
	public function getUrlRetourOk(): ?string {
		return $this->urlRetourOk;
	}

	/**
	 * @param string|null $urlRetourOk
	 */
	public function setUrlRetourOk( ?string $urlRetourOk ): void {
		$this->urlRetourOk = $urlRetourOk;
	}

	/**
	 * @return string|null
	 */
	public function getUrlRetourErreur(): ?string {
		return $this->urlRetourErreur;
	}

	/**
	 * @param string|null $urlRetourErreur
	 */
	public function setUrlRetourErreur( ?string $urlRetourErreur ): void {
		$this->urlRetourErreur = $urlRetourErreur;
	}

	/**
	 * @return bool|null
	 */
	public function getThreeDSecureDebrayable(): ?bool {
		return $this->threeDSecureDebrayable;
	}

	/**
	 * @param bool|null $threeDSecureDebrayable
	 */
	public function setThreeDSecureDebrayable( ?bool $threeDSecureDebrayable ): void {
		$this->threeDSecureDebrayable = $threeDSecureDebrayable;
	}

	/**
	 * @return string|null
	 */
	public function getThreeDSecureChallenge(): ?string {
		return $this->threeDSecureChallenge;
	}

	/**
	 * @param string|null $threeDSecureChallenge
	 */
	public function setThreeDSecureChallenge( ?string $threeDSecureChallenge ): void {
		$this->threeDSecureChallenge = $threeDSecureChallenge;
	}

	/**
	 * @return string|null
	 */
	public function getLibelleMonetique(): ?string {
		return $this->libelleMonetique;
	}

	/**
	 * @param string|null $libelleMonetique
	 */
	public function setLibelleMonetique( ?string $libelleMonetique ): void {
		$this->libelleMonetique = $libelleMonetique;
	}

	/**
	 * @return string|null
	 */
	public function getLibelleMoneticoLocalite(): ?string {
		return $this->libelleMoneticoLocalite;
	}

	/**
	 * @param string|null $libelleMoneticoLocalite
	 */
	public function setLibelleMoneticoLocalite( ?string $libelleMoneticoLocalite ): void {
		$this->libelleMoneticoLocalite = $libelleMoneticoLocalite;
	}

	/**
	 * @return string[]|null
	 */
	public function getDesactiveMoyenPaiement(): ?array {
		return $this->desactiveMoyenPaiement;
	}

	/**
	 * @param string[]|null $desactiveMoyenPaiement
	 */
	public function setDesactiveMoyenPaiement( ?array $desactiveMoyenPaiement ): void {
		$this->desactiveMoyenPaiement = $desactiveMoyenPaiement;
	}

	/**
	 * @return string|null
	 */
	public function getAliasCb(): ?string {
		return $this->aliasCb;
	}

	/**
	 * @param string|null $aliasCb
	 */
	public function setAliasCb( ?string $aliasCb ): void {
		$this->aliasCb = $aliasCb;
	}

	/**
	 * @return bool|null
	 */
	public function getForceSaisieCb(): ?bool {
		return $this->forceSaisieCb;
	}

	/**
	 * @param bool|null $forceSaisieCb
	 */
	public function setForceSaisieCb( ?bool $forceSaisieCb ): void {
		$this->forceSaisieCb = $forceSaisieCb;
	}

	/**
	 * @return string|null
	 */
	public function getProtocole(): ?string {
		return $this->protocole;
	}

	/**
	 * @param string|null $protocole
	 */
	public function setProtocole( ?string $protocole ): void {
		$this->protocole = $protocole;
	}

	/**
	 * @return \MoneticoDemoWebKit\Monetico\Request\CofidisPaymentInformations|null
	 */
	public function getCofidis(): ?\MoneticoDemoWebKit\Monetico\Request\CofidisPaymentInformations {
		return $this->cofidis;
	}

	/**
	 * @param \MoneticoDemoWebKit\Monetico\Request\CofidisPaymentInformations|null $cofidis
	 */
	public function setCofidis( ?\MoneticoDemoWebKit\Monetico\Request\CofidisPaymentInformations $cofidis ): void {
		$this->cofidis = $cofidis;
	}
}