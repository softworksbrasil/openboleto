<?php
declare(strict_types=1);
/*
 * OpenBoleto - Geração de boletos bancários em PHP
 *
 * LICENSE: The MIT License (MIT)
 *
 * Copyright (C) 2013 Estrada Virtual
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace OpenBoleto\Banco;

use OpenBoleto\BoletoAbstract;

/**
 * Classe boleto Cresol
 *
 * @package    OpenBoleto
 * @author     André Corneta <https://github.com/softworksbrasil>
 * @copyright  Copyright (c) 2025 Softworks (https://www.softworksbrasil.com)
 * @license    MIT License
 * @version    1.0
 */
class Cresol extends BoletoAbstract
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = '133';

    /**
     * Localização do logotipo do banco, relativo à pasta de imagens
     * @var string
     */
    protected $logoBanco = 'cresol.jpg';

    /**
     * Define os nomes das carteiras para exibição no boleto
     * @var array
     */
    protected $carteiras = ['09'];

    /**
     * Retorna o Nosso Número com ou sem o dígito verificador
     * @param bool $incluirDigito Se deve incluir o dígito verificador
     * @return string
     */
    public function getNossoNumero($incluirDigito = true)
    {
        $numero = self::zeroFill($this->getSequencial(), 11); // 11 dígitos
        if (!$incluirDigito) {
            return $numero;
        }
        $carteira = self::zeroFill($this->getCarteira(), 2); // 2 dígitos
        $dv = $this->digitoVerificadorNossoNumero($carteira, $numero);
        return $numero . $dv;
    }

    /**
     * Calcula o dígito verificador do Nosso Número (1 dígito, Módulo 11)
     * Baseado em CalculoDV::cresolNossoNumero do laravel-boleto
     * @param string $carteira Carteira formatada (2 dígitos)
     * @param string $numero Número do boleto formatado (11 dígitos)
     * @return string
     */
    protected function digitoVerificadorNossoNumero($carteira, $numero)
    {
        $base = $carteira . $numero; // Concatena carteira (2) + número (11) = 13 dígitos
        $soma = 0;
        $peso = 2;

        // Percorre da direita para a esquerda
        for ($i = strlen($base) - 1; $i >= 0; $i--) {
            $soma += $base[$i] * $peso;
            $peso = $peso == 7 ? 2 : $peso + 1;
        }

        $resto = $soma % 11;
        $digito = $resto == 0 ? 0 : 11 - $resto;
        
        // Se o dígito for maior que 9, retorna 0 (conforme laravel-boleto)
        if ($digito > 9) {
            $digito = 0;
        }

        // Retorna como 1 dígito
        return $digito;
    }

    /**
     * Gera o Nosso Número completo (com dígito verificador)
     * Implementação obrigatória para BoletoAbstract
     * @return string
     */
    public function gerarNossoNumero()
    {
        $numero = self::zeroFill($this->getSequencial(), 11); // 11 dígitos
        $carteira = self::zeroFill($this->getCarteira(), 2); // 2 dígitos
        $dv = $this->digitoVerificadorNossoNumero($carteira, $numero);
        return $numero . $dv;
    } 

    /**
     * Retorna o Campo Livre (25 posições)
     * @return string
     */
    public function getCampoLivre()
    {
        $agencia = self::zeroFill($this->getAgencia(), 4); // 4 dígitos
        $carteira = self::zeroFill($this->getCarteira(), 2); // 2 dígitos
        $nossoNumero = self::zeroFill($this->getSequencial(), 11);
        $conta = self::zeroFill($this->getConta(), 7);
        
        // Campo Livre: agência (4) + carteira (2) + Nosso Número (11) + cedente (7) + '0' (1)
        return $agencia . $carteira . $nossoNumero . $conta . '0';
    }

    /**
     * Retorna o código do cedente formatado (ex.: 1234/1234567)
     * @return string
     */
    public function getAgenciaCodigoCedente()
    {
        return self::zeroFill($this->getAgencia(), 4) . '/' . self::zeroFill($this->getConta(), 7);
    }

}