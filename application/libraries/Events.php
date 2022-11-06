<?php

class Events {

    function __construct($log = FALSE) {
        if ( !empty($log) ) {
           $this->write_log($log);
        }
    }

    public function write_log ($log = FALSE) {

        if ($log === FALSE) {
            return FALSE;
        }

        $logpath = '/home/bressanweb/_logs/log-'. date('Y-m-d') .'.log';
        $content = '';

        if (!file_exists($logpath)) {
            $content .= "\xEF\xBB\xBF";
            $content .= "# IMPORTANTE -->";
            $content .= "\n#\tEsse arquivo foi criado automaticamente pelo sistema .";
            $content .= "\n#\tA remoção ou alteração desse arquivo dificultará a análise de possíveis problemas durante a execução do sistema.\n\n";
        }

        if (!$fp = @fopen($logpath, 'ab')) {
            return FALSE;
        }

        $content .= date('Y-m-d H:i:s') ."\t\t". $log . "\n";

        flock($fp, LOCK_EX);
        fwrite($fp, $content);
        flock($fp, LOCK_UN);
        fclose($fp);

        // @chmod($logpath, '755');

        return TRUE;
    }
}