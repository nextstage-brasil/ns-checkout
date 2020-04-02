# Library para checkou simplificado
- Exemplo de uso
```
<?php
require '../vendor/autoload.php';
$email = 'YOUR_EMAIL';
$token = 'YOUR_TOKEN';
$sandobox = true;
$pagseguro = new nsCheckout\Pagseguro\Pagseguro($email, $token, $sandobox);

// Obtenção de sessao
echo "Session: '" . $pagseguro->getSessionCode() . "'<br/>";
```


