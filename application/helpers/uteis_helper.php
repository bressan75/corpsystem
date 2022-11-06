<?php

if (!function_exists('ver_dados')) {
    
    function ver_dados($cliente) {
        
        if(empty($cliente['cliente']) || empty($cliente['documento']) || empty($cliente['email']) || empty($cliente['telefone']) || empty($cliente['nascimento'])) {
            return false;
        }
        
        if (count(explode(' ', $cliente['cliente'])) < 2) {
            return false;
        }
        
        if (valid_cpf($cliente['documento']) === false) {
            return false;
        }
        
        if (!preg_match('/^\([0-9]{2}\)\s[0-9]{4}\-[0-9]{4,5}$/', $cliente['telefone'])) {
            return false;
        }

        return true;
    }
}

if (!function_exists('ver_endereco')) {
    
    function ver_endereco($cliente) {
        
        if(empty($cliente['cep']) || empty($cliente['endereco']) || empty($cliente['numero']) || empty($cliente['bairro']) || empty($cliente['cidade']) || empty($cliente['estado'])) {
            return false;
        }
        
        return true;
    }
}

if (!function_exists('ps_parcelas')) {
	function ps_parcelas($total) {
		
		$total = round($total, 2);
		
		$opc = '<option value="1">À vista '. number_format($total, 2, ',', '.') .'</option>';
		for ($i=2; $i<=12; $i++) {
			if ($i > 6) {
				$total += $total * (3.99/100);
			}
			$valor = $total / $i;
			
			if ($valor > 10) {
				$opc .= '<option value="'. $i .'">'. $i . 'x de '. number_format($valor, 2, ',', '.') .' '. ($i <= 6 ? 'sem' : 'com') .' juros</option>';
			}
		}
		
		return $opc;
	}
}

if (!function_exists('valid_cpf')) {
    
    function valid_cpf ($cpf) {
		
        $cpf = str_replace('.', '', $cpf);
        $cpf = str_replace('-', '', $cpf);

        if(($cpf == '11111111111') ||
		   ($cpf == '22222222222') ||
		   ($cpf == '33333333333') ||
		   ($cpf == '44444444444') ||
		   ($cpf == '55555555555') ||
		   ($cpf == '66666666666') ||
		   ($cpf == '77777777777') ||
		   ($cpf == '88888888888') ||
		   ($cpf == '99999999999') ||
		   ($cpf == '00000000000')) {
			
            return false;
        }

        $dv_informado = substr($cpf, 9,2);

        for($i=0; $i<=8; $i++) {
            $digito[$i] = substr($cpf, $i,1);
        }

        $posicao = 10;
        $soma = 0;

        for($i=0; $i<=8; $i++) {
            $soma = $soma + $digito[$i] * $posicao;
            $posicao = $posicao - 1;
        }

        $digito[9] = $soma % 11;

        if($digito[9] < 2) {
            $digito[9] = 0;
        } else {
            $digito[9] = 11 - $digito[9];
        }

        $posicao = 11;
        $soma = 0;

        for ($i=0; $i<=9; $i++) {
            $soma = $soma + $digito[$i] * $posicao;
            $posicao = $posicao - 1;
        }

        $digito[10] = $soma % 11;

        if ($digito[10] < 2) {
            $digito[10] = 0;
        } else {
            $digito[10] = 11 - $digito[10];
        }

        $dv = $digito[9] * 10 + $digito[10];

        return ($dv != $dv_informado) ? false : true;
    }
    
}