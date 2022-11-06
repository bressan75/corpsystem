<?php

class Vendas_model extends CI_Model
{
    function __construct () 
    {
        parent::__construct();
    }

    function listar_vendas ($ini, $fim, $pagina = '0') {
        
        $query = $this->db->query('select a.*, b.cliente, b.origem, c.nome autor from pedidos a, cliente b, usuario c where a.cliente_id = b.id and a.usuario_id = c.id and a.data between "'. $ini .'" and "'. $fim .'" order by a.data desc, a.id desc limit '. $pagina .', 30');

        $arr = array();
        
        foreach ($query->result_array() as $row) {
            
            $row['total'] = $this->valor_total($row['id']);
            $row['itens'] = $this->itens($row['id']);
            
            $arr[] = $row;
        }
        
        return $arr;
    }	
    
    function valor_total ($id) {
        
        $query = $this->db->query('select sum(quantidade * valor) total from pedidos_itens where pedido_id = "'. $id .'" and status in ("Ativo") and tipo = "Produto"');

        $total = 0;
        
        if ($query->num_rows() > 0) {
        
            $row = $query->row_array();            
            $total = $row['total'];
        }
        
        return $total;
    }

    function itens ($id) {
        
        $query = $this->db->query('select * from pedidos_itens where pedido_id = "'. $id .'" and status not in("Cancelado") and tipo = "Produto" order by id');
        
        return $query->result_array();
    }
    
    function total_vendas ($cid = '') {
       
        $where = '';
        
        if (!empty($cid)) {
            $where = ' cliente_id = "'. $cid .'" and ';
        }
       
        $query = $this->db->query('select id from pedidos where '. $where .' status in("Ativo")');
        
        return $query->num_rows();
    }	
	    
    function total_lista_vendas ($ini, $fim) {
       
        $query = $this->db->query('select id from pedidos where status in("Ativo") and data between "'. $ini .'" and "'. $fim .'"');
        
        return $query->num_rows();
    }
}