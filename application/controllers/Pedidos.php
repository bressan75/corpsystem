<?php class Pedidos extends CI_Controller {
    
    public function __construct () {
        parent::__construct();
        if (!$this->session->userdata('login')) {
            redirect('login');
        }
    }
    
    public function index($status = 'pendente')
	{
        $this->listar_pedidos($status);
	}
    
    function listar_pedidos ($status = 'pendente', $pagina = 0) {

		/*if($this->session->userdata('nivel') != 'Administrador' &&
           $this->session->userdata('nivel') != 'Gerente') {
            exit('Acesso inválido');
        }*/
	
        $this->load->model('pedido_model', 'pedido');
        $this->load->model('cliente_model', 'cliente');
		$this->load->model('logs_model', 'logs');
		
		$this->load->helper('text');
		
		$this->logs->gravar();
        
        $ordem = $this->session->userdata('ordem');        
        $total = $this->pedido->total_lista_pedidos($status);
        
        $config['first_link'] = 'Primeira Página &nbsp;&nbsp;';
        $config['last_link']  = '&nbsp;&nbsp; Última Página';
        $config['next_link']  = 'Próxima &raquo;';
        $config['prev_link']  = '&laquo; Anterior';
        $config['base_url']   = site_url('pedidos/listar-pedidos/'. $status);
        $config['uri_segment'] = 4; 
        $config['total_rows'] = $total;
        $config['per_page']   = 30;

        $this->load->library('pagination', $config);

        $pedidos = $this->pedido->listar_pedidos($status, $pagina);
        $paginacao = $this->pagination->create_links();
        
        $dados['ordem'] = str_replace('_desc', '', $ordem);
        $dados['pagina'] = $pagina;
        $dados['status'] = $status;
        $dados['pedidos'] = $pedidos;
        $dados['paginacao'] = $paginacao;
        
		$dados['scripts'] = array(
			'js/cliente.js'
		);
		
		$this->load->view('pedidos/index.phtml', $dados);
    }
    
    function adicionar_pedido ($cid = '') {
		
		$this->load->model('logs_model', 'logs');
		
		$this->load->model('estoque_model', 'estoque');
		
		$this->load->library('form_validation');

        $this->form_validation->set_rules('cid', 'Cliente', 'required');
        
        if ($this->input->post('cid') == 'novo') {
            
            $this->form_validation->set_rules('cliente', 'Nome', 'required');
            $this->form_validation->set_rules('documento', 'Documento');
			$this->form_validation->set_rules('nascimento', 'Data de nascimento');
            $this->form_validation->set_rules('telefone', 'Telefone');
            $this->form_validation->set_rules('email', 'E-mail');
            $this->form_validation->set_rules('cep', 'CEP', 'required');
            $this->form_validation->set_rules('endereco', 'Endereço', 'required');
            $this->form_validation->set_rules('numero', 'Número', 'required');
            $this->form_validation->set_rules('complemento', 'Complemento');
            $this->form_validation->set_rules('bairro', 'Bairro', 'required');
            $this->form_validation->set_rules('cidade', 'Cidade', 'required');
            $this->form_validation->set_rules('estado', 'Estado', 'required');
            $this->form_validation->set_rules('observacao', 'Observação');
            $this->form_validation->set_rules('origem', 'Origem', 'required');        
        }                
        
        $this->form_validation->set_rules('quantidade[]', 'Quantidade');
        
        $this->form_validation->set_rules('encargo_valor', 'Valor');  
        $this->form_validation->set_rules('encargo_percentual', 'Percentual');
        
        $this->form_validation->set_rules('postagem', 'Postagem', 'required');
		$this->form_validation->set_rules('rastreio', 'Rastreio');		
		$this->form_validation->set_rules('data', 'Data', 'required');		
        
        $this->form_validation->set_error_delimiters('', '');
        
		if ($this->form_validation->run() == FALSE)
		{
            $this->load->model('cliente_model', 'cliente');			
            
			$this->logs->gravar();
            
            $dados['scripts'] = array(
                'js/cliente.js'
            );            
            
            $dados['estoques'] = $this->estoque->listar_estoque();
            
            $dados['cid'] = $cid;            
            
			$dados['clientes'] = $this->cliente->listar_todos_clientes();
            
            $this->load->view('pedidos/adicionar-pedido.phtml', $dados);
            
        } else {            			
			
            $login = $this->session->userdata('login');
            
            if ($this->input->post('cid') == 'novo') {
            
				$cliente = $this->input->post('cliente');
				$email   = $this->input->post('email');
				
				$nascimento  = $this->input->post('nascimento');
				list($dia, $mes, $ano) = explode('/', $nascimento);
				
				$nascimento = $ano .'-'. $mes .'-'. $dia;
				
                $this->db->insert('cliente', array (
                    'usuario_id' => $login['id'],
                    'cliente' => $this->input->post('cliente'),
					'documento' => $this->input->post('documento'),
					'nascimento' => $nascimento,
                    'email' => $this->input->post('email'),
                    'telefone' => $this->input->post('telefone'),
                    'cep' => $this->input->post('cep'),
                    'endereco' => $this->input->post('endereco'),
                    'numero' => $this->input->post('numero'),
                    'complemento' => $this->input->post('complemento'),
                    'bairro' => $this->input->post('bairro'),
                    'cidade' => $this->input->post('cidade'),
                    'estado' => $this->input->post('estado'),
                    'observacao' => $this->input->post('observacao'),
                    'origem' => $this->input->post('origem'),
                    'data' => date('Y-m-d H:i:s')
                ));
                
                $cid = $this->db->insert_id();
                
                $this->logs->gravar('cliente-adicionado:'. $cid);	
            
            } else {
                
                $cid = $this->input->post('cid');
				$query = $this->db->query('select cliente, email from cliente where id = "'. $cid .'"');
				$row   = $query->row_array();
				
				$cliente = $row['cliente'];
				$email   = $row['email'];
            }                       
            
			list($dia, $mes, $ano) = explode('/', $this->input->post('data'));
			$data = $ano .'/'. $mes .'/'. $dia;
			
            $this->db->insert('pedidos', array (
                'usuario_id' => $login['id'],
                'cliente_id' => $cid,                
                'postagem' => $this->input->post('postagem'),
				'rastreio' => $this->input->post('rastreio'),
                'data' => $data,
                'data_status' => date('Y-m-d H:i:s')
            ));
            
            $pid = $this->db->insert_id();
            
			$this->logs->gravar('pedido-adicionado:'. $pid);
                        		
			$ids = $this->input->post('ids');
		
        	$produto = '';
			
			if(!is_array($ids)) {
				
				$this->session->set_flashdata('erro', 'Desculpe, informe a quantidade corretamente e tente novamente!');
        		redirect(pedidos/adicionar-pedido);
				
        	}else{

        		foreach ($ids as $referenciaId) {

        			// Array da seleção quantidade
        			$qtds = $this->input->post('qtd_'.$referenciaId);

                    $dados = array (
                        'pedido_id' => $pid,
                        'item' => $referenciaId,
						'descricao' => $this->estoque->descricao($referenciaId),
                        'quantidade' => $qtds,                        
                        'custo' => $this->estoque->custo($referenciaId),
						'valor' => $this->estoque->valor($referenciaId),
                        'tipo' => 'Produto'
                    );
                    
                    $this->db->insert('pedidos_itens', $dados); 					
        		}
        	}            
            
            if ($this->input->post('encargo_valor')) {
            
                $this->db->insert('pedidos_itens', array (
                    'pedido_id' => $pid,
                    'item' => 'Valor (R$)',
                    'quantidade' => '1',
                    'valor' => str_replace(',', '.', str_replace('.', '', $this->input->post('encargo_valor'))),
                    'tipo' => 'Encargo'
                ));            
            }
            
            if ($this->input->post('encargo_percentual')) {
            
                $this->db->insert('pedidos_itens', array (
                    'pedido_id' => $pid,
                    'item' => 'Percentual (%)',
                    'quantidade' => '1',
                    'valor' => str_replace(',', '.', str_replace('.', '', $this->input->post('encargo_percentual'))),
                    'tipo' => 'Encargo'
                ));
            }
			
            $this->session->set_flashdata('sucesso', 'O pedido foi adicionado com sucesso!');
            
            redirect('pedidos/listar-pedidos');
        }
    }
	
	function editar_pedido ($pid = '') {
		
		$this->load->model('logs_model', 'logs');
		
		$this->load->model('estoque_model', 'estoque');
		
		$this->load->library('form_validation');
        
        $this->form_validation->set_rules('quantidade[]', 'Quantidade');        
        $this->form_validation->set_rules('encargo_valor', 'Valor');  
        $this->form_validation->set_rules('encargo_percentual', 'Percentual');        
        $this->form_validation->set_rules('postagem', 'Postagem', 'required');
		$this->form_validation->set_rules('rastreio', 'Rastreio');
		$this->form_validation->set_rules('status', 'Status', 'required');
        $this->form_validation->set_rules('data', 'Data', 'required');		
		
        $this->form_validation->set_error_delimiters('', '');
		
		if ($this->form_validation->run() == FALSE)
		{
            $this->load->model('cliente_model', 'cliente');
			$this->load->model('pedido_model', 'pedido');			
            
			$this->logs->gravar();
            
            $dados['scripts'] = array(
                'js/cliente.js'
            );
            
            $pedido = $this->pedido->dados_pedido($pid);
			$cliente = $this->cliente->dados_cliente($pedido['cliente_id']);
			
			$dados['estoques'] = $this->estoque->listar_estoque();			            
            $dados['pid'] = $pid;            
            $dados['cliente'] = $cliente;
			$dados['pedido'] = $pedido;			
            
            $this->load->view('pedidos/editar-pedido.phtml', $dados);
            
        } else {

            $login = $this->session->userdata('login');
            
			$query = $this->db->query('select b.cliente, b.email, a.loja_id, a.status from pedidos a inner join cliente b on a.cliente_id = b.id where a.id = "'. $pid .'"');
			$row   = $query->row_array();
						
            
			list($dia, $mes, $ano) = explode('/', $this->input->post('data'));
			$data = $ano .'/'. $mes .'/'. $dia;
			
			$this->db->where('id', $pid);
            $this->db->update('pedidos', array (                
                'postagem' => $this->input->post('postagem'),
				'rastreio' => $this->input->post('rastreio'),
				'conferido' => 'Sim',
				'status' => $this->input->post('status'),
				'data' => $data
            ));
            
			$this->logs->gravar('pedido-editado:'. $pid);
			
			$ids = $this->input->post('ids');
			
			$produto = '';
            
			
			if(!is_array($ids)) {
				
				$this->session->set_flashdata('erro', 'Desculpe, informe a quantidade corretamente e tente novamente!');
        		redirect(pedidos/adicionar-pedido);
				
        	}else{
				
				// Zera todos itens para inserir os novos				
				$dados = array (
					'status' => 'Cancelado'
				);				
			
				$this->db->where('pedido_id', $pid);
				$this->db->where( array('pedido_id' => $pid, 'tipo' => 'Produto') );
				$this->db->update('pedidos_itens', $dados);   				
				
        		foreach ($ids as $referenciaId) {

        			// Array da seleção quantidade
        			$qtds = $this->input->post('qtd_'.$referenciaId);

                    $dados = array (
                        'pedido_id' => $pid,
                        'item' => $referenciaId,
						'descricao' => $this->estoque->descricao($referenciaId),
                        'quantidade' => $qtds,                        
                        'custo' => $this->estoque->custo($referenciaId),
						'valor' => $this->estoque->valor($referenciaId),
                        'tipo' => 'Produto'
                    );
                    
                    $this->db->insert('pedidos_itens', $dados); 					
        		}

			}				
			
            if ($this->input->post('encargo_valor')) {
            
				if ($this->input->post('encargo_valor_id')) {
					$this->db->where('id', $this->input->post('encargo_valor_id'));
					$this->db->update('pedidos_itens', array (
						'item' => 'Valor (R$)',
						'quantidade' => '1',
						'valor' => str_replace(',', '.', str_replace('.', '', $this->input->post('encargo_valor'))),
						'tipo' => 'Encargo'
					));
				} else {
					
					$this->db->insert('pedidos_itens', array (
						'pedido_id' => $pid,
						'item' => 'Valor (R$)',
						'quantidade' => '1',
						'valor' => str_replace(',', '.', str_replace('.', '', $this->input->post('encargo_valor'))),
						'tipo' => 'Encargo'
					)); 
					
				}
            } else if ($this->input->post('encargo_valor_id')) {
				
				$this->db->where('id', $this->input->post('encargo_valor_id'));
				$this->db->delete('pedidos_itens');
				
			}
            
            if ($this->input->post('encargo_percentual')) {
            
				if ($this->input->post('encargo_percentual_id')) {			
					$this->db->where('id', $this->input->post('encargo_percentual_id'));
					$this->db->update('pedidos_itens', array (
						'item' => 'Percentual (%)',
						'quantidade' => '1',
						'valor' => str_replace(',', '.', str_replace('.', '', $this->input->post('encargo_percentual'))),
						'tipo' => 'Encargo'
					));
				} else {
					
					$this->db->insert('pedidos_itens', array (
						'pedido_id' => $pid,
						'item' => 'Percentual (%)',
						'quantidade' => '1',
						'valor' => str_replace(',', '.', str_replace('.', '', $this->input->post('encargo_percentual'))),
						'tipo' => 'Encargo'
					));					
				}
            } else if ($this->input->post('encargo_percentual_id')) {
				
				$this->db->where('id', $this->input->post('encargo_percentual_id'));
				$this->db->delete('pedidos_itens');
				
			}
            
            $this->session->set_flashdata('sucesso', 'O pedido foi editado com sucesso!');
            
            redirect('pedidos/listar-pedidos');
        }
    }
	
	function ver_pedido ($cid, $pid) {
		
		$this->load->model('cliente_model', 'cliente');
        $this->load->model('pedido_model', 'pedido');
		$this->load->model('logs_model', 'logs');
		
		$this->logs->gravar();
		
        $cliente = $this->cliente->dados_cliente($cid);
		$pedidos = $this->pedido->dados_pedido_cliente($cid, $pid);
    
        $dados['pid'] = $pid;
        $dados['cliente'] = $cliente;
		$dados['pedidos'] = $pedidos;			
        
        $this->load->view('pedidos/ver-pedido.phtml', $dados);
    }

	function remover_pedido ($status, $id) {

        if($this->session->userdata('nivel') != 'Administrador' &&
           $this->session->userdata('nivel') != 'Gerente') {
            exit('Acesso inválido');
        }
    
		$this->load->model('logs_model', 'logs');
		$this->logs->gravar('pedido-removido:'. $id);
	
        $this->session->set_flashdata('sucesso', 'O pedido foi removido com sucesso!');
    
        $this->db->where('id', $id);
        $this->db->update('pedidos', array(
            'status' => 'Removido'
        ));
        
        redirect('pedidos/listar-pedidos/'. $status);
    }
	
	function status_pedido ($status, $id, $num) {

		$arr = array(
			1 => 'Pendente',
			2 => 'Produção',
			5 => 'Enviado',
			6 => 'Concluído',
			7 => 'Cancelado'
		);
		
		$query = $this->db->query('select b.cliente, b.email, a.loja_id, a.status, a.rastreio from pedidos a inner join cliente b on a.cliente_id = b.id where a.id = "'. $id .'"');
		$row   = $query->row_array();
		
		if ($arr[$num] != $row['status']) {
						
			$this->load->model('logs_model', 'logs');
			
			$this->logs->gravar('pedido-status-alterado:'. $id .':'. $arr[$num]);
		
			$this->session->set_flashdata('sucesso', 'O status do pedido foi alterado com sucesso!');
		
			$this->db->where('id', $id);
			$this->db->update('pedidos', array(
				'status' => $arr[$num],
				'data_status' => date('Y-m-d H:i:s')
			));			
			
			if ($pos  = strpos($row['cliente'], ' ')) {
				$nome = substr($row['cliente'], 0, $pos);
				$nome = trim($nome);
			}
			
			$pedido = !empty($row['loja_id']) ? $row['loja_id'] : $id;		
			
			if (!empty($row['loja_id'])) {
				$LOJA = $this->load->database('loja', true);
				$LOJA->query('update jm_order set order_status_id = "'. $sts .'" where order_id = "'. $row['loja_id'] .'"');
				$LOJA->insert('jm_order_history', array(
					'order_id' => $row['loja_id'],
					'order_status_id' => $sts,
					'notify' => '1',
					'date_added' => date('Y-m-d H:i:s')
				));
			}			
        }
		
        redirect('pedidos/listar-pedidos/'. $status);
    }	
}