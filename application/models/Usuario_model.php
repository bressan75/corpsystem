<?php

class Usuario_model extends CI_Model
{
    function __construct () 
    {
        parent::__construct();
    }

    function listar_usuarios ($status, $pagina) {
        
        $where = '';
        
        if ($status != 'todos') {
            
            $transf = array(
                'ativos'   => 'Ativo',
                'inativos' => 'Inativo'
            );
            
            $where = ' and status = "'. (!empty($transf[$status]) ? $transf[$status] : '') .'"';
        }
        
        $query = $this->db->query('select * from usuario where status not in("Removido") '. $where . ' order by id limit '. $pagina .', 30');
        
        return $query->result_array();
    }
    
    function total_usuarios ($status) {
        
        $where = '';
        
        if ($status != 'todos') {
            
            $transf = array(
                'ativos'   => 'Ativo',
                'inativos' => 'Inativo'
            );
            
            $where = 'and status = "'. (!empty($transf[$status]) ? $transf[$status] : '') .'"';
        }
        
        $query = $this->db->query('select id from usuario where status not in("Removido") '. $where);
        
        return $query->num_rows();
    }
    
    function dados_usuario ($id) {
        
        $query = $this->db->query('select * from usuario where id = "'. $id .'" and status in ("Ativo", "Inativo")');
        
        return $query->row_array();
    }
    
    function checar_usuario ($id, $usuario) {
        
        $query = $this->db->query('select * from usuario where id != "'. $id .'" and usuario = "'. $usuario .'" and status in ("Ativo", "Inativo")');
        
        return $query->num_rows();
    }
    
    function usuario_existe ($usuario) {
        
        $query = $this->db->query('select * from usuario where usuario = "'. $usuario .'" and status in ("Ativo", "Inativo")');
        
        return $query->num_rows();
    }
    
    function autenticar ($usuario, $senha) {
        
        $query = $this->db->query('select * from usuario where usuario = "'. $usuario .'"
            and senha = "'. $senha .'" and status = "Ativo" limit 1');
        
        if ($query->num_rows() > 0) {
        
            return $query->row_array();
        }
        
        return false;
    }
}