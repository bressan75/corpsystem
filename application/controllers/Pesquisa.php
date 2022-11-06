<?php
class Pesquisa extends CI_Controller {

    public function __construct () {
        parent::__construct();
        if (!$this->session->userdata('login')) {
            redirect('login');
        }
    }
    
    public function index($tabela = '')
	{
        $this->listar_resultado($tabela);        
	}
    
    public function listar_resultado ($tabela = '', $pagina = 0) {
        
        $this->load->model('pesquisa_model', 'pesquisa');
        $this->load->model('logs_model', 'logs');
        
		$this->load->helper('text');
		
        if ($palavra = $this->input->post('palavra')) {
            
			$this->logs->gravar('pesquisa:'. $palavra);
			
            $this->session->set_userdata('palavra', $this->input->post('palavra'));
        }
        
        $palavra = $this->session->userdata('palavra');
        $tabelas = array();
        $resultado = $this->pesquisa->resultado($palavra);
        $total = 0;
        
        if (count($resultado) > 0) {
         
            $tabelas = array_keys($resultado);
            
            if (empty($tabela)) {
                
                $tabela = $tabelas[0];            
            }
    
            $total = count($resultado[$tabela]);
            $resultado = array_slice($resultado[$tabela], $pagina, 30);
        }
        
        $config['first_link'] = 'Primeira Página &nbsp;&nbsp;';
        $config['last_link']  = '&nbsp;&nbsp; Última Página';
        $config['next_link']  = 'Próxima &raquo;';
        $config['prev_link']  = '&laquo; Anterior';
        $config['base_url']   = site_url('pesquisa/listar-resultado/'. $tabela);
        $config['uri_segment'] = 4;        
        $config['total_rows'] = $total;
        $config['per_page']   = 30;
        
        $this->load->library('pagination', $config);

        $paginacao = $this->pagination->create_links();
        
        $nomes = array(
            'clientes' => 'Clientes',
            'fornecedores' => 'Fornecedores',
            'pedidos' => 'Pedidos',
            'orcamentos' => 'Orçamentos',
            'vendas' => 'Vendas'
        );
        
        $dados['pagina'] = $pagina;
        $dados['tabelas'] = $tabelas;
        $dados['tabela'] = $tabela;
        $dados['nomes'] = $nomes;
        $dados['resultado'] = $resultado;
        $dados['paginacao'] = $paginacao;
        
		$this->load->view('pesquisa/index.phtml', $dados);
    }
}