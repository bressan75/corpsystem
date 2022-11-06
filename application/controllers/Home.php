<?php
class Home extends CI_Controller {
    
    public function __construct () {
        parent::__construct();
        if (!$this->session->userdata('login')) {
            redirect('login');
        }
    }

	public function index()
	{
        $this->load->helper('text');
        $this->load->model('logs_model', 'logs');
        $this->load->model('cliente_model', 'cliente');
        $this->load->model('pedido_model', 'pedido');        
        
        $this->logs->gravar();
        
        $login = $this->session->userdata('login');
        $nivel = $this->session->userdata('nivel');
        
        $nome = explode(' ', $login['nome']);
        
        $fornecedores = array();
        
        if ($nivel == 'Administrador' || $nivel == 'Gerente') {
        
            $this->load->model('relatorio_model', 'relatorio');
            
            $clientes_pedidos_ativos = $this->relatorio->clientes_pedidos_ativos();
            $pedido_status = $this->relatorio->pedido_status();
            $pedido_valor_mensal = $this->relatorio->pedido_valor();            
            
            $dados['clientes_pedidos_ativos'] = $clientes_pedidos_ativos;
            $dados['pedido_status'] = $pedido_status;
            $dados['pedido_valor_mensal'] = $pedido_valor_mensal;
                                   
            $dados['scripts'] = array(
                'js/highstock/highstock.js', 
                'js/highcharts/modules/exporting.js'
            );
        }
        
        $clientes = $this->cliente->listar_ultimos_clientes();
        $pedidos = $this->pedido->listar_ultimos_pedidos();        
        
        $dados['clientes'] = $clientes;
        $dados['pedidos'] = $pedidos;		
        $dados['fornecedores'] = $fornecedores;
        
        $dados['nome']  = $nome[0];
        
        $this->load->view('home.phtml', $dados);
	}
}
