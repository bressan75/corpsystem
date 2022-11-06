<?php 

class JM_Loader extends CI_Loader {

    public function __construct()
    {
        parent::__construct();
    }

    function view($view, $vars = array(), $return = FALSE)
    {
        $vars['_content'] = $this->_ci_load(array(
            '_ci_view' => $view,
            '_ci_vars' => $this->_ci_object_to_array($vars),
            '_ci_return' => true));
        
        if (!empty($vars['_tpl']) && $vars['_tpl'] == true) {
        
            return $vars['_content'];
        }
        
        return $this->_ci_load(array(
            '_ci_view' => 'comuns/template.phtml',
            '_ci_vars' => $this->_ci_object_to_array($vars),
            '_ci_return' => $return)
        );
    }
}