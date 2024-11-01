<?php

namespace MoneticoDemoWebKit\Monetico\Collections;

abstract class PaymentProtocol {
	const COFIDIS_1_EURO = '1euro';
	const COFIDIS_3X_CB = '3xcb';
	const COFIDIS_4X_CB = '4xcb';
	const PAYPAL = 'paypal';
	const LYFPAY = 'lyfpay';
}