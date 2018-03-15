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

use DLX\Ajudantes\Visao as AjdVisao;

define('TXT_VALIDACAO_CPF_INVALIDO', AjdVisao::traduzirTexto('O CPF informado é inválido!'));
define('TXT_VALIDACAO_CNPJ_INVALIDO', AjdVisao::traduzirTexto('O CNPJ informado é inválido!'));
define('TXT_VALIDACAO_GTIN', AjdVisao::traduzirTexto('Número inválido para qualquer padrão GTIN: EAN 8, EAN 13 ou DUN 14'));
define('TXT_VALIDACAO_ARQUIVO_UPLOAD', AjdVisao::traduzirTexto('O tamanho ou a extensão do(s) arquivo(s) são inválidos!'));
define('TXT_AJAX_SALVANDO_REGISTRO', AjdVisao::traduzirTexto('Salvando esse registro... Por favor aguarde.'));
define('TXT_AJAX_ENVIANDO_EMAIL', AjdVisao::traduzirTexto('Enviando e-mail... Por favor aguarde.'));
define('TXT_AJAX_ACESSANDO', AjdVisao::traduzirTexto('Acessando o sistema... Por favor aguarde.'));
define('TXT_AJAX_SALVANDO_ARQUIVO', AjdVisao::traduzirTexto('Salvando arquivo... Por favor aguarde.'));
define('TXT_AJAX_EXCLUINDO_REGISTROS', AjdVisao::traduzirTexto('Excluindo registros... Por favor aguarde.'));

class HTMLForm {
    # Trechos de códigos HTML
    const HTML_ROTULO = '<label for="%s" class="form-rotulo">%s:</label>';
    const HTML_DICA = '<span class="form-dica">%s</span>';
    const HTML_INPUT = '<input %s/>';
    const HTML_TEXTAREA = '<textarea %s>%s</textarea>';
    const HTML_BUTTON = '<button %s>%s</button>';
    const HTML_SELECT = '<select %s>%s</select>';
    const HTML_OPTION = '<option value="%s"%s>%s</option>';
    const HTML_CHK_SIM_NAO = '<input %s/><label for="%s"></label>';

