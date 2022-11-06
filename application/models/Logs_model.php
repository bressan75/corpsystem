<?php
class Logs_model extends CI_Model {
    
    function gravar ($requisicao = '') 
    {
        $this->load->library('user_agent');    
    
        if (empty($requisicao)) {
        
            $requisicao = 'uri:'. ($this->uri->uri_string() ? $this->uri->uri_string() : 'home');
        
        } else {
            
            $requisicao = 'str:'. $requisicao;
        }
    
        $login = $this->session->userdata('login');
        
        $this->db->insert('logs', array(
            'usuario_id' => $login['id'],
            'requisicao' => $requisicao,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'agente' => $this->agent->agent_string(),
            'tempo' => time()
        ));
    }   
}