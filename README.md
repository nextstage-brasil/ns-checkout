# Library para checkou simplificado

. Configuração inicial
Para começar o uso, é necessário a criaçã do arquivo no mesmo diretório do composer.json com o nome nsCheckoutConfig.php, preenchendo os dados:
```
<?php
$nsCheckoutConfig = [
    'useSandbox' => true, # true: usar ambiente sandbox, false, produção
    'sandbox' => [
        'email' => '',
        'token' => '',
    ],
    'producao' => [
        'email' => '',
        'token' => '',
    ]
];
```
    
- Exemplo de uso, obter sessao e ouvir notificacoes
```
<?php
require_once '../../../vendor/autoload.php';
$pagseguro = new nsCheckout\Pagseguro\Pagseguro();

echo "<h1> Testes Nextstage Checkout</h1>";

// obtencao de sessao - necessario para iniciar uma venda
echo "<code>Sessão: ";
$sessao = $pagseguro->getSessionCode();
var_export($sessao);
echo "</code><br/>";

// ouvir uma alteracao de pedido
echo "<code>Listener: ";
$ret = $pagseguro->transactionListener('35A12E-D9E829E82900-9224D94F8DBF-97D6C4');
var_export($ret);
echo "</code><br/>";
```

- exemplode uso: registrar uma venda
A aplicação espera um array com os dados a serem enviados no formato abaixo. 
```
$CheckoutData = [
    'creditCardToken' => '', # número do cartão de crédito, caso seja tipo cartão
    'senderHash' => '', # hash obtido na biblioteca javascript do pagseguro com dados do cartão
    'paymentMode' => 'default',
    'paymentMethod' => 'CREDIT_CARD', # ou BOLETO ou ONLINE_DEBIT
    'currency' => 'BRL',
    'extraAmount' => '0.00', # extras caso exista
    //pedido
    'itemId1' => 1,
    'itemDescription1' => 'Description Item 1',
    'itemAmount1' => '1.00',
    'itemQuantity1' => 1,
    'reference' => 'Reference_item_1',
    'senderName' => 'Nome do comprador',
    'senderCPF' => 'CPF do comprador',
    'senderAreaCode' => '48',
    'senderPhone' => '99999999',
    'senderEmail' => 'email do comprador',
    //endereco
    'shippingAddressStreet' => 'WEB',
    'shippingAddressNumber' => '9999',
    'shippingAddressDistrict' => 'WEB',
    'shippingAddressPostalCode' => '88000000',
    'shippingAddressCity' => 'WEB',
    'shippingAddressState' => 'SC',
    'shippingAddressCountry' => 'BRA',
    'shippingType' => 3, ## 1 sedex, 2. transportadora, 3, indefinido ou web
    //'shippingCost' => '0.00',
    // Itens para pagamneto em cartao de credito
    'installmentQuantity' => 1, # Número de parcelas
    'installmentValue' => false, # valor da parcela
    'noInterestInstallmentQuantity' => 4, # quantidade de parcelas oferecida
    // pagamento
    'paymentMethodGroup1' => '',
    'paymentMethodConfigKey1_1' => 'MAX_INSTALLMENTS_NO_INTEREST',
    'paymentMethodConfigValue1_1' => 2,
    'creditCardHolderName' => '',
    'creditCardHolderCPF' => '',
    'creditCardHolderBirthDate' => '01/01/1990',
    'creditCardHolderAreaCode' => 83,
    'creditCardHolderPhone' => '999999999',
    'billingAddressStreet' => 'Address',
    'billingAddressNumber' => '1234',
    'billingAddressDistrict' => 'Bairro',
    'billingAddressPostalCode' => '58075000',
    'billingAddressCity' => 'João Pessoa',
    'billingAddressState' => 'PB',
    'billingAddressCountry' => 'BRA'
];
// ouvir uma alteracao de pedido
echo "<code>Venda com cartão ";
$ret = $pagseguro->checkout($CheckouData);
var_export($ret);
echo "</code><br/>";

```

