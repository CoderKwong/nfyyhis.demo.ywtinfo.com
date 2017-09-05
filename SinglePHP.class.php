<?php
if (!defined('IS_INITPHP')) exit('Access Denied!');   
/**
 * 获取和设置配置参数 支持批量定义
 * 如果$key是关联型数组，则会按K-V的形式写入配置
 * 如果$key是数字索引数组，则返回对应的配置数组
 * @param string|array $key 配置变量
 * @param array|null $value 配置值
 * @return array|null
 */
function C($key,$value=null){
    static $_config = array();
    $args = func_num_args();
    if($args == 1){
        if(is_string($key)){  //如果传入的key是字符串
            return isset($_config[$key])?$_config[$key]:null;
        }
        if(is_array($key)){
            if(array_keys($key) !== range(0, count($key) - 1)){  //如果传入的key是关联数组
                $_config = array_merge($_config, $key);
            }else{
                $ret = array();
                foreach ($key as $k) {
                    $ret[$k] = isset($_config[$k])?$_config[$k]:null;
                }
                return $ret;
            }
        }
    }else{
        if(is_string($key)){
            $_config[$key] = $value;
        }else{
            halt('传入参数不正确');
        }
    }
    return null;
}

/**
 * 调用Widget
 * @param string $name widget名
 * @param array $data 传递给widget的变量列表，key为变量名，value为变量值
 * @return void
 */
function W($name, $data = array()){
    $fullName = $name.'Widget';
    if(!class_exists($fullName)){
        halt('Widget '.$name.'不存在');
    }
    $widget = new $fullName();
    $widget->invoke($data);
}

/**
 * 获取输入参数 支持过滤和默认值
 * 使用方法:
 * <code>
 * I('id',0); 获取id参数 自动判断get或者post
 * I('post.name','','htmlspecialchars'); 获取$_POST['name']
 * I('get.'); 获取$_GET
 * </code>
 * @param string $name 变量的名称 支持指定类型
 * @param mixed $default 不存在的时候默认值
 * @param mixed $filter 参数过滤方法
 * @return mixed
 */