    /**
     * @var array Vetor com as configurações de inputs
     */
    private static $conf_inputs = [
        'arquivo'     => ['type' => 'file', 'name' => '', 'id' => 'arq-', 'class' => 'form-controle -arquivo'],
        'busca'       => ['type' => 'search', 'name' => '', 'id' => 'bus-', 'class' => 'form-controle -busca'],
        'cor'         => ['type' => 'color', 'name' => '', 'id' => 'cor-', 'class' => 'form-controle -cor'],
        'data'        => ['type' => 'date', 'name' => '', 'id' => 'dt-', 'class' => 'form-controle -data'],
        'hora'        => ['type' => 'time', 'name' => '', 'id' => 'hr-', 'class' => 'form-controle -data'],
        'data-hora'   => ['type' => 'datetime-local', 'name' => '', 'id' => 'dh-', 'class' => 'form-controle -data'],
        'email'       => ['type' => 'email', 'name' => '', 'id' => 'mail-', 'class' => 'form-controle -email'],
        'numero'      => ['type' => 'number', 'name' => '', 'id' => 'num-', 'class' => 'form-controle -numero'],
        'range'       => ['type' => 'range', 'name' => '', 'id' => 'num-', 'class' => 'form-controle -range'],
        'senha'       => ['type' => 'password', 'name' => '', 'id' => 'sen-', 'class' => 'form-controle -senha'],
        'fone'        => ['type' => 'tel', 'name' => '', 'id' => 'tel-', 'class' => 'form-controle -fone', 'pattern' => EXPREG_TELEFONE_GERAL],
        'texto'       => ['type' => 'text', 'name' => '', 'id' => 'txt-', 'class' => 'form-controle -texto'],
        'url'         => ['type' => 'url', 'name' => '', 'id' => 'txt-', 'class' => 'form-controle -url'],
        'select'      => ['name' => '', 'id' => 'sel-', 'class' => 'form-controle -select'],
        'textarea'    => ['name' => '', 'id' => 'txt-', 'class' => 'form-controle -textarea'],

        # Campos personalizados
        'cpf'         => ['type' => 'text', 'name' => '', 'id' => 'txt-', 'data-vld-func' => 'validaCPF', 'data-vld-msg' => TXT_VALIDACAO_CPF_INVALIDO, 'pattern' => EXPREG_CPF, 'data-mask' => MASK_CPF, 'class' => 'form-controle -cpf'],
        'cnpj'        => ['type' => 'text', 'name' => '', 'id' => 'txt-', 'data-vld-func' => 'validaCNPJ', 'data-vld-msg' => TXT_VALIDACAO_CNPJ_INVALIDO, 'pattern' => EXPREG_CNPJ, 'data-mask' => MASK_CNPJ, 'class' => 'form-controle -cnpj'],
        'cep'         => ['type' => 'text', 'name' => '', 'id' => 'txt-', 'pattern' => EXPREG_CEP, 'data-mask' => MASK_CEP, 'class' => 'form-controle -cep'],
        'gtin'        => ['type' => 'text', 'name' => '', 'id' => 'txt-', 'data-vld-func' => 'validaEAN', 'data-vld-msg' => TXT_VALIDACAO_GTIN, 'class' => 'form-controle -gtin'],
        'moeda'       => ['type' => 'number', 'name' => '', 'id' => 'num-', 'step' => '0.01', 'placeholder' => 'R$ 100,00', 'class' => 'form-controle -moeda'],
        'dia'         => ['type' => 'number', 'name' => '', 'id' => 'num-', 'min' => 1, 'max' => 31, 'class' => 'form-controle -numero'],
        'mes'         => ['type' => 'number', 'name' => '', 'id' => 'num-', 'min' => 1, 'max' => 12, 'class' => 'form-controle -numero'],
        'ano'         => ['type' => 'number', 'name' => '', 'id' => 'num-', 'min' => 1111, 'max' => 9999, 'class' => 'form-controle -numero'],
        'chk-sim-nao' => ['type' => 'checkbox', 'name' => '', 'id' => 'chk-', 'class' => 'form-controle -chk-sim-nao']
    ];

