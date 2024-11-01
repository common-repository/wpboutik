<?php

namespace MoneticoDemoWebKit\Monetico\Request;

class OrderContextShipping implements \JsonSerializable {
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
	private $phone;

	/**
	 * @var ?string
	 */
	private $shipIndicator;

	/**
	 * @var ?string
	 */
	private $deliveryTimeframe;

	/**
	 * @var ?\DateTime
	 */
	private $firstUseDate;

	/**
	 * @var ?bool
	 */
	private $matchBillingAddress;


	public function jsonSerialize(): mixed {
		return array_filter( [
			"civility"            => $this->getCivility(),
			"name"                => $this->getName(),
			"firstName"           => $this->getFirstName(),
			"lastName"            => $this->getLastName(),
			"address"             => $this->getAddress(),
			"addressLine1"        => $this->getAddressLine1(),
			"addressLine2"        => $this->getAddressLine2(),
			"addressLine3"        => $this->getAddressLine3(),
			"city"                => $this->getCity(),
			"postalCode"          => $this->getPostalCode(),
			"country"             => $this->getCountry(),
			"stateOrProvince"     => $this->getStateOrProvince(),
			"countrySubdivision"  => $this->getCountrySubdivision(),
			"email"               => $this->getEmail(),
			"phone"               => $this->getPhone(),
			"shipIndicator"       => $this->getShipIndicator(),
			"deliveryTimeframe"   => $this->getDeliveryTimeframe(),
			"firstUseDate"        => $this->getFormatFirstUseDate(),
			"matchBillingAddress" => $this->getMatchBillingAddress()
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
	public function getShipIndicator(): ?string {
		return $this->shipIndicator;
	}

	/**
	 * @param string|null $shipIndicator
	 */
	public function setShipIndicator( ?string $shipIndicator ): void {
		$this->shipIndicator = $shipIndicator;
	}

	/**
	 * @return string|null
	 */
	public function getDeliveryTimeframe(): ?string {
		return $this->deliveryTimeframe;
	}

	/**
	 * @param string|null $deliveryTimeframe
	 */
	public function setDeliveryTimeframe( ?string $deliveryTimeframe ): void {
		$this->deliveryTimeframe = $deliveryTimeframe;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getFirstUseDate(): ?\DateTime {
		return $this->firstUseDate;
	}

	/**
	 * @return string|null
	 */
	public function getFormatFirstUseDate(): ?string {
		if ( $this->getFirstUseDate() instanceof \DateTime ) {
			return $this->getFirstUseDate()->format( "Y-m-d" );
		}

		return null;
	}

	/**
	 * @param \DateTime|null $firstUseDate
	 */
	public function setFirstUseDate( ?\DateTime $firstUseDate ): void {
		$this->firstUseDate = $firstUseDate;
	}

	/**
	 * @return bool|null
	 */
	public function getMatchBillingAddress(): ?bool {
		return $this->matchBillingAddress;
	}

	/**
	 * @param bool|null $matchBillingAddress
	 */
	public function setMatchBillingAddress( ?bool $matchBillingAddress ): void {
		$this->matchBillingAddress = $matchBillingAddress;
	}


}