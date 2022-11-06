<?php
class Clientes extends CI_Controller {

    public function __construct () {
        parent::__construct();
        if (!$this->session->userdata('login')) {
            redirect('login');
        }
    }

	public function index($status = 'ativos')
	{
        $this->listar_clientes($status);        
	}
    
    function listar_clientes ($status = 'ativos', $pagina = 0) {

        $this->load->model('cliente_model', 'cliente');
		$this->load->model('logs_model', 'logs');
		$this->logs->gravar();
        
        $ordem = $this->session->userdata('ordem');        
        $total = $this->cliente->total_clientes($status);
        
        $config['first_link'] = 'Primeira Página &nbsp;&nbsp;';
        $config['last_link']  = '&nbsp;&nbsp; Última Página';
        $config['next_link']  = 'Próxima &raquo;';
        $config['prev_link']  = '&laquo; Anterior';
        $config['base_url']   = site_url('clientes/listar-clientes/'. $status);
        $config['uri_segment'] = 4;        
        $config['total_rows'] = $total;
        $config['per_page']   = 30;

        $this->load->library('pagination', $config);

        $clientes = $this->cliente->listar_clientes($status, $pagina);
        $paginacao = $this->pagination->create_links();
        
        $dados['status'] = $status;
        $dados['ordem'] = str_replace('_desc', '', $ordem);
        $dados['pagina'] = $pagina;
        $dados['clientes'] = $clientes;
        $dados['paginacao'] = $paginacao;
        
		$this->load->view('clientes/index.phtml', $dados);
    }
    
	function cep_cliente () {
        
        $cep = $this->input->post('cep');
        $cep = str_replace('-', '', $cep);
        
		$stream = stream_context_create(array('http' => 
			array(
				'timeout' => 5,
			)
		));
		
        if(!$json = @file_get_contents('http://api.postmon.com.br/v1/cep/'. $cep, false, $stream)) {
            
            $json = '{}';
        }
		
		header('Content-Type: application/json');
		
		echo $json;
    }
	
    function ordenar_cliente ($status, $pagina, $ordenar) {
        
		$this->load->model('logs_model', 'logs');
		$this->logs->gravar();
		
        $ordem = $this->session->userdata('ordem');
        
        if ($ordem['cliente'] == $ordenar) {
            
            if (substr($ordenar, -5) == '_desc') {
                
                $ordenar = str_replace('_desc', '', $ordenar);
                
            } else {
                
                $ordenar = $ordenar .'_desc';
            }
        }
        
        $this->session->set_userdata('ordem', array(
            'cliente' => $ordenar
        ));
        
        redirect('clientes/listar-clientes/'. $status .'/'. $pagina);
    }
    
    function adicionar_cliente () {
        
		$this->load->model('logs_model', 'logs');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('cliente', 'Cliente', 'required');
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
        
        $this->form_validation->set_error_delimiters('', '');
        
		if ($this->form_validation->run() == FALSE)
		{
			$this->logs->gravar();
			
			$this->load->model('cliente_model', 'cliente');
			
            $dados['scripts'] = array(
                'js/cliente.js'
            );
            
            $this->load->view('clientes/adicionar-cliente.phtml', $dados);
            
        } else {
            
            $login = $this->session->userdata('login');
            
			$nascimento  = $this->input->post('nascimento');
			list($dia, $mes, $ano) = explode('/', $nascimento);
			
			$nascimento = $ano .'-'. $mes .'-'. $dia;
			
            $dados = array (
                'usuario_id' => $login['id'],
                'cliente' => trim($this->input->post('cliente')),                
                'documento' => trim($this->input->post('documento')),                
                'nascimento' => $nascimento,                
                'email' => trim($this->input->post('email')),
                'telefone' => $this->input->post('telefone'),
				'cep' => $this->input->post('cep'),
				'endereco' => trim($this->input->post('endereco')),
				'numero' => trim($this->input->post('numero')),
				'complemento' => trim($this->input->post('complemento')),
				'bairro' => trim($this->input->post('bairro')),
				'cidade' => trim($this->input->post('cidade')),
				'estado' => trim($this->input->post('estado')),
                'observacao' => trim($this->input->post('observacao')),
				'origem' => $this->input->post('origem'),
                'data' => date('Y-m-d H:i:s')
            );
            
            $this->db->insert('cliente', $dados);
            
			$this->logs->gravar('cliente-adicionado:'. $this->db->insert_id());			
			
            $this->session->set_flashdata('sucesso', 'O cliente foi adicionado com sucesso!');
            
            redirect('clientes');
        }
    }
    
