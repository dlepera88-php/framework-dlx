<?php
/**
 * Created by PhpStorm.
 * User: dlepera
 * Date: 24/05/16
 * Time: 18:32
 */

namespace DLX\Excecao;

use Exception;

class DLX extends Exception {
    /**
     * @var string Tipo da mensagem que será exibida ao usuário
     */
    protected $tipo_msg;

    /**
     * @var string Como a mensagem deverá ser 'printada' na tela
     */
    protected $como_exibir;


    /**
     * @return string
     */
    public function getTipoMsg() {
        return $this->tipo_msg;
    }


    /**
     * @param string $tipo_msg
     */
    public function setTipoMsg($tipo_msg) {
        $this->tipo_msg = filter_var($tipo_msg, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
    }


    /**
     * @return string
     */
    public function getComoExibir() {
        return $this->como_exibir;
    }


    /**
     * @param string $como_exibir
     */
    public function setComoExibir($como_exibir) {
        $this->como_exibir = filter_var(strtolower($como_exibir), FILTER_VALIDATE_REGEXP, [
            'options' => ['regexp' => '~^(json|html|texto)$~'],
            'flags'   => FILTER_NULL_ON_FAILURE
        ]);
    }


    public function __construct($message, $code, $tipo_msg = '-erro', $como_exibir = 'json', Exception $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->setTipoMsg($tipo_msg);
        $this->setComoExibir($como_exibir);
    } // Fim do método __construct


    /**
     * Resgatar a mensagem
     *
     * @return string
     */
    public function obterMensagem() {
        switch ($this->como_exibir) {
            case 'json':
                return json_encode([
                    'mensagem' => $this->getMessage(),
                    'tipo'     => $this->getTipoMsg()
                ]);

            case 'html':
                return "<p class=\"mostrar-msg {$this->getTipoMsg()}\">{$this->getMessage()}</p>";

            case 'texto': return $this->getMessage();
        } // Fim switch
    } // Fim do método obterMensagem
}
