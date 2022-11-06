<?php

class Cliente_model extends CI_Model
{
    function __construct () 
    {
        parent::__construct();
    }

    function listar_clientes ($status, $pagina) {
        
        $this->load->model('vendas_model', 'vendas');
        $this->load->model('pedido_model', 'pedido');
        
        $ordem = $this->session->userdata('ordem');
        
        $order = 'order by data desc';
        
        if (!empty($ordem['cliente']) && $ordem['cliente']) {
            
            $order = 'order by '. str_replace('_', ' ', $ordem['cliente']);
        }
        
        $where = '';
        
        if ($status != 'todos') {
            
            $transf = array(
                'ativos'   => 'Ativo',
                'inativos' => 'Inativo'
            );
            
            $where = ' and status = "'. (!empty($transf[$status]) ? $transf[$status] : '') .'"';
        }
        
        $query = $this->db->query('select * from cliente where status not in("Removido") '. $where . ' '. $order .' limit '. $pagina .', 30');
        
        $arr = array();
        
        foreach ($query->result_array() as $row) {
            
            $pedidos = $this->pedido->total_pedidos($row['id']);
            $vendas  = $this->vendas->total_vendas($row['id']);
            
            $row['pedidos'] = $pedidos + $vendas;
            $arr[] = $row;
        }
        
        return $arr;
    }
    
    
    function listar_ultimos_clientes () {
        
        $this->load->model('vendas_model', 'vendas');
        $this->load->model('pedido_model', 'pedido');
        
        $query = $this->db->query('select * from cliente where status not in("Removido") order by data desc limit 5');
        
        $arr = array();
        
        foreach ($query->result_array() as $row) {
            $pedidos = $this->pedido->total_pedidos($row['id']);
            $vendas  = $this->vendas->total_vendas($row['id']);
            
            $row['pedidos'] = $pedidos + $vendas;
            $arr[] = $row;
        }
        
        return $arr;
    }
    
    function listar_todos_clientes () {
        
        $query = $this->db->query('select id, cliente from cliente where status not in("Removido") order by cliente desc');
        
        return $query->result_array();
    }
    
    function total_clientes ($status) {
        
        $where = '';
        
        if ($status != 'todos') {
            
            $transf = array(
                'ativos'   => 'Ativo',
                'inativos' => 'Inativo'
            );
            
            $where = 'and status = "'. (!empty($transf[$status]) ? $transf[$status] : '') .'"';
        }
        
        $query = $this->db->query('select id from cliente where status not in("Removido") '. $where);
        
        return $query->num_rows();
    }
    
    function dados_cliente ($id) {
        
        $query = $this->db->query('select * from cliente where id = "'. $id .'" and status in ("Ativo", "Inativo")');
        
        return $query->row_array();
    }
    
    function busca_nome ($nome) {
        
        $query = $this->db->query('select id, cliente, documento from cliente where cliente like "%'. $nome .'%" and status not in("Removido") limit 5');
        
        return $query->result_array();
    }
    
    function busca_documento ($documento) {
        
        $query = $this->db->query('select id, cliente, documento from cliente where documento like "%'. $documento .'%" and status not in("Removido") limit 5');
        
        return $query->result_array();
    }
}