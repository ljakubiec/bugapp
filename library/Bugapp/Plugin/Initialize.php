<?php
class Bugapp_Plugin_Initialize extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var Zend_Config
     */
    protected static $_config;

    /**
     * @var string Current environment
     */
    protected $_env;

    /**
     * @var Zend_Controller_Front
     */
    protected $_front;

    /**
     * @var string Path to application root
     */
    protected $_appPath;

    /**
     * Constructor
     *
     * Initialize environment, application path, and configuration.
     * 
     * @param  string $env 
     * @param  string|null $appPath
     * @return void
     */
    public function __construct($env, $appPath = null)
    {
        $this->_setEnv($env);
        if (null === $appPath) {
            $appPath = realpath(dirname(__FILE__) . '/../../../application');
        }
        $this->_appPath = $appPath;

        $this->_front = Zend_Controller_Front::getInstance();
    }

    /**
     * Route startup
     * 
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        $this->initDb();
        $this->initHelpers();
        $this->initView();
        $this->initPlugins();
        $this->initRoutes();
        $this->initControllers();
    }

    /**
     * Get config object (static)
     * 
     * @return Zend_Config
     */
    public static function getConfig()
    {
        return self::$_config;
    }

    /**
     * Initialize DB
     * 
     * @return void
     */
    public function initDb()
    {
        $config = $this->_getConfig();
        if (!isset($config->db)) {
            return;
        }

        $db = Zend_Db::factory($config->db);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        Zend_Registry::set('db', $db);
    }

    /**
     * Initialize action helpers
     * 
     * @return void
     */
    public function initHelpers()
    {
        Zend_Controller_Action_HelperBroker::addPrefix('Bugapp_Helper');
    }

    /**
     * Initialize view and layout
     * 
     * @return void
     */
    public function initView()
    {
        // Setup View
        $view = new Zend_View();
        $view->doctype('XHTML1_TRANSITIONAL');
        $view->placeholder('nav')->setPrefix('<div id="nav">')
                                 ->setPostfix('</div>');

        // Set view in ViewRenderer
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->setView($view);

        // Initialize layouts
        Zend_Layout::startMvc($this->_appPath . '/views/layouts');
    }

    /**
     * Initialize plugins
     * 
     * @return void
     */
    public function initPlugins()
    {
        $this->_front->registerPlugin(new Bugapp_Plugin_Auth());
    }

    /**
     * Initialize routes
     * 
     * @return void
     */
    public function initRoutes()
    {
    }

    /**
     * Initialize controller directories
     * 
     * @return void
     */
    public function initControllers()
    {
        $this->_front->addControllerDirectory($this->_appPath . '/controllers/');
    }

    /**
     * Get configuration object
     * 
     * @return Zend_Config
     */
    protected function _getConfig()
    {
        if (null === self::$_config) {
            $configData = include $this->_appPath . '/config/site.php';
            if (!array_key_exists($this->_env, $configData)) {
                throw new Exception(sprintf('No configuration available for env %s', $this->_env));
            }
            self::$_config = new Zend_Config($configData[$this->_env], true);
            self::$_config->root = $this->_appPath;
        }
        return self::$_config;
    }

    /**
     * Set environment
     * 
     * @param  string $env 
     * @return void
     */
    protected function _setEnv($env)
    {
        if (!in_array($env, array('development', 'test', 'production'))) {
            $env = 'development';
        }
        $this->_env = $env;
    }
}
