<?php

abstract class ShellModule extends Module {

    protected $dispatcher = null;
    protected $command = '';

    const SHELL_COMMAND_EMPTY=1;
    const SHELL_MODULE_DISABLED=2;
    const SHELL_UNAUTHORIZED=3;
    const SHELL_INVALID_COMMAND=4;
    const SHELL_MODULE_NOT_FOUND=5;
    const SHELL_LOAD_KUROGO_ERROR=6;
    const SHELL_NO_MODULE=7;

    protected function setDispatcher(&$dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    protected function Dispatcher() {
        
        return $this->dispatcher;
    }

    /**
     * Set the command
     * @param string the command
     */
    protected function setCommand($command) {
        $this->command = $command;
    }

    /**
     * The module is disabled.
     */
    protected function moduleDisabled() {
        //$error = new KurogoError(2, 'Module Disabled', 'This module has been disabled');
        //$this->throwError($error);
        $this->stop(self::SHELL_MODULE_DISABLED);
    }

    /**
     * The module must be run securely (https)
     */
    protected function secureModule() {
        return false;
    }

    /**
     * The user cannot access this module
     */
    protected function unauthorizedAccess() {
        //$error = new KurogoError(4, 'Unauthorized', 'You are not permitted to use the '.$this->getModuleVar('title', 'module').' module');
        //$this->throwError($error);
        $this->stop(self::SHELL_UNAUTHORIZED);
    }

    /**
     * An unrecognized command was requested
     */
    protected function invalidCommand() {
        //$error = new KurogoError(5, 'Invalid Command', "The $this->id module does not understand $this->command");
        //$this->throwError($error);
        $this->stop(self::SHELL_INVALID_COMMAND);
    }

    /**
     * Throw a fatal error in the shell. Used for user created errors like invalid parameters. Stops
     * execution and displays the error
     */
    protected function throwError($error) {
        $string = array(
            'code:'.$error->getCode(),
            'title:'.$error->getTitle(),
            'message:'.$error->getMessage()
        );
        $this->error($string);
        $this->stop();
    }

    protected function getShellConfig($name, $opts=0) {
        $opts = $opts | ConfigFile::OPTION_CREATE_WITH_DEFAULT;
        $config = ModuleConfigFile::factory($this->configModule, "shell-$name", $opts, $this);
        return $config;
    }

    protected function getShellConfigData($name) {
        $config = $this->getShellConfig($name);
        return $config->getSectionVars(Config::EXPAND_VALUE);
    }

    protected function loadSiteConfigFile($name, $opts=0) {
        $config = ConfigFile::factory($name, 'site', $opts);
        Kurogo::siteConfig()->addConfig($config);

        return $config->getSectionVars(true);
    }

    public static function getAllModules() {
        $configFiles = glob(SITE_CONFIG_DIR . "/*/module.ini");
        $modules = array();

        foreach ($configFiles as $file) {
            if (preg_match("#" . preg_quote(SITE_CONFIG_DIR,"#") . "/([^/]+)/module.ini$#", $file, $bits)) {
                $id = $bits[1];
                try {
                    if ($module = ShellModule::factory($id)) {
                       $modules[$id] = $module;
                    }
                } catch (KurogoException $e) {
                }
            }
        }
        ksort($modules);
        return $modules;
    }

    /**
     * Prompts the user for input, and returns it.
     */
    protected function in($prompt, $options = null, $default = null) {
        return $this->Dispatcher()->getInput($prompt, $options, $default);
    }

    protected function out($string, $newLine = true) {
        $string = is_array($string) ? implode(PHP_EOL, $string) : $string;
        $this->Dispatcher()->stdout($string, $newLine);
    }

    protected function error($string) {
        $string = is_array($string) ? implode(PHP_EOL, $string) : $string;
        $this->Dispatcher()->stderr($string);
    }

    protected function stop($status = 0) {
        $this->Dispatcher()->stop($status);
    }
    /**
     * Factory method
     * @param string $id the module id to load
     * @param string $command the command to execute
     * @param array $args an array of arguments
     * @param KurogoShellDispatcher $dispatcher an object of KurogoShellDispatcher
     * @return ShellModule
     */

    public static function factory($id, $command='', $args=array(), $dispatcher = null) {
        if (!$module = parent::factory($id, 'shell')) {
            return false;
        }
        if ($command) {
            $module->setDispatcher($dispatcher);
            $module->init($command, $args);
        }

        return $module;
    }

    /**
     * Initialize the request
     */
    protected function init($command='', $args=array()) {
        parent::init();
        $this->setArgs($args);
        $this->setCommand($command);
    }

    /**
     * Execute the command. Will call initializeForCommand() which should set the version, error and response
     * values appropriately
     */
    public function executeCommand() {
        if (empty($this->command)) {
            $this->stop(self::SHELL_COMMAND_EMPTY);
            //$error = new KurogoError(6, 'Command not specified', "");
            //$this->throwError($error);
        }
        return $this->initializeForCommand();
    }

    /**
     * All modules must implement this method to handle the logic of each shell command.
     */
    abstract protected function initializeForCommand();
    
    protected function preFetchData(DataModel $controller, &$response) {
    	$retriever = $controller->getRetriever();
    	return $retriever->getData($response);
    }
    
    /* subclasses can override this method to return all controllers list */
    protected function getAllControllers() {
        $controllers = array();
        
        if ($feeds = $this->loadFeedData()) {
            foreach ($feeds as $index => $feesData) {
                if ($feed = $this->getFeed($index)) {
                    $controllers[] = $feed;
                }
            }
        }
        
        return $controllers;
    }
    
    protected function preFetchAllData() {
    	$time = 0;
    	$controllers = $this->getAllControllers() ;
		foreach ($controllers as $controller) {
			$out = "Fetching $this->configModule: " . $controller->getTitle() . ". ";
			$data = $this->preFetchData($controller, $response);
			if ($response->getFromCache()) {
				$out .= "In cache";
			} else {
				$time += $response->getTimeElapsed();
				$out .= "Took " . sprintf("%.2f", $response->getTimeElapsed()) . " seconds";
			}
			$this->out($out);
		}
		$this->out(count($controllers) . " feeds took " . sprintf("%.2f", $time) . " seconds");
    }
}



