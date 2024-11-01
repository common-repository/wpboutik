<?php

namespace MoneticoDemoWebKit\Monetico\Collections;

abstract class ThreeDSecureChallengeValue {
	// The merchant has no preference about performing client authentication
	const NO_PREFERENCE = 'no_preference';

	// The merchant wishes the client authentification to be performed
	const CHALLENGE_PREFERRED = 'challenge_preferred';

	// The merchant demands that the client authentification is performed
	const CHALLENGE_MANDATED = 'challenge_mandated';

	// The merchant wishes the client authentification to be bypassed
	const NO_CHALLENGE_REQUESTED = 'no_challenge_requested';

	// The merchant wishes the client authentification to be bypassed because a strong authentication
	// of the client has already been done
	const NO_CHALLENGE_REQUESTED_STRONG_AUTHENTICATION = 'no_challenge_requested_strong_authentication';

	// The merchant wishes the client authentification to be bypassed because the merchant is a trusted
	// third party
	const NO_CHALLENGE_REQUESTED_TRUSTED_THIRD_PARTY = 'no_challenge_requested_trusted_third_party';

	// The merchant wishes the client authentification to be bypassed because a risk analysis has been
	// carried out
	const NO_CHALLENGE_REQUESTED_RISK_ANALYSIS = 'no_challenge_requested_risk_analysis';
}