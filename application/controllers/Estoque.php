<?php

class Estoque  extends CI_Controller {
    
    public function __construct () {
        parent::__construct();
        if (!$this->session->userdata('login')) {
            redirect('login');
        }
    }
    
    function index()
    {
		$this->listar_produtos();
    }
    
    function listar_produtos () {
        
        $this->load->library('form_validation');

        $this->form_validation->set_rules('codigo', 'Código', 'required');
        $this->form_validation->set_rules('modelo', 'Modelo');
        $this->form_validation->set_rules('categoria', 'Categoria', 'required');
        $this->form_validation->set_rules('quantidade', 'Quantidade', 'required');
        $this->form_validation->set_rules('bruto', 'V. Bruto');
        $this->form_validation->set_rules('valor', 'V. Venda', 'required');
        $this->form_validation->set_rules('descricao', 'Descrição', 'required');  
        
        $this->form_validation->set_error_delimiters('', '');
        
		if ($this->form_validation->run() == FALSE)
		{
            $this->load->model('estoque_model', 'estoque');		            
            $this->load->model('logs_model', 'logs');
            $this->logs->gravar();
            
            $produtos = $this->estoque->listar_produtos();
            $dados['produtos'] = $produtos;
            
            $dados['scripts'] = array(
                'js/cliente.js',
                'js/estoque.js'
            );
            
            $this->load->view('estoque/index.phtml', $dados);
        
        
        } else {
            
            $codigo = $this->input->post('codigo');
            $modelo = $this->input->post('modelo');
            $categoria = $this->input->post('categoria');
            $quantidade = $this->input->post('quantidade');
            $bruto = $this->input->post('bruto');
            $valor = $this->input->post('valor');
            $descricao = $this->input->post('descricao');  
            
            //$path1 = '/home/bressanweb/';
            $path2 = 'img/estoque/';
            
            $img   = '';
            
            if(!empty($_FILES['imagem']['tmp_name'])) {
                        
                $filen = $_FILES['imagem']['tmp_name'];
                $filec = $_FILES['imagem']['name'];
                
                //$con_images = $path1 . $path2 . $filec;
				$con_images = $path2 . $filec;
				
				//echo $con_images; die;
                
                copy($filen, $con_images);
                
                $img = $path2 . $filec;
            }
            
            $dados = array (
                'codigo' => $codigo,
                'modelo' => $modelo,
                'categoria' => $categoria,
                'imagem' => $img,
                'quantidade' => $quantidade > 0 ? $quantidade : 1,
                'descricao' => $descricao,
                'bruto' => str_replace(',', '.', str_replace('.', '', $bruto)),
                'valor' => str_replace(',', '.', str_replace('.', '', $valor)),
                'data' => date('Y-m-d H:i:s')
            );
            
            if (empty($bruto)) {
                unset($dados['bruto']);
            }
            
            if (empty($img)) {
                unset($dados['imagem']);
            }
            
            $this->db->insert('estoque', $dados);
            
            redirect('estoque/listar-produtos');
        }
    }
    
