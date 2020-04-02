//'use strict';

var myapp = angular.module('myapp', []);

var CONFIG = {
    api_url: '',
    prefix_cookies: 'myapp_'
};

//tratamento para persistencia do carrinho
function setCookie(key, value) {
    var expires = new Date();
    expires.setTime(expires.getTime() + 31536000000); //1 year  
    document.cookie = key + '=' + value + ';expires=' + expires.toUTCString();
}

function getCookie(key) {
    var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
    return keyValue ? keyValue[2] : null;
}

//usar ao receber o retorno dos carrinhos
function setCarrinho($carrinho) {
    setCookie('trlcr', $carrinho);
}

// usar para enviar os carrinhos
function getCarrinho() {
    return getCookie('trlcr');
}

function animateCSS(element, animationName, callback) {
    const node = document.querySelector(element);
    node.classList.add('animated', animationName);
    
    function handleAnimationEnd() {
        node.classList.remove('animated', animationName);
        node.removeEventListener('animationend', handleAnimationEnd);
        
        if (typeof callback === 'function')
            callback();
    }
    
    node.addEventListener('animationend', handleAnimationEnd);
}

function sessionStorageSet(name, value) {
    sessionStorage.setItem(VAR_TOKEN_PREFIX + name, value);
}
function sessionStorageGet(name) {
    sessionStorage.getItem(VAR_TOKEN_PREFIX + name);
}