function I($name,$default='',$filter=null) {
	if(strpos($name,'.')) { // 指定参数来源
		//判断参数$name中是否包括.号
		list($method,$name) =   explode('.',$name,2);
		//如果包括.号将.号前后分隔，并且分别赋值给$method以及$name
	}else{ // 默认为自动判断
		//如果没有.号
		$method =   'param';
	}
	switch(strtolower($method)) {//将$method转换为小写
		//如果$method为get，则$input为$_GET
		case 'get'     :   $input =& $_GET;break;
		//如果$method为get，则$input为$_POST
		case 'post'    :   $input =& $_POST;break;
		//如果为put，则将post的原始数据转参数给$input
		case 'put'     :   parse_str(file_get_contents('php://input'), $input);break;
		//如果是param
		case 'param'   :
			//判断$_SERVER['REQUEST_METHOD']
			switch($_SERVER['REQUEST_METHOD']) {
				//如果为post，则$input的内容为$_POST的内容
				case 'POST':
					$input  =  $_POST;
					break;
				//如果为PUT.则input的内容为PUT的内容
				case 'PUT':
					parse_str(file_get_contents('php://input'), $input);
					break;
				//默认为$_GET的内容
				default:
					$input  =  $_GET;
			}
			break;
		//如果$method为request，则$input为$_REQUEST
		case 'request' :   $input =& $_REQUEST;   break;
		//如果$method为session，则$input为$_SESSION
		case 'session' :   $input =& $_SESSION;   break;
		//如果$method为cookie，则$input为$_COOKIE
		case 'cookie'  :   $input =& $_COOKIE;    break;
		//如果$method为server，则$input为$_SERVER
		case 'server'  :   $input =& $_SERVER;    break;
		//如果$method为globals，则$input为$GLOBALS
		case 'globals' :   $input =& $GLOBALS;    break;
		//默认返回空
		default:
			return NULL;
	}
	/**
	 * 到此为止，已经根据传入的参数的需要（第一个参数.号前面的），把所有的变量都取到了。下面就是返回变量的内容了。
	**/
	//如果$name为空，也就是I()第一个参数的.号后面为空的时候
	if(empty($name)) { // 获取全部变量
		//获取到的变量$input全部复制给$data
		$data       =   $input;
		//array_walk_recursive — 对数组中的每个成员递归地应用用户函数
		//将$data的键值作为filter_exp函数的第一个参数，键名作为第二个参数
		//如果$data的键值中含有or或者exp这两个字符，自动在后面加一个空格
		array_walk_recursive($data,'filter_exp');
		//判断过滤参数是否有，如果有的话，就直接使用过滤方法，如果没有的话，就使用配置中的过滤方法
		$filters    =   isset($filter)?$filter:C('DEFAULT_FILTER');
		if($filters) {
			$filters    =   explode(',',$filters);
			//将过滤参数中的每个方法都应用到$data中
			foreach($filters as $filter){
				//将$data的每个值使用$filters过滤
				$data   =   array_map_recursive($filter,$data); // 参数过滤
			}
		}
	}elseif(isset($input[$name])) { // 取值操作
		$data       =   $input[$name];
		is_array($data) && array_walk_recursive($data,'filter_exp');
		$filters    =   isset($filter)?$filter:C('DEFAULT_FILTER');
		if($filters) {
			$filters    =   explode(',',$filters);
			foreach($filters as $filter){
				if(function_exists($filter)) {
					$data   =   is_array($data)?array_map_recursive($filter,$data):$filter($data); // 参数过滤
				}else{
					$data   =   filter_var($data,is_int($filter)?$filter:filter_id($filter));
					if(false === $data) {
						return   isset($default)?$default:NULL;
					}
				}
			}
		}
	}else{ // 变量默认值
		$data       =    isset($default)?$default:NULL;
	}
	
	
	return $data;
}





/**
 * 终止程序运行
 * @param string $str 终止原因
 * @param bool $display 是否显示调用栈，默认不显示
 * @return void
 */
function halt($str, $display=false){
    Log::fatal($str.' debug_backtrace:'.var_export(debug_backtrace(), true));
    header("Content-Type:text/html; charset=utf-8");
    if($display){
        echo "<pre>";
        debug_print_backtrace();
        echo "</pre>";
    }
    echo $str;
    exit;
}

/**
 * 获取数据库实例
 * @return DB
 */
function M(){
    $dbConf = C(array('DB_HOST','DB_PORT','DB_USER','DB_PWD','DB_NAME','DB_CHARSET'));
    return DB::getInstance($dbConf);
}

/**
 * 如果文件存在就include进来
 * @param string $path 文件路径
 * @return void
 */
function includeIfExist($path){
    if(file_exists($path)){
        include $path;
    }
}

/**
 * 总控类
 */
class SinglePHP {
    /**
     * 控制器
     * @var string
     */
    private $c;
    /**
     * Action
     * @var string
     */
    private $a;
    /**
     * 单例
     * @var SinglePHP
     */
    private static $_instance;

    /**
     * 构造函数，初始化配置
     * @param array $conf
     */
    private function __construct($conf){
        C($conf);
    }
    private function __clone(){}

