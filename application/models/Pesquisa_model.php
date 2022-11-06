<?php

class Pesquisa_model extends CI_Model
{
    function __construct () 
    {
        parent::__construct();
    }
        
    public function resultado ($palavra) {
        
        $palavra = trim($palavra);
        
        if (strlen($palavra) < 1) {
            return array();
        }
        
        $clientes = $this->clientes($palavra);        
        $pedidos = $this->pedidos($palavra);        
        
        $arr = array();
        
        if (count($clientes) > 0) {
            $arr['clientes'] = $clientes;
        }
        
        if (count($pedidos) > 0) {
            $arr['pedidos'] = $pedidos;
        }       
        
        return $arr;
    }
    
    public function clientes ($palavra) {
        
        $this->load->model('vendas_model', 'vendas');
        $this->load->model('pedido_model', 'pedido');
        
        if (substr($palavra, 0, 8) == 'cliente:' && count(explode(':', $palavra)) == 3) {
            
            list($tabela, $coluna, $termo) = explode(':', $palavra);            
            
            $tabela = trim($tabela);
            $coluna = trim($coluna);
            $termo = trim($termo);
            
            switch ($coluna) {
                case 'cliente':
                case 'telefone':
                case 'email':
                case 'cep':
                case 'endereco':
                case 'bairro':
                case 'cidade':
                case 'estado':
                case 'observacao':
                case 'origem':
                case 'status':
                    $query = $this->db->query('select * from cliente where status not in ("Removido") and '. $coluna .' like "'. $termo .'"');                
                break;                
                case 'data':
                case 'modificado':
                    list($dia, $mes, $ano) = explode('/', $termo);
                    $query = $this->db->query('select * from cliente where status not in ("Removido") and '. $coluna .' like "'. $ano .'-'. $mes .'-'. $dia .'%"');
                    break;                
                default:
                    return array();
                    break;
            }
        } else {
            
            $query = $this->db->query('select * from cliente where status not in ("Removido") and (cliente like "%'. $palavra .'%" or
                    telefone like "%'. $palavra .'%" or email like "%'. $palavra .'%" or cep like "%'. $palavra .'%" or
                        endereco like "%'. $palavra .'%" or bairro like "%'. $palavra .'%" or cidade like "%'. $palavra .'%" or
                            estado like "%'. $palavra .'%" or observacao like "%'. $palavra .'%" or origem like "%'. $palavra .'%" or
                        status like "%'. $palavra .'%")');
        }
        
        $arr = array();
        
        foreach ($query->result_array() as $row) {
            $pedidos = $this->pedido->total_pedidos($row['id']);
            $vendas  = $this->vendas->total_vendas($row['id']);
            
            $row['pedidos'] = $pedidos + $vendas;
            $arr[] = $row;
        }
		
        return $arr;
    }    
        
    public function pedidos ($palavra) {
                 
        $this->load->model('pedido_model', 'pedido');
        
        if (substr($palavra, 0, 7) == 'pedido:' && count(explode(':', $palavra)) == 3) {
            
            list($tabela, $coluna, $termo) = explode(':', $palavra);            
            
            $tabela = trim($tabela);
            $coluna = trim($coluna);
            $termo = trim($termo);
            
            switch ($coluna) {
                case 'id':
				case 'status':
                case 'postagem':
                case 'rastreio':
                case 'status':
                    $query = $this->db->query('select a.*, b.nome autor, c.cliente, c.origem from pedidos a, usuario b, cliente c where a.usuario_id = b.id and a.cliente_id = c.id and a.status not in ("Removido") and a.'. $coluna .' like "'. $termo .'"');                
                break;                
                case 'data':
                case 'modificado':
                    list($dia, $mes, $ano) = explode('/', $termo);
                    $query = $this->db->query('select a.*, b.nome autor, c.cliente, c.origem from pedidos a, usuario b, cliente c where a.usuario_id = b.id and a.cliente_id = c.id and a.status not in ("Removido") and a.'. $coluna .' like "'. $ano .'-'. $mes .'-'. $dia .'%"');
                    break;                
                default:
                    return array();
                    break;
            }
                        
        } else {
            
            $query = $this->db->query('select a.*, c.nome autor, d.cliente, d.origem from pedidos a, pedidos_itens b, usuario c, cliente d where b.pedido_id = a.id and a.usuario_id = c.id and a.cliente_id = d.id and a.status not in ("Removido") and
                (a.id like "%'. $palavra .'%" or c.nome like "%'. $palavra .'%" or b.item like "%'. $palavra .'%" or b.descricao like "%'. $palavra .'%" or a.postagem like "%'. $palavra .'%" or a.rastreio like "%'. $palavra .'%" or a.status like "%'. $palavra .'%" or a.data like "%'. $palavra .'%") group by a.id');
        }
        
        $arr = array();
        
        foreach ($query->result_array() as $row) {
            
			$row['total'] = $this->pedido->valor_total($row['id']);
            
            $row['itens'] = $this->pedido->itens($row['id'], 'Produto');
            
            $arr[] = $row;
        }
        
        return $arr;
    }    
}