    function editar_produto ($id = '') {
        
        if (empty($id)) {
            redirect('estoque/listar-produtos');
        }
        
        $this->load->library('form_validation');

        $this->form_validation->set_rules('categoria', 'Categoria', 'required');
        $this->form_validation->set_rules('codigo', 'Código', 'required');
        $this->form_validation->set_rules('modelo', 'Modelo');
        $this->form_validation->set_rules('quantidade', 'Quantidade', 'required');
        $this->form_validation->set_rules('bruto', 'V. Bruto');
        $this->form_validation->set_rules('valor', 'V. Venda', 'required');
        $this->form_validation->set_rules('descricao', 'Descrição', 'required');
        $this->form_validation->set_rules('status', 'Status', 'required'); 
        
        $this->form_validation->set_error_delimiters('', '');
        
		if ($this->form_validation->run() == FALSE)
		{
            $this->load->model('estoque_model', 'estoque');		            
            $this->load->model('logs_model', 'logs');
            $this->logs->gravar();
            
            $produto = $this->estoque->dados_produto($id);
            
            $dados['id'] = $id;
            $dados['produto'] = $produto;
            
            $dados['scripts'] = array(
                'js/cliente.js',
                'js/estoque.js'
            );
            
            $this->load->view('estoque/editar-produto.phtml', $dados);
        
        } else {
            
            $codigo = $this->input->post('codigo');
            $modelo = $this->input->post('modelo');
            $categoria = $this->input->post('categoria');
            $quantidade = $this->input->post('quantidade');
            $bruto = $this->input->post('bruto');
            $valor = $this->input->post('valor');
            $descricao = $this->input->post('descricao');
            $status = $this->input->post('status');
            
            //$path1 = '/home/bressanweb/';
            $path2 = 'img/estoque/';
            
            $img   = '';
            
            if(!empty($_FILES['imagem']['tmp_name'])) {
                        
                $filen = $_FILES['imagem']['tmp_name'];
                $filec = $_FILES['imagem']['name'];
                
                $con_images = $path1 . $path2 . $filec;
                
                copy($filen, $con_images);
                
                $img = $path2 . $filec;
            }
            
            $dados = array (
                'codigo' => $codigo,
                'modelo' => $modelo,
                'categoria' => $categoria,
                'imagem' => $img,
                'quantidade' => $quantidade > 0 ? $quantidade : 1,
                'descricao' => $descricao,
                'bruto' => str_replace(',', '.', str_replace('.', '', $bruto)),
                'valor' => str_replace(',', '.', str_replace('.', '', $valor)),
                'status' => $status
            );
            
            if (empty($bruto)) {
                unset($dados['bruto']);
            }
            
            if (empty($img)) {
                unset($dados['imagem']);
            }
            
            $this->db->where('id', $id);
            $this->db->update('estoque', $dados);
            
            redirect('estoque/listar-produtos');
        }
    }
    
    function excluir_produto ($id) {

		$this->load->model('logs_model', 'logs');
		$this->logs->gravar('estoque-removido:'. $id);
	
        $this->session->set_flashdata('sucesso', 'O produto foi removido do estoque com sucesso!');
    
        $this->db->where('id', $id);
        $this->db->update('estoque', array(
            'status' => 'Removido'
        ));
        
        redirect('estoque/listar-produtos');
    }
    
    function desabilitar_produto ($id) {

		$this->load->model('logs_model', 'logs');
		$this->logs->gravar('estoque-desabilitado:'. $id);
	
        $this->session->set_flashdata('sucesso', 'O produto foi desabilitado do estoque com sucesso!');
    
        $this->db->where('id', $id);
        $this->db->update('estoque', array(
            'status' => 'Inativo'
        ));
        
        redirect('estoque/listar-produtos');
    }
    
    function habilitar_produto ($id) {

		$this->load->model('logs_model', 'logs');
		$this->logs->gravar('estoque-habilitado:'. $id);
	
        $this->session->set_flashdata('sucesso', 'O produto foi habilitado do estoque com sucesso!');
    
        $this->db->where('id', $id);
        $this->db->update('estoque', array(
            'status' => 'Ativo'
        ));
        
        redirect('estoque/listar-produtos');
    }
    
    
    function atualiza_item ($item = '') {
        
        if (empty($item)) {
            exit(0);
        }
        
        $id = $this->input->post('id');
        $valor = $this->input->post('valor');
        $valor = str_replace(',', '.', $valor);
        
        switch ($item) {
            case 'valor':
                $this->db->where('id', $id);
                $this->db->update('estoque', array(
                    'valor' => $valor
                ));
            break;
            case 'bruto':
                $this->db->where('id', $id);
                $this->db->update('estoque', array(
                    'bruto' => $valor
                ));
            break;
            case 'quantidade':
                $this->db->where('id', $id);
                $this->db->update('estoque', array(
                    'quantidade' => $valor
                ));
            break;
        }
    }
    
    function atualiza_codigo () {
        
        $this->load->model('estoque_model', 'estoque');	
        $this->load->helper('string');
        
        $categoria = $this->input->post('categoria');
        $prefixo   = substr(strtoupper(accents($categoria)), 0, 3);
        
        $codigo = $this->estoque->codigo_produto($categoria);
        
        $codigo = $prefixo . $codigo;
        
        echo $codigo;
    }
}