    /**
     * @var array Vetor com as configurações de botões
     */
    private static $conf_botoes = [
        'salvar'       => ['type' => 'submit', 'class' => 'botao -salvar', 'data-ajax' => true, 'data-ajax-msg' => TXT_AJAX_SALVANDO_REGISTRO],
        'cancelar'     => ['type' => 'reset', 'class' => 'botao -cancelar'],
        'enviar-email' => ['type' => 'submit', 'class' => 'botao -enviar', 'data-ajax' => true, 'data-ajax-msg' => TXT_AJAX_ENVIANDO_EMAIL],
        'entrar'       => ['type' => 'submit', 'class' => 'botao -entrar', 'data-ajax' => true, 'data-ajax-msg' => TXT_AJAX_ACESSANDO],
        'upload'       => ['type' => 'submit', 'class' => 'botao -upload', 'data-ajax' => true, 'data-ajax-msg' => TXT_AJAX_SALVANDO_ARQUIVO]
    ];


// Campos ----------------------------------------------------------------------------------------------------------- //
    /**
     * Adicionar uma nova configuração de campo
     *
     * @param string $nome Nome da nova configuração
     * @param array  $conf Vetor contendo os parâmetros com seus valores padrões
     */
    public static function novoCampo($nome, array $conf) {
        static::$conf_inputs[filter_var($nome)] = filter_var($conf, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    } // Fim do método novoCampo


    /**
     * Montar um trecho HTML correspondente ao tipo de campo especificado em $conf
     *
     * @param string      $conf   Tipo de campo a ser criado
     * @param string      $nome   Nome do campo
     * @param string      $id     ID atribuído a esse campo
     * @param mixed|null  $valor  [opcional] Valor inicial do campo
     * @param string|null $rotulo [opcional] Rótulo de referência do campo
     * @param string|null $dica   [opcional] Dica vinculada a esse campo
     * @param array       $params [opcional] Outros atributos do campo
     *
     * @return string
     */
    public static function campoGeral($conf, $nome, $id, $valor = null, $rotulo = null, $dica = null, array $params = []) {
        $conf = array_replace_recursive(static::$conf_inputs[$conf], $params);
        $conf['name'] = filter_var($nome);
        $conf['id'] .= filter_var($id);

        if (isset($valor)) {
            $conf['value'] = filter_var($valor);
        } // Fim if

        return static::htmlRotulo($conf['id'], $rotulo) . static::htmlDica($dica) .
            sprintf(static::HTML_INPUT, Vetores::array2AtributosHTML($conf));
    } // Fim do método campoGeral


    /**
     * Montar um trecho HTML para um campo de telefone
     *
     * @param string      $nome         Nome do campo
     * @param string      $id           ID atribuído a esse campo
     * @param mixed|null  $valor        [opcional] Valor inicial do campo
     * @param string|null $rotulo       [opcional] Rótulo de referência do campo
     * @param string|null $dica         [opcional] Dica vinculada a esse campo
     * @param array       $params       [opcional] Outros atributos do campo
     * @param bool        $nove_digitos [opcional] Informa se o telefone possui nove dígitos ou não
     *
     * @return string
     */
    public static function campoTelefone($nome, $id, $valor = null, $rotulo = null, $dica = null, array $params = [], $nove_digitos = false) {
        $nove_digitos = $nove_digitos || preg_match('~^\(?[0-9]{2}[\)\s]+?[0-9]{1}\s+[0-9]{4}\-?[0-9]{4}$~', $valor);
        $params['data-mask'] = $nove_digitos ? MASK_TELEFONE_CELULAR_9 : MASK_TELEFONE_CELULAR_8;
        $params['data-mask8'] = MASK_TELEFONE_CELULAR_8;
        $params['data-mask9'] = MASK_TELEFONE_CELULAR_9;

        return static::campoGeral('fone', $nome, $id, $valor, $rotulo, $dica, $params) .
            '<span class="form-tel9digitos">' .
            "<input type=\"checkbox\" id=\"check9-{$id}\" data-acao=\"alterar-mask-telefone\" data-acao-param-telefone=\"tel-{$id}\"" . ($nove_digitos ? ' CHECKED' : '') . "/>" .
            "<label for=\"check9-{$id}\">" . AjdVisao::traduzirTexto('Esse telefone possui o 9º dígito') . '</label>' .
            '</span>';
    } // Fim do método campoTelefone


    /**
     * Montar um trecho HTML para uma caixa de texto (TEXTAREA)
     *
     * @param string      $nome   Nome do campo
     * @param string      $id     ID atribuído a esse campo
     * @param mixed|null  $valor  [opcional] Valor inicial do campo
     * @param string|null $rotulo [opcional] Rótulo de referência do campo
     * @param string|null $dica   [opcional] Dica vinculada a esse campo
     * @param array       $params [opcional] Outros atributos do campo
     *
     * @return string
     */
    public static function caixaTexto($nome, $id, $valor = null, $rotulo = null, $dica = null, array $params = []) {
        $conf = array_replace_recursive(static::$conf_inputs['textarea'], $params);
        $conf['name'] = filter_var($nome);
        $conf['id'] = 'txt-' . filter_var($id);

        return static::htmlRotulo($conf['id'], $rotulo) . static::htmlDica($dica) .
            sprintf(static::HTML_TEXTAREA, Vetores::array2AtributosHTML($conf), (string)$valor);
    } // Fim do método caixaTexto


    /**
     * Montar um trecho HTML para um combo de seleção (SELECT)
     *
     * @param string      $nome    Nome do campo
     * @param string      $id      ID atribuído a esse campo
     * @param mixed|null  $valor   [opcional] Valor inicial do campo
     * @param string|null $rotulo  [opcional] Rótulo de referência do campo
     * @param string|null $dica    [opcional] Dica vinculada a esse campo
     * @param array       $params  [opcional] Outros atributos do campo
     * @param array       $options Vetor multimensional com as opções a serem adicionadas nesse combo
     * @param array|null  $option_inicial Configurações do <option></option> inicial a ser exibido quando
     * nenhuma outra opção for selecionada.
     * 
     * @return string
     */
    public static function comboSelect($nome, $id, $valor, $rotulo = null, $dica = null, array $params = [], array $options = [], $option_inicial = [['VALOR' => '', 'TEXTO' => 'Selecione uma opção']]) {
        $conf = array_replace_recursive(static::$conf_inputs['select'], $params);
        $conf['name'] = filter_var($nome);
        $conf['id'] .= filter_var($id);

        if (!empty($options)) {
            if (!Vetores::arrayMulti($options)) {
                $options = array_map(
                    function ($v){
                        return ['VALOR' => $v, 'TEXTO' => $v];
                    }, $options);
            } // Fim if

            // Adicionar o option inicial com valor em branco, para indicar o usuário a selecionar uma das
            // opções
            $options = array_merge($option_inicial, $options);
        } else {
            // Se o array $options estiver vazio, informar ao usuário que nenhuma opção foi adicionada ao
            // select / combobox
            $options = [['VALOR' => '', 'TEXTO' => AjdVisao::traduzirTexto('Nenhuma opção adicionada')]];
        } // Fim if ... else

        return static::htmlRotulo($conf['id'], $rotulo) . static::htmlDica($dica) .
            sprintf(
                static::HTML_SELECT,
                Vetores::array2AtributosHTML($conf),
                implode("\n", array_map(function ($opcao) use ($valor) {
                    return sprintf(
                        static::HTML_OPTION,
                        $opcao['VALOR'],
                        in_array($opcao['VALOR'], (array)$valor) ? ' SELECTED' : '',
                        $opcao['TEXTO']
                    );
                }, $options))
            );
    } // Fim do método comboSelect


    /**
     * Adicionar uma opção a um SELECT
     * @param mixed  $valor Valor a ser atribuído a opção
     * @param string $texto Texto de exibição da opção
     * @return array
     */
    public static function csAdicionarOpcao($valor, $texto) {
        return [[
            'VALOR' => $valor,
            'TEXTO' => $texto
        ]];
    } // Fim do método csAdicionarOpcao


    /**
     * Montar campo de alternação entre sim e não
     *
     * @param string      $nome        Nome do campo
     * @param string      $id          ID atribuído a esse campo
     * @param bool        $selecionado Define se o checkbox está selecionado (true) ou não (false)
     * @param string|null $rotulo      [opcional] Rótulo de referência do campo
     * @param string|null $dica        [opcional] Dica vinculada a esse campo
     * @param array       $params      [opcional] Outros atributos do campo
     *
     * @return string
     */
    public static function chkSimNao($nome, $id, $selecionado = false, $rotulo = null, $dica = null, array $params = []) {
        $conf = array_replace_recursive(static::$conf_inputs['chk-sim-nao'], $params);
        $conf['name'] = filter_var($nome);
        $conf['id'] .= filter_var($id);

        if ($selecionado) {
            $conf['checked'] = 'checked';
        } // Fim if

        return (isset($rotulo) ? '<span class="form-rotulo">' . $rotulo . '</span>' : '') . static::htmlDica($dica) .
            sprintf(static::HTML_CHK_SIM_NAO, Vetores::array2AtributosHTML($conf), $conf['id']);
    } // Fim do método chkSimNao


    /**
     * Montar campo de alternação entre sim e não
     *
     * @param string      $nome      Nome do campo
     * @param string      $id        ID atribuído a esse campo
     * @param string|null $rotulo    [opcional] Rótulo de referência do campo
     * @param string|null $dica      [opcional] Dica vinculada a esse campo
     * @param array       $params    [opcional] Outros atributos do campo
     * @param mixed       $extensoes Vetor com as extensões a serem aceitas por esse campo ou lista das extensões
     *                               separadas por vírgula
     *
     * @return string
     */
    public static function arquivoUpload($nome, $id, $rotulo = null, $dica = null, array $params = [], $extensoes = null) {
        $conf = array_replace_recursive(static::$conf_inputs['arquivo'], $params);
        $conf['name'] = filter_var($nome);
        $conf['id'] .= filter_var($id);

        if (!empty($extensoes)) {
            $extensoes = is_array($extensoes) ? implode(', ', $extensoes) : $extensoes;
            $conf['data-vld-func'] = 'validaUpload';
            $conf['data-vld-msg'] = TXT_VALIDACAO_ARQUIVO_UPLOAD;
            $conf['data-vld-exts'] = $extensoes;
        } // Fim if

        return static::htmlRotulo($conf['id'], $rotulo) . static::htmlDica($dica) .
            sprintf(static::HTML_INPUT, Vetores::array2AtributosHTML($conf), $conf['id']) .
            (!empty($extensoes) ? '<br/>Extensões: ' . preg_replace('~,(\s?[a-z]{3,4})$~', ' e ${1}', $extensoes) : '');
    } // Fim do método arquivoUpload


// Botões ----------------------------------------------------------------------------------------------------------- //
    /**
     * Adicionar uma nova configuração de botão
     *
     * @param string $nome Nome da nova configuração
     * @param array  $conf Vetor contendo os parâmetros com seus valores padrões
     */
    public static function novoBotao($nome, array $conf) {
        static::$conf_botoes[filter_var($nome)] = filter_var($conf, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    } // Fim do método novoBotao


    /**
     * Montar trecho HTML referente ao botão (BUTTON)
     *
     * @param string $conf   Tipo de botão a ser criado
     * @param string $texto  Texto de exibição do botão
     * @param array  $params [opcional] Outros atributos do botão
     *
     * @return string
     */
    public static function botao($conf, $texto, array $params = []) {
        $conf = array_replace_recursive(static::$conf_botoes[$conf], $params);

        return sprintf(static::HTML_BUTTON, Vetores::array2AtributosHTML($conf), $texto);
    } // Fim do método botao


// Outros ----------------------------------------------------------------------------------------------------------- //
    /**
     * Gerar o trecho HTML de um rótulo de campo
     *
     * @param string $id    ID do campo ao qual esse rótulo está vinculado
     * @param string $texto Texto de exibição do rótulo
     *
     * @return string
     */
    public static function htmlRotulo($id, $texto) {
        return isset($texto) ? sprintf(static::HTML_ROTULO, $id, $texto) : '';
    } // Fim do método htmlRotulo


    /**
     * Gerar o trecho HTML de uma dica de campo
     *
     * @param string $texto Dica do campo
     *
     * @return string
     */
    public static function htmlDica($texto) {
        return isset($texto) ? sprintf(static::HTML_DICA, $texto) : '';
    } // Fim do método htmlDica

    /**
     * Converter um vetor em campos HTML.
     * As chaves do vetor serão consideradas como os nomes dos campos e os respectivos valores, valores
     * dos campos.
     * 
     * @param array $vetor Vetor a ser convertido em campo (input).
     * @param string $tipo Tipo de campo a ser criado (atributo type). Padrão: hidden
     * 
     * @return string Retorna o HTML correspondente
     */
    public static function array2Input(array $vetor, $tipo = 'hidden') {
        define('HTML_INPUT_CAMPO', '<input type="%s" name="%s" value="%s" class="form-controle -texto"/>' . PHP_EOL);
        $html = '';

        foreach ($vetor as $campo => $valor) {
            $html .= sprintf(HTML_INPUT_CAMPO, $tipo, $campo, $valor);
        } // Fim foreach

        return $html;
    } // Fim do método array2Input
} // Fim do Ajudante HTMLForm
