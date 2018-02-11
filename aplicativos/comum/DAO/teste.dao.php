<?php

namespace Comum\DAO;

trait Teste {
    protected $teste;

    public function getTeste() {
        return $this->teste;
    }

    public function setTeste($teste) {
        $this->teste = $teste;
    }
}