    /**
     * 获取单例
     * @param array $conf
     * @return SinglePHP
     */
    public static function getInstance($conf){
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self($conf);
        }
        return self::$_instance;
    }
    /**
     * 运行应用实例
     * @access public
     * @return void
     */
    public function run(){
		

		//打开WSDL
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=='POST') {
			$url='http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['PHP_SELF'].'?wsdl'; 
			$servidorSoap = new SoapServer($url);
			$servidorSoap->setClass('YourCode');
			$servidorSoap->handle();
		}else if(isset($_REQUEST['soap_method'])){ 
			// && isset($_REQUEST['Input'])
			//soap_method=QueryScheduleTimeInfo&Input= 
			$_ENV["yourCode"] = new YourCode();     
			$res = call_user_func(array($_ENV["yourCode"],$_REQUEST['soap_method']),$_REQUEST['Input']); 
			echo $res;
		}else{
			include './SoapDiscovery.class.php';
			// 创建WSDL
			$disco = new SoapDiscovery('YourCode','BS.OEPSTANWebService');
			header("Content-type: text/xml");
			if (isset($_SERVER['QUERY_STRING']) && strcasecmp($_SERVER['QUERY_STRING'],'wsdl')==0) {
				echo $disco->getWSDL();
			}else {
				echo $disco->returnWSDL();
			}
		}
		
    }

    /**
     * 自动加载函数
     * @param string $class 类名
     */
    public static function autoload($class){
        if(substr($class,-10)=='Controller'){
            includeIfExist(C('APP_FULL_PATH').'/Controller/'.$class.'.class.php');
        }elseif(substr($class,-6)=='Widget'){
            includeIfExist(C('APP_FULL_PATH').'/Widget/'.$class.'.class.php');
        }else{
            includeIfExist(C('APP_FULL_PATH').'/Lib/'.$class.'.class.php');
        }
    }
	 
}

/**
 * 控制器类
 */
class Controller {
    /**
     * 视图实例
     * @var View
     */
    private $_view;

    /**
     * 构造函数，初始化视图实例，调用hook
     */
    public function __construct(){
        $this->_view = new View();
        $this->_init();
    }

    /**
     * 前置hook
     */
    protected function _init(){}
    /**
     * 渲染模板并输出
     * @param null|string $tpl 模板文件路径
     * 参数为相对于App/View/文件的相对路径，不包含后缀名，例如index/index
     * 如果参数为空，则默认使用$controller/$action.php
     * 如果参数不包含"/"，则默认使用$controller/$tpl
     * @return void
     */
    protected function display($tpl=''){
        if($tpl === ''){
            $trace = debug_backtrace();
            $controller = substr($trace[1]['class'], 0, -10);
            $action = substr($trace[1]['function'], 0 , -6);
            $tpl = $controller . '/' . $action;
        }elseif(strpos($tpl, '/') === false){
            $trace = debug_backtrace();
            $controller = substr($trace[1]['class'], 0, -10);
            $tpl = $controller . '/' . $tpl;
        }
        $this->_view->display($tpl);
    }
    /**
     * 为视图引擎设置一个模板变量
     * @param string $name 要在模板中使用的变量名
     * @param mixed $value 模板中该变量名对应的值
     * @return void
     */
    protected function assign($name,$value){
        $this->_view->assign($name,$value);
    }
    /**
     * 将数据用json格式输出至浏览器，并停止执行代码
     * @param array $data 要输出的数据
     */
    protected function ajaxReturn($data){
        echo json_encode($data);
        exit;
    }
	 
    /**
     * 重定向至指定url
     * @param string $url 要跳转的url
     * @param void
     */
    protected function redirect($url){
        header("Location: $url");
        exit;
    }
}

/**
 * 视图类
 */
class View {
    /**
     * 视图文件目录
     * @var string
     */
    private $_tplDir;
    /**
     * 视图文件路径
     * @var string
     */
    private $_viewPath;
    /**
     * 视图变量列表
     * @var array
     */
    private $_data = array();
    /**
     * 给tplInclude用的变量列表
     * @var array
     */
    private static $tmpData;

