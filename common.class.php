<?php
defined('IS_INITPHP') || die('Access Denied!');

/**
 * 扩展类库
 * 
 * @version 0.1.0
 * @author clh
 */
class commonClass{
	
	
	/**
	 *生成时间戳
	 * @return string
	 */
	function timestamp() {
		list($s1, $s2) = explode(' ', microtime());		
		return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)));
	}
	
	
	/**
	 * 生成时间
	 * @return string
	 */
	function timenow() { 
		return date("Y-m-d H:i:s", time());
	}
	
	
	/**
	* 生成时间
	* @return string
	*/
	function datenow() { 
		return date("Y-m-d", time());
	}
	
	
	
	/**
	* 生成时间
	* @return string
	*/
	function datemore() { 
		return date("Y-m-d", strtotime("+7 day"));
	}
	
	/**
	 * 生成时间戳大于 多少分钟，30分种有效期 
	 * @param $timestamp 时戳
	 * @return string
	 */
	function timestampeq($timestamp=0) {
		list($s1, $s2) = explode(' ', microtime());		
		return (float) floatval($timestamp)+ (floatval(60*3000));
	}
	
	/**
	 * 用户token 
	 * @param $uid 用户ID
	 * @param $timestamp 时戳
	 * @return string
	 */
	function token($uid=0,$timestamp=0) {  
		return md5($uid.$timestamp);
	}	
	
	
	/**
	 *生成时间戳+随机码 唯一订单ID号
	 * @return string
	 */
	function orderId() {
		list($s1, $s2) = explode(' ', microtime());		
		return (float)sprintf('%.0f', (floatval($s1) + floatval($s2))).$this->randomnum();
	}
	
	
	
	/**
	 * 先验证用户密码是否正确 
	 * @param string $appKey 用户的KEY
	 * @param string $appToken 请求的token
	 * @return boolean
	 */
	function checkToken($appKey,$appToken)
	{         
		/*$bool=false;
		switch (strtoupper($appToken))
		{
			case strtoupper(md5($appKey."|".date("Y-m-d H", strtotime()))):  
				$bool=true;
				break;  
			case strtoupper(md5($appKey."|".date("Y-m-d H", strtotime("+1 hour")))):  
				$bool=true;
				break;
			case strtoupper(md5($appKey."|".date("Y-m-d H", strtotime("-1 hour")))):  
				$bool=true;
				break;
			default:
				$bool=false;
				break;
		} 
		return $bool;*/
		return true;
	}  
	
	/**
	 * 用于在检测过滤列表的方法名 
	 * @param string $funName 请求方法名
	 * @return boolean
	 */
	function checkAction($accessName,$actionName)
	{   
		$bool=true; 
		$noCheckFun = $accessName;
		$json = json_decode($noCheckFun[0]);   
		if (strpos('|'.$json->funName.'|', '|'.$actionName.'|') !== false){
			$bool=false;  
		}    
		return $bool;
	} 
	
	
	
	/**
	* 获取返回错
	*/
	function commErr($type){
		$res ="<Response><ResultCode>1</ResultCode><ResultContent>失败</ResultContent>";
		switch ($type)
		{
			case "1": 
				$res="<Response><ResultCode>1</ResultCode><ResultContent>appUser和appToken不能为空</ResultContent>";
				break;  
			case "2": 
				$res="<Response><ResultCode>1</ResultCode><ResultContent>appUser不正确</ResultContent>";
				break;
			case "3": 
				$res="<Response><ResultCode>1</ResultCode><ResultContent>appToken不正确</ResultContent>";
				break;
			case "4": 
				$res="<Response><ResultCode>1</ResultCode><ResultContent>没有权限访问</ResultContent>";
				break;
			case "5": 
				$res="<Response><ResultCode>1</ResultCode><ResultContent>系统出错</ResultContent>";
				break;
		}
		echo $res;
	}
	
	/**
	* 获取地址错误 
	*/
	function urlErr(){
		//jsonp回调参数，必需   
		$callback = isset($_GET['callback']) ? trim($_GET['callback']) : '';  
		$jsonData["dataToken"]["timestamp"]=""; 
		$jsonData["dataToken"]["token"]="";    
		$jsonData["dataToken"]["status"]=0; 
		$jsonData["dataToken"]["info"]="验证失败"; 
		$jsonData["dataInfo"]["status"]="0"; 
		$jsonData["dataInfo"]["info"]="非法访问";
		$jsonData["dataInfo"]["data"]="";
		//返回格式，有回调或没有回调两种方式
		if($callback){
			echo $callback . '(' .json_encode($jsonData) .')';  //返回格式，回调来jsonp 必需 json 数据  
		}else{
			echo json_encode($jsonData);  //返回格式，必需 json 数据  
		}   
	}
	
	function array_multi2single($array)
	{
		static $result_array=array(); 
		foreach($array as $key=>$value)
		{
			if(is_array($value))
			{
				$this->array_multi2single($value);
			}
			else  
				$result_array[]=$value;
		}
		return $result_array;
	} 

	/**
	* 将多维数组转为一维数组
	* @author echo 
	* @param array $arr
	* @return array
	*/
	function ArrMd2Ud($arr) {
		#将数值第一元素作为容器，作地址赋值。
		$ar_room = &$arr[key($arr)];
		#第一容器不是数组进去转呀
		if (!is_array($ar_room)) {
			#转为成数组
			$ar_room = array($ar_room);
		}
		#指针下移
		next($arr);
		#遍历
		while (list($k, $v) = each($arr)) {
			#是数组就递归深挖，不是就转成数组
			$v = is_array($v) ? call_user_func(__FUNCTION__, $v) : array($v);
			#递归合并
			$ar_room = array_merge_recursive($ar_room, $v);
			#释放当前下标的数组元素
			unset($arr[$k]);
		}
		return $ar_room;
	}
	/**
	 * 获取客户端IP地址
	 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @param boolean $adv 是否进行高级模式获取（有可能被伪装） 
	 * @return mixed
	 */
	function ip($type = 0,$adv=false) {
		$type       =  $type ? 1 : 0;
		static $ip  =   NULL;
		if ($ip !== NULL) return $ip[$type];
		if($adv){
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				$pos    =   array_search('unknown',$arr);
				if(false !== $pos) unset($arr[$pos]);
				$ip     =   trim($arr[0]);
			}elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
				$ip     =   $_SERVER['HTTP_CLIENT_IP'];
			}elseif (isset($_SERVER['REMOTE_ADDR'])) {
				$ip     =   $_SERVER['REMOTE_ADDR'];
			}
		}elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ip     =   $_SERVER['REMOTE_ADDR'];
		}
		// IP地址合法验证
		$long = sprintf("%u",ip2long($ip));
		$ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
		return $ip[$type];
	}

	//用php从身份证中提取生日,包括15位和18位身份证 
	function getIDCardInfo($IDCard){
		$result['error']=0;//0：未知错误，1：身份证格式错误，2：无错误
		$result['flag']='';//0标示成年，1标示未成年
		$result['tdate']='';//生日，格式如：2012-11-15
		if(!eregi("^[1-9]([0-9a-zA-Z]{17}|[0-9a-zA-Z]{14})$",$IDCard)){
			$result['error']=1;
			return $result;
		}else{
			if(strlen($IDCard)==18){
				$tyear=intval(substr($IDCard,6,4));
				$tmonth=intval(substr($IDCard,10,2));
				$tday=intval(substr($IDCard,12,2));
				if($tyear>date("Y")||$tyear<(date("Y")-100)){
					$flag=0;
				}elseif($tmonth<0||$tmonth>12){
					$flag=0;
				}elseif($tday<0||$tday>31){
					$flag=0;
				}else{
					$tdate=$tyear."-".$tmonth."-".$tday."";
					if((time()-mktime(0,0,0,$tmonth,$tday,$tyear))>18*365*24*60*60){
						$flag=0;
					}else{
						$flag=1;
					}
				}
			}elseif(strlen($IDCard)==15){
				$tyear=intval("19".substr($IDCard,6,2));
				$tmonth=intval(substr($IDCard,8,2));
				$tday=intval(substr($IDCard,10,2));
				if($tyear>date("Y")||$tyear<(date("Y")-100)){
					$flag=0;
				}elseif($tmonth<0||$tmonth>12){
					$flag=0;
				}elseif($tday<0||$tday>31){
					$flag=0;
				}else{
					$tdate=$tyear."-".$tmonth."-".$tday."";
					if((time()-mktime(0,0,0,$tmonth,$tday,$tyear))>18*365*24*60*60){
						$flag=0;
					}else{
						$flag=1;
					}
				}
			}
		}
		$result['error']=2;//0：未知错误，1：身份证格式错误，2：无错误
		$result['isAdult']=$flag;//0标示成年，1标示未成年
		$result['birthday']=$tdate;//生日日期
		return $result;
	}
	
	
	
	/**
	 * 发送HTTP状态
	 * @param integer $code 状态码
	 * @return void
	 */
	function send_http_status($code) {
		static $_status = array(
			// Informational 1xx
			100 => 'Continue',
			101 => 'Switching Protocols',
			// Success 2xx
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			// Redirection 3xx
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Moved Temporarily ',  // 1.1
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			// 306 is deprecated but reserved
			307 => 'Temporary Redirect',
			// Client Error 4xx
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			// Server Error 5xx
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			509 => 'Bandwidth Limit Exceeded'
			);
		if(isset($_status[$code])) {
			header('HTTP/1.1 '.$code.' '.$_status[$code]);
			// 确保FastCGI模式下正常
			header('Status:'.$code.' '.$_status[$code]);
		}
	}

	function think_filter(&$value){
		// TODO 其他安全过滤

		// 过滤查询特殊字符
		if(preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i',$value)){
			$value .= ' ';
		}
	}

	// 不区分大小写的in_array实现
	function in_array_case($value,$array){
		return in_array(strtolower($value),array_map('strtolower',$array));
	}

	/**
	 * 对用户的密码进行加密
	 * @param $password
	 * @param $encrypt //传入加密串，在修改密码时做认证
	 * @return array/password
	 */
	function password($password, $encrypt='') {
		$pwd = array();
		$pwd['encrypt'] =  $encrypt ? $encrypt : getRandChar();
		$pwd['password'] = md5(md5(trim($password)).$pwd['encrypt']);
		return $encrypt ? $pwd['password'] : $pwd;
	}

	/**
	 * 生成随机字符串
	 * @param string $lenth 长度
	 * @return string 字符串
	 */
	function randomstr($lenth = 6) {
		return random($lenth, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
	}
	
	
	/**
	 * 生成随机数字串
	 * @param string $lenth 长度
	 * @return string 字符串
	 */
	function randomnum() {
		
		$str = null;
		$strPol = "0123456789";
		$max = strlen($strPol)-1;

		for($i=0;$i<6;$i++){
			$str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
		}

		return $str;
	} 
	
	function getRandChar($length=6){
		$str = null;
		$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen($strPol)-1;

		for($i=0;$i<$length;$i++){
			$str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
		}

		return $str;
	} 
	
	function post_check($post)     
	{     
		if (!get_magic_quotes_gpc()) // 判断magic_quotes_gpc是否为打开     
		{     
			$post = addslashes($post); // 进行magic_quotes_gpc没有打开的情况对提交数据的过滤     
		}     
		$post = str_replace("_", "\_", $post); // 把 '_'过滤掉     
		$post = str_replace("%", "\%", $post); // 把' % '过滤掉     
		$post = nl2br($post); // 回车转换     
		$post= htmlspecialchars($post); // html标记转换        
		return $post;     
	} 
	
	
	/**
	 * 简单对称加密算法之加密
	 * @param String $string 需要加密的字串
	 * @param String $skey 加密EKY
	 * @author Anyon Zou <zoujingli@qq.com>
	 * @date 2013-08-13 19:30
	 * @update 2014-10-10 10:10
	 * @return String
	 */
	function encode($string = '', $skey = 'hongzeit') {
		$strArr = str_split(base64_encode($string));
		$strCount = count($strArr);
		foreach (str_split($skey) as $key => $value)
			$key < $strCount && $strArr[$key].=$value;
		return str_replace(array('=', '+', '/'), array('O0O0O', 'o000o', 'oo00o'), join('', $strArr));
	}

	/**
	 * 简单对称加密算法之解密
	 * @param String $string 需要解密的字串
	 * @param String $skey 解密KEY
	 * @author Anyon Zou <zoujingli@qq.com>
	 * @date 2013-08-13 19:30
	 * @update 2014-10-10 10:10
	 * @return String
	 */
	function decode($string = '', $skey = 'hongzeit') {
		$strArr = str_split(str_replace(array('O0O0O', 'o000o', 'oo00o'), array('=', '+', '/'), $string), 2);
		$strCount = count($strArr);
		foreach (str_split($skey) as $key => $value)
			$key <= $strCount  && isset($strArr[$key]) && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
		return base64_decode(join('', $strArr));
	}

	
	//$str = 'abcdef'; 
	//$key = 'www.helloweba.com'; 
	//echo authcode($str,'ENCODE',$key,0); //加密 
	//$str = '56f4yER1DI2WTzWMqsfPpS9hwyoJnFP2MpC8SOhRrxO7BOk'; 
	//echo authcode($str,'DECODE',$key,0); //解密
	function authcode($string, $operation = 'D', $key = 'hongzeit', $expiry = 0) {   
		// 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙   
		$ckey_length = 4;   
		
		// 密匙   
		$key = md5($key ? $key : $GLOBALS['discuz_auth_key']);   
		
		// 密匙a会参与加解密   
		$keya = md5(substr($key, 0, 16));   
		// 密匙b会用来做数据完整性验证   
		$keyb = md5(substr($key, 16, 16));   
		// 密匙c用于变化生成的密文   
		$keyc = $ckey_length ? ($operation == 'D' ? substr($string, 0, $ckey_length): 
			substr(md5(microtime()), -$ckey_length)) : '';   
		// 参与运算的密匙   
		$cryptkey = $keya.md5($keya.$keyc);   
		$key_length = strlen($cryptkey);   
		// 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)， 
		//解密时会通过这个密匙验证数据完整性   
		// 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确   
		$string = $operation == 'D' ? base64_decode(substr($string, $ckey_length)) :  
			sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;   
		$string_length = strlen($string);   
		$result = '';   
		$box = range(0, 255);   
		$rndkey = array();   
		// 产生密匙簿   
		for($i = 0; $i <= 255; $i++) {   
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);   
		}   
		// 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度   
		for($j = $i = 0; $i < 256; $i++) {   
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;   
			$tmp = $box[$i];   
			$box[$i] = $box[$j];   
			$box[$j] = $tmp;   
		}   
		// 核心加解密部分   
		for($a = $j = $i = 0; $i < $string_length; $i++) {   
			$a = ($a + 1) % 256;   
			$j = ($j + $box[$a]) % 256;   
			$tmp = $box[$a];   
			$box[$a] = $box[$j];   
			$box[$j] = $tmp;   
			// 从密匙簿得出密匙进行异或，再转成字符   
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));   
		}   
		if($operation == 'D') {  
			// 验证数据有效性，请看未加密明文的格式   
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&  
				substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {   
					return substr($result, 26);   
				} else {   
					return '';   
				}   
			} else {   
				// 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因   
				// 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码   
				return $keyc.str_replace('=', '', base64_encode($result));   
			}   
		} 
		
		
		//加解密函数encrypt()
		//$str = 'abc'; 
		//$key = 'www.helloweba.com'; 
		//$token = encrypt($str, 'E', $key); 
		//echo '加密:'.encrypt($str, 'E', $key); 
		//echo '解密：'.encrypt($str, 'D', $key); 
		function encrypt($string,$operation,$key='hongzeit'){ 
			$key=md5($key); 
			$key_length=strlen($key); 
			$string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string; 
			$string_length=strlen($string); 
			$rndkey=$box=array(); 
			$result=''; 
			for($i=0;$i<=255;$i++){ 
				$rndkey[$i]=ord($key[$i%$key_length]); 
				$box[$i]=$i; 
			} 
			for($j=$i=0;$i<256;$i++){ 
				$j=($j+$box[$i]+$rndkey[$i])%256; 
				$tmp=$box[$i]; 
				$box[$i]=$box[$j]; 
				$box[$j]=$tmp; 
			} 
			for($a=$j=$i=0;$i<$string_length;$i++){ 
				$a=($a+1)%256; 
				$j=($j+$box[$a])%256; 
				$tmp=$box[$a]; 
				$box[$a]=$box[$j]; 
				$box[$j]=$tmp; 
				$result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256])); 
			} 
			if($operation=='D'){ 
				if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8)){ 
					return substr($result,8); 
				}else{ 
					return''; 
				} 
			}else{ 
				return str_replace('=','',base64_encode($result)); 
			} 
		}
		
		/**  
		* 发送get请求  
		* @param string $url 请求地址  
		* @param array $post_data post键值对数据  
		* @return string  
		*/  
		function send_get($url) {    
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
			$result = curl_exec($ch);  
			return $result;   
		}
		
		
		/**  
		* 发送post请求  
		* @param string $url 请求地址  
		* @param array $post_data post键值对数据  
		* @return string  
		*/  
		function send_post($url, $post_data) {   
			
			$postdata = http_build_query($post_data);   
			$options = array(   
				'http' => array(   
					'method' => 'POST',   
					'header' => 'Content-type:text/xml',   
					'content' => $postdata,   
					'timeout' => 15 * 60 // 超时时间（单位:s）   
					)   
				);   
			$context = stream_context_create($options);   
			$result = file_get_contents($url, false, $context);    
			return $result;   
		} 
		
		/**
		 * php异步请求
		 * $args array[
		 *   "host":主机
		 *   "url":地址
		 *   "method":方法
		 *   "data":数据
		 * ]
		 */
		function asyn_request($args) {
			$host = $args["host"] ?  $args["host"] : "localhost";//主机
			$method = $args["method"] == "POST" ? "POST" : "GET";//方法    
			$url = $args["url"] ? $args["url"] : "http://".$host ;//地址
			$data = is_array($args["data"]) ? $args["data"] : array();//请求参数    
			$fp = @fsockopen($host,80,$errno,$errstr,30); 
			//错误
			if(!$fp){echo"$errstr ($errno)<br/>\n";exit;}
			
			$qstr = http_build_query($data);//请求参数
			
			$params.= $method == "GET" ? "GET {$url}?{$qstr} HTTP/1.1\r\n" :  "POST {$url} HTTP/1.1\r\n";
			$params.= "Host: ".$host."\r\n";
			$params.= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.1.5) Gecko/20091102 Firefox/3.5.5\r\n";
			$params.= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
			$params.= "Accept-Language: zh-cn,zh;q=0.5\r\n";
			$params.= "Accept-Encoding: gzip,deflate\r\n";
			$params.= "Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7\r\n";
			$params.= "Keep-Alive: 300\r\n";
			$params.= "Connection: keep-alive\r\n";
			$params.= "Content-Type: application/x-www-form-urlencoded\r\n";
			$params.= "Content-Length: ".strlen($qstr)."\r\n\r\n";
			$params.= $method == "GET" ? null :$qstr;
			
			//file_put_contents("C:\\http.txt",$params);
			
			fwrite($fp, $params);
			fclose($fp);
			
			
			/*	$args["host"] = "localhost";//主机
				$args["url"] = "http://localhost/test/socket/doing.php";//异步执行的脚本
				$args["method"] = "POST";//请求方式
				$args["data"] = array("a"=>"中","b"=>"国");//参数 
				asyn_request($args);*/
			
		}
		
		
		
		//发送XML返回数据
		function sendDataByCurlJosn($url,$data){
			
			$header[] = "Content-type: text/xml";        //定义content-type为xml,注意是数组
			$ch = curl_init ($url);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch,CURLOPT_TIMEOUT,60);  //定义超时3秒钟  
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  
			$response = curl_exec($ch);
			if(curl_errno($ch)){ 
				Log::cur_err(curl_error($ch)); 
			}
			curl_close($ch); 
			
			Log::posthis($url.$data); 
			
			return $response;
		}
		
		//发送XML返回数据
		function sendDataByCurl($url,$data){
			
			$header[] = "Content-type: text/xml";        //定义content-type为xml,注意是数组
			$ch = curl_init ($url);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch,CURLOPT_TIMEOUT,60);  //定义超时3秒钟  
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  
			$response = curl_exec($ch);
			if(curl_errno($ch)){ 
				Log::cur_err(curl_error($ch)); 
			}
			curl_close($ch); 
			
			Log::posthis($url.$data); 
			
			try{       
				
				
				Log::posthis($response); 
				
				//$response = mb_convert_encoding($response, 'UTF-8', 'GB2312');
				//$txt = mb_convert_encoding($txt, 'UTF-8', 'GBK'); 
				//print_r($response);
				
				$response = str_replace("\r\n","",$response);
				$response = str_replace("\n","",$response);
				$response = str_replace("\t","",$response);
				
				$xml = simplexml_load_string($response);  
				header("Content-Type: text/html; charset=UTF-8");
				if($xml) { 
					Log::gethis($response); 
				} else {
					Log::xmlerr($response); 
				}  
				
			} catch(exception $e){
				Log::xmltc($e);  
			} 
			return $xml;
		}
		
		
		
		
		//发送XML返回数据
		function sendDataByCurlCode($url,$data){
			
			$header[] = "Content-type: text/xml";        //定义content-type为xml,注意是数组
			$ch = curl_init ($url);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch,CURLOPT_TIMEOUT,60);  //定义超时3秒钟  
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  
			$response = curl_exec($ch);
			if(curl_errno($ch)){ 
				Log::cur_err(curl_error($ch)); 
			}
			curl_close($ch); 
			
			Log::posthis($url.$data); 
			
			try{       
				
				$response = mb_convert_encoding($response, 'UTF-8', 'GB2312');
				//$response = mb_convert_encoding($response, 'UTF-8', 'GBK');  
				$response = new SimpleXMLElement($response);  
				//加入反馈内容
				$property = $response->result->attributes()->property;
				$response->addChild("property",$property); 
				//$xml = simplexml_load_string($response);   
				header("Content-Type: text/html; charset=UTF-8");
				if($response) { 
					Log::gethis($response); 
				} else {
					Log::xmlerr($response); 
				}  
				
			} catch(exception $e){
				Log::xmltc($e);  
			} 
			return $response;
		}
		
		/**
		* 把对象转换成数组
		* @param   object  $object 要转换的对象
		* @return  array
		*/
		function objectToArray($object) {
			if( count($object)==0 )  return trim((string)$object);
			$result = array();
			$object = is_object($object) ? get_object_vars($object) : $object;
			foreach ($object as $key => $val) {
				$val = (is_object($val) || is_array($val)) ? objectToArray($val) : $val;
				$result[$key] = $val;
			}
			return $result;
		}
		

		function ToHtml($xml)
		{
			$xml = str_replace("&","&amp;",$xml);
			$xml = str_replace("<","&lt;",$xml);
			$xml = str_replace(">","&gt;",$xml);
			$xml = str_replace("\r\n","<br />",$xml);
			$xml = str_replace("\n","<br />",$xml);
			$xml = str_replace("\t"," ",$xml);
			$xml = str_replace("\"","&quot;",$xml);
			$xml = str_replace('{}','""',$xml); 
			return $xml;
		}
		
		function SoapToXml($xml)
		{ 
			$startStr=strrpos($xml,"<![CDATA[");
			$endStr=strpos($xml,"]]>");
			$xml=substr($xml,$startStr,$endStr-$startStr);  
			$xml = str_replace("<![CDATA[>","",$xml);
			$xml = str_replace("<![CDATA[","",$xml); 
			$xml = str_replace("]]]]>","",$xml);
			$xml = str_replace("]]>","",$xml);
			$xml = str_replace("<ILLEGAL VALUE>","",$xml);  
			return $xml;
		}
	} 

?>
