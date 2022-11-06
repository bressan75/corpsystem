<?php
class Estoque_model extends CI_Model {

    function __construct () 
    {
        parent::__construct();
    }

    function listar_produtos () {
        
        $query = $this->db->query('select * from estoque where status not in ("Removido") order by status, modelo, codigo');
        
        $arr = array();
        
        foreach ($query->result_array() as $row) {
            
            $arr[$row['categoria']][] = $row;
            
        }
        
        return $arr;
    }
	
    function listar_estoque () {
        
        $query = $this->db->query('select * from estoque where status not in ("Removido") order by status, modelo, codigo');
        
        $arr = array();
        
        foreach ($query->result_array() as $row) {
            
            $arr[] = $row;
            
        }
        
        return $arr;
    }	
    
    function listar_produtos_vendas ($q = '') {
        
        $w = '';
        
        if (!empty($q)) {
            $w = ' (codigo like "'. $q .'%" or modelo like "%'. $q .'%" or descricao like "%'. $q .'%") and ';
        }
        
        $query = $this->db->query('select * from estoque where '. $w .' status in ("Ativo") and quantidade > 0 order by status, modelo, codigo');
        
        $arr = array();
        
        foreach ($query->result_array() as $row) {
            
            $arr[$row['categoria']][] = $row;
            
        }
        
        return $arr;
    }
    
    function dados_produto ($id) {
        
        $query = $this->db->query('select * from estoque where id = "'. $id .'" and status not in ("Removido")');
        
        return $query->row_array();
    }
    
    function dados_produto_ativo ($id) {
        
        $query = $this->db->query('select * from estoque where id = "'. $id .'" and status in ("Ativo")');
        
        return $query->row_array();
    }
    
    
    function codigo_produto ($categoria) {
        
         $query = $this->db->query('select id, codigo from estoque where categoria = "'. $categoria .'" and status not in("Removido") order by id desc limit 1');
        
        if ($query->num_rows() == 0) {
            
            $codigo = '0001';
       
        } else {
            
            $row = $query->row_array();
            
            if (preg_match('/[A-Z]{3}[0-9]{4}/', $row['codigo'])) {
                $codigo = (int)preg_replace('/[A-Z]+/', '', $row['codigo']);
            } else {
                $codigo = $row['id'];
            }
            
            $codigo = sprintf('%04d', ++$codigo);  
        }
        
        return $codigo;
    }
    
    function reduzir_quantidade ($id, $qtd) {
        
        $query = $this->db->query('select quantidade from estoque where id = "'. $id .'"');
        
        if ($query->num_rows() > 0) {
            
            $row = $query->row_array();
            $quantidade = $row['quantidade'] - $qtd;
            
            $this->db->where('id', $id);
            $this->db->update('estoque', array(
                'quantidade' => $quantidade
            ));
        }        
    }
    
    function aumentar_quantidade ($id, $qtd) {
        
        $query = $this->db->query('select quantidade from estoque where id = "'. $id .'"');
        
        if ($query->num_rows() > 0) {
            
            $row = $query->row_array();
            $quantidade = $row['quantidade'] + $qtd;
            
            $this->db->where('id', $id);
            $this->db->update('estoque', array(
                'quantidade' => $quantidade
            ));
        }        
    }

	public function valor ($id) {

		$query = $this->db->query('select valor from estoque where id = "'.$id.'"');

		if ($query->num_rows() == 0) {
			return false;
		}

		$row = $query->row_array();

		return $row['valor'];
	}	
	
	public function custo ($id) {

		$query = $this->db->query('select bruto from estoque where id = "'.$id.'"');

		if ($query->num_rows() == 0) {
			return false;
		}

		$row = $query->row_array();

		return $row['bruto'];
	}	
	
	public function descricao ($id) {

		$query = $this->db->query('select modelo, categoria, descricao from estoque where id = "'.$id.'"');

		if ($query->num_rows() == 0) {
			return false;
		}

		$row = $query->row_array();

		return $row['modelo'].' - '.$row['categoria'].' - '.$row['descricao'];
	}		
}