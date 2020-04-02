# Library para checkou simplificado

<?php
require '../vendor/autoload.php';
$email = 'YOUR_EMAIL';
$token = 'YOUR_TOKEN';
$sandobox = true;
$pagseguro = new nsCheckout\Pagseguro\Pagseguro($email, $token, $sandobox);

echo "Session: '" . $pagseguro->getSessionCode() . "'<br/>";



