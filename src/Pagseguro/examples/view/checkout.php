<?php
$file = 'autoload.php';
$dir = '../';
while (!file_exists($dir . $file)) {
    $dir .= '../';
}
require_once $dir . $file;
$pagseguro = new nsCheckout\Pagseguro\Pagseguro();
$scriptPagseguro = $pagseguro->getLinkToJavascriptForDirectPayemnt();
?>
<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <title>Checkout Sample - Nextstage Checkout</title>
        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <!--imports necessários: ajustar caminhos conforme necessidade -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.13.0/css/all.css" integrity="sha384-Bfad6CLCknfcloXFOyFnlgtENryhrpZCe29RTifKEixXQZ38WheV+i/6YWSzkz3V" crossorigin="anonymous">
        <link href = "css/style.css" rel = "stylesheet">
        <link href = "css/loader.css" rel = "stylesheet">
        <link href = "css/animate.css" rel = "stylesheet">



        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>


        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.7.9/angular.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>






        <script type="text/javascript" src="js/angular-locale_pt-br.js"></script>

        <script type="text/javascript" src="js/ng-app.js"></script>

    </head>
    <body>
        <main ng-app="myapp" ng-controller="AppController">
            <section id="cart_list" class="container py-3 my-3 card" ng-controller="CheckoutController" >
                <div class="row">
                    <div class="col-md-4 order-md-2 mb-4" >
                        <h4 class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Descrição</span>
                            <span class="badge badge-secondary badge-pill">{{Carrinho.itens.length}} itens</span>
                        </h4>
                        <ul class="list-group mb-3" style="max-height: 400px; overflow-y: auto">
                            <li ng-repeat="item in Carrinho.itens" class="list-group-item d-flex justify-content-between lh-condensed">
                                <div class="row">
                                    <div class="col-3">
                                        <img ng-src="{{item.icone}}" class="img-fluid"/>
                                    </div>
                                    <div class="col-9">
                                        <div>
                                            <h6 class="my-0">{{item.nome}}</h6>
                                        </div>
                                        <span class="text-muted">{{item.valor|currency}}</span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <ul class="list-group mb-3">
                            <li class="list-group-item d-flex justify-content-between text-strong">
                                <span>Total</span>
                                <strong>{{Carrinho.valorPagar|currency}}</strong>
                            </li>
                        </ul>

                    </div>


                    <div class="col-md-8 order-md-1">
                        <h4 class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Pagamento</span>
                        </h4>
                        <div class="d-block my-3">
                            <div class="custom-control custom-radio">
                                <input id="credit" name="paymentMethod" type="radio" class="custom-control-input" ng-click="card.paymentMethod = 'CREDIT_CARD'" checked required>
                                <label class="custom-control-label" for="credit">Cartão de crédito</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input id="debit" name="paymentMethod" type="radio" class="custom-control-input" ng-click="card.paymentMethod = 'BOLETO'" required>
                                <label class="custom-control-label" for="debit">Boleto</label>
                            </div>
                            <!--
                            <div class="custom-control custom-radio">
                                <input id="paypal" name="paymentMethod" type="radio" class="custom-control-input" required>
                                <label class="custom-control-label" for="paypal">PayPal</label>
                            </div>
                            -->
                        </div>
                        <!-- pagamento por cartão de crédito -->
                        <div ng-show="card.paymentMethod === 'CREDIT_CARD'">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cc-name">Nome no cartão</label>
                                    <input type="text" class="form-control" id="cc-name" ng-model="card.cardName" placeholder="" required>
                                    <small class="text-muted">Exatamente como aparece no cartão</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="cc-name">CPF</label>
                                    <input type="text" class="form-control cpf" id="cc-name" ng-model="card.cardCpf" placeholder="" required>
                                    <small class="text-muted">CPF do proprietário do cartão</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5 mb-3">
                                    <label for="cc-number">Número do cartão</label>
                                    <input type="text" class="form-control credit_card" ng-model="card.number" id="cc-number" placeholder="" required>
                                    <small class="text-muted">Somente os números do cartão</small>
                                    <div class="invalid-feedback">
                                        Número do cartão é obrigatório
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="cc-expiration">Validade</label>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <select class="form-control" ng-model="card.val_mes"  ng-options="item as item for item in Meses"></select>
                                            <small class="text-muted">Mês de validade</small>
                                        </div>
                                        <div class="col-sm-6">
                                            <select class="form-control" ng-model="card.val_ano" ng-options="item as item for item in Anos"></select>
                                            <small class="text-muted">Ano de validade</small>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">
                                        Validade é obrigatório
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="cc-cvv">Código de segurança</label>
                                    <input type="text" class="form-control" ng-model="card.cvv" id="cc-cvv" placeholder="" required>
                                    <small class="text-muted">3 números. Comum estar atrás do cartão</small>
                                    <div class="invalid-feedback">
                                        CVV é obrigatório
                                    </div>
                                </div>
                            </div>


                            <div class="d-block my-3">
                                <div class="custom-control custom-radio">
                                    <input id="mycard_yes" name="mycard" type="radio" class="custom-control-input" ng-click="card.mycard = 'YES'" checked required>
                                    <label class="custom-control-label" for="mycard_yes">Cartão esta no meu nome</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input id="mycard_no" name="mycard" type="radio" class="custom-control-input" ng-click="card.mycard = 'NO'" required>
                                    <label class="custom-control-label" for="mycard_no">Cartão em nome de outra pessoa</label>
                                </div>
                                <!--
                                <div class="custom-control custom-radio">
                                    <input id="paypal" name="paymentMethod" type="radio" class="custom-control-input" required>
                                    <label class="custom-control-label" for="paypal">PayPal</label>
                                </div>
                                -->
                            </div>



                        </div>
                        <!-- pagamento por boleto -->
                        <div ng-show="fp === 'BOLETO'">

                        </div>
                        <hr class="mb-4">
                        <button class="btn btn-primary btn-lg btn-block" ng-click="send()" type="submit">Efetuar pagamento</button>
                        </form>
                    </div>
                </div>

            </section>
        </main>
        <?= $scriptPagseguro ?>
        <!-- loading -->
        <div id="loading" style="z-index:9999999" class="modal" role="dialog" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog text-center pt-5">
                <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
            </div>
        </div>
    </body>
</html>
