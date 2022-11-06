<?php
class Login extends CI_Controller {

	public function index()
	{
        $this->load->library('form_validation');

        $this->form_validation->set_rules('usuario', 'Usuário', 'required|callback_auth');
        $this->form_validation->set_rules('senha', 'Senha', 'required');
        
        $this->form_validation->set_error_delimiters('', '');
        
		if ($this->form_validation->run() == FALSE)
		{                
            $dados['_tpl'] = true;
            
            echo $this->load->view('login.phtml', $dados);
            
        } else {
            
			$this->db->query('delete from logs where tempo <= "'. (time()-7776000) .'"');
			
            redirect('home');
        }
	}
    
    public function sair () {
		
		$this->load->model('logs_model', 'logs');
		$this->logs->gravar('saiu');      
		
        $this->session->sess_destroy();
        redirect('login');
    }
    
    public function auth () {
        
        $this->load->model('usuario_model', 'usuario');
        
        $usuario = $this->input->post('usuario');
        $senha = $this->input->post('senha');
        
        if ($login = $this->usuario->autenticar($usuario, $senha)) {
			
            $this->session->set_userdata('login', array(
                'id' 	=> $login['id'],
                'nome' 	=> $login['nome']
            ));
			
			$this->session->set_userdata('nivel', $login['nivel']);

			$this->load->model('logs_model', 'logs');
			$this->logs->gravar('autenticado');            
            return true;
        }
        
        return false;
    }
}
