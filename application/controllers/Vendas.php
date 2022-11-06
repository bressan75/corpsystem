<?php

class Vendas  extends CI_Controller {
    
    public function __construct () {
        parent::__construct();
        if (!$this->session->userdata('login')) {
            redirect('login');
        }
    }
    
    function index()
    {
		$this->listar_vendas();
    }
        
	function listar_vendas ($pagina = 0) {
        
        if ($this->input->post('data_ini')) {
            $this->session->set_userdata('data_ini', $this->input->post('data_ini'));
        }
        
        if ($this->input->post('data_fim')) {
            $this->session->set_userdata('data_fim', $this->input->post('data_fim'));
        }
        
        if ($this->session->userdata('data_ini')) {
            list($dia, $mes, $ano) = explode('/', $this->session->userdata('data_ini'));
            $ini = $ano .'-'. $mes .'-'. $dia;
            $dados['ini'] = $this->session->userdata('data_ini');
        }
        else {
            $ini = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')-7, date('Y')));
            $dados['ini'] = date('d/m/Y', mktime(0, 0, 0, date('m'), date('d')-7, date('Y')));
        }

        if ($this->session->userdata('data_fim')) {
            list($dia, $mes, $ano) = explode('/', $this->session->userdata('data_fim'));
            $fim = $ano .'-'. $mes .'-'. $dia;
            $dados['fim'] = $this->session->userdata('data_fim');
        }
        else {
            $fim = date('Y-m-d');
            $dados['fim'] = date('d/m/Y');
        }   
        
        $this->load->library('cart');
        $this->load->model('vendas_model', 'vendas');
        $this->load->model('logs_model', 'logs');
        $this->load->helper('text');
        
        $this->logs->gravar();
        
        $ordem = $this->session->userdata('ordem');        
        $total = $this->vendas->total_lista_vendas($ini, $fim);
        
        $config['first_link'] = 'Primeira Página &nbsp;&nbsp;';
        $config['last_link']  = '&nbsp;&nbsp; Última Página';
        $config['next_link']  = 'Próxima &raquo;';
        $config['prev_link']  = '&laquo; Anterior';
        $config['base_url']   = site_url('vendas/listar-vendas');
        $config['uri_segment'] = 3; 
        $config['total_rows'] = $total;
        $config['per_page']   = 30;

        $this->load->library('pagination', $config);

        $vendas = $this->vendas->listar_vendas($ini, $fim, $pagina);
        $paginacao = $this->pagination->create_links();
        
        $total_itens = $this->cart->total_items();
        
        $dados['ordem'] = str_replace('_desc', '', $ordem);
        $dados['pagina'] = $pagina;
        $dados['paginacao'] = $paginacao;
        $dados['vendas'] = $vendas;
        $dados['total_itens'] = $total_itens;
        
        $dados['styles'] = array(
            'js/libraries/jquery-ui/css/cupertino/jquery-ui-1.8.16.custom.css'
        );
        
        $dados['scripts'] = array(
            'js/libraries/jquery-ui/js/jquery-ui-1.8.16.custom.min.js',
            'js/cliente.js',
            'js/vendas.js'
        );
        
        $this->load->view('vendas/listar-vendas.phtml', $dados);
    }
        
    function ver_venda ($cid, $vid) {
        
        $this->load->library('cart');
        $this->load->model('vendas_model', 'vendas');
		$this->load->model('logs_model', 'logs');
		
		$this->logs->gravar();
		
		$vendas = $this->vendas->dados_vendas_cliente($cid, $vid);
		$total_itens = $this->cart->total_items();
         
        $dados['vendas']  = $vendas;
        $dados['total_itens'] = $total_itens;
        
		$dados['cid'] = $cid;
        $dados['vid'] = $vid;
        
        $dados['scripts'] = array(
			'js/cliente.js'
		);
                
        $this->load->view('vendas/ver-venda.phtml', $dados);
    }        
}
