<?php

namespace MoneticoDemoWebKit\Monetico\Request;

class OrderContextBilling implements \JsonSerializable {
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
	 * @var string
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
	 * @var string
	 */
	private $city;

	/**
	 * @var string
	 */
	private $postalCode;

	/**
	 * @var string
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
	private $mobilePhone;

	/**
	 * @var ?string
	 */
	private $homePhone;

	/**
	 * @var ?string
	 */
	private $workPhone;

	/**
	 * OrderContextBilling constructor.
	 *
	 * @param $addressLine1 string
	 * @param $city         string
	 * @param $postalCode   string
	 * @param $country      string
	 */
	public function __construct( $addressLine1, $city, $postalCode, $country ) {
		$this->setAddressLine1( $addressLine1 );
		$this->setCity( $city );
		$this->setPostalCode( $postalCode );
		$this->setCountry( $country );
	}

	public function jsonSerialize(): mixed {
		return array_filter( [
			"civility"           => $this->getCivility(),
			"name"               => $this->getName(),
			"firstName"          => $this->getFirstName(),
			"lastName"           => $this->getLastName(),
			"middleName"         => $this->getMiddleName(),
			"address"            => $this->getAddress(),
			"addressLine1"       => $this->getAddressLine1(),
			"addressLine2"       => $this->getAddressLine2(),
			"addressLine3"       => $this->getAddressLine3(),
			"city"               => $this->getCity(),
			"postalCode"         => $this->getPostalCode(),
			"country"            => $this->getCountry(),
			"stateOrProvince"    => $this->getStateOrProvince(),
			"countrySubdivision" => $this->getCountrySubdivision(),
			"email"              => $this->getEmail(),
			"phone"              => $this->getPhone(),
			"mobilePhone"        => $this->getMobilePhone(),
			"homePhone"          => $this->getHomePhone(),
			"workPhone"          => $this->getWorkPhone()
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
	 * @return string
	 */
	public function getAddressLine1(): string {
		return $this->addressLine1;
	}

	/**
	 * @param string $addressLine1
	 */
	public function setAddressLine1( string $addressLine1 ): void {
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
	 * @return string
	 */
	public function getCity(): string {
		return $this->city;
	}

	/**
	 * @param string $city
	 */
	public function setCity( string $city ): void {
		$this->city = $city;
	}

	/**
	 * @return string
	 */
	public function getPostalCode(): string {
		return $this->postalCode;
	}

	/**
	 * @param string $postalCode
	 */
	public function setPostalCode( string $postalCode ): void {
		$this->postalCode = $postalCode;
	}

	/**
	 * @return string
	 */
	public function getCountry(): string {
		return $this->country;
	}

	/**
	 * @param string $country
	 */
	public function setCountry( string $country ): void {
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
	public function getMobilePhone(): ?string {
		return $this->mobilePhone;
	}

	/**
	 * @param string|null $mobilePhone
	 */
	public function setMobilePhone( ?string $mobilePhone ): void {
		$this->mobilePhone = $mobilePhone;
	}

	/**
	 * @return string|null
	 */
	public function getHomePhone(): ?string {
		return $this->homePhone;
	}

	/**
	 * @param string|null $homePhone
	 */
	public function setHomePhone( ?string $homePhone ): void {
		$this->homePhone = $homePhone;
	}

	/**
	 * @return string|null
	 */
	public function getWorkPhone(): ?string {
		return $this->workPhone;
	}

	/**
	 * @param string|null $workPhone
	 */
	public function setWorkPhone( ?string $workPhone ): void {
		$this->workPhone = $workPhone;
	}


}

?>