// etse controller ira setar algumas variaveis em $rootScope para uso em outros controller
myapp.controller('AppController', function AppController($rootScope, $scope, $http, $timeout) {
    console.log('AppController Started');
    $timeout(function () {
        // formatar campo tipo double
        $('.number').mask('000.000.000.000,00', {reverse: true});
        $(".cep").mask('99999-999');
        $(".fone").mask('(99)999999990');
        $('.cpf').mask('999.999.999-99');
        $('.hora').mask('99:99');
        $('.data').mask('99/99/9999');
        //$('.geo').mask('000.0000000', {reverse: true});
    }, 1000);
    
    
    // produzira chamadas
    $rootScope.call = function (recurso, data, sendToken, success) {
        var $headers = {'Content-Type': 'application/json'};
        if (sendToken === true) {
            $headers.Token = $scope.token || 'ASDFQWER';
        }
        $http({
            method: 'POST',
            url: 'https://api.trilhasbr.com/' + recurso,
            data: data,
            headers: $headers
        }).then(function ($response) {
            //console.info('RESPONSE', $response);
            success($response.data);
        }, function errorCallback($response) {
            $response.data.status = $response.status;
            switch ($response.status) {
                case 401: // token inválido
                    $scope.setNomeCliente(false);
                    // se foi solicitado enviar token, precisa de login. refazer
                    if (sendToken === true) {
                        $rootScope.login(function (ret) {
                            if (ret === true) {
                                $rootScope.call(recurso, data, sendToken, function (data) {
                                    success(data);
                                });
                            }
                        });
                    } else {
                        success($response.data);
                    }
                    break;
                default:
                    alert('Ocorreu um erro inesperado: ' + $response.data.error);
                    success($response.data);
            }
        });
    };
    
    $rootScope.setTemplate = function (val) {
        var template = '';
        if (typeof val === 'object') {
            template = '<ul>';
            $.map(val, function (value, index) {
                template += '<li>' + value + '</li>';
            });
            template += '</ul>';
        } else if (typeof val === '[object Array]') {
            template = '<ul>';
            angular.forEach(val, function (value, key) {
                template += '<li>' + value + '</li>';
            });
            template += '</ul>';
        } else {
            template = val;
        }
        $rootScope.Template = '<p class="text-center">' + template + '</p>';
    };
    
    $rootScope.setAlert = function (data, showMessageOK, icon, title) {
        //$('#loading').modal('hide');
        $rootScope.loading('hide');
        $rootScope.Template = '';
        $('#barraAviso').hide();
        //if (data.result === "SUCCESS") {
        if (data.error === false) {
            if (showMessageOK !== 'ERROR_ONLY') { // se não quiser imprimir o tudo certo, mas quando houver erro somente
                if (data.content) {
                    txt = typeof data.content.result === 'string' ? data.content.result : 'Tudo Certo!';
                    icon = data.content.icon || false;
                    title = data.content.title || false;
                    result = data.content.result || false;
                } else {
                    txt = 'Tudo Certo!';
                }
                
                $print = txt !== 'Tudo Certo!';
                //console.info("rootScope.setinfo", data);
                if (showMessageOK) {
                    Swal.fire({
                        icon: icon || 'success',
                        title: title || txt,
                        html: result || ''
                    });
                } else if ($print) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        showCloseButton: true,
                        timer: 10000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: icon || 'info',
                        title: title || txt,
                        text: result || ''
                    });
                }
            }
        } else {
            //console.info("rootScope.setAlert", data);
            $rootScope.setTemplate(data.error);
            Swal.fire({
                icon: data.content.icon || 'info',
                title: data.content.title || 'Verifique',
                html: $rootScope.Template
            });
            //$rootScope.showAlertModal(argsAlert);
        }
    };
    
    
    $rootScope.loading = function (escolha, msg, modal) {
        msg = msg ? msg : 'Processando';
        if (escolha === 'show') {
            if (modal !== 'ignore') {
                if (!($('#loading').is(':visible'))) {
                    $("#loading").modal({backdrop: "static"});
                }
            }
        } else {
            $('#loading').modal('hide');
        }
    };
    
    $rootScope.login = function (success) {
        $rootScope.loading('hide');
        Swal.fire({
            title: 'Acessar seu cadastro',
            html: 'Informe seu email e senha para acessar seu cadastro' +
                    '<input id="swal-input1" class="swal2-input" placeholder="Informe seu email" type="email">' +
                    '<input id="swal-input2" class="swal2-input" placeholder="Informe sua senha" type="password">',
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Continuar',
            cancelButtonText: 'Não lembro meus dados',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                var $login = document.getElementById('swal-input1').value;
                var $pass = document.getElementById('swal-input2').value;
                return $http({
                    method: 'POST',
                    url: 'https://api.trilhasbr.com/site/login',
                    data: {
                        email: $login,
                        pass: $pass
                    }
                }).then(function success(response) {
                    console.log(response);
                    if (response.data.error) {
                        Swal.showValidationMessage(response.data.error);
                    } else {
                        $scope.setNomeCliente(response.data.content.nome);
                        $scope.setToken(response.data.content.token);
                        return  response.data.content;
                    }
                    
                });
            }
        }).then((result) => {
            if (result.value) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    onOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
                
                Toast.fire({
                    icon: 'success',
                    title: 'Seja bem vindo ' + $scope.nomeCliente
                });
                success(true);
            } else {
                location.href = "forget.php";
                success(false);
            }
        });
        
    };
    $scope.showLogin = function () {
        $rootScope.login();
    };
    
    $scope.showAreaCliente = function () {
        alert('em breve');
    };
    
    $scope.setToken = function (token) {
        sessionStorage.setItem('trl-tkn', token);
        $scope.token = token;
    };
    $scope.setNomeCliente = function (name) {
        sessionStorage.setItem('trl-cli-name', name);
        $scope.nomeCliente = name;
    };
    // init das avariaveis conforme sessao
    $scope.nomeCliente = sessionStorage.getItem('trl-cli-name') || false;
    $scope.token = sessionStorage.getItem('trl-tkn') || false;
});

myapp.controller('CadastroController', function CartListController($scope, $rootScope, $http) {
    $scope.cad = {
        new : true
    };
    
    // enviar o cadastro pra salvar
    $scope.send = function () {
        $rootScope.loading('show', 'Enviando seu cadastro', 'modal');
        $rootScope.call('site/cadastro', $scope.cad, false, function (data) {
            $rootScope.loading();
            $rootScope.setAlert(data, true);
            console.info('Cad-Retorno', data);
            if (data.error === false) {
                $('#cad-container').html(data.content.result
                        + '<br/><br/><div class="text-center"><a href="index.php" class="mt-5">Página inicial</a></div>');
            }
        });
    };
    
    $scope.esqueciSenha = function () {
        $rootScope.loading('show', 'Solicitando nova senha', 'modal');
        $rootScope.call('site/forget', $scope.cad, false, function (data) {
            $rootScope.loading();
            $rootScope.setAlert(data, true);
            console.info('Cad-Retorno', data);
            if (data.error === false) {
                $('#cad-container').html(data.content.result
                        + '<br/><br/><div class="text-center"><a href="index.php" class="mt-5">Página inicial</a></div>');
            }
            
        });
    };
});

