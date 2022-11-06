<?php

class Pedido_model extends CI_Model
{
    function __construct () 
    {
        parent::__construct();
    }

    function listar_pedidos ($status = 'pendente', $pagina = '0') {
                       
        $query = $this->db->query('select a.*, b.cliente, b.origem, c.nome autor from pedidos a, cliente b, usuario c where a.cliente_id = b.id and a.usuario_id = c.id and a.status in("'. $status .'") order by data asc, id asc limit '. $pagina .', 30');

        $arr = array();
        
        foreach ($query->result_array() as $row) {
            
            $row['total'] = $this->valor_total($row['id']);
                                   
            $row['itens'] = $this->itens($row['id'], 'Produto');
            
            $arr[] = $row;
        }
        
        return $arr;
    }
    
    function listar_ultimos_pedidos () {                
        
        $query = $this->db->query('select a.*, b.cliente, c.nome autor from pedidos a, cliente b, usuario c where a.cliente_id = b.id and a.usuario_id = c.id and a.status not in ("Removido") order by data desc limit 5');

        $arr = array();
        
        foreach ($query->result_array() as $row) {
            
            $row['total'] = $this->valor_total($row['id']);
            
            $row['itens'] = $this->itens($row['id'], 'Produto');
            
            $arr[] = $row;
        }
        
        return $arr;
    }
    
    function total_pedidos ($cid = '') {
       
        $where = '';
        
        if (!empty($cid)) {
            $where = ' cliente_id = "'. $cid .'" and ';
        }
       
        $query = $this->db->query('select id from pedidos where '. $where .' status not in("Removido")');
        
        return $query->num_rows();
    }
    
    function total_lista_pedidos ($status = '') {
       
        $where = '';
        
        if (!empty($status)) {
            $where = ' where status = "'. $status .'"';
        }
       
        $query = $this->db->query('select id from pedidos '. $where);
        
        return $query->num_rows();
    }
    
    function dados_pedido ($id) {
        
        $query = $this->db->query('select * from pedidos where id = "'. $id .'"');
        
        $arr = $query->row_array();
        
        $arr['itens'] = $this->itens($id, 'Produto');
        $arr['total'] = $this->valor_total($id);
        
        $arr['encargo'] = $this->itens($id, 'Encargo');
        $arr['frete'] = $this->itens($id, 'Postagem');
        
        return $arr;
    }
    
    function pedido_id ($codigo) {
        
        $query = $this->db->query('select id from pedidos where codigo = "'. $codigo .'"');
        $row   = $query->row_array();
        
        return $row['id'];
    }
    
    function postagem_existe ($pedido, $item) {
        
        $query = $this->db->query('select id from pedidos_itens where pedido_id = "'. $pedido .'" and item = "'. $item .'" and status = "Ativo"');
        
        return $query->num_rows() ? true : false;
    }
        
    function dados_pedido_cliente ($cid, $pid = '') {
        
        $where = '';
        
        if (!empty($pid)) {
            
            $where = ' id = "'. $pid .'" and ';
        }
        
        $query = $this->db->query('select * from pedidos where '. $where .' cliente_id = "'. $cid .'" and status not in("Removido")');
        
        $arr = array();
        
        $total = 0;
        
        foreach ($query->result_array() as $row) {
                                   
            $row['itens'] = $this->itens($row['id']);
            $row['subtotal'] = $this->valor_total($row['id']);
            
            $arr[$row['status']][] = $row;
        }
        
        $ordem = array();
        
        if (!empty($arr['Pendente'])) {
            $ordem[] = 'Pendente';
        }
        
        if (!empty($arr['Produção'])) {
            $ordem[] = 'Produção';
        }
        
        if (!empty($arr['Refazer'])) {
            $ordem[] = 'Refazer';
        }
        
        if (!empty($arr['Enviado'])) {
            $ordem[] = 'Enviado';
        }
        
        if (!empty($arr['Concluído'])) {
            $ordem[] = 'Concluído';
        }
        
        if (!empty($arr['Removido'])) {
            $ordem[] = 'Removido';
        }
        
        if (!empty($arr['Cancelado'])) {
            $ordem[] = 'Cancelado';
        }
        
        $narr = array_merge(array_flip($ordem), $arr);
        
        return $narr;
    }
    
    
    function itens ($id, $tipo = '') {
        
        $where = '';
        
        if (!empty($tipo)) {
            
            $where = ' tipo = "'. $tipo .'" and ';
        }
        
        $query = $this->db->query('select * from pedidos_itens where '. $where .' pedido_id = "'. $id .'" and status = "Ativo" order by tipo, id');
        
        $arr = array();
        
        foreach ($query->result_array() as $row) {            
			$arr[$row['tipo']][] = $row;		
        }
        
        return $query->result_array();		
    }
    
    function valor_total ($id) {
        
        $query = $this->db->query('select sum(quantidade * valor) total from pedidos_itens where pedido_id = "'. $id .'" and tipo in("Produto") and status = "Ativo"');

        $total = 0;
        
        if ($query->num_rows() > 0) {
        
            $row = $query->row_array();            
            $total = $row['total'];
        
            $percentual = $this->db->query('select sum(quantidade * valor) total from pedidos_itens where pedido_id = "'. $id .'" and item = "Percentual (%)" and tipo in("Encargo") and status = "Ativo"');
            $percentual = $percentual->row_array();
        
            $total -= (($total / 100) * $percentual['total']);
        
            $valor = $this->db->query('select sum(quantidade * valor) total from pedidos_itens where pedido_id = "'. $id .'" and item = "Valor (R$)" and tipo in("Encargo") and status = "Ativo"');
            $valor = $valor->row_array();
            
            $total -= $valor['total'];
        }
        
        return $total;
    }
}