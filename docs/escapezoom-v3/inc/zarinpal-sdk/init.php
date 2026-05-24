<?php
require get_template_directory() . '/inc/zarinpal-sdk/vendor/autoload.php';

use Http\Client\Common\Plugin\HeaderDefaultsPlugin;
use ZarinPal\Sdk\ClientBuilder;
use ZarinPal\Sdk\Options;
use ZarinPal\Sdk\ZarinPal;
use ZarinPal\Sdk\Endpoint\PaymentGateway\RequestTypes\VerifyRequest;
use ZarinPal\Sdk\HttpClient\Exception\ResponseException;

