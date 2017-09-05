<?php
if (!defined('IS_INITPHP')) exit('Access Denied!');   
class DatabaseDao { 
	
	//说明
	function select($sqlString,$resultType){  
		$data = M()->query($sqlString);   
		if($resultType=="entity"){
			return $data[0];
		}else if($resultType=="list")	{
			return $data;
		}else if($resultType=="rows")	{  
			return $data[0]["rows"];
		}else if($resultType=="return"){
			if($data){
				return array("return"=>"true");
			}else{
				return false;
			}
		}else{
			return $data;
		}
	}
	
	function update($sqlString,$resultType){ 
		$data = M()->execute($sqlString);     
		//if($resultType=="boolean"){
		if($data){  
			return array("return"=>"true");
		}else{ 
			return false;
		}
		//}else{ 
		//	return $data;
		//} 
		
	}
	
	function insert ($sqlString,$resultType){
		
		if($resultType=="boolean"){
			$data = M()->execute($sqlString);   
			if($data){
				return array("return"=>"true");
			}else{
				return false;
			}
		}else if($resultType=="autoid"){			
			$data = M()->executeinsert($sqlString);    
			if($data){
				return array("autoid"=>$data);
			}else{
				return "";
			}
		} else{
			$data = M()->execute($sqlString);   
			if($data){ 
				return array("return"=>"true");
			}else{ 
				return false;
			}
		}
	}
	
	function delete($sqlString,$resultType){ 
		$data = M()->execute($sqlString);    
		return $data; 
	}
	
	
	/**   
	* [array_to_sql 根据数组key和value拼接成需要的sql]
	* @param [type] $array  [key, value结构数组]   
	* @param string $type  [sql类型insert,update]  
	* @param array $exclude [排除的字段]   
	* @return [string]     [返回拼接好的sql]   
	*/  
	function array_to_sql($array, $type='insert', $exclude = array()){
		$sql = '';    
		if(count($array) > 0){      
			foreach ($exclude as $exkey) {       
				unset($array[$exkey]);//剔除不要的key     
			}      
			if('insert' == $type){       
				$keys = array_keys($array);       
				$values = array_values($array);   
				$col = implode("`, `", $keys);      
				$val = implode("', '", $values);   
				$sql = "(`$col`) values('$val')";  
			}else if('update' == $type){       
				$tempsql = '';       
				$temparr = array();  
				foreach ($array as $key => $value) {          
					$tempsql = "'$key' = '$value'";         
					$temparr[] = $tempsql;        
				}        
				$sql = implode(",", $temparr);     
			}    
		}    
		return $sql; 
	}
	
	
}

?>