    /**
     * @param string $tplDir
     */
    public function __construct($tplDir=''){
        if($tplDir == ''){
            $this->_tplDir = './'.C('APP_PATH').'/View/';
        }else{
            $this->_tplDir = $tplDir;
        }

    }
    /**
     * 为视图引擎设置一个模板变量
     * @param string $key 要在模板中使用的变量名
     * @param mixed $value 模板中该变量名对应的值
     * @return void
     */
    public function assign($key, $value) {
        $this->_data[$key] = $value;
    }
    /**
     * 渲染模板并输出
     * @param null|string $tplFile 模板文件路径，相对于App/View/文件的相对路径，不包含后缀名，例如index/index
     * @return void
     */
    public function display($tplFile) {
        $this->_viewPath = $this->_tplDir . $tplFile . '.php';
        unset($tplFile);
        extract($this->_data);
        include $this->_viewPath;
    }
    /**
     * 用于在模板文件中包含其他模板
     * @param string $path 相对于View目录的路径
     * @param array $data 传递给子模板的变量列表，key为变量名，value为变量值
     * @return void
     */
    public static function tplInclude($path, $data=array()){
        self::$tmpData = array(
            'path' => C('APP_FULL_PATH') . '/View/' . $path . '.php',
            'data' => $data,
        );
        unset($path);
        unset($data);
        extract(self::$tmpData['data']);
        include self::$tmpData['path'];
    }
}

/**
 * Widget类
 * 使用时需继承此类，重写invoke方法，并在invoke方法中调用display
 */
class Widget {
    /**
     * 视图实例
     * @var View
     */
    protected $_view;
    /**
     * Widget名
     * @var string
     */
    protected $_widgetName;

    /**
     * 构造函数，初始化视图实例
     */
    public function __construct(){
        $this->_widgetName = get_class($this);
        $dir = C('APP_FULL_PATH') . '/Widget/Tpl/';
        $this->_view = new View($dir);
    }

    /**
     * 处理逻辑
     * @param mixed $data 参数
     */
    public function invoke($data){}

    /**
     * 渲染模板
     * @param string $tpl 模板路径，如果为空则用类名作为模板名
     */
    protected function display($tpl=''){
        if($tpl == ''){
            $tpl = $this->_widgetName;
        }
        $this->_view->display($tpl);
    }

    /**
     * 为视图引擎设置一个模板变量
     * @param string $name 要在模板中使用的变量名
     * @param mixed $value 模板中该变量名对应的值
     * @return void
     */
    protected function assign($name,$value){
        $this->_view->assign($name,$value);
    }
}

/**
 * 数据库操作类
 * 使用方法：
 * DB::getInstance($conf)->query('select * from table');
 * 其中$conf是一个关联数组，需要包含以下key：
 * DB_HOST DB_USER DB_PWD DB_NAME
 * 可以用DB_PORT和DB_CHARSET来指定端口和编码，默认3306和utf8
 */
class DB {
    /**
     * 数据库链接
     * @var resource
     */
    private $_db;
    /**
     * 保存最后一条sql
     * @var string
     */
    private $_lastSql;
    /**
     * 上次sql语句影响的行数
     * @var int
     */
    private $_rows;
    /**
     * 上次sql执行的错误
     * @var string
     */
    private $_error;
    /**
     * 实例数组
     * @var array
     */
    private static $_instance = array();

    /**
     * 构造函数
     * @param array $dbConf 配置数组
     */
    private function __construct($dbConf){
        if(!isset($dbConf['DB_CHARSET'])){
            $dbConf['DB_CHARSET'] = 'utf8';
        }
        $this->_db = mysql_connect($dbConf['DB_HOST'].':'.$dbConf['DB_PORT'],$dbConf['DB_USER'],$dbConf['DB_PWD']);
        if($this->_db === false){
            halt(mysql_error());
        }
        $selectDb = mysql_select_db($dbConf['DB_NAME'],$this->_db);
        if($selectDb === false){
            halt(mysql_error());
        }
        mysql_set_charset($dbConf['DB_CHARSET']);
    }
    private function __clone(){}

