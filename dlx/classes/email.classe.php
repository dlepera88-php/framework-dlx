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

/* TODO: Completar as informações de configurações do site e log de registro do LogEmail */

namespace DLX\Classes;

use Comum\Modelos\LogEmail;
use DLX\Ajudantes\Visao as AjdVisao;
use DLX\Excecao\DLX as DLXExcecao;
use PainelDLX\Admin\Modelos\ConfigEmail;

require_once 'phpmailer/class.phpmailer.php';
require_once 'phpmailer/class.smtp.php';

class Email {
    # Instâncias utilizadas
    private $php_mailer;
    private $config_email;
    public $log_email;

    /**
     * @var bool $gravar_log_auto
     * Define se o log deve ser gravado automaticamente após a tentativa
     * de envio do email
     */
    protected $gravar_log_auto = false;

    public function isGravarLogAuto() {
        return (bool)$this->gravar_log_auto;
    }

    public function setGravarLogAuto($gravar_log_auto) {
        $this->gravar_log_auto = filter_var($gravar_log_auto, FILTER_VALIDATE_BOOLEAN);
    }

    public function __construct() {
        # Instanciar o PHP-Mailer
        $this->php_mailer = new \PHPMailer();
        $this->php_mailer->SetLanguage('br');

        # Instanciar o modelo ConfigEmail
        $this->config_email = new ConfigEmail();
        
        // Carregar as configurações de envio de email na classe
        // PHPMailer
        $this->carregarConf();

        # Instanciar o modelo LogEmail
        $this->log_email = new LogEmail();
    } // Fim do método mágico __construct

    /**
     * Carregar as configurações
     *
     * @param ConfigEmail|int $id ID da configuração a ser carregada. Se não for informado será carregada a
     *                            configuração flagada como 'Principal'
     *
     * @throws DLXExcecao
     */
    public function carregarConf() {
        # Definir servidor como SMTP
        $this->php_mailer->IsSMTP();

        # Dados do servidor
        $this->php_mailer->Host = $this->config_email->getServidor();
        $this->php_mailer->Port = $this->config_email->getPorta();
        $this->php_mailer->SMTPAuth = (bool)$this->config_email->isAutent();
        $this->php_mailer->SMTPSecure = $this->config_email->getCripto();
        $this->php_mailer->Username = $this->config_email->getConta();
        $this->php_mailer->Password = $this->config_email->getSenha();
        $this->php_mailer->From = $this->config_email->getConta();
        $this->php_mailer->FromName = $this->config_email->getDeNome();
        $this->php_mailer->AddReplyTo($this->config_email->getResponderPara());
        $this->php_mailer->IsHTML((bool)$this->config_email->isHtml());
    } // Fim do método carregarConf

    /**
     * Enviar o e-mail
     *
     * @param string $dest    Email ou emails do destinatário separados por ; (ponto e vírgula)
     * @param string $assunto Assunto do e-mail
     * @param string $corpo   Corpo do e-mail
     * @param string $classe  Nome da classe que fez o envio do email         
     * @param string $cc      Email ou emails que receberão uma cópia, separados por ; (ponto e vírgula)
     * @param string $cco     Email ou emails que receberão uma cópia oculta, separados por ; (ponto e vírgula)
     *
     * @return boolean false em caso de falha e true em caso de sucesso
     */
    public function enviar($dest, $assunto, $corpo, $classe = null, $cc = null, $cco = null) {
        # Garantir que ainda não existam endereços
        $this->php_mailer->ClearAllRecipients();

        # Incluir as imagens que estão dentro do corpo do e-mail
        preg_match_all('~<img\s+src=\"(.+)\"~', $corpo, $imagens);

        foreach ($imagens[1] as $k => $img) {
            $img = str_replace(AjdVisao::hostCompleto(), '', $img);
            preg_match('~/([\w]+)\.[a-z0-9]{2,4}$~', $img, $nome);
            $corpo = str_replace("src=\"{$imagens[1][$k]}\"", "src=\"cid:{$nome[1]}\"", $corpo);

            $this->php_mailer->AddEmbeddedImage($img, $nome[1]);
        } // Fim foreach

        # Corpo do e-mail
        $this->php_mailer->Subject = utf8_decode($assunto);
        $this->php_mailer->Body = utf8_decode($corpo);

        # Incluir os destinatários
        $dests = explode(';', $dest);
        $dest_cc = explode(';', $cc);
        $dest_cco = explode(';', $cco);

        foreach ($dests as $d) {
            $this->php_mailer->AddAddress($d);
        } // Fim foreach

        foreach ($dest_cc as $d) {
            $this->php_mailer->AddCC($d);
        } // Fim foreach

        foreach ($dest_cco as $d) {
            $this->php_mailer->AddBCC($d);
        } // Fim foreach

        // Enviar o e-mail e gravar o retorno na variável $envio, para retornar no
        // final da função
        $envio = $this->php_mailer->Send();
        
        // Armazenar a mensagem de erro (caso tenha) na classe de log
        $this->log_email->setStatus($this->php_mailer->ErrorInfo);

        // Gravar o log automaticamente, caso assim seja configurado
        if ($this->isGravarLogAuto()) {
            $this->gravarLog($classe, $assunto, $corpo, $dest, $cc, $cco);
        } // Fim if

        return $envio;
    } // Fim do método enviar


    /**
     * Gravar o log da tentativa/envio do e-mail
     * 
     * @param string|null $classe       Nome da classe que fez o envio do e-mail
     * @param string|null $assunto      Assunto do e-mail enviado
     * @param string|null $corpo        Corpo do e-mail enviado
     * @param string|null $para         Destinatário(s) principal(is) do e-mail
     * @param string|null $copia        [opicional] Detinatário(s) que receberão uma cópia do e-mail
     * @param string|null $copia_oculta [opicional] Destinatário(s) que receberão uma cópia oculta do e-mail
     *
     * @return array|mixed
     */
    public function gravarLog($classe = null, $assunto = null, $corpo = null, $para = null, $copia = null, $copia_oculta = null) {
        // Atribuir as informações do Log
        $this->log_email->setClasse($classe);
        $this->log_email->setAssunto($assunto);
        $this->log_email->setCorpo($corpo);
        $this->log_email->setPara($para);
        $this->log_email->setCopia($copia);
        $this->log_email->setCopiaOculta($copia_oculta);
        
        // Salvar o log na base de dados
        return $this->log_email->salvar();
    } // Fim do método gravarLog
} // Fim da classe Email