    function editar_cliente ($id) {
        
        $this->load->library('form_validation');
		$this->load->model('logs_model', 'logs');

        $this->form_validation->set_rules('cliente', 'Cliente', 'required');
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
        
        $this->form_validation->set_error_delimiters('', '');
        
		if ($this->form_validation->run() == FALSE)
		{
            $this->load->model('cliente_model', 'cliente');
			
			$this->logs->gravar();
        
            $cliente = $this->cliente->dados_cliente($id);
        
            $dados['id'] = $id;
            $dados['cliente'] = $cliente;
            $dados['scripts'] = array(
                'js/cliente.js'
            );
            
            $this->load->view('clientes/editar-cliente.phtml', $dados);
            
        } else {
            
			$nascimento  = $this->input->post('nascimento');
			list($dia, $mes, $ano) = explode('/', $nascimento);
			
			$nascimento = $ano .'-'. $mes .'-'. $dia;
			
            $dados = array (
                'cliente' => trim($this->input->post('cliente')),                
                'documento' => trim($this->input->post('documento')),
				'nascimento' => $nascimento,      
                'email' => trim($this->input->post('email')),
                'telefone' => $this->input->post('telefone'),
				'cep' => $this->input->post('cep'),
				'endereco' => trim($this->input->post('endereco')),
				'numero' => trim($this->input->post('numero')),
				'complemento' => trim($this->input->post('complemento')),
				'bairro' => trim($this->input->post('bairro')),
				'cidade' => trim($this->input->post('cidade')),
				'estado' => trim($this->input->post('estado')),
                'observacao' => trim($this->input->post('observacao')),
				'origem' => $this->input->post('origem'),
                'status' => $this->input->post('status')
            );
            
            $this->db->where('id', $id);
            $this->db->update('cliente', $dados);
            
			$this->logs->gravar('cliente-editado:'. $id);
			
            $this->session->set_flashdata('sucesso', 'O cliente foi editado com sucesso!');
            
            redirect('clientes');
        }
    }
    
    function historico ($id) {
		
		$this->load->model('cliente_model', 'cliente');
        $this->load->model('pedido_model', 'pedido');
		$this->load->model('vendas_model', 'vendas');
		$this->load->model('logs_model', 'logs');
		
		$this->logs->gravar();
		
        $cliente = $this->cliente->dados_cliente($id);
		$pedidos = $this->pedido->dados_pedido_cliente($id);
		$vendas  = $this->vendas->dados_vendas_cliente($id);
    
        $dados['id'] = $id;
        $dados['cliente'] = $cliente;
		$dados['pedidos'] = $pedidos;
		$dados['vendas']  = $vendas;
        
		$dados['scripts'] = array(
			'js/cliente.js'
		);
		
        $this->load->view('clientes/historico.phtml', $dados);
    }
    
    function remover_cliente ($status, $id) {

        if($this->session->userdata('nivel') != 'Administrador' &&
           $this->session->userdata('nivel') != 'Gerente') {
            exit('Acesso inválido');
        }
    
		$this->load->model('logs_model', 'logs');
		$this->logs->gravar('cliente-removido:'. $id);
	
        $this->session->set_flashdata('sucesso', 'O cliente foi removido com sucesso!');
    
        $this->db->where('id', $id);
        $this->db->update('cliente', array(
            'status' => 'Removido'
        ));
        
        redirect('clientes/listar-clientes/'. $status);
    }
	
	
	function status_venda ($cid, $id, $num) {

		$arr = array(
			1 => 'Concluído',
			2 => 'Cancelado'
		);
		
		$query = $this->db->query('select a.status, a.estoque_id, a.quantidade from vendas_itens a inner join vendas b on a.vendas_id = b.id 
			where a.id = "'. $id .'" and b.cliente_id = "'. $cid .'"');
		
		if ($query->num_rows() > 0) {
		
			$row   = $query->row_array();
			
			if ($arr[$num] != $row['status']) {
				
				$this->load->model('estoque_model', 'estoque');
				
				$this->load->model('logs_model', 'logs');
				$this->logs->gravar('vendas-status-alterado:'. $id .':'. $arr[$num]);
			
				$this->session->set_flashdata('sucesso', 'O status da venda foi alterado com sucesso!');
			
				$this->db->where('id', $id);
				$this->db->update('vendas_itens', array(
					'status' => $arr[$num],
					'data_status' => date('Y-m-d H:i:s')
				));
				
				switch ($arr[$num]) {
					case 'Cancelado':
						if ($row['status'] == 'Concluído') {				
							$this->estoque->aumentar_quantidade($row['estoque_id'], $row['quantidade']);
						}
						break;
					case 'Concluído':
						if ($row['status'] == 'Cancelado') {
							$this->estoque->reduzir_quantidade($row['estoque_id'], $row['quantidade']);
						}
						break;					
				}
			}
			
		}
		
        redirect('clientes/historico/'. $cid);
    }
}