    /**
     * 获取DB类
     * @param array $dbConf 配置数组
     * @return DB
     */
    static public function getInstance($dbConf){
        if(!isset($dbConf['DB_PORT'])){
            $dbConf['DB_PORT'] = '3306';
        }
        $key = $dbConf['DB_HOST'].':'.$dbConf['DB_PORT'];
        if(!isset(self::$_instance[$key]) || !(self::$_instance[$key] instanceof self)){
            self::$_instance[$key] = new self($dbConf);
        }
        return self::$_instance[$key];
    }
    /**
     * 转义字符串
     * @param string $str 要转义的字符串
     * @return string 转义后的字符串
     */
    public function escape($str){
        return mysql_real_escape_string($str, $this->_db);
    }
    /**
     * 查询，用于select语句
     * @param string $sql 要查询的sql
     * @return bool|array 查询成功返回对应数组，失败返回false
     */
    public function query($sql){
        $this->_rows = 0;
        $this->_error = '';
        $this->_lastSql = $sql;
        $this->logSql();
        $res = mysql_query($sql,$this->_db);
        if($res === false){
            $this->_error = mysql_error($this->_db);
            $this->logError();
            return false;
        }else{
			$this->_rows = mysql_num_rows($res); 
            $result = array();
			if($this->_rows >0) {
				while($row = mysql_fetch_array($res, MYSQL_ASSOC)){
					$result[]   =   $row;
				}
				mysql_data_seek($res,0);
			}  
            return $result;
        }
    }
    /**
     * 查询，用于insert/update/delete语句
     * @param string $sql 要查询的sql
     * @return bool|int 查询成功返回影响的记录数量，失败返回false
     */
    public function execute($sql) {
        $this->_rows = 0;
        $this->_error = '';
        $this->_lastSql = $sql;
        $this->logSql();
        $result =   mysql_query($sql, $this->_db) ;
        if ( false === $result) {
            $this->_error = mysql_error($this->_db);
            $this->logError();
            return false;
        } else {
            $this->_rows = mysql_affected_rows($this->_db);
            return $this->_rows;
        }
    }
	
	/**
	    * 查询，用于insert/update/delete语句
	    * @param string $sql 要查询的sql
	    * @return bool|int 查询成功返回影响的记录数量，失败返回false
	    */
	public function executeinsert($sql) {
		$this->_rows = 0;
		$this->_error = '';
		$this->_lastSql = $sql;
		$this->logSql();
		$result =   mysql_query($sql, $this->_db) ;
		if ( false === $result) {
			$this->_error = mysql_error($this->_db);
			$this->logError();
			return false;
		} else {
			$this->_rows = mysql_insert_id($this->_db);
			return $this->_rows;
		}
	}
	
    /**
     * 获取上一次查询影响的记录数量
     * @return int 影响的记录数量
     */
    public function getRows(){
        return $this->_rows;
    }
    /**
     * 获取上一次insert后生成的自增id
     * @return int 自增ID
     */
    public function getInsertId() {
        return mysql_insert_id($this->_db);
    }
    /**
     * 获取上一次查询的sql
     * @return string sql
     */
    public function getLastSql(){
        return $this->_lastSql;
    }
    /**
     * 获取上一次查询的错误信息
     * @return string 错误信息
     */
    public function getError(){
        return $this->_error;
    }

    /**
     * 记录sql到文件
     */
    private function logSql(){
        Log::sql($this->_lastSql);
    }

    /**
     * 记录错误日志到文件
     */
    private function logError(){
        $str = '[SQL ERR]'.$this->_error.' SQL:'.$this->_lastSql;
        Log::warn($str);
    }
}

/**
 * 日志类
 * 使用方法：Log::fatal('error msg');
 * 保存路径为 App/Log，按天存放
 * fatal和warning会记录在.log.wf文件中
 */
