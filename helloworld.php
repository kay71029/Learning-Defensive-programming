<?php
class DaemonSignalListener
{
    
    protected $processId;
    protected $pidFile = 'daemon.pid';
    protected $logFile = 'phpdeamon.log';

    public function execute()
    {
        if (pcntl_fork()) {
            pcntl_wait($status);
            exit;
        }
        
        posix_setsid();
        $pid = pcntl_fork();
        
        if ($pid) {
            $this->processId = $pid;
            $this->storeProcessId();
            exit;
        }
        
        $this->addLog('Daemonized');
        fwrite(STDOUT, "Daemon Start\n-----------------------------------------\n");
        $this->registerSignalHandler();
        declare (ticks = 1);
        
        while (true) {
            $this->doExecute();
        }
        return $this;
    }
    protected function doExecute()
    {
        
    }
    protected function registerSignalHandler()
    {
        $this->addLog('registerHendler');
        pcntl_signal(SIGINT, array($this, 'shutdown'));
        pcntl_signal(SIGTERM, array($this, 'shutdown'));
        pcntl_signal(SIGUSR1, array($this, 'customSignal'));
    }
    protected function storeProcessId()
    {
        $file = $this->pidFile;
        $folder = dirname($file);
        
        if (!is_dir($folder)) {
            mkdir($folder);
        }
        file_put_contents($file, $this->processId);
        return $this;
    }
    public function shutdown($signal)
    {
        $this->addLog('Shutdown by signal: ' . $signal);
        $pid = file_get_contents($this->pidFile);
        @ unlink($this->pidFile);
        passthru('kill -9 ' . $pid);
        exit;
    }
    public function customSignal($signal)
    {
        $this->addLog('Execute custom signal: ' . $signal);
    }
    protected function addLog($text)
    {
        $file = $this->logFile;
        $time = new Datetime();
        $text = sprintf("%s - %s\n", $text, $time->format('Y-m-d H:i:s'));
        $fp = fopen($file, 'a+');
        fwrite($fp,$text);
        fclose($fp);
    }
}
$daemon = new DaemonSignalListener();
$daemon->execute();