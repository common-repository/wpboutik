<?php

namespace MoneticoDemoWebKit\Monetico\Request;

class OrderContextClient implements \JsonSerializable {
	/**
	 * @var ?string
	 */
	private $civility;

	/**
	 * @var ?string
	 */
	private $name;

	/**
	 * @var ?string
	 */
	private $firstName;

	/**
	 * @var ?string
	 */
	private $lastName;

	/**
	 * @var ?string
	 */
	private $middleName;

	/**
	 * @var ?string
	 */
	private $address;

	/**
	 * @var ?string
	 */
	private $addressLine1;

	/**
	 * @var ?string
	 */
	private $addressLine2;

	/**
	 * @var ?string
	 */
	private $addressLine3;

	/**
	 * @var ?string
	 */
	private $city;

	/**
	 * @var ?string
	 */
	private $postalCode;

	/**
	 * @var ?string
	 */
	private $country;

	/**
	 * @var ?string
	 */
	private $stateOrProvince;

	/**
	 * @var ?string
	 */
	private $countrySubdivision;

	/**
	 * @var ?string
	 */
	private $email;

	/**
	 * @var ?string
	 */
	private $birthLastName;

	/**
	 * @var ?string
	 */
	private $birthCity;

	/**
	 * @var ?string
	 */
	private $birthPostalCode;

	/**
	 * @var ?string
	 */
	private $birthCountry;

	/**
	 * @var ?string
	 */
	private $birthStateOrProvince;

	/**
	 * @var ?string
	 */
	private $birthCountrySubdivision;

	/**
	 * @var ?\DateTime
	 */
	private $birthdate;

	/**
	 * @var ?string
	 */
	private $phone;

	/**
	 * @var ?string
	 */
	private $nationalIDNumber;

	/**
	 * @var ?bool
	 */
	private $suspiciousAccountActivity;

	/**
	 * @var ?string
	 */
	private $authenticationMethod;

	/**
	 * @var ?\DateTime
	 */
	private $authenticationTimestamp;

	/**
	 * @var ?string
	 */
	private $priorAuthenticationMethod;

	/**
	 * @var ?\DateTime
	 */
	private $priorAuthenticationTimestamp;

	/**
	 * @var ?\DateTime
	 */
	private $paymentMeanAge;

	/**
	 * @var ?int
	 */
	private $lastYearTransactions;

	/**
	 * @var ?int
	 */
	private $last24HoursTransactions;

	/**
	 * @var ?int
	 */
	private $addCardNbLast24Hours;

	/**
	 * @var ?int
	 */
	private $last6MonthsPurchase;

	/**
	 * @var ?\DateTime
	 */
	private $lastPasswordChange;

	/**
	 * @var ?\DateTime
	 */
	private $accountAge;

	/**
	 * @var ?\DateTime
	 */
	private $lastAccountModification;

	public function jsonSerialize(): mixed {
		return array_filter( [
			"civility"                     => $this->getCivility(),
			"name"                         => $this->getName(),
			"firstName"                    => $this->getFirstName(),
			"lastName"                     => $this->getLastName(),
			"middleName"                   => $this->getMiddleName(),
			"address"                      => $this->getAddress(),
			"addressLine1"                 => $this->getAddressLine1(),
			"addressLine2"                 => $this->getAddressLine2(),
			"addressLine3"                 => $this->getAddressLine3(),
			"city"                         => $this->getCity(),
			"postalCode"                   => $this->getPostalCode(),
			"country"                      => $this->getCountry(),
			"stateOrProvince"              => $this->getStateOrProvince(),
			"countrySubdivision"           => $this->getCountrySubdivision(),
			"email"                        => $this->getEmail(),
			"birthLastName"                => $this->getBirthLastName(),
			"birthCity"                    => $this->getBirthCity(),
			"birthPostalCode"              => $this->getBirthPostalCode(),
			"birthCountry"                 => $this->getBirthCountry(),
			"birthStateOrProvince"         => $this->getBirthStateOrProvince(),
			"birthCountrySubdivision"      => $this->getBirthCountrySubdivision(),
			"birthdate"                    => $this->getFormatedBirthdate(),
			"phone"                        => $this->getPhone(),
			"nationalIDNumber"             => $this->getNationalIDNumber(),
			"suspiciousAccountActivity"    => $this->getSuspiciousAccountActivity(),
			"authenticationMethod"         => $this->getAuthenticationMethod(),
			"authenticationTimestamp"      => $this->getFormatedAuthenticationTimestamp(),
			"priorAuthenticationMethod"    => $this->getPriorAuthenticationMethod(),
			"priorAuthenticationTimestamp" => $this->getFormatedPriorAuthenticationTimestamp(),
			"paymentMeanAge"               => $this->getFormatedPaymentMeanAge(),
			"lastYearTransactions"         => $this->getLastYearTransactions(),
			"last24HoursTransactions"      => $this->getLast24HoursTransactions(),
			"addCardNbLast24Hours"         => $this->getAddCardNbLast24Hours(),
			"last6MonthsPurchase"          => $this->getLast6MonthsPurchase(),
			"lastPasswordChange"           => $this->getFormatedLastPasswordChange(),
			"accountAge"                   => $this->getFormatedAccountAge(),
			"lastAccountModification"      => $this->getFormatedLastAccountModification()
		], function ( $value ) {
			return ! is_null( $value );
		} );
	}

