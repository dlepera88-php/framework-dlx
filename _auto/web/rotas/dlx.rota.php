<?php

// Erros HTTP --------------------------------------------------------------- //
\DLX::$dlx->adicionarRota('^%home%erro-http/(\d+)$', [
    'aplicativo' => '',
    'modulo'     => 'Comum',
    'controle'   => 'ErroHTTP',
    'acao'       => 'mostrarErro',
    'params'     => '/-/:status_http'
], ['get', 'post']);
