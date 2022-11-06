<?php
class Usuarios extends CI_Controller {

    public function __construct () {
        parent::__construct();
        if (!$this->session->userdata('login')) {
            redirect('login');
        }
        
        if($this->session->userdata('nivel') != 'Administrador') {
            exit('Acesso inválido');
        }
    }


	public function index($status = 'ativos')
	{
        $this->listar_usuarios($status);        
	}
    
    function listar_usuarios ($status = 'ativos', $pagina = 0) {

        $this->load->model('usuario_model', 'usuario');
        $this->load->model('logs_model', 'logs');
		
		$this->logs->gravar();
		
        $total = $this->usuario->total_usuarios($status);
        
        $config['first_link'] = 'Primeira Página &nbsp;&nbsp;';
        $config['last_link']  = '&nbsp;&nbsp; Última Página';
        $config['next_link']  = 'Próxima &raquo;';
        $config['prev_link']  = '&laquo; Anterior';
        $config['base_url']   = site_url('usuarios/listar-usuarios/'. $status);
        $config['uri_segment'] = 4;        
        $config['total_rows'] = $total;
        $config['per_page']   = 30;

        $this->load->library('pagination', $config);

        $usuarios = $this->usuario->listar_usuarios($status, $pagina);
        $paginacao = $this->pagination->create_links();
        
        $dados['status'] = $status;
        $dados['usuarios'] = $usuarios;
        $dados['paginacao'] = $paginacao;
        
		$this->load->view('usuarios/index.phtml', $dados);
    }
    
    function adicionar_usuario () {
        
        $this->load->library('form_validation');
		$this->load->model('logs_model', 'logs');
		
        $this->form_validation->set_rules('nome', 'Nome', 'required');
        $this->form_validation->set_rules('email', 'E-mail', 'required|valid_email');
        $this->form_validation->set_rules('usuario', 'Usuário', 'required|callback_usuario_existe');
        $this->form_validation->set_rules('senha', 'Senha', 'required');
        
        $this->form_validation->set_error_delimiters('', '');
        
		if ($this->form_validation->run() == FALSE)
		{
			$this->logs->gravar();
			
            $this->load->view('usuarios/adicionar-usuario.phtml');
            
        } else {
            
            $login = $this->session->userdata('login');
            
            $dados = array (
                'usuario_id' => $login['id'],
                'nome' => $this->input->post('nome'),
                'email' => $this->input->post('email'),
                'usuario' => $this->input->post('usuario'),
                'senha' => $this->input->post('senha'),
                'data' => date('Y-m-d H:i:s')
            );
            
            $this->db->insert('usuario', $dados);
            
			$this->logs->gravar('usuario-adicionado'. $this->db->insert_id());
			
            $this->session->set_flashdata('sucesso', 'O usuário foi adicionado com sucesso!');
            
            redirect('usuarios');
        }
    }
    
    function editar_usuario ($id) {
        
        $this->load->library('form_validation');
		$this->load->model('logs_model', 'logs');
		
        $this->form_validation->set_rules('nome', 'Nome', 'required');
        $this->form_validation->set_rules('email', 'E-mail', 'required|valid_email');
        $this->form_validation->set_rules('usuario', 'Usuário', 'required|callback_checar_usuario');
        $this->form_validation->set_rules('senha', 'Senha', 'required');
        
        $this->form_validation->set_error_delimiters('', '');
        
		if ($this->form_validation->run() == FALSE)
		{
            $this->load->model('usuario_model', 'usuario');
        
			$this->logs->gravar();
		
            $usuario = $this->usuario->dados_usuario($id);
        
            $dados['id'] = $id;
            $dados['usuario'] = $usuario;
        
            $this->load->view('usuarios/editar-usuario.phtml', $dados);
            
        } else {
            
            $dados = array (
                'nome' => $this->input->post('nome'),
                'usuario' => $this->input->post('usuario'),
                'email' => $this->input->post('email'),
                'senha' => $this->input->post('senha'),
                'status' => $this->input->post('status')
            );
            
            $this->db->where('id', $id);
            $this->db->update('usuario', $dados);
            
			$this->logs->gravar('usuario-adicionado'. $id);
			
            $this->session->set_flashdata('sucesso', 'O usuário foi editado com sucesso!');
            
            redirect('usuarios');
        }
    }
    
    function remover_usuario ($status, $id) {

		$this->load->model('logs_model', 'logs');
		$this->logs->gravar('usuario-removido:'. $id);
	
        $this->session->set_flashdata('sucesso', 'O usuário foi removido com sucesso!');
    
        $this->db->where('id', $id);
        $this->db->update('usuario', array(
            'status' => 'Removido'
        ));
        
        redirect('usuarios/listar-usuarios/'. $status);
    }
    
    
    function checar_usuario () {
        
        $this->load->model('usuario_model', 'usuario');
        
        $id = $this->input->post('id');
        $usuario = $this->input->post('usuario');
        
        $total = $this->usuario->checar_usuario($id, $usuario);
        
        if ($total > 0) {
            
            return false;
        
        } else {
            
            return true;
        }        
    }
    
    function usuario_existe () {
        
        $this->load->model('usuario_model', 'usuario');
        
        $usuario = $this->input->post('usuario');
        
        $total = $this->usuario->usuario_existe($usuario);
        
        if ($total > 0) {
            
            return false;
        
        } else {
            
            return true;
        }        
    }
}