	/**
	 * @return string|null
	 */
	public function getCivility(): ?string {
		return $this->civility;
	}

	/**
	 * @param string|null $civility
	 */
	public function setCivility( ?string $civility ): void {
		$this->civility = $civility;
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
	public function getFirstName(): ?string {
		return $this->firstName;
	}

	/**
	 * @param string|null $firstName
	 */
	public function setFirstName( ?string $firstName ): void {
		$this->firstName = $firstName;
	}

	/**
	 * @return string|null
	 */
	public function getLastName(): ?string {
		return $this->lastName;
	}

	/**
	 * @param string|null $lastName
	 */
	public function setLastName( ?string $lastName ): void {
		$this->lastName = $lastName;
	}

	/**
	 * @return string|null
	 */
	public function getMiddleName(): ?string {
		return $this->middleName;
	}

	/**
	 * @param string|null $middleName
	 */
	public function setMiddleName( ?string $middleName ): void {
		$this->middleName = $middleName;
	}

	/**
	 * @return string|null
	 */
	public function getAddress(): ?string {
		return $this->address;
	}

	/**
	 * @param string|null $address
	 */
	public function setAddress( ?string $address ): void {
		$this->address = $address;
	}

	/**
	 * @return string|null
	 */
	public function getAddressLine1(): ?string {
		return $this->addressLine1;
	}

	/**
	 * @param string|null $addressLine1
	 */
	public function setAddressLine1( ?string $addressLine1 ): void {
		$this->addressLine1 = $addressLine1;
	}

	/**
	 * @return string|null
	 */
	public function getAddressLine2(): ?string {
		return $this->addressLine2;
	}

	/**
	 * @param string|null $addressLine2
	 */
	public function setAddressLine2( ?string $addressLine2 ): void {
		$this->addressLine2 = $addressLine2;
	}

	/**
	 * @return string|null
	 */
	public function getAddressLine3(): ?string {
		return $this->addressLine3;
	}

	/**
	 * @param string|null $addressLine3
	 */
	public function setAddressLine3( ?string $addressLine3 ): void {
		$this->addressLine3 = $addressLine3;
	}

	/**
	 * @return string|null
	 */
	public function getCity(): ?string {
		return $this->city;
	}

	/**
	 * @param string|null $city
	 */
	public function setCity( ?string $city ): void {
		$this->city = $city;
	}

	/**
	 * @return string|null
	 */
	public function getPostalCode(): ?string {
		return $this->postalCode;
	}

	/**
	 * @param string|null $postalCode
	 */
	public function setPostalCode( ?string $postalCode ): void {
		$this->postalCode = $postalCode;
	}

	/**
	 * @return string|null
	 */
	public function getCountry(): ?string {
		return $this->country;
	}

	/**
	 * @param string|null $country
	 */
	public function setCountry( ?string $country ): void {
		$this->country = $country;
	}

	/**
	 * @return string|null
	 */
	public function getStateOrProvince(): ?string {
		return $this->stateOrProvince;
	}

	/**
	 * @param string|null $stateOrProvince
	 */
	public function setStateOrProvince( ?string $stateOrProvince ): void {
		$this->stateOrProvince = $stateOrProvince;
	}

	/**
	 * @return string|null
	 */
	public function getCountrySubdivision(): ?string {
		return $this->countrySubdivision;
	}

	/**
	 * @param string|null $countrySubdivision
	 */
	public function setCountrySubdivision( ?string $countrySubdivision ): void {
		$this->countrySubdivision = $countrySubdivision;
	}

	/**
	 * @return string|null
	 */
	public function getEmail(): ?string {
		return $this->email;
	}

	/**
	 * @param string|null $email
	 */
	public function setEmail( ?string $email ): void {
		$this->email = $email;
	}

	/**
	 * @return string|null
	 */
	public function getBirthLastName(): ?string {
		return $this->birthLastName;
	}

	/**
	 * @param string|null $birthLastName
	 */
	public function setBirthLastName( ?string $birthLastName ): void {
		$this->birthLastName = $birthLastName;
	}

	/**
	 * @return string|null
	 */
	public function getBirthCity(): ?string {
		return $this->birthCity;
	}

	/**
	 * @param string|null $birthCity
	 */
	public function setBirthCity( ?string $birthCity ): void {
		$this->birthCity = $birthCity;
	}

	/**
	 * @return string|null
	 */
	public function getBirthPostalCode(): ?string {
		return $this->birthPostalCode;
	}

	/**
	 * @param string|null $birthPostalCode
	 */
	public function setBirthPostalCode( ?string $birthPostalCode ): void {
		$this->birthPostalCode = $birthPostalCode;
	}

	/**
	 * @return string|null
	 */
	public function getBirthCountry(): ?string {
		return $this->birthCountry;
	}

	/**
	 * @param string|null $birthCountry
	 */
	public function setBirthCountry( ?string $birthCountry ): void {
		$this->birthCountry = $birthCountry;
	}

	/**
	 * @return string|null
	 */
	public function getBirthStateOrProvince(): ?string {
		return $this->birthStateOrProvince;
	}

	/**
	 * @param string|null $birthStateOrProvince
	 */
	public function setBirthStateOrProvince( ?string $birthStateOrProvince ): void {
		$this->birthStateOrProvince = $birthStateOrProvince;
	}

	/**
	 * @return string|null
	 */
	public function getBirthCountrySubdivision(): ?string {
		return $this->birthCountrySubdivision;
	}

	/**
	 * @param string|null $birthCountrySubdivision
	 */
	public function setBirthCountrySubdivision( ?string $birthCountrySubdivision ): void {
		$this->birthCountrySubdivision = $birthCountrySubdivision;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getBirthdate(): ?\DateTime {
		return $this->birthdate;
	}

	/**
	 * @return string|null
	 */
	public function getFormatedBirthdate(): ?string {
		if ( $this->getBirthdate() instanceof \DateTime ) {
			return $this->getBirthdate()->format( "Y-m-d" );
		}

		return null;
	}

	/**
	 * @param \DateTime|null $birthdate
	 */
	public function setBirthdate( ?\DateTime $birthdate ): void {
		$this->birthdate = $birthdate;
	}

	/**
	 * @return string|null
	 */
	public function getPhone(): ?string {
		return $this->phone;
	}

	/**
	 * @param string|null $phone
	 */
	public function setPhone( ?string $phone ): void {
		$this->phone = $phone;
	}

	/**
	 * @return string|null
	 */
	public function getNationalIDNumber(): ?string {
		return $this->nationalIDNumber;
	}

	/**
	 * @param string|null $nationalIDNumber
	 */
	public function setNationalIDNumber( ?string $nationalIDNumber ): void {
		$this->nationalIDNumber = $nationalIDNumber;
	}

	/**
	 * @return bool|null
	 */
	public function getSuspiciousAccountActivity(): ?bool {
		return $this->suspiciousAccountActivity;
	}

	/**
	 * @param bool|null $suspiciousAccountActivity
	 */
	public function setSuspiciousAccountActivity( ?bool $suspiciousAccountActivity ): void {
		$this->suspiciousAccountActivity = $suspiciousAccountActivity;
	}

	/**
	 * @return string|null
	 */
	public function getAuthenticationMethod(): ?string {
		return $this->authenticationMethod;
	}

	/**
	 * @param string|null $authenticationMethod
	 */
	public function setAuthenticationMethod( ?string $authenticationMethod ): void {
		$this->authenticationMethod = $authenticationMethod;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getAuthenticationTimestamp(): ?\DateTime {
		return $this->authenticationTimestamp;
	}

	/**
	 * @return string|null
	 */
	public function getFormatedAuthenticationTimestamp(): ?string {
		if ( $this->getAuthenticationTimestamp() instanceof \DateTime ) {
			return $this->getAuthenticationTimestamp()->format( "Y-m-d\TH:i:s\Z" );
		}

		return null;
	}

	/**
	 * @param \DateTime|null $authenticationTimestamp
	 */
	public function setAuthenticationTimestamp( ?\DateTime $authenticationTimestamp ): void {
		$this->authenticationTimestamp = $authenticationTimestamp;
	}

	/**
	 * @return string|null
	 */
	public function getPriorAuthenticationMethod(): ?string {
		return $this->priorAuthenticationMethod;
	}

	/**
	 * @param string|null $priorAuthenticationMethod
	 */
	public function setPriorAuthenticationMethod( ?string $priorAuthenticationMethod ): void {
		$this->priorAuthenticationMethod = $priorAuthenticationMethod;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getPriorAuthenticationTimestamp(): ?\DateTime {
		return $this->priorAuthenticationTimestamp;
	}

	/**
	 * @return string|null
	 */
	public function getFormatedPriorAuthenticationTimestamp(): ?string {
		if ( $this->getPriorAuthenticationTimestamp() instanceof \DateTime ) {
			return $this->getPriorAuthenticationTimestamp()->format( "Y-m-d\TH:i:s\Z" );
		}

		return null;
	}

	/**
	 * @param \DateTime|null $priorAuthenticationTimestamp
	 */
	public function setPriorAuthenticationTimestamp( ?\DateTime $priorAuthenticationTimestamp ): void {
		$this->priorAuthenticationTimestamp = $priorAuthenticationTimestamp;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getPaymentMeanAge(): ?\DateTime {
		return $this->paymentMeanAge;
	}

	/**
	 * @return string|null
	 */
	public function getFormatedPaymentMeanAge(): ?string {
		if ( $this->getPaymentMeanAge() instanceof \DateTime ) {
			return $this->getPaymentMeanAge()->format( "Y-m-d" );
		}

		return null;
	}

	/**
	 * @param \DateTime|null $paymentMeanAge
	 */
	public function setPaymentMeanAge( ?\DateTime $paymentMeanAge ): void {
		$this->paymentMeanAge = $paymentMeanAge;
	}

	/**
	 * @return int|null
	 */
	public function getLastYearTransactions(): ?int {
		return $this->lastYearTransactions;
	}

	/**
	 * @param int|null $lastYearTransactions
	 */
	public function setLastYearTransactions( ?int $lastYearTransactions ): void {
		$this->lastYearTransactions = $lastYearTransactions;
	}

	/**
	 * @return int|null
	 */
	public function getLast24HoursTransactions(): ?int {
		return $this->last24HoursTransactions;
	}

	/**
	 * @param int|null $last24HoursTransactions
	 */
	public function setLast24HoursTransactions( ?int $last24HoursTransactions ): void {
		$this->last24HoursTransactions = $last24HoursTransactions;
	}

	/**
	 * @return int|null
	 */
	public function getAddCardNbLast24Hours(): ?int {
		return $this->addCardNbLast24Hours;
	}

	/**
	 * @param int|null $addCardNbLast24Hours
	 */
	public function setAddCardNbLast24Hours( ?int $addCardNbLast24Hours ): void {
		$this->addCardNbLast24Hours = $addCardNbLast24Hours;
	}

	/**
	 * @return int|null
	 */
	public function getLast6MonthsPurchase(): ?int {
		return $this->last6MonthsPurchase;
	}

	/**
	 * @param int|null $last6MonthsPurchase
	 */
	public function setLast6MonthsPurchase( ?int $last6MonthsPurchase ): void {
		$this->last6MonthsPurchase = $last6MonthsPurchase;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getLastPasswordChange(): ?\DateTime {
		return $this->lastPasswordChange;
	}

	/**
	 * @return string|null
	 */
	public function getFormatedLastPasswordChange(): ?string {
		if ( $this->getLastPasswordChange() instanceof \DateTime ) {
			return $this->getLastPasswordChange()->format( "Y-m-d" );
		}

		return null;
	}

	/**
	 * @param \DateTime|null $lastPasswordChange
	 */
	public function setLastPasswordChange( ?\DateTime $lastPasswordChange ): void {
		$this->lastPasswordChange = $lastPasswordChange;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getAccountAge(): ?\DateTime {
		return $this->accountAge;
	}

	/**
	 * @return string|null
	 */
	public function getFormatedAccountAge(): ?string {
		if ( $this->getAccountAge() instanceof \DateTime ) {
			return $this->getAccountAge()->format( "Y-m-d" );
		}

		return null;
	}

	/**
	 * @param \DateTime|null $accountAge
	 */
	public function setAccountAge( ?\DateTime $accountAge ): void {
		$this->accountAge = $accountAge;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getLastAccountModification(): ?\DateTime {
		return $this->lastAccountModification;
	}

	/**
	 * @return string|null
	 */
	public function getFormatedLastAccountModification(): ?string {
		if ( $this->getLastAccountModification() instanceof \DateTime ) {
			return $this->getLastAccountModification()->format( "Y-m-d" );
		}

		return null;
	}

	/**
	 * @param \DateTime|null $lastAccountModification
	 */
	public function setLastAccountModification( ?\DateTime $lastAccountModification ): void {
		$this->lastAccountModification = $lastAccountModification;
	}
}