class Log{
    /**
     * 打日志，支持SAE环境
     * @param string $msg 日志内容
     * @param string $level 日志等级
     * @param bool $wf 是否为错误日志
     */
    public static function write($msg, $level='DEBUG', $wf=false){
        if(function_exists('sae_debug')){ //如果是SAE，则使用sae_debug函数打日志
            $msg = "[{$level}]".$msg;
            sae_set_display_errors(false);
            sae_debug(trim($msg));
            sae_set_display_errors(true);
        }else{
            $msg = date('[ Y-m-d H:i:s ]')."[{$level}]".$msg."\r\n";
			$logPath = 'Log/'.date('Ymd').'.log';
            if($wf){
                $logPath .= '.wf';
            }
            file_put_contents($logPath, $msg, FILE_APPEND);
        }
    }

    /**
     * 打印fatal日志
     * @param string $msg 日志信息
     */
    public static function fatal($msg){
		$msg = iconv("utf-8","gb2312",$msg);
        self::write($msg, 'FATAL', true);
    }

    /**
     * 打印warning日志
     * @param string $msg 日志信息
     */
    public static function warn($msg){
		$msg = iconv("utf-8","gb2312",$msg);
        self::write($msg, 'WARN', true);
    }

    /**
     * 打印notice日志
     * @param string $msg 日志信息
     */
    public static function notice($msg){
		$msg = iconv("utf-8","gb2312",$msg);
        self::write($msg, 'NOTICE');
    }
	
	/**
	    * 打印gethis日志
	    * @param string $msg 日志信息
	    */
	public static function gethis($msg){
		$msg = iconv("utf-8","gb2312",$msg);
		self::write($msg, 'GETHIS');
	}
	
	
	/**
	   * 打印gethis日志
	   * @param string $msg 日志信息
	   */
	public static function posthis($msg){
		$msg = iconv("utf-8","gb2312",$msg);
		self::write($msg, 'POSTHIS');
	}
	
	
	/**
	    * 打印xmlerr日志
	    * @param string $msg 日志信息
	    */
	public static function xmlerr($msg){
		self::write($msg, 'XMLERR');
	}


	/**
	    * 打印xmltc日志
	    * @param string $msg 日志信息
	    */
	public static function xmltc($msg){
		self::write($msg, 'XMLTC');
	}
	
	

	/**
	    * 打印syncdberr日志
	    * @param string $msg 日志信息
	    */
	public static function syncdberr($msg){
		self::write($msg, 'SYNCDBERR');
	}
	
	
	/**
	    * 打印cur_err日志
	    * @param string $msg 日志信息
	    */
	public static function cur_err($msg){
		self::write($msg, 'CURERR');
	}
	

	/**
	 * 打印返回前台JSON日志
	 * @param string $msg 日志信息
	 */
	public static function json($msg){
		$msg = iconv("utf-8","gb2312",$msg);
		self::write($msg, 'JSON');
	}

	
    /**
     * 打印debug日志
     * @param string $msg 日志信息
     */
    public static function debug($msg){
		$msg = iconv("utf-8","gb2312",$msg);
        self::write($msg, 'DEBUG');
    }

    /**
     * 打印sql日志
     * @param string $msg 日志信息
     */
    public static function sql($msg){
		$msg = iconv("utf-8","gb2312",$msg);
        self::write($msg, 'SQL');
    }
	
	
	/**
	 * 打印getpost日志
	 * @param string $msg 日志信息
	 */
	public static function getpost($msg){
		self::write($msg, 'GETPOST');
	}
	
}

/**
 * ExtException类，记录额外的异常信息
 */
class ExtException extends Exception{
    /**
     * @var array
     */
    protected $extra;

    /**
     * @param string $message
     * @param array $extra
     * @param int $code
     * @param null $previous
     */
    public function __construct($message = "", $extra = array(), $code = 0, $previous = null){
        $this->extra = $extra;
        parent::__construct($message, $code, $previous);
    }

    /**
     * 获取额外的异常信息
     * @return array
     */
    public function getExtra(){
        return $this->extra;
    }
}
