<?php

namespace nsCheckout\Pagseguro;

class Pagseguro {

    private $SANDBOX_ENVIRONMENT;
    private $PAGSEGURO_API_URL, $PAGSEGURO_EMAIL, $PAGSEGURO_TOKEN, $library;

    function __construct($email = false, $token = false, $sandbox = true) {
        if ($email && $token) {
            $this->SANDBOX_ENVIRONMENT = $sandbox;
            $this->PAGSEGURO_API_URL = 'https://ws.pagseguro.uol.com.br/v2';
            if ($this->SANDBOX_ENVIRONMENT !== false) {
                $this->PAGSEGURO_API_URL = 'https://ws.sandbox.pagseguro.uol.com.br/v2';
            }
            $this->PAGSEGURO_EMAIL = $email;
            $this->PAGSEGURO_TOKEN = $token;
        } else {
            $config = \NsUtil\Helper::nsIncludeConfigFile(__DIR__ . '/nsCheckoutConfig.php');
            $this->SANDBOX_ENVIRONMENT = $config['useSandbox'];
            if ($this->SANDBOX_ENVIRONMENT !== false) {
                $this->PAGSEGURO_API_URL = 'https://ws.sandbox.pagseguro.uol.com.br/v2';
                $this->PAGSEGURO_EMAIL = $config['sandbox']['email'];
                $this->PAGSEGURO_TOKEN = $config['sandbox']['token'];
            } else {
                $this->PAGSEGURO_API_URL = 'https://ws.pagseguro.uol.com.br/v2';
                $this->PAGSEGURO_EMAIL = $config['producao']['email'];
                $this->PAGSEGURO_TOKEN = $config['producao']['token'];
            }
        }
    }

    public function getLinkToJavascriptForDirectPayemnt() {
        if ($this->SANDBOX_ENVIRONMENT !== false) {
            $sandbox = 'sandbox.';
        }
        return '<script type="text/javascript" src="https://stc.' . $sandbox . 'pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js"></script>';
    }

    public function isSandBox() {
        return $this->SANDBOX_ENVIRONMENT;
    }

    public function trataError($error) {
        $out = [];
        foreach ($error as $err) {
            //if ((int) $err === 0) {
            $out[] = $err->message; // json_encode(json_decode($err)->message);
            //}
        }
        return $out;
    }

    /**
     * Cahamda para api
     * @param type $url
     * @param type $params
     * @param type $method
     * @param array $header
     * @return type
     */
    private function call($url, $params = [], $method = 'POST', array $header = ['Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8']) {
        $params['email'] = $this->PAGSEGURO_EMAIL;
        $params['token'] = $this->PAGSEGURO_TOKEN;

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0', //set user agent
            CURLOPT_COOKIEFILE => "cookie.txt", //set cookie file
            CURLOPT_COOKIEJAR => "cookie.txt", //set cookie jar
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_FOLLOWLOCATION => true, // follow redirects
            CURLOPT_ENCODING => "", // handle all encodings
            CURLOPT_AUTOREFERER => true, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 30, // timeout on connect
            CURLOPT_TIMEOUT => 15, // timeout on response
            CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER => false
        ];
        if (count($header) > 0) {
            $options[CURLOPT_HTTPHEADER] = $header;
        }
        $options[CURLOPT_VERBOSE] = false;
        switch ($method) {
            case 'POST':
                $options[CURLOPT_POST] = true;
                //$options[CURLOPT_POSTFIELDS] = json_encode($params);
                $options[CURLOPT_POSTFIELDS] = http_build_query($params, '', '&');
                break;
            default:
                if (count($params) > 0) {
                    $url = sprintf("%s?%s", $url, http_build_query($params));
                }
                $options[CURLOPT_URL] = $url;
        }
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $data = curl_exec($ch);
        //echo curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        if ($data === 'Unauthorized') {
            return (object) ['error' => $data];
        }
        //return $data;
        return json_decode(json_encode(simplexml_load_string($data)));
    }

    public function getSessionCode() {
        $response = $this->call($this->PAGSEGURO_API_URL . "/sessions");
        return (($response->error) ? $response->error : $response->id);
    }

    // O array padrão está no arquivo \Pagseguro\samples\CheckoutDataExample.php. Copie e use o padrão. Não altere este arquivo.
    public function checkout(array $CheckoutData) {
        $data = $CheckoutData;
        require __DIR__ . '/CheckoutDataExample.php';
        $dados = array_merge($CheckoutData, $data); // para garantir que todos os campos necessários virão aqui
        // formatacao de valores padrao
        $dados['senderCPF'] = preg_replace("/[^0-9]/", "", $dados['senderCPF']);
        $dados['creditCardHolderCPF'] = preg_replace("/[^0-9]/", "", $dados['creditCardHolderCPF']);
        $dados['installmentValue'] = number_format($dados['installmentValue'], '2');
        $dados['itemAmount1'] = number_format($dados['itemAmount1'], '2');
        $dados['receiverEmail'] = $this->PAGSEGURO_EMAIL;
        $response = $this->call($this->PAGSEGURO_API_URL . "/transactions", $dados);
        return $response;
    }

    public function transactionListener($notificationCode) {
        $response = $this->call($this->PAGSEGURO_API_URL . "/transactions/notifications/$notificationCode", [], 'GET');
        return $response;
    }

}
