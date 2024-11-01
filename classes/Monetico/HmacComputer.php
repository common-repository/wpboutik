<?php

namespace MoneticoDemoWebKit\Monetico;

class HmacComputer {
	/**
	 * Returns a key that can be used for computing the seal.
	 * Some legacy keys were provided with non hexadecimal character. This methods converts these special
	 * characters to their correct hexadecimal representation.
	 *
	 * With legacy keys, the two last characters might be non hexadecimal.
	 * If it occurs, they have to be converted back to hexadecimal using the following rule :
	 * - Before last character : take the ASCII value and subtract 23 to have the real value (eg: 'P' has ASCII code 80 => 80-23 = 57 which is ASCII code for '9' character)
	 * - Last character : if it is an 'M' character, replace it with '0'
	 *
	 * @param string $key
	 *
	 * @return string
	 *
	 */

	public function sealFields( array $fields, string $key ) {
		$stringToSeal = $this->getStringToSeal( $fields );

		return $this->sealString( $stringToSeal, $this->getUsableKey( $key ) );
	}

	public function validateSeal( array $fields, string $key, string $expectedSeal ) {
		if ( $fields !== null ) {
			$computedSeal = $this->sealFields( $fields, $key );

			return strtoupper( $computedSeal ) === strtoupper( $expectedSeal ) ? true : false;
		}

		return false;
	}

	public function getStringToSeal( array $formFields ) {
		// The string to be sealed is composed of all the form fields sent
		// 1. ordered alphabetically (numbers first, then capitalized letter, then other letters)
		// 2. represented using the format key=value
		// 3. separated by "*" character
		// Please refer to technical documentation for more details
		ksort( $formFields );
		array_walk( $formFields, function ( &$item, $key ) {
			$item = "$key=$item";
		} );

		return implode( '*', $formFields );
	}

	private function sealString( string $stringToSeal, string $key ) {
		$MAC = hash_hmac(
			"sha1",
			$stringToSeal,
			hex2bin( $key )
		);

		return $MAC;
	}

	private function getUsableKey( string $key ) {
		$hexStrKey = substr( $key, 0, 38 );
		$hexFinal  = "" . substr( $key, 38, 2 ) . "00";

		$cca0 = ord( $hexFinal );

		if ( $cca0 > 70 && $cca0 < 97 ) {
			$hexStrKey .= chr( $cca0 - 23 ) . substr( $hexFinal, 1, 1 );
		} else {
			if ( substr( $hexFinal, 1, 1 ) == "M" ) {
				$hexStrKey .= substr( $hexFinal, 0, 1 ) . "0";
			} else {
				$hexStrKey .= substr( $hexFinal, 0, 2 );
			}
		}

		return $hexStrKey;
	}
}

?>