myapp.controller('CheckoutController', function CheckoutController($scope, $rootScope, $timeout) {
    console.log('CheckoutController Init');
    $scope.Meses = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
    $scope.Anos = [];
    ano = new Date().getFullYear();
    for (var i = ano; i < (ano + 30); i++) {
        $scope.Anos.push(i.toString());
    }
    $timeout(function () {
        $scope.init();
    }, 1000);
    
    $scope.card = {
        paymentMethod: 'CREDIT_CARD',
        mycard: 'YES'
    };
    //## testes
    $scope.card = {
        paymentMethod: 'CREDIT_CARD',
        mycard: 'YES',
        val_mes: '12',
        val_ano: '2030',
        cvv: '123',
        number: '4111 1111 1111 1111',
        cardName: 'João comprador',
        cardCpf: '156.009.442-76'
    };
    // obter o carrinho atual
    $scope.Carrinho = {itens: []};
    
    $rootScope.call('site/carrinho/list', {trlcr: getCarrinho()}, false, function (data) {
        $scope.Carrinho = data.content;
        
        console.info('Carrinho', $scope.Carrinho);
    });
    
    $scope.init = function () {
        $rootScope.call('site/carrinho/gs', {}, false, function (data) {
            // setar session
            PagSeguroDirectPayment.setSessionId(data.content);
            // obter formas de pagamento - futuro
            PagSeguroDirectPayment.getPaymentMethods({
                success: function (json) {
                    console.info('PaymentMethods', json);
                },
                error: function (json) {
                    console.log(json);
                    var erro = "";
                    for (i in json.errors) {
                        erro = erro + json.errors[i];
                    }
                    alert(erro);
                },
                complete: function (json) {
                }
            });
        });
        // masks
        $(".credit_card").mask('9999 9999 9999 9999');
        $("#cc-cvv").mask('999');
        $(".cpf").mask('999.999.999-99');
    };
    $scope.send = function () {
        $rootScope.loading('show', 'Validando dados', 'modal');
        // Funcao que busca a bandeira do cartão, e cria o cardtoken para envio
        PagSeguroDirectPayment.getBrand({
            cardBin: $scope.card.number.replace(/ /g, ''),
            success: function (json) {
                var brand = json.brand.name;
                $scope.card.bandeira = json.brand.name;
                console.info('BRAND', json);
                var param = {
                    cardNumber: $scope.card.number.replace(/ /g, ''),
                    brand: brand,
                    cvv: $scope.card.cvv,
                    expirationMonth: $scope.card.val_mes,
                    expirationYear: $scope.card.val_ano,
                    success: function (json) {
                        var token = json.card.token;
                        $scope.card.creditCardToken = token;
                        //$("input[name='token']").val(token);
                        //console.log("Token: " + token);
                        $scope.pay();
                    },
                    error: function (json) {
                        $rootScope.loading();
                        console.info('BRAND-PARAM-ERROR', json);
                        alert('Erro ao obter o brand - veja console');
                        console.log(json);
                    },
                    complete: function (json) {
                    }
                };
                PagSeguroDirectPayment.createCardToken(param);
            },
            error: function (json) {
                $rootScope.loading();
                Swal.fire('Preecha corretamente todos os campos');
                console.info('BRAND-ERROR', json);
            },
            complete: function (json) {
            }
        });
    };
    $scope.pay = function () {
        $rootScope.loading('show', 'Registrando pagamento', 'modal');
        $scope.card.senderHash = PagSeguroDirectPayment.getSenderHash();
        // aqui gerar a chamada para API com os dados para o pagamento
        toSend = angular.copy($scope.card);
        delete($scope.card.val_mes);
        delete($scope.card.val_ano);
        delete($scope.card.cvv);
        delete($scope.card.number);
        $rootScope.call('site/carrinho/checkout', {trlcr: getCarrinho(), payment: JSON.stringify(toSend)}, true, function (data) {
            console.info('retorno do checou', data);
            $rootScope.setAlert(data);
            if (data.error === false) {
                Swal.fire({
                    title: 'Tudo certo!',
                    icon: 'success',
                    html: 'Seu pedido foi registrado com numero XXXX. Logo após a confirmação de pagamento, seu pedido será processado e as fotos estarão disponíveis no Ambiente do cliente. Você receberá um email informando.',
                    onClose: () => {
                        $rootScope.loading('show', '', 'modal');
                        window.location = 'index.php';
                    }
                });
            }
        });
    };
});
