<?php

class Relatorio_model extends CI_Model
{
    function __construct () 
    {
        parent::__construct();
    }
    
    public function clientes_pedidos_ativos () {

        $meses = array();
        $valor = array();
        $data = array();

        $de = date('Y-m-d', mktime(0, 0, 0, date('m')-2, date('d'), date('Y')));
        $ate = date('Y-m-d');
    
        list($de_ano, $de_mes, $de_dia) = explode('-', $de);
        list($at_ano, $at_mes, $at_dia) = explode('-', $ate);

        $nde = '';
        $nat = '';

        $nmk_de = '';
        $nmk_at = '';
        $mk_at = mktime(0, 0, 0, $at_mes, date('t', mktime(0, 0, 0, $at_mes, $at_dia, $at_ano)), $at_ano);

        $cmes = $de_mes;

        $c = 0;
		
		$meses = array();
		$valor = array();
		
        while ($nmk_at < $mk_at)
        {
            $nmk_de = mktime(0, 0, 0, $cmes, 01, $de_ano);
            $nmk_at = mktime(23, 59, 59, $cmes, date('t', mktime(0, 0, 0, $cmes, $de_dia, $de_ano)), $de_ano);

            $nde = date('Y-m-d 00:00:00', $nmk_de);
            $nat = date('Y-m-d 23:59:59', $nmk_at);

			if ($nmk_at > time()) {
				$nat = date('Y-m-d 23:59:59');
			}
			
            $cliente = $this->total_clientes($nde, $nat);
            $pedido = $this->total_pedidos($nde, $nat);
			
            $cmes++;

                $data['Clientes'][] = array (
                    'data' => (int)$cliente['total'],
                    'mes'  => $c
                );
                $data['Pedidos'][] = array (
                    'data' => (int)$pedido['total'],
                    'mes'  => $c
                );
                        
            $meses[] = date('m/y', mktime(0, 0, 0, $cmes, 0, $de_ano));
			
            $c++;
        }

		foreach ($data as $key => $val) {      
            $valor[] = array (
                'name' => $key,
                'data' => $this->data($val, $meses),
                'lineWidth' => 1,
                'visible' => true,
                'shadow' => false
            );
        }

        $dados['meses'] = $meses;
        $dados['valor'] = $valor;
        
        return $dados;
    }
    
    public function total_clientes ($de, $at) {
        
        $query = $this->db->query('select count(*) total from cliente where status in ("Ativo") and data between "'. $de .'" and "'. $at .'"');
        return $query->row_array();
    }
    
    public function total_pedidos ($de, $at) {
        $query = $this->db->query('select count(*) total from pedidos where status not in ("Cancelado", "Removido") and data between "'. $de .'" and "'. $at .'"');
        return $query->row_array();
    }
    
    public function pedido_status () {
        
        $query = $this->db->query('select status, count(*) total from pedidos where status not in ("Removido") group by status');
        
        $arr = array();
        
        $total = 0;
        
        foreach ($query->result_array() as $row) {
            $arr[$row['status']] = $row['total'];
            $total += $row['total'];
        }

        foreach (array('Pendente', 'Produção', 'Refazer', 'Enviado', 'Concluído', 'Cancelado') as $status) {
            if (!empty($arr[$status])) {
                $arr[$status] = round(($arr[$status]/$total) * 100, 2);
            } else {
                $arr[$status] = 0;
            }
        }
        
        return $arr;
    }
    
    public function pedido_valor () {
        
        $meses = array();
        $valor = array();
        $data = array();

        $de = date('Y-m-d', mktime(0, 0, 0, date('m')-2, date('d'), date('Y')));
        $ate = date('Y-m-d');
    
        list($de_ano, $de_mes, $de_dia) = explode('-', $de);
        list($at_ano, $at_mes, $at_dia) = explode('-', $ate);

        $nde = '';
        $nat = '';

        $nmk_de = '';
        $nmk_at = '';
        $mk_at = mktime(0, 0, 0, $at_mes, date('t', mktime(0, 0, 0, $at_mes, $at_dia, $at_ano)), $at_ano);

        $cmes = $de_mes;

        $c = 0;
		
		$meses = array();
		$valor = array();
		
        while ($nmk_at < $mk_at)
        {
            $nmk_de = mktime(0, 0, 0, $cmes, 01, $de_ano);
            $nmk_at = mktime(23, 59, 59, $cmes, date('t', mktime(0, 0, 0, $cmes, $de_dia, $de_ano)), $de_ano);

            $nde = date('Y-m-d 00:00:00', $nmk_de);
            $nat = date('Y-m-d 23:59:59', $nmk_at);

			if ($nmk_at > time()) {
				$nat = date('Y-m-d 23:59:59');
			}
			
            $pedido = $this->pedido_valor_mensal($nde, $nat);
			
            $cmes++;

            $data['Pendente'][] = array (
                'data' => $pedido['Pendente'],
                'mes'  => $c
            );
            $data['Produção'][] = array (
                'data' => $pedido['Produção'],
                'mes'  => $c
            );					
			$data['Enviado'][] = array (
                'data' => $pedido['Enviado'],
                'mes'  => $c
            );			
			$data['Concluído'][] = array (
                'data' => $pedido['Concluído'],
                'mes'  => $c
            );			
			$data['Cancelado'][] = array (
                'data' => $pedido['Cancelado'],
                'mes'  => $c
            );
                        
            $meses[] = date('m/y', mktime(0, 0, 0, $cmes, 0, $de_ano));
			
            $c++;
        }

		foreach ($data as $key => $val) {
            
            $valor[] = array (
                'name' => $key,
                'data' => $this->data($val, $meses),
                'lineWidth' => 1,
                'visible' => true,
                'shadow' => false
            );
        }

        $dados['meses'] = $meses;
        $dados['valor'] = $valor;
        
        return $dados;
    }
    
    function pedido_valor_mensal ($de, $at) {
        
        $query = $this->db->query('select a.status, sum(b.quantidade * b.valor) total from pedidos a, pedidos_itens b
            where a.status not in ("Removido") and b.status not in ("Cancelado") and b.tipo not in ("Encargo") and a.id = b.pedido_id and a.data between "'. $de .'" and "'. $at .'" group by a.status');
        
        $arr = array();
        
        foreach ($query->result_array() as $row) {
            $arr[$row['status']] = round($row['total'], 2);
        }
        
        foreach (array('Pendente', 'Cancelado', 'Produção', 'Refazer', 'Enviado', 'Concluído') as $status) {
            if (empty($arr[$status])) {
                $arr[$status] = null;
            }
        }
        
        return $arr;
    }
    
    function data ($data, $meses, $tipo = '') {
        
        $result = array();

        for ($i=0; $i<count($meses); $i++) {
            foreach ($data as $key => $val) {
                if ($val['mes'] == $i) {
                    if ($val['data'] > 0) {
                        $result[$i] = $val['data'];
                    }
                }
                else if (empty($result[$i])) {
                    $result[$i] = null;
                }
            }
        }

        return $result;
    }
}