<?php
/**
 * framework-dlx
 * @version: v1.17.07
 * @author: Diego Lepera
 *
 * Created by Diego Lepera on 2017-07-28. Please report any bug at
 * https://github.com/dlepera88-php/framework-dlx/issues
 *
 * The MIT License (MIT)
 * Copyright (c) 2017 Diego Lepera http://diegolepera.xyz/
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace DLX\Ajudantes;


class Sessao {
    /**
     * Iniciar uma sessão
     * @param string $nome      Nome da sessão. Será atribuído ao COOKIE identificador da sessão
     * @param string $id        Identificador da sessão. Será o valor do COOKIE identificador
     *                          da sessão
     * @param bool   $restaurar Define se a sessão será restaurada, ou seja, resgatará os dados de uma sessão iniciada
     *                          previamente
     *
     * @return bool
     */
    public static function iniciarSessao($nome, $id = null, $restaurar = false) {
        if (static::sessaoAtiva()) {
            return true;
        } // Fim if

        if ($restaurar) {
            $cookie_sessao = filter_input(INPUT_COOKIE, $nome, FILTER_DEFAULT, FILTER_NULL_ON_FAILURE);

            if ($cookie_sessao === false || !file_exists(session_save_path() . "/sess_{$cookie_sessao}")) {
                return false;
            } // Fim if
        } else {
            $cookie_sessao = md5($id);
        } // Fim if

        // Fazer com que o cookie da sessão seja acessível apenas dentro da raíz desse sistema
        session_set_cookie_params(
            0,
            \DLX::$dlx->config('aplicativo', 'raiz')
        );

        session_name($nome);
        session_id($cookie_sessao);

        return session_start();
    } // Fim do método iniciarSessao


    /**
     * Verificar se há uma sessão ativa
     *
     * @return bool
     */
    public static function sessaoAtiva() {
        return session_status() === PHP_SESSION_ACTIVE;
    } // Fim do método sessaoAtiva


    /**
     * Obter informações contidas na sessão
     *
     * @param string $nome           Nome da informação
     * @param int    $filtro         Filtro a ser aplicado na informação
     * @param mixed  $nao_encontrado Valor a ser retornado caso a informação não seja localizada na sessão ou a sessão
     *                               não esteja ativa
     * @param array  $filtro_opcoes  Vetor com as opções a serem passadas ao filtro
     *
     * @return mixed|null|string
     */
    public static function dadoSessao($nome, $filtro = FILTER_DEFAULT, $nao_encontrado = null, array $filtro_opcoes = []) {
        if (static::sessaoAtiva()) {
            return array_key_exists($nome, $_SESSION)
                ? filter_var($_SESSION[$nome], $filtro, $filtro_opcoes)
                : 'Informação não encontrada na sessão!';
        } // Fim if

        return $nao_encontrado;
    } // Fim do método dadoSessao


    /**
     * Encerrar a sessão por completo.
     *
     * Excluir o cookie e remover o arquivo de sessão do servidor
     *
     * @return bool
     */
    public static function encerrarSessao() {
        if (self::sessaoAtiva()) {
            $nome = session_name();
            $prefixo = session_id();
            unlink(session_save_path() . "/sess_{$prefixo}");
            setcookie($nome, null, time() - 1);

            return session_destroy();
        } // Fim if

        return true;
    } // Fim do método encerrarSessao
} // Fim do Ajudante Sessao
