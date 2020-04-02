<?php

/**
 * Obviamente, esta classe não ira funcionar pois é apenas uma tentativa de agiliar a criação do tratamento do pedido..
 */
$dados = $_POST; // Use algum tipo de filtro para evitar injection ok??

class Pedido {

    private $DEPARA_STATUS_PAGSEGURO = [
        '1' => 1,
        '3' => 50,
        '4' => 50,
        '7' => 80
    ];

    public function registraVenda($dados) {
        $dao = new EntityManager();
        $payment = (object) $dados;
        $params = array(
            'creditCardToken' => $payment->creditCardToken,
            'senderHash' => $payment->senderHash,
            'paymentMethod' => $payment->paymentMethod, # ou BOLETO ou ONLINE_DEBIT
            'itemId1' => '0001',
            'itemDescription1' => 'Produto que esta sendo comprado',
            'itemAmount1' => $payment->pedido->valorLiquido,
            'reference' => $payment->pedido->idPedido,
            'senderName' => $payment->pedido->Usuario->nomeUsuario,
            'senderCPF' => $payment->pedido->Usuario->cpfUsuario,
            'senderEmail' => $payment->pedido->Usuario->emailUsuario,
            'installmentValue' => $payment->pedido->valorLiquido, # Valor da parcela
            'paymentMethodGroup1' => $payment->paymentMethod,
            'creditCardHolderName' => $payment->cardName,
            'creditCardHolderCPF' => $payment->cardCpf,
        );
        $pagseguro = new \nsCheckout\Pagseguro\Pagseguro();
        if ($pagseguro->isSandBox()) {
            $params['senderEmail'] = 'teste@sandbox.pagseguro.com.br';
        }
        $retGateway = $pagseguro->checkout($params);
        if ($retGateway->error) {
            return ['error' => $pagseguro->trataError($retGateway->error)];
        }
        // adicionar retorno ao pedido
        $json = json_encode([
            'vlr_liquido' => $retGateway->netAmount,
            'vlr_bruto' => $retGateway->grossAmount,
        ]);
        $dao->execQueryAndReturn("update pedido set extras_pedido= extras_pedido || '$json'::jsonb where id_pedido= " . $pedido['idPedido']);

        $this->listenerPagseguro($retGateway);
    }

    /**
     * Ira receber o arquivo de retorno de consulta do pagseguro, e inserir um log do pedido
     * @param array $dados
     * @return type
     */
    public function listenerPagseguro($dados) {
        $dados = json_decode(json_encode($dados));
        switch (true) {
            case strlen($dados->code) > 0: // ja eh uma notificacao do pagseguro
                break;
            case strlen($dados->notificationCode) > 0: // tem um codigo, para buscar os dados
                $pag = new \nsCheckout\Pagseguro\Pagseguro();
                $dd = $pag->transactionListener($dados->notificationCode);
                if (strlen($dd->code) === 0) {
                    Log::error('Consulta ao Pagseguro não retornou', $dados);
                    return [];
                }
                $dados = $dd;
                break;
            default:
                Log::error('Listener pagseguro: nenhuma condição atingida', $dados);
                return [];
        }// salvar em logs, caso necessario
        $dao = new EntityManager(new JsonTable([
            'chaveJsonTable' => 'PAGSEGURO_NOTIFICACOES',
            'dataJsonTable' => json_encode($dados)
        ]));
        $dao->save();
        // realizar updates no sistema
        $this->updateFromPagseguro($dados);
        return [];
    }

    /**
     * Atualiza um pedido com base nos dados enviados pelo pagseguro
     * @param type $json
     * @return boolean
     */
    private function updateFromPagseguro($JsonFromNotification) {

        $dd = json_decode(json_encode($JsonFromNotification));
        $idPedido = (int) str_replace('PED_', '', $dd->reference);
        $status = (string) $dd->status;

        //return;
        if (isset($this->DEPARA_STATUS_PAGSEGURO[$status])) {
            $dao = new EntityManager(new Pedido());
            $pedido = $dao->getById($idPedido);
            if ($pedido instanceof Pedido) {
                if ($this->DEPARA_STATUS_PAGSEGURO[$status] > (int) $pedido->getStatus()->getOrderStatus()) {
                    $st = $dao->setObject(new Status())->getAll(['orderStatus' => (int) $this->DEPARA_STATUS_PAGSEGURO[$status], 'entidadeStatus' => 'PEDIDO'])[0];
                    if ($st instanceof Status) {
                        $pedido->setIdStatus($st->getId());
                        $dao->setObject($pedido)->save();
                        $dao->execQueryAndReturn("select * from ns_distribue_comissao()");
                        // para status= 4, liberar os valores em caixa
                    } else {
                        Log::error('Pagseguro: Status mapeado não localizado em banco (PCC397)', [
                            'statusEnviado' => $status,
                            'statusMapeado' => $this->DEPARA_STATUS_PAGSEGURO[$status]
                        ]);
                    }
                } else {
                    Log::error('Pagseguro: Novo status não é maior que o atual (PCC392)', [
                        'atual' => $pedido->getStatus()->getOrderStatus(),
                        'statusEnviado' => $status,
                        'novo' => $this->DEPARA_STATUS_PAGSEGURO[$status]
                    ]);
                }
            } else {
                Log::error('Atualização pedido do pagseguro: Pedido não localizado (PCC392)', ['idPedido' => $idPedido]);
            }
        } else {
            Log::error('Pagseguro: Status não setado (PCC396)', ['statusEnviado' => $status]);
        }

        return true;
    }

}
