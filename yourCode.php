<?php


include './DatabaseDao.class.php'; 
include './common.class.php'; 
//引入 COMMON new 对象 
$_ENV["commonClass"] = new commonClass();   
//引入数据DAO层 
$_ENV["dbDao"]	= new DatabaseDao();

class YourCode {
	
	//获取患者基本信息(3300)
	public function GetPatInfo($input) { 
		Log::posthis("GetPatInfo:req\r\n".$input);   
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象 
		
		//==============封装HIS请求============== 
		
		$PatientCard = $req->PatientCard;   
		$PatientID =  $req->PatientID;
		$IDNo =  $req->IDNo;    
		$PatientName = $req->PatientName;  
		
		if($PatientID!=""){ 
			$sqlString ="SELECT * FROM his_pat_index_master WHERE patientId='".$PatientID."'"; 
		}else if($IDNo!="" && $PatientName!=""){ 
			$sqlString ="SELECT * FROM his_pat_index_master WHERE idNo='".$IDNo."' and trueName='".$PatientName."'"; 
		}else if($PatientCard!=""){ 
			$sqlString ="SELECT * FROM his_pat_index_master WHERE cardId='".$PatientCard."'"; 
		} 
		$sqlData = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"entity");  
		
		$res = "<Response>";
		if($sqlData){
			$res .= "<ResultCode>0</ResultCode><ResultContent>查询成功</ResultContent>";
		}else{
			$res .= "<ResultCode>1</ResultCode><ResultContent>查询失败</ResultContent>";
		} 
		$res .="<PatientID>".$sqlData['patientId']."</PatientID><PatientName>".$sqlData['trueName']."</PatientName><Sex>".$sqlData['sex']."</Sex><SexCode>".(strval($sqlData['sex']) == "男" ? "1":"2")."</SexCode><DOB>".$sqlData['birthDay']."</DOB><TelephoneNo>".$sqlData['tel']."</TelephoneNo><Mobile>".$sqlData['phone']."</Mobile><DocumentID></DocumentID><Address>".$sqlData['address']."</Address><IDTypeCode>".$sqlData['idNoType']."</IDTypeCode><IDTypeDesc></IDTypeDesc><IDNo>".$sqlData['idNo']."</IDNo><InsureCardNo></InsureCardNo><AccInfo>-200^^^0^0^0^^^^^P^PC^^</AccInfo><CardNo>".$sqlData['cardId']."</CardNo><CardType>02</CardType><ParentlifeAccount>".$sqlData['fee']."</ParentlifeAccount>";		
		$res .= "</Response>";
		
		Log::posthis("GetPatInfo:res\r\n".$res); 
		return $res;
		//return "<Response><ResultCode>0</ResultCode><ResultContent></ResultContent><PatientID>33043015</PatientID><PatientName>刘伟洪</PatientName><Sex>男</Sex><SexCode>1</SexCode><DOB>1986-07-15</DOB><TelephoneNo>13660494104</TelephoneNo><Mobile>13660494104</Mobile><DocumentID></DocumentID><Address>广东省广州市天河区上社</Address><IDTypeCode>1</IDTypeCode><IDTypeDesc></IDTypeDesc><IDNo>445122198702185253</IDNo><InsureCardNo></InsureCardNo><AccInfo>-200^^^0^0^0^^^^^P^PC^^</AccInfo><CardNo>00239855</CardNo><CardType>02</CardType><ParentlifeAccount>5</ParentlifeAccount></Response>";
	} 
	
	//建索引（3014）
	public function SavePatientCard($input) {
		Log::posthis("SavePatientCard:req\r\n".$input); 
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象 
		
		//==============封装HIS请求==============  
		$IDNo =  $req->IDNo;    
		$PatientName = $req->PatientName;  
		$DOB = $req->DOB;  
		$Address = $req->Address;  
		$Mobile = $req->Mobile;  
		$IDType = $req->IDType;  
		$Nation = $req->Nation;  
		
		$Sex = (strval($req->Sex) == "1" ? "男":"女");     		
		
		$sqlString ="SELECT * FROM his_pat_index_master WHERE idNo='".$IDNo."' and trueName='".$PatientName."'"; 
		$sqlData = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"entity");
		
		$res = "<Response>";
		if($sqlData){
			$res .= "<ResultCode>1</ResultCode><ResultContent>主索引存在</ResultContent><ActiveFlag></ActiveFlag><PatientID></PatientID>";
		}else{
			$sqlString ="SELECT MAX(patientId+1) as pid,MAX(cardId+1) cid FROM his_pat_index_master "; 
			$sqlData1 = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"entity");  
			$pid = str_pad($sqlData1['pid'],8,"0",STR_PAD_LEFT);
			$cid = str_pad($sqlData1['cid'],8,"0",STR_PAD_LEFT);
			$sqlString ="insert into his_pat_index_master (trueName,phone,email,idNoType,idNo,birthDay,tel,address,sex,nation,province,city,area,patientId,cardId,fee,createDate) VALUES ('$PatientName','$Mobile','','$IDType','$IDNo','$DOB','$Mobile','$Address','$Sex','$Nation','','','','$pid','','0','".$this->datenow()."')";
			$sqlData2 = call_user_func(array($_ENV["dbDao"],"insert"),$sqlString,"boolean");
			if($sqlData2){
				$res .= "<ResultCode>0</ResultCode><ResultContent>创建成功</ResultContent><ActiveFlag></ActiveFlag><PatientID>$pid</PatientID>";
			}else{
				$res .= "<ResultCode>1</ResultCode><ResultContent>创建失败</ResultContent><ActiveFlag></ActiveFlag><PatientID></PatientID>";
			} 
		} 
		$res .= "</Response>";
		Log::posthis("SavePatientCard:res\r\n".$res); 
		return $res;
		//return "<Response><PatientID>".$this->randomnum()."</PatientID><ResultCode>0<ResultCode><ResultContent>创建成功<ResultContent><ActiveFlag></ActiveFlag></Response>";
	} 
	
	//查询二级科室列表(1012) 
	public function QueryDepartment($input) {  
		Log::posthis("QueryDepartment:req\r\n".$input); 	
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象
		
		$TradeCode = "1012";
		$ExtOrgCode = "南方医务通"; 
		$ExtUserID = "NFYWT";  
		$ClientType = ""; 
		$HospitalId = ""; 
		$DepartmentType = ""; 
		$DepartmentCode = $req->DepartmentCode;   
		
		$postData = "<Request><TradeCode>$TradeCode</TradeCode><ExtOrgCode>$ExtOrgCode</ExtOrgCode><ClientType>$ClientType</ClientType><HospitalId>$HospitalId</HospitalId><DepartmentType>$DepartmentType</DepartmentType><DepartmentCode>$DepartmentCode</DepartmentCode><ExtUserID>$ExtUserID</ExtUserID></Request>";
		$postData = str_replace(' ','%20',$postData); 
		$wsdl = "http://yygh1.dept.nfyy.com/csp/oep/DHC.OEP.BS.OEPSTANWebService.cls?soap_method=QueryDepartment&Input=";
		$result = file_get_contents($wsdl.$postData);    
		$res = call_user_func(array($_ENV["commonClass"],"SoapToXml"),$result);  
		
		Log::posthis("QueryDepartment:res\r\n".$res); 
		return $res; 
	} 
	
	//查询医生列表(1013)
	public function QueryDoctor($input) {
		Log::posthis("QueryDoctor:req\r\n".$input); 	
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象
		
		$TradeCode = "1013";
		$ExtOrgCode = "南方医务通"; 
		$ExtUserID = "NFYWT";  
		$ClientType = "";
		$HospitalId = "";
		$DoctorName =  $req->DoctorName;
		$DepartmentCode =  $req->DepartmentCode;
		
		$postData = "<Request><TradeCode>$TradeCode</TradeCode><ExtOrgCode>$ExtOrgCode</ExtOrgCode><ClientType>$ClientType</ClientType><HospitalId>$HospitalId</HospitalId><ExtUserID>$ExtUserID</ExtUserID><DepartmentCode>$DepartmentCode</DepartmentCode><DoctorName>$DoctorName</DoctorName></Request>";
		$postData = str_replace(' ','%20',$postData); 
		$wsdl = "http://yygh1.dept.nfyy.com/csp/oep/DHC.OEP.BS.OEPSTANWebService.cls?soap_method=QueryDoctor&Input=";
		$result = file_get_contents($wsdl.$postData);    
		$res = call_user_func(array($_ENV["commonClass"],"SoapToXml"),$result);  
		
		Log::posthis("QueryDoctor:res\r\n".$res); 
		return $res; 
	}  
	
	//查询排班记录(1004)
	public function QuerySchedule($input) {
		Log::posthis("QuerySchedule:req\r\n".$input); 	
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象
		
		$TradeCode = "1004";
		$ExtOrgCode = "南方医务通"; 
		$ExtUserID = "NFYWT";  
		$ClientType = ""; 
		$HospitalId = ""; 
		$DeptType = ""; 
		$DoctorCode = $req->DoctorCode; 
		$SessType = ""; 
		$StartDate = $req->StartDate; 
		$EndDate = $req->EndDate; 
		$RBASSessionCode = ""; 
		$ServiceCode = ""; 
		$StopScheduleFlag = ""; 
		$DepartmentCode = $req->DepartmentCode;
		$SearchCode = "";   
		
		
		$postData = "<Request><HospitalId>$HospitalId</HospitalId><ExtOrgCode>$ExtOrgCode</ExtOrgCode><ExtUserID>$ExtUserID</ExtUserID><ClientType>$ClientType</ClientType><TradeCode>$TradeCode</TradeCode><DeptType>$DeptType</DeptType><DoctorCode>$DoctorCode</DoctorCode><SessType>$SessType</SessType><StartDate>$StartDate</StartDate><EndDate>$EndDate</EndDate><RBASSessionCode>$RBASSessionCode</RBASSessionCode><ServiceCode>$ServiceCode</ServiceCode><StopScheduleFlag>$StopScheduleFlag</StopScheduleFlag><DepartmentCode>$DepartmentCode</DepartmentCode><SearchCode>$SearchCode</SearchCode></Request>";		
		$postData = str_replace(' ','%20',$postData);  
		$wsdl = "http://yygh1.dept.nfyy.com/csp/oep/DHC.OEP.BS.OEPSTANWebService.cls?soap_method=QuerySchedule&Input=";
		$result = file_get_contents($wsdl.$postData);    
		$res = call_user_func(array($_ENV["commonClass"],"SoapToXml"),$result);  
		
		$resx = simplexml_load_string($res);
		
		$res = "<Response>"; 
		$res .= "<ResultCode>".$resx->ResultCode."</ResultCode><ResultContent>".$resx->ResultContent."</ResultContent><RecordCount>".$resx->RecordCount."</RecordCount>";
		$res .= "<Schedules>"; 
		
		$xmldata = $resx->Schedules->Schedule;		
		
		foreach($xmldata as $key=>$v){     
			 
			$res .="<Schedule><ScheduleItemCode>".$v->ScheduleItemCode."</ScheduleItemCode><ServiceDate>".$v->ServiceDate."</ServiceDate><WeekDay>".$v->WeekDay."</WeekDay><SessionCode>".$v->SessionCode."</SessionCode><SessionName>".$v->SessionName."</SessionName><DepartmentCode>".$v->DepartmentCode."</DepartmentCode><DepartmentName>".$v->DepartmentName."</DepartmentName><ClinicRoomCode>".$v->ClinicRoomCode."</ClinicRoomCode><ClinicRoomName>".$v->ClinicRoomName."</ClinicRoomName><DoctorCode>".$v->DoctorCode."</DoctorCode><DoctorName>".$v->DoctorName."</DoctorName><ImageUrl>".$v->ImageUrl."</ImageUrl><DoctorTitleCode>".$v->DoctorTitleCode."</DoctorTitleCode><DoctorTitle>".$v->DoctorTitle."</DoctorTitle><DoctorSpec>".$v->DoctorSpec."</DoctorSpec><DoctorSessTypeCode>".$v->DoctorSessTypeCode."</DoctorSessTypeCode><DoctorSessType>".$v->DoctorSessType."</DoctorSessType><ServiceCode>".$v->ServiceCode."</ServiceCode><ServiceName>".$v->ServiceName."</ServiceName><Fee>0.01</Fee><RegFee>0.01</RegFee><CheckupFee>0</CheckupFee><ServiceFee>0</ServiceFee><OtherFee>0</OtherFee><AvailableNumStr>".$v->AvailableNumStr."</AvailableNumStr><AdmitAddress>".$v->AdmitAddress."</AdmitAddress><AdmitTimeRange>".$v->AdmitTimeRange."</AdmitTimeRange><Note>".$v->Note."</Note><StartTime>".$v->StartTime."</StartTime><EndTime>".$v->EndTime."</EndTime><TimeRangeFlag>".$v->TimeRangeFlag."</TimeRangeFlag><ScheduleStatus>".$v->ScheduleStatus."</ScheduleStatus><ScheduleNum>".$v->ScheduleNum."</ScheduleNum><AvailableTotalNum>".$v->AvailableTotalNum."</AvailableTotalNum><AvailableLeftNum>".$v->AvailableLeftNum."</AvailableLeftNum></Schedule>";
			$sqlString ="insert into his_reginfo (deptId,deptName,doctorId,doctorName,doctorTitle,regCode,regDate,regWeekDay,timeFlag,timeName,address,regStatus,regTotalCount,regleaveCount,fee,regFee,treatFee,isTimeReg) VALUES ('$v->DepartmentCode','$v->DepartmentName','$v->DoctorCode','$v->DoctorName','$v->DoctorTitleCode','$v->ScheduleItemCode','$v->ServiceDate','$v->WeekDay','$v->SessionCode','$v->SessionName','$v->AdmitAddress','$v->ScheduleStatus','$v->AvailableLeftNum','$v->AvailableTotalNum','0.01','0.01','0','$v->TimeRangeFlag')";
			call_user_func(array($_ENV["dbDao"],"insert"),$sqlString,"return");   
		} 
		$res .= "</Schedules>"; 
		$res .= "</Response>"; 
		
		Log::posthis("QuerySchedule:res\r\n".$res); 
		return $res;
		//return "<Response><ResultCode>0</ResultCode><ResultContent>成功</ResultContent><RecordCount>5</RecordCount><Schedules><Schedule><ScheduleItemCode>1805||438</ScheduleItemCode><ServiceDate>".$this->datenow1(0)."</ServiceDate><WeekDay>星期三</WeekDay><SessionCode>S</SessionCode><SessionName>上午</SessionName><DepartmentCode>425</DepartmentCode><DepartmentName>肛肠科门诊</DepartmentName><ClinicRoomCode></ClinicRoomCode><ClinicRoomName></ClinicRoomName><DoctorCode>1805</DoctorCode><DoctorName>李胜龙</DoctorName><ImageUrl>http://192.168.1.76:8000/LISL(李胜龙).jpg</ImageUrl><DoctorTitleCode>副主任医师</DoctorTitleCode><DoctorTitle>副主任医师</DoctorTitle><DoctorSpec></DoctorSpec><DoctorSessTypeCode>47</DoctorSessTypeCode><DoctorSessType>副高号</DoctorSessType><ServiceCode></ServiceCode><ServiceName></ServiceName><Fee>7</Fee><RegFee>1</RegFee><CheckupFee>6</CheckupFee><ServiceFee></ServiceFee><OtherFee>0</OtherFee><AvailableNumStr></AvailableNumStr><AdmitAddress>门诊三楼</AdmitAddress><AdmitTimeRange></AdmitTimeRange><Note></Note><StartTime>08:00</StartTime><EndTime>11:30</EndTime><TimeRangeFlag>Y</TimeRangeFlag><ScheduleStatus>N</ScheduleStatus><ScheduleNum>0</ScheduleNum><AvailableTotalNum>20</AvailableTotalNum><AvailableLeftNum>20</AvailableLeftNum></Schedule><Schedule><ScheduleItemCode>1805||439</ScheduleItemCode><ServiceDate>".$this->datenow1(1)."</ServiceDate><WeekDay>星期四</WeekDay><SessionCode>S</SessionCode><SessionName>上午</SessionName><DepartmentCode>425</DepartmentCode><DepartmentName>肛肠科门诊</DepartmentName><ClinicRoomCode></ClinicRoomCode><ClinicRoomName></ClinicRoomName><DoctorCode>1805</DoctorCode><DoctorName>李胜龙</DoctorName><ImageUrl>http://192.168.1.76:8000/LISL(李胜龙).jpg</ImageUrl><DoctorTitleCode>副主任医师</DoctorTitleCode><DoctorTitle>副主任医师</DoctorTitle><DoctorSpec></DoctorSpec><DoctorSessTypeCode>47</DoctorSessTypeCode><DoctorSessType>副高号</DoctorSessType><ServiceCode></ServiceCode><ServiceName></ServiceName><Fee>7</Fee><RegFee>1</RegFee><CheckupFee>6</CheckupFee><ServiceFee></ServiceFee><OtherFee>0</OtherFee><AvailableNumStr></AvailableNumStr><AdmitAddress>门诊三楼</AdmitAddress><AdmitTimeRange></AdmitTimeRange><Note></Note><StartTime>08:00</StartTime><EndTime>11:30</EndTime><TimeRangeFlag>Y</TimeRangeFlag><ScheduleStatus>N</ScheduleStatus><ScheduleNum>0</ScheduleNum><AvailableTotalNum>20</AvailableTotalNum><AvailableLeftNum>20</AvailableLeftNum></Schedule><Schedule><ScheduleItemCode>1805||440</ScheduleItemCode><ServiceDate>".$this->datenow1(2)."</ServiceDate><WeekDay>星期五</WeekDay><SessionCode>S</SessionCode><SessionName>上午</SessionName><DepartmentCode>425</DepartmentCode><DepartmentName>肛肠科门诊</DepartmentName><ClinicRoomCode></ClinicRoomCode><ClinicRoomName></ClinicRoomName><DoctorCode>1805</DoctorCode><DoctorName>李胜龙</DoctorName><ImageUrl>http://192.168.1.76:8000/LISL(李胜龙).jpg</ImageUrl><DoctorTitleCode>副主任医师</DoctorTitleCode><DoctorTitle>副主任医师</DoctorTitle><DoctorSpec></DoctorSpec><DoctorSessTypeCode>47</DoctorSessTypeCode><DoctorSessType>副高号</DoctorSessType><ServiceCode></ServiceCode><ServiceName></ServiceName><Fee>7</Fee><RegFee>1</RegFee><CheckupFee>6</CheckupFee><ServiceFee></ServiceFee><OtherFee>0</OtherFee><AvailableNumStr></AvailableNumStr><AdmitAddress>门诊三楼</AdmitAddress><AdmitTimeRange></AdmitTimeRange><Note></Note><StartTime>08:00</StartTime><EndTime>11:30</EndTime><TimeRangeFlag>Y</TimeRangeFlag><ScheduleStatus>N</ScheduleStatus><ScheduleNum>0</ScheduleNum><AvailableTotalNum>20</AvailableTotalNum><AvailableLeftNum>20</AvailableLeftNum></Schedule><Schedule><ScheduleItemCode>1805||441</ScheduleItemCode><ServiceDate>".$this->datenow1(3)."</ServiceDate><WeekDay>星期六</WeekDay><SessionCode>S</SessionCode><SessionName>上午</SessionName><DepartmentCode>425</DepartmentCode><DepartmentName>肛肠科门诊</DepartmentName><ClinicRoomCode></ClinicRoomCode><ClinicRoomName></ClinicRoomName><DoctorCode>1805</DoctorCode><DoctorName>李胜龙</DoctorName><ImageUrl>http://192.168.1.76:8000/LISL(李胜龙).jpg</ImageUrl><DoctorTitleCode>副主任医师</DoctorTitleCode><DoctorTitle>副主任医师</DoctorTitle><DoctorSpec></DoctorSpec><DoctorSessTypeCode>47</DoctorSessTypeCode><DoctorSessType>副高号</DoctorSessType><ServiceCode></ServiceCode><ServiceName></ServiceName><Fee>7</Fee><RegFee>1</RegFee><CheckupFee>6</CheckupFee><ServiceFee></ServiceFee><OtherFee>0</OtherFee><AvailableNumStr></AvailableNumStr><AdmitAddress>门诊三楼</AdmitAddress><AdmitTimeRange></AdmitTimeRange><Note></Note><StartTime>08:00</StartTime><EndTime>11:30</EndTime><TimeRangeFlag>Y</TimeRangeFlag><ScheduleStatus>N</ScheduleStatus><ScheduleNum>0</ScheduleNum><AvailableTotalNum>20</AvailableTotalNum><AvailableLeftNum>20</AvailableLeftNum></Schedule><Schedule><ScheduleItemCode>1805||443</ScheduleItemCode><ServiceDate>".$this->datenow1(4)."</ServiceDate><WeekDay>星期一</WeekDay><SessionCode>S</SessionCode><SessionName>上午</SessionName><DepartmentCode>425</DepartmentCode><DepartmentName>肛肠科门诊</DepartmentName><ClinicRoomCode></ClinicRoomCode><ClinicRoomName></ClinicRoomName><DoctorCode>1805</DoctorCode><DoctorName>李胜龙</DoctorName><ImageUrl>http://192.168.1.76:8000/LISL(李胜龙).jpg</ImageUrl><DoctorTitleCode>副主任医师</DoctorTitleCode><DoctorTitle>副主任医师</DoctorTitle><DoctorSpec></DoctorSpec><DoctorSessTypeCode>47</DoctorSessTypeCode><DoctorSessType>副高号</DoctorSessType><ServiceCode></ServiceCode><ServiceName></ServiceName><Fee>7</Fee><RegFee>1</RegFee><CheckupFee>6</CheckupFee><ServiceFee></ServiceFee><OtherFee>0</OtherFee><AvailableNumStr></AvailableNumStr><AdmitAddress>门诊三楼</AdmitAddress><AdmitTimeRange></AdmitTimeRange><Note></Note><StartTime>08:00</StartTime><EndTime>11:30</EndTime><TimeRangeFlag>Y</TimeRangeFlag><ScheduleStatus>N</ScheduleStatus><ScheduleNum>0</ScheduleNum><AvailableTotalNum>20</AvailableTotalNum><AvailableLeftNum>20</AvailableLeftNum></Schedule></Schedules></Response>";
	} 
	
	//查询医生号源分时信息(10041)
	public function QueryScheduleTimeInfo($input) {
		Log::posthis("QueryScheduleTimeInfo:req\r\n".$input); 	
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象
		
		$TradeCode = "10041";
		$ExtOrgCode = "南方医务通"; 
		$ExtUserID = "NFYWT";  
		$ClientType = ""; 
		$HospitalId = "";  
		$RBASSessionCode = $req->RBASSessionCode; 
		$ScheduleItemCode = "";   
		$DepartmentCode = $req->DepartmentCode; 
		$DoctorCode = $req->DoctorCode; 
		$ServiceDate = $req->ServiceDate;   
		
		$postData = "<Request><TradeCode>$TradeCode</TradeCode><ExtOrgCode>$ExtOrgCode</ExtOrgCode><ClientType>$ClientType</ClientType><HospitalId>$HospitalId</HospitalId><ExtUserID>$ExtUserID</ExtUserID><DepartmentCode>$DepartmentCode</DepartmentCode><DoctorCode>$DoctorCode</DoctorCode><RBASSessionCode>$RBASSessionCode</RBASSessionCode><ScheduleItemCode>$ScheduleItemCode</ScheduleItemCode><ServiceDate>$ServiceDate</ServiceDate></Request>";
		$postData = str_replace(' ','%20',$postData); 
		$wsdl = "http://yygh1.dept.nfyy.com/csp/oep/DHC.OEP.BS.OEPSTANWebService.cls?soap_method=QueryScheduleTimeInfo&Input=";
		$result = file_get_contents($wsdl.$postData);    
		$res = call_user_func(array($_ENV["commonClass"],"SoapToXml"),$result);  
		Log::posthis("QueryScheduleTimeInfo:res\r\n".$res); 
		return $res;
		
		//return "<Response><ResultCode>0</ResultCode><ResultContent>成功</ResultContent><RecordCount></RecordCount><TimeRanges><TimeRange><ScheduleItemCode>1803||633</ScheduleItemCode><ServiceDate>".$this->datenow()."</ServiceDate><WeekDay>".$this->dateweek()."</WeekDay><SessionCode>上午</SessionCode><SessionName>S</SessionName><StartTime>08:00</StartTime><EndTime>08:30</EndTime><AvailableTotalNum></AvailableTotalNum><AvailableLeftNum></AvailableLeftNum><TimeInfos><TimeInfo><AvailableNo>1</AvailableNo><AvailableNoTime>08:00:00</AvailableNoTime><AvailableFlag>N</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>2</AvailableNo><AvailableNoTime>08:07:30</AvailableNoTime><AvailableFlag>N</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>3</AvailableNo><AvailableNoTime>08:15:00</AvailableNoTime><AvailableFlag>N</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>4</AvailableNo><AvailableNoTime>08:22:30</AvailableNoTime><AvailableFlag>N</AvailableFlag></TimeInfo></TimeInfos></TimeRange><TimeRange><ScheduleItemCode>1803||633</ScheduleItemCode><ServiceDate>".$this->datenow()."</ServiceDate><WeekDay>".$this->dateweek()."</WeekDay><SessionCode>上午</SessionCode><SessionName>S</SessionName><StartTime>08:30</StartTime><EndTime>09:00</EndTime><AvailableTotalNum></AvailableTotalNum><AvailableLeftNum></AvailableLeftNum><TimeInfos><TimeInfo><AvailableNo>5</AvailableNo><AvailableNoTime>08:30:00</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>6</AvailableNo><AvailableNoTime>08:37:30</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>7</AvailableNo><AvailableNoTime>08:45:00</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>8</AvailableNo><AvailableNoTime>08:52:30</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo></TimeInfos></TimeRange><TimeRange><ScheduleItemCode>1803||633</ScheduleItemCode><ServiceDate>".$this->datenow()."</ServiceDate><WeekDay>".$this->dateweek()."</WeekDay><SessionCode>上午</SessionCode><SessionName>S</SessionName><StartTime>09:00</StartTime><EndTime>09:30</EndTime><AvailableTotalNum></AvailableTotalNum><AvailableLeftNum></AvailableLeftNum><TimeInfos><TimeInfo><AvailableNo>9</AvailableNo><AvailableNoTime>09:00:00</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>10</AvailableNo><AvailableNoTime>09:07:30</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>11</AvailableNo><AvailableNoTime>09:15:00</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>12</AvailableNo><AvailableNoTime>09:22:30</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo></TimeInfos></TimeRange><TimeRange><ScheduleItemCode>1803||633</ScheduleItemCode><ServiceDate>".$this->datenow()."</ServiceDate><WeekDay>".$this->dateweek()."</WeekDay><SessionCode>上午</SessionCode><SessionName>S</SessionName><StartTime>09:30</StartTime><EndTime>10:00</EndTime><AvailableTotalNum></AvailableTotalNum><AvailableLeftNum></AvailableLeftNum><TimeInfos><TimeInfo><AvailableNo>13</AvailableNo><AvailableNoTime>09:30:00</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>14</AvailableNo><AvailableNoTime>09:37:30</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>15</AvailableNo><AvailableNoTime>09:45:00</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>16</AvailableNo><AvailableNoTime>09:52:30</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo></TimeInfos></TimeRange><TimeRange><ScheduleItemCode>1803||633</ScheduleItemCode><ServiceDate>".$this->datenow()."</ServiceDate><WeekDay>".$this->dateweek()."</WeekDay><SessionCode>上午</SessionCode><SessionName>S</SessionName><StartTime>10:00</StartTime><EndTime>10:30</EndTime><AvailableTotalNum></AvailableTotalNum><AvailableLeftNum></AvailableLeftNum><TimeInfos><TimeInfo><AvailableNo>17</AvailableNo><AvailableNoTime>10:00:00</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>18</AvailableNo><AvailableNoTime>10:10:00</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>19</AvailableNo><AvailableNoTime>10:20:00</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo></TimeInfos></TimeRange><TimeRange><ScheduleItemCode>1803||633</ScheduleItemCode><ServiceDate>".$this->datenow()."</ServiceDate><WeekDay>".$this->dateweek()."</WeekDay><SessionCode>上午</SessionCode><SessionName>S</SessionName><StartTime>10:30</StartTime><EndTime>11:00</EndTime><AvailableTotalNum></AvailableTotalNum><AvailableLeftNum></AvailableLeftNum><TimeInfos><TimeInfo><AvailableNo>20</AvailableNo><AvailableNoTime>10:30:00</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>21</AvailableNo><AvailableNoTime>10:40:00</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>22</AvailableNo><AvailableNoTime>10:50:00</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo></TimeInfos></TimeRange><TimeRange><ScheduleItemCode>1803||633</ScheduleItemCode><ServiceDate>".$this->datenow()."</ServiceDate><WeekDay>".$this->dateweek()."</WeekDay><SessionCode>上午</SessionCode><SessionName>S</SessionName><StartTime>11:00</StartTime><EndTime>11:30</EndTime><AvailableTotalNum></AvailableTotalNum><AvailableLeftNum></AvailableLeftNum><TimeInfos><TimeInfo><AvailableNo>23</AvailableNo><AvailableNoTime>11:00:00</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>24</AvailableNo><AvailableNoTime>11:10:00</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo><TimeInfo><AvailableNo>25</AvailableNo><AvailableNoTime>11:20:00</AvailableNoTime><AvailableFlag>Y</AvailableFlag></TimeInfo></TimeInfos></TimeRange></TimeRanges></Response>";
	} 
	
	//查询停诊医生信息 (1107)
	public function QueryStopDoctorInfo($input) {
		//<Request><TradeCode>1107</TradeCode><ExtOrgCode>云医院</ExtOrgCode><ExtUserID>YYY</ExtUserID><ClientType></ClientType><HospitalId></HospitalId><DepartmentCode></DepartmentCode><StartDate>2017-02-09</StartDate><EndDate>2017-02-19</EndDate></Request>
		return "<Response><ResultCode>0</ResultCode><ResultContent>成功</ResultContent><RecordCount>1</RecordCount><StopDoctorInfos><StopDoctorInfo><ScheduleCode>1803||633</ScheduleCode><ServiceDate>".$this->datenow()."</ServiceDate><TimeRangeCode>S</TimeRangeCode><TimeRangeName>上午</TimeRangeName><StartTime>11:00</StartTime><EndTime>11:30</EndTime><DepartmentCode>425</DepartmentCode><DepartmentName></DepartmentName><DoctorCode>1803</DoctorCode><DoctorName></DoctorName><Reason>临时有事</Reason><OrderId></OrderId><ReplaceDoctorId></ReplaceDoctorId><ReplaceDoctorName></ReplaceDoctorName><AdmitRange>11:00-11:30</AdmitRange></StopDoctorInfo><StopDoctorInfos></Response>";
	} 
	
	//预约（1000）
	public function BookService($input) {
		Log::posthis("BookService:req\r\n".$input); 	
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象
		
		$TradeCode = "1000";
		$ExtOrgCode = "南方医务通"; 
		$ExtUserID = "NFYWT";  
		$ClientType = ""; 
		$HospitalId =""; 
		$TransactionId = $req->TransactionId; 
		$ScheduleItemCode = $req->ScheduleItemCode; 
		$CardType = "02"; 
		$CredTypeCode ="01"; 
		$IDCardNo =$req->IDCardNo; 
		$TelePhoneNo = ""; 
		$MobileNo = $req->MobileNo; 
		$PatientName = $req->PatientName;  
		$PayFlag =""; 
		$PayModeCode =""; 
		$PayBankCode = ""; 
		$PayCardNo =""; 
		$PayFee =$req->RegFee; 
		$PayInsuFee =""; 
		$PayInsuFeeStr=""; 
		$PayTradeNo =""; 
		$LockQueueNo =""; 
		$Gender =""; 
		$Address =""; 
		$HISApptID =""; 
		$SeqCode =""; 
		$AdmitRange =""; 
		$StartTime =$req->StartTime; 
		$EndTime =$req->EndTime;
		$PatientID = $req->PatientID;
		
		//INSERT INTO hz_appointsmaster (orderId,hospitalId,deptId,doctorId,regDate,timeFlag,startTime,endTime,customerUserId,customerFamilyId,orderTime,fee,treatfee) VALUES (\'{F_orderId}\',\'{I_hospitalId}\',\'{I_deptId}\',\'{I_doctorId}\',\'{I_regDate}\',\'{I_timeFlag}\',\'{I_startTime}\',\'{I_endTime}\',\'{M_id}\',\'{I_customerFamilyId}\',\'{F_timenow}\',\'{I_regFee}\',\'{I_treatFee}\')
		$sqlString ="SELECT * FROM his_pat_index_master WHERE  patientId='".$PatientID."'"; 
		$sqlData = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"entity");
		
		$res = "<Response>";
		if(!$sqlData){
			$res .= "<ResultCode>1</ResultCode><ResultContent>病人ID不存在</ResultContent>";
		}else{ 
			$sqlString ="SELECT * FROM his_reginfo WHERE  regCode='".$ScheduleItemCode."'"; 
			$sqlData1 = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"entity");
			if($sqlData1){  
				
				//------计算序号-------------
				$sqlString ="SELECT COUNT(*)+1 AS seqCode FROM his_appoints_master where orderIdHIS='$ScheduleItemCode'"; 
				$sqlDataMax = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"entity");  
				$SeqCode =  $sqlDataMax['seqCode'];
				///------------------- 
				
				$sqlString ="INSERT INTO his_appoints_master (orderId,regDate,startTime,endTime,orderTime,fee,orderIdHIS,seqCode,patientId,payFlag,cancelFlag,infoFlag,returnFlag,timeName,address,patientName,doctorId,doctorName,deptId,deptName,doctorTitle) VALUES 	('$TransactionId','".$sqlData1['regDate']."','$StartTime','$EndTime','".$this->timenow()."','".$sqlData1['fee']."','$ScheduleItemCode','$SeqCode','$PatientID','1','1','1','1','".$sqlData1['timeName']."','".$sqlData1['address']."','".$sqlData['trueName']."','".$sqlData1['doctorId']."','".$sqlData1['doctorName']."','".$sqlData1['deptId']."','".$sqlData1['deptName']."','".$sqlData1['doctorTitle']."')";
				$sqlData2 = call_user_func(array($_ENV["dbDao"],"insert"),$sqlString,"autoid");
				if($sqlData2){
					$res .= "<ResultCode>0</ResultCode><ResultContent>预约成功</ResultContent><OrderCode>".$ScheduleItemCode."||".$SeqCode."</OrderCode><SeqCode>$SeqCode</SeqCode><RegFee>".$sqlData1['fee']."</RegFee><AdmitRange></AdmitRange><AdmitAddress>".$sqlData1['address']."</AdmitAddress><OrderContent></OrderContent><TransactionId></TransactionId>";
				}else{
					$res .= "<ResultCode>1</ResultCode><ResultContent>预约失败</ResultContent>";
				}
			}else{
				$res .= "<ResultCode>1</ResultCode><ResultContent>预约失败</ResultContent>";
			}
		} 
		$res .= "</Response>";
		Log::posthis("BookService:res\r\n".$res); 
		return $res;
		//return "<Response><ResultCode>0</ResultCode><ResultContent>预约成功</ResultContent><OrderCode>1805||438||1</OrderCode><SeqCode>7</SeqCode><RegFee>7</RegFee><AdmitRange></AdmitRange><AdmitAddress>门诊三楼</AdmitAddress><OrderContent>^6674506^陈测试^男^7 号(上午)^7^GCKMZ-肛肠科门诊^李胜龙^2016-11-01^64216^2016-11-02^^</OrderContent><TransactionId></TransactionId></Response>";
	} 
	
	//病人取号确认（2001）
	public function OPAppArrive($input) {
		Log::posthis("OPAppArrive:req\r\n".$input); 	
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象
		
		//==============封装HIS请求==============  
		$TradeCode = "2001";
		$ExtOrgCode = "南方医务通"; 
		$ExtUserID = "NFYWT";  
		$ClientType = "";
		$HospitalId = "";
		$OrderCode = $req->OrderCode;
		$PatientID = $req->PatientID;
		$PayOrderId = "";
		$PayAmt = $req->PayAmt;
		$PayModeCode = $req->PayModeCode; 
		$OrgHISTradeNo = "";
		$PayCardNo= "";
		$RevTranFlag = "";
		$BankDate = "";
		$BankAccDate = "";
		$TransactionId = $req->TransactionId;
		$BankTradeNo= "";
		$PayDate = "";
		$PayTime = $req->PayTime;
		$PayTradeStr = "";
		$BankCode = "";
		$OrgPaySeqNo = "";
		$PayInsuFeeStr = "";
		$ResultContent = "";
		$PayOrderId = "";
		$PayTradeNo = $req->PayTradeNo;
		
		
		$sqlString ="SELECT * FROM his_pat_index_master WHERE patientId='$PatientID' "; 
		$sqlData1 = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"entity");
		
		$res = "<Response>";
		if(!$sqlData1){
			$res .= "<ResultCode>1</ResultCode><ResultContent>订单已支付或不存</ResultContent>";
		}else{
			$sqlString ="SELECT * FROM his_appoints_master WHERE cancelFlag='1' and payFlag='1' AND  CONCAT(orderIdHIS,'||',seqCode)='$OrderCode' "; 
			$sqlData = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"entity");
			
			if(!$sqlData){
				$res .= "<ResultCode>1</ResultCode><ResultContent>订单已支付或不存</ResultContent>";
			}else{  
				/*if($PayModeCode=="CPP"){
					if(floatval($sqlData1['fee'])>=floatval($PayAmt)){ 
						$sqlString ="update his_pat_index_master set fee=fee-$PayAmt where patientId='".$PatientID."'";
						call_user_func(array($_ENV["dbDao"],"update"),$sqlString,"return");
						
						$sqlString ="update his_appoints_master set payFlag='0',infoFlag='0',payModeCode='$PayModeCode' where id='".$sqlData['id']."'";
						$sqlData2 = call_user_func(array($_ENV["dbDao"],"update"),$sqlString,"return");  
						if($sqlData2){
							
							$sqlString ="INSERT INTO his_clinicmaster(regId, regDate,orderIdHis, STATUS, patName, patientId, admitDate, hospitalId, deptId, deptName, doctorId, doctorName, doctorTitle, regFee, seqCode, admitAddress, sessionName, admitRange, serviceName, insuRegInfo, returnFlag, startTime, endTime, transactionId)	 VALUES('".$sqlData['id']."', '".$sqlData['orderTime']."','$OrderCode', 'N', '".$sqlData['patientName']."', '".$sqlData['patientId']."', '".$sqlData['regDate']."', '1000', '".$sqlData['deptId']."', '".$sqlData['deptName']."', '".$sqlData['doctorId']."', '".$sqlData['doctorName']."', '".$sqlData['doctorTitle']."', '".$sqlData['fee']."','".$sqlData['seqCode']."', '".$sqlData['address']."', '".$sqlData['timeName']."', '', '', '', 'Y', '".$sqlData['startTime']."', '".$sqlData['endTime']."', '')";
							call_user_func(array($_ENV["dbDao"],"insert"),$sqlString,"return");				
							$res .= "<ResultCode>0</ResultCode><ResultContent>取号成功</ResultContent><SeqCode>".$sqlData['seqCode']."</SeqCode><RegFee>".$sqlData['fee']."元</RegFee><AdmitRange>".$sqlData['regDate']."^".$sqlData['timeName']."</AdmitRange><AdmitAddress>".$sqlData['address']."</AdmitAddress><TransactionId></TransactionId><AdmNo>".$sqlData['id']."</AdmNo>";
						}else{
							$res .= "<ResultCode>1</ResultCode><ResultContent>支付失败</ResultContent>";
						} 
						
					} else{
						$res .= "<ResultCode>1</ResultCode><ResultContent>就诊卡费用不足</ResultContent>";
					}
				}else{*/
					
					$sqlString ="update his_appoints_master set payFlag='0',infoFlag='0',payModeCode='$PayModeCode' where id='".$sqlData['id']."'";
					$sqlData2 = call_user_func(array($_ENV["dbDao"],"update"),$sqlString,"return");  
					if($sqlData2){
						
						$sqlString ="INSERT INTO his_clinicmaster(regId, regDate,orderIdHis, STATUS, patName, patientId, admitDate, hospitalId, deptId, deptName, doctorId, doctorName, doctorTitle, regFee, seqCode, admitAddress, sessionName, admitRange, serviceName, insuRegInfo, returnFlag, startTime, endTime, transactionId)	 VALUES('".$sqlData['id']."', '".$sqlData['orderTime']."','$OrderCode', 'N', '".$sqlData['patientName']."', '".$sqlData['patientId']."', '".$sqlData['regDate']."', '1000', '".$sqlData['deptId']."', '".$sqlData['deptName']."', '".$sqlData['doctorId']."', '".$sqlData['doctorName']."', '".$sqlData['doctorTitle']."', '".$sqlData['fee']."','".$sqlData['seqCode']."', '".$sqlData['address']."', '".$sqlData['timeName']."', '', '', '', 'Y', '".$sqlData['startTime']."', '".$sqlData['endTime']."', '')";
						call_user_func(array($_ENV["dbDao"],"insert"),$sqlString,"return");				
						$res .= "<ResultCode>0</ResultCode><ResultContent>取号成功</ResultContent><SeqCode>".$sqlData['seqCode']."</SeqCode><RegFee>".$sqlData['fee']."元</RegFee><AdmitRange>".$sqlData['regDate']."^".$sqlData['timeName']."</AdmitRange><AdmitAddress>".$sqlData['address']."</AdmitAddress><TransactionId></TransactionId><AdmNo>".$sqlData['id']."</AdmNo>";
					}else{
						$res .= "<ResultCode>1</ResultCode><ResultContent>支付失败</ResultContent>";
					} 
				//}
			} 
		}
		$res .= "</Response>";
		Log::posthis("OPAppArrive:res\r\n".$res); 
		return $res;
		//return "<Reponse><ResultCode>0</ResultCode><ResultContent>取号成功</ResultContent><SeqCode>15</SeqCode><RegFee>  4.00元</RegFee><AdmitRange>2016-09-26^上午</AdmitRange><AdmitAddress>门诊楼三楼D区</AdmitAddress><TransactionId></TransactionId><AdmNo>301</AdmNo></Reponse>";
	} 
	
	//取消预约（1108）
	public function CancelOrder($input) {
		Log::posthis("CancelOrder:req\r\n".$input); 	
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象
		
		$TradeCode ="1108";
		$ExtOrgCode = "南方医务通"; 
		$ExtUserID = "NFYWT";  
		$ClientType = ""; 
		$HospitalId = "";    
		$TransactionId =$req->TransactionId; 
		$OrderCode =$req->OrderCode;
		
		$sqlString ="SELECT * FROM his_appoints_master WHERE cancelFlag='1' and payFlag='1' AND    CONCAT(orderIdHIS,'||',seqCode)='$OrderCode' "; 
		$sqlData = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"entity");
		
		$res = "<Response>";
		if(!$sqlData){
			$res .= "<ResultCode>1</ResultCode><ResultContent>订单取消预约或不存</ResultContent>";
		}else{  
			$sqlString ="update his_appoints_master set cancelFlag='0' where id='".$sqlData['id']."'";
			$sqlData2 = call_user_func(array($_ENV["dbDao"],"update"),$sqlString,"return");  
			if($sqlData2){
				$res .= "<ResultCode>0</ResultCode><ResultContent>取消预约成功</ResultContent>";
			}else{
				$res .= "<ResultCode>1</ResultCode><ResultContent>取消预约失败</ResultContent>";
			} 
		} 
		$res .= "</Response>";
		Log::posthis("CancelOrder:res\r\n".$res); 
		return $res;
		//return "<Reponse><ResultCode>0</ResultCode><ResultContent>取消预约成功</ResultContent><Reponse>";
	} 
	
	//退号(1003)
	public function CancelReg($input) {
		Log::posthis("CancelReg:req\r\n".$input); 	
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象
		
		//==============封装HIS请求============== 
		$TradeCode = "1108";
		$ExtOrgCode = "南方医务通"; 
		$ExtUserID = "NFYWT";  
		$ClientType = ""; 
		$HospitalId = "";
		$TransactionId =  $req->TransactionId;
		$AdmNo = $req->AdmNo;
		$RefundType = "TF"; 
		$BankCode = "";
		$BankDate = "";
		$BankTradeNo = "";
		$ResultCode = "";
		$ResultContent = "";
		$PayCardNo = "";
		$BankAccDate = "";
		$RevTranFlag = "";
		$PatientID = $req->PatientID;
		$PayAmt = "";
		$OrgHISTradeNo = "";
		$OrgPaySeqNo = ""; 
		$PayOrderId = $req->PayOrderId;
		$PayAmt = $req->PayAmt;
		$PayModeCode = $req->PayModeCode;
		
		$sqlString ="SELECT * FROM his_appoints_master WHERE cancelFlag='1' and payFlag='0' AND id='$AdmNo' "; 
		$sqlData = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"entity");
		
		$res = "<Response>";
		if(!$sqlData){
			$res .= "<ResultCode>1</ResultCode><ResultContent>订单已退号或不存</ResultContent>";
		}else{  
			$sqlString ="update his_appoints_master set cancelFlag='0',returnFlag='0' where id='".$sqlData['id']."'";
			$sqlData2 = call_user_func(array($_ENV["dbDao"],"update"),$sqlString,"return");  
			if($sqlData2){
				/*if($PayModeCode=="CPP"){
					$sqlString ="update his_pat_index_master set fee=fee+$PayAmt where patientId='".$PatientID."' and cardId!=''";
					call_user_func(array($_ENV["dbDao"],"update"),$sqlString,"return");  
				}*/
				$sqlString ="update his_clinicmaster set status='Y',returnFlag='N' where regId='".$sqlData['id']."'";
				$sqlData2 = call_user_func(array($_ENV["dbDao"],"update"),$sqlString,"return");  
				
				$res .= "<ResultCode>0</ResultCode><ResultContent>退号成功</ResultContent><TransactionId></TransactionId><ReturnFee>".$sqlData['fee']."</ReturnFee>";
			}else{
				$res .= "<ResultCode>1</ResultCode><ResultContent>退号失败</ResultContent>";
			} 
		} 
		$res .= "</Response>";
		Log::posthis("CancelReg:res\r\n".$res); 
		return $res;
		//return "<Response><ResultCode>0</ResultCode><ResultContent>取消预约成功</ResultContent><TransactionId></TransactionId><ReturnFee>50</ReturnFee></Response>";
	} 
	
	//查询挂号记录(1104)
	public function QueryAdmOPReg($input) {
		Log::posthis("QueryAdmOPReg:req\r\n".$input); 	
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象 
		
		//HQTJ 可以查询全部渠道的挂号记录
		//==============封装HIS请求==============
		$TradeCode = "1104"; 
		$ExtOrgCode ="南方医务通"; 
		$ExtUserID = "HQTJ";   
		$ClientType = ""; 
		$HospitalId = "";    
		$CardType = ""; 
		$PatientCard = "";
		$PatientID = $req->PatientID;
		$StartDate = $req->StartDate;
		$EndDate = $req->EndDate;
		
		$sqlString ="SELECT * FROM his_clinicmaster WHERE  patientId='$PatientID' and admitDate>='$StartDate' and admitDate<='$EndDate' and clinicFlag='0'"; 
		$sqlDataList = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"list");
		
		$res = "<Response>";
		if(!$sqlDataList){
			$res .= "<ResultCode>1</ResultCode><ResultContent>查询不到相关数据</ResultContent>";
		}else{  
			$res .= "<ResultCode>0</ResultCode><ResultContent>查询成功</ResultContent>";
			$res .= "<RecordCount>".count($sqlDataList)."</RecordCount>";
			$res .= "<Orders>";
			foreach($sqlDataList as $key=>$v){
				$res .= "<Order><RegID>".$v['regId']."</RegID><RegDate>".$v['regDate']."</RegDate><Status>".($v['status']=='N' ? '正常':'退号')."</Status><PatName>".$v['patName']."</PatName><PatientID>".$v['patientId']."</PatientID><AdmitDate>".$v['admitDate']."</AdmitDate><HospitalName>南方医科大学南方医院</HospitalName><DepartmentCode>".$v['deptId']."</DepartmentCode><Department>".$v['deptName']."</Department><DoctorCode>".$v['doctorId']."</DoctorCode><Doctor>".$v['doctorName']."</Doctor><DoctorTitle>".$v['doctorTitle']."</DoctorTitle><RegFee>".$v['fee']."元</RegFee><SeqCode>".$v['seqCode']."</SeqCode><AdmitAddress></AdmitAddress><SessionName>".$v['timeName']."</SessionName><AdmitRange>".$v['startTime']."-".$v['endTime']."</AdmitRange><ServiceName></ServiceName><InsuRegInfo></InsuRegInfo><ReturnFlag>".$v['returnFlag']."</ReturnFlag><StartTime>".$v['startTime']."</StartTime><EndTime>".$v['endTime']."</EndTime><TransactionId></TransactionId></Order>";  
			}
			$res .= "</Orders>"; 
		} 
		$res .= "</Response>";
		Log::posthis("QueryAdmOPReg:res\r\n".$res); 
		return $res;
		//return "<Response><ResultCode>0</ResultCode><ResultContent>查询成功</ResultContent><RecordCount>1</RecordCount><Orders><Order><RegID>65149</RegID><RegDate>2017-01-06</RegDate><Status>正常</Status><PatName>王丽娜</PatName><PatientID>35031336</PatientID><AdmitDate>2017-01-10</AdmitDate><HospitalName>南方医科大学南方医院</HospitalName><DepartmentCode>309</DepartmentCode><Department>产科门诊</Department><DoctorCode>1330</DoctorCode><Doctor>钟梅</Doctor><DoctorTitle>正高号</DoctorTitle><RegFee>  9.00元</RegFee><SeqCode>22</SeqCode><AdmitAddress></AdmitAddress><SessionName>上午</SessionName><AdmitRange>09:30-10:00</AdmitRange><ServiceName></ServiceName><InsuRegInfo></InsuRegInfo><ReturnFlag>N</ReturnFlag><StartTime></StartTime><EndTime></EndTime><TransactionId></TransactionId></Order></Orders></Response>";
	}
	
	//查询预约记录（1005）
	public function QueryOrder($input) {
		Log::posthis("QueryOrder:req\r\n".$input); 	
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象 
		
		//HQTJ 可以查询全部渠道的挂号记录
		//==============封装HIS请求==============
		$TradeCode = "1104"; 
		$ExtOrgCode ="南方医务通"; 
		$ExtUserID = "HQTJ";   
		$ClientType = ""; 
		$HospitalId = "";    
		$CardType = ""; 
		$PatientCard = "";
		$PatientID = $req->PatientID;
		$StartDate = $req->StartDate;
		$EndDate = $req->EndDate;
		
		$sqlString ="SELECT * FROM his_appoints_master WHERE  patientId='$PatientID' and regDate>='$StartDate' and regDate<='$EndDate' "; 
		$sqlDataList = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"list");
		
		$res = "<Response>";
		if(!$sqlDataList){
			$res .= "<ResultCode>1</ResultCode><ResultContent>查询不到相关数据</ResultContent>";
		}else{  
			$res .= "<ResultCode>0</ResultCode><ResultContent>查询成功</ResultContent>";
			$res .= "<RecordCount>".count($sqlDataList)."</RecordCount>";
			$res .= "<Orders>";
			foreach($sqlDataList as $key=>$v){
				$res .= "<Order><OrderCode>".$v['orderIdHIS']."||".$v['seqCode']."</OrderCode><OrderApptDate>".$v['orderTime']."</OrderApptDate><OrderStatus>".($v['cancelFlag']=='1' ? '正常':'退号')."</OrderStatus><OrderApptUser>".$v['patientName']."</OrderApptUser><PatientNo>".$v['patientId']."</PatientNo><AdmitDate>".$v['regDate']."</AdmitDate><Department>".$v['deptName']."</Department><Doctor>".$v['doctorName']."</Doctor><DoctorTitle>".$v['doctorTitle']."</DoctorTitle><RegFee>".$v['fee']."</RegFee><SeqCode>".$v['seqCode']."</SeqCode><AdmitAddress>".$v['address']."</AdmitAddress><SessionName>".$v['timeName']."</SessionName><OrderContent></OrderContent><AdmitRange>".$v['startTime']."-".$v['endTime']."</AdmitRange><TelePhoneNo></TelePhoneNo><MobileNo></MobileNo><AllowRefundFlag>".($v['cancelFlag']=='1' ? 'Y':'N')."</AllowRefundFlag><PayFlag>".($v['payFlag']=='1' ? 'P':'TB')."</PayFlag><HospitalName>南方医科大学南方医院</HospitalName><ServiceName></ServiceName><TimeRange>".$v['startTime']."-".$v['endTime']."</TimeRange></Order>";  
			}
			$res .= "</Orders>"; 
		} 
		$res .= "</Response>";
		Log::posthis("QueryOrder:res\r\n".$res); 
		return $res; 
	}
	
	//通过就诊号获取导诊单信息（90020）—南方医院版
	public function GetDirectListByAdm($input) {
		return "<Response><resultCode>0</resultCode><errorMsg>成功</errorMsg><invoiceList><invoice><head><patientID>ZA00001</patientID><patientName>zhangd</patientName><sex>男</sex><age>23</age><admReason>自费</admReason><cost>1000</cost><doctorName>doctorLi</doctorName><diagnose>感冒</diagnose><guser>张溜</guser><payTime>2016-09-09 09:09:09</payTime></head><body><laboratory><specimenList><specimen><specimenDesc>血清</specimenDesc><guide>请您到门诊二楼采血室采血</guide><prompt>不用禁食</prompt></specimen><specimen><specimenDesc>血清333</specimenDesc><guide>请您到门诊二楼采血室采血3333333</guide><prompt>不用禁食3333333</prompt></specimen></specimenList></laboratory><examination><examList><examItem><itemName>肝胆，脾彩超检查----111</itemName><amt>200----111</amt><date>2016-09-09----111</date><ordDept>外科门诊----111</ordDept><guide>请您到外科门诊B超室做检查----111</guide><depLocPosition>门诊四楼B超室----111</depLocPosition><bookedNote>去除膏药等体外异物。----111</bookedNote></examItem><examItem><itemName>肝胆，脾彩超检查----222</itemName><amt>200----222</amt><date>2016-09-09----222</date><ordDept>外科门诊----222</ordDept><guide>请您到外科门诊B超室做检查----222</guide><depLocPosition>门诊四楼B超室----222</depLocPosition><bookedNote>去除膏药等体外异物。----222</bookedNote></examItem><examItem><itemName>肝胆，脾彩超检查----333</itemName><amt>200----333</amt><date>2016-09-09----333</date><ordDept>外科门诊----333</ordDept><guide>请您到外科门诊B超室做检查----333</guide><depLocPosition>门诊四楼B超室----333</depLocPosition><bookedNote>去除膏药等体外异物。----333</bookedNote></examItem></examList></examination><treatment><treatDeptList><treatDept><deptName>皮肤科门诊--11</deptName><guide>请您到门诊四楼皮肤科门诊区 皮肤科门诊---11</guide><treatItemList><treatItem><itemName>光动力治疗---333</itemName><qty>4</qty><uom>次</uom><amt>23</amt></treatItem></treatItemList></treatDept><treatDept><deptName>皮肤科门诊--22</deptName><guide>请您到门诊四楼皮肤科门诊区 皮肤科门诊---22</guide><treatItemList><treatItem><itemName>光动力治疗---333</itemName><qty>4</qty><uom>次</uom><amt>23</amt></treatItem></treatItemList></treatDept></treatDeptList></treatment><drug><baseDrug><baseDrugDeptList><baseDrugDept><deptName>磁共振室--1</deptName><guide>请您到第一医技楼磁共振室--1</guide><baseDrugItemList><baseDrugItem><itemName>注射液--11</itemName><qty>2</qty><uom>支</uom><amt>11</amt></baseDrugItem><baseDrugItem><itemName>注射液--22</itemName><qty>3</qty><uom>支</uom><amt>12</amt></baseDrugItem><baseDrugItem><itemName>注射液--33</itemName><qty>4</qty><uom>支</uom><amt>13</amt></baseDrugItem></baseDrugItemList></baseDrugDept><baseDrugDept><deptName>磁共振室--2</deptName><guide>请您到第一医技楼磁共振室--2</guide><baseDrugItemList><baseDrugItem><itemName>注射液--11</itemName><qty>2</qty><uom>支</uom><amt>11</amt></baseDrugItem><baseDrugItem><itemName>注射液--22</itemName><qty>3</qty><uom>支</uom><amt>12</amt></baseDrugItem><baseDrugItem><itemName>注射液--33</itemName><qty>4</qty><uom>支</uom><amt>13</amt></baseDrugItem></baseDrugItemList></baseDrugDept><baseDrugDept><deptName>磁共振室--3</deptName><guide>请您到第一医技楼磁共振室--3</guide><baseDrugItemList><baseDrugItem><itemName>注射液--11</itemName><qty>2</qty><uom>支</uom><amt>11</amt></baseDrugItem><baseDrugItem><itemName>注射液--22</itemName><qty>3</qty><uom>支</uom><amt>12</amt></baseDrugItem><baseDrugItem><itemName>注射液--33</itemName><qty>4</qty><uom>支</uom><amt>13</amt></baseDrugItem></baseDrugItemList></baseDrugDept><baseDrugDept><deptName>磁共振室--4</deptName><guide>请您到第一医技楼磁共振室--4</guide><baseDrugItemList><baseDrugItem><itemName>注射液--11</itemName><qty>2</qty><uom>支</uom><amt>11</amt></baseDrugItem><baseDrugItem><itemName>注射液--22</itemName><qty>3</qty><uom>支</uom><amt>12</amt></baseDrugItem><baseDrugItem><itemName>注射液--33</itemName><qty>4</qty><uom>支</uom><amt>13</amt></baseDrugItem></baseDrugItemList></baseDrugDept></baseDrugDeptList></baseDrug><druglist><drugItem><drugType>西药、中成药</drugType><guide>请您到门诊一楼门诊药房</guide><window>5号窗口</window><prompt>请先到自助机报到后，再等待取药</prompt></drugItem><drugItem><drugType>中草药</drugType><guide>请您到门诊一楼草药房</guide><window>7号窗口</window><prompt>请先到自助机报到后，再等待取药</prompt></drugItem></druglist></drug></body></invoice><invoice><head><patientID>ZA00001</patientID><patientName>zhangd</patientName><sex>男</sex><age>23</age><admReason>自费</admReason><cost>1000</cost><doctorName>doctorLi</doctorName><diagnose>感冒</diagnose><guser>张溜</guser><payTime>2016-09-09 09:09:09</payTime></head><body><laboratory><specimenList><specimen><specimenDesc>血清</specimenDesc><guide>请您到门诊二楼采血室采血</guide><prompt>不用禁食</prompt></specimen><specimen><specimenDesc>血清333</specimenDesc><guide>请您到门诊二楼采血室采血3333333</guide><prompt>不用禁食3333333</prompt></specimen></specimenList></laboratory><examination><examList><examItem><itemName>肝胆，脾彩超检查----111</itemName><amt>200----111</amt><date>2016-09-09----111</date><ordDept>外科门诊----111</ordDept><guide>请您到外科门诊B超室做检查----111</guide><depLocPosition>门诊四楼B超室----111</depLocPosition><bookedNote>去除膏药等体外异物。----111</bookedNote></examItem><examItem><itemName>肝胆，脾彩超检查----222</itemName><amt>200----222</amt><date>2016-09-09----222</date><ordDept>外科门诊----222</ordDept><guide>请您到外科门诊B超室做检查----222</guide><depLocPosition>门诊四楼B超室----222</depLocPosition><bookedNote>去除膏药等体外异物。----222</bookedNote></examItem><examItem><itemName>肝胆，脾彩超检查----333</itemName><amt>200----333</amt><date>2016-09-09----333</date><ordDept>外科门诊----333</ordDept><guide>请您到外科门诊B超室做检查----333</guide><depLocPosition>门诊四楼B超室----333</depLocPosition><bookedNote>去除膏药等体外异物。----333</bookedNote></examItem></examList></examination><treatment><treatDeptList><treatDept><deptName>皮肤科门诊--11</deptName><guide>请您到门诊四楼皮肤科门诊区 皮肤科门诊---11</guide><treatItemList><treatItem><itemName>光动力治疗---333</itemName><qty>4</qty><uom>次</uom><amt>23</amt></treatItem></treatItemList></treatDept><treatDept><deptName>皮肤科门诊--22</deptName><guide>请您到门诊四楼皮肤科门诊区 皮肤科门诊---22</guide><treatItemList><treatItem><itemName>光动力治疗---333</itemName><qty>4</qty><uom>次</uom><amt>23</amt></treatItem></treatItemList></treatDept></treatDeptList></treatment><drug><baseDrug><baseDrugDeptList><baseDrugDept><deptName>磁共振室--1</deptName><guide>请您到第一医技楼磁共振室--1</guide><baseDrugItemList><baseDrugItem><itemName>注射液--11</itemName><qty>2</qty><uom>支</uom><amt>11</amt></baseDrugItem><baseDrugItem><itemName>注射液--22</itemName><qty>3</qty><uom>支</uom><amt>12</amt></baseDrugItem><baseDrugItem><itemName>注射液--33</itemName><qty>4</qty><uom>支</uom><amt>13</amt></baseDrugItem></baseDrugItemList></baseDrugDept><baseDrugDept><deptName>磁共振室--2</deptName><guide>请您到第一医技楼磁共振室--2</guide><baseDrugItemList><baseDrugItem><itemName>注射液--11</itemName><qty>2</qty><uom>支</uom><amt>11</amt></baseDrugItem><baseDrugItem><itemName>注射液--22</itemName><qty>3</qty><uom>支</uom><amt>12</amt></baseDrugItem><baseDrugItem><itemName>注射液--33</itemName><qty>4</qty><uom>支</uom><amt>13</amt></baseDrugItem></baseDrugItemList></baseDrugDept><baseDrugDept><deptName>磁共振室--3</deptName><guide>请您到第一医技楼磁共振室--3</guide><baseDrugItemList><baseDrugItem><itemName>注射液--11</itemName><qty>2</qty><uom>支</uom><amt>11</amt></baseDrugItem><baseDrugItem><itemName>注射液--22</itemName><qty>3</qty><uom>支</uom><amt>12</amt></baseDrugItem><baseDrugItem><itemName>注射液--33</itemName><qty>4</qty><uom>支</uom><amt>13</amt></baseDrugItem></baseDrugItemList></baseDrugDept><baseDrugDept><deptName>磁共振室--4</deptName><guide>请您到第一医技楼磁共振室--4</guide><baseDrugItemList><baseDrugItem><itemName>注射液--11</itemName><qty>2</qty><uom>支</uom><amt>11</amt></baseDrugItem><baseDrugItem><itemName>注射液--22</itemName><qty>3</qty><uom>支</uom><amt>12</amt></baseDrugItem><baseDrugItem><itemName>注射液--33</itemName><qty>4</qty><uom>支</uom><amt>13</amt></baseDrugItem></baseDrugItemList></baseDrugDept></baseDrugDeptList></baseDrug><druglist><drugItem><drugType>西药、中成药</drugType><guide>请您到门诊一楼门诊药房</guide><window>5号窗口</window><prompt>请先到自助机报到后，再等待取药</prompt></drugItem><drugItem><drugType>中草药</drugType><guide>请您到门诊一楼草药房</guide><window>7号窗口</window><prompt>请先到自助机报到后，再等待取药</prompt></drugItem></druglist></drug></body></invoice></invoiceList></Response>";
	} 
	
	//通过就诊号获取门诊病历
	public function GetOPEMRInfo($input) {
		return '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="/hip/emrviewdoctor/resources/南方医院门急诊病历.xslt"?><clinicalDocument xmlns="urn:hl7-org:v3" xmlns:mif="urn:hl7-org:v3/mif" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><documentHeader><realmCode></realmCode><typeId>Single</typeId><template>1104</template><id>286117||1</id><title>门诊病历</title><effectiveTime>20170210153030</effectiveTime><confidentiality code="级别代码"></confidentiality><versionNumber>8</versionNumber><author id="819">吴晓亮</author><custodian>南方医科大学南方医院</custodian><patient><medicareNo>000032669</medicareNo><admvisitNo>252005</admvisitNo><medRecordNo></medRecordNo><healthCardNo>362422199303016224</healthCardNo><certificate><name code=""></name><value></value></certificate><addr desc="现住址"><text>14^360000^江西省!164^360800^吉安市!1262^360803^青原区!江西省吉安市青原区富滩镇宋溪大道3号</text><houseNumber></houseNumber><streetName></streetName><township></township><county></county><city></city><state>江西省</state><postalCode></postalCode></addr><name>李兰泓</name><telecom>18889629429</telecom><administrativeGender code="2">女</administrativeGender><maritalStatus code=""></maritalStatus><ethnicGroup code="01">汉族</ethnicGroup><age year="23" month="" day="" hour="">23岁</age></patient><participant><code code=""></code><addr desc="联系人地址"><text>0</text><houseNumber></houseNumber><streetName></streetName><township></township><county></county><city></city><state></state><postalCode></postalCode></addr><telecom></telecom><name></name></participant></documentHeader><structuredBody><E0004 desc="姓名"></E0004><E0005 desc="性别代码"></E0005><E0006 desc="性别描述"></E0006><E0007 desc="出生日期"></E0007><E0008 desc="年龄"></E0008><E0023 desc="民族代码"></E0023><E0024 desc="民族描述"></E0024><E0028 desc="职业类别代码"></E0028><E0029 desc="职业类别描述"></E0029><E0030 desc="婚姻代码"></E0030><E0031 desc="婚姻描述"></E0031><E0032 desc="患者住址（全）"></E0032><E0033 desc="现住址-门牌号码"></E0033><E0034 desc="现住地址-村（街、路、弄等）"></E0034><E0035 desc="现住址-乡（镇、街道办事处）"></E0035><E0036 desc="现住址-县（区）"></E0036><E0037 desc="现住址-市（地区）"></E0037><E0038 desc="现住址-省（自治区、直辖市）"></E0038><E0039 desc="现住址邮政编码"></E0039><E0050 desc="工作单位名称"></E0050><E0000 desc="门诊号"></E0000><E0152 desc="门诊类型代码"></E0152><E0153 desc="门诊类型名称"></E0153><E0156 desc="科室id">253</E0156><E0077 desc="科室">脊柱骨科门诊</E0077><E0158 desc="医生签名日期"></E0158><E0159 desc="医生签名时间"></E0159><E0160 desc="签名医生代码"></E0160><E0161 desc="签名医生姓名"></E0161><E0183 desc="卡号"></E0183><E0081 desc="入院时情况"></E0081><E0184 desc="入院情况代码"></E0184><E0185 desc="入院途径代码"></E0185><E0186 desc="入院途径名称"></E0186><E0201 desc="病人ID">6873553</E0201><E0150 desc="费别代码">1</E0150><E0151 desc="费别名称">自费</E0151><E0082 desc="患者入院日期">2017-02-10 14:43:18</E0082><E0220 desc="门诊次数">1</E0220><section code="S0001" desc="主诉"><text>腰痛半年余，间断性，无双下肢活动感觉异常；</text></section><section code="S0002" desc="现病史"><text></text><E01 desc="临床症状">腰痛半年余，间断性，无双下肢活动感觉异常；</E01><E02 desc="体征">下腰椎轻度压痛</E02><E03 desc="特殊病种"></E03></section><section code="S0003" desc="既往史"><text></text></section><section code="S0019" desc="食物或药物过敏史"><text></text></section><section code="S0020" desc="体格检查"><text>下腰椎轻度压痛</text><E01 desc="体温（℃）"></E01><E02 desc="脉率（次/min）"></E02><E03 desc="呼吸频率（次/min）"></E03><E04 desc="收缩压（mmHg）"></E04><E05 desc="舒张压(mmHg)"></E05><E06 desc="身高（cm）"></E06><E07 desc="体重（kg）"></E07><E08 desc="BP（mmHg）"></E08></section><section code="S0149" desc="门诊诊断"><text>1.腰痛,</text><section code="S0048" desc="诊断"><text>1.腰痛()</text><E01 desc="诊断名称">腰痛</E01><E02 desc="诊断代码">M54.502</E02><E07 desc="医生填写的诊断"></E07><E09 desc="诊断序号">1</E09></section></section><section code="S0072" desc="意见"><text>门诊随访。</text></section><section code="S0073" desc="处置"><text></text></section><section code="S0158" desc="门急诊医嘱"><text>处置:检查 腰椎正侧位片</text><section code="S0067" desc="医嘱"><text>腰椎正侧位片</text><E01 desc="医嘱开始日期">64324</E01><E02 desc="医嘱开始时间">55920</E02><E03 desc="医嘱代码">5410||1</E03><E04 desc="医嘱名称">腰椎正侧位片</E04><E06 desc="医嘱频次名称"></E06><E07 desc="医嘱单次剂量"></E07><E09 desc="医嘱单次剂量单位描述"></E09><E11 desc="医嘱用法描述"></E11><E12 desc="医嘱开立医师代码">826</E12><E13 desc="医嘱开立医师姓名">吴晓亮</E13><E14 desc="医嘱审核日期"></E14><E15 desc="医嘱审核时间"></E15><E25 desc="药品规格"></E25><E26 desc="药品总量"></E26><E28 desc="药品总量单位描述"></E28><E29 desc="医嘱大类代码"></E29><E30 desc="医嘱大类描述">检查</E30><E31 desc="处方号"></E31><E32 desc="中草药服用方式"></E32><E33 desc="本处方内的排序"></E33><E34 desc="本处方医嘱总量"></E34></section></section></structuredBody></clinicalDocument>';
	} 
	
	//通过就诊号获取处方
	public function GetPresc($input) {
		return "<Response><ResultCode>0</ResultCode><ErrorMsg></ErrorMsg><TradeCode></TradeCode><PatientID>000032669</PatientID><PatName>李兰泓</PatName><PatSex>女</PatSex><PatAge>23岁</PatAge><PatType></PatType><PatPhone>18889629429</PatPhone><PatAdress></PatAdress><Diagnose>1.腰痛</Diagnose><PatInsNo></PatInsNo><EmpStatusNo></EmpStatusNo><Prescs><Presc><PrescNo>O17021004071</PrescNo><PrescType>普通</PrescType><OrderSttDate>2017-02-20 14:50</OrderSttDate><PrescLoc>脊柱骨科门诊</PrescLoc><RepLoc>门诊药房</RepLoc><PrescDoc>吴晓亮</PrescDoc><Presclist><Presclist><OrderName>洛索洛芬钠片(乐松)[60mg*20片]</OrderName><OrderDoseQty>60mg</OrderDoseQty><Orserfreq>3/日</Orserfreq><PackQty>2</PackQty><OrderPackUOM>盒(20)</OrderPackUOM><OrderPhdur>7天</OrderPhdur><OrderPrice>55.6000</OrderPrice><OrderInst>口服</OrderInst></Presclist><Presclist><OrderName>珍宝丸[0.1g*180丸]</OrderName><OrderDoseQty>1g</OrderDoseQty><Orserfreq>3/日</Orserfreq><PackQty>2</PackQty><OrderPackUOM>盒(180)</OrderPackUOM><OrderPhdur>7天</OrderPhdur><OrderPrice>103.6600</OrderPrice><OrderInst>口服</OrderInst></Presclist><Presclist><OrderName>复方南星止痛膏[1*6贴]</OrderName><OrderDoseQty>1贴</OrderDoseQty><Orserfreq>1/日</Orserfreq><PackQty>2</PackQty><OrderPackUOM>盒(6)</OrderPackUOM><OrderPhdur>7天</OrderPhdur><OrderPrice>88.1600</OrderPrice><OrderInst>外用</OrderInst></Presclist></Presclist></Presc></Prescs></Response>";
	} 
	
	
	//排队候诊(4001)
	public function WaitingQueue($input) {
		return "<Response><TradeCode>0</TradeCode><TransactionId>123123</TransactionId><ResultCode>0</ResultCode><ResultContent>成功</ResultContent><PatName>王东</PatName><AdmLoc>门诊楼5楼一区</AdmLoc><AdmDoc>李可</AdmDoc><WaitingNumber>20</WaitingNumber></Response>";
	}
	
	//检验报告列表查询
	public function LISgetReport($input) {
		Log::posthis("LISgetReport:req\r\n".$input); 	
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象 
		
		//==============封装HIS请求==============
		$ClinicSeq = $req->ClinicSeq;  
		 
		$sqlString ="SELECT * FROM his_clinicmaster WHERE regId='$ClinicSeq'"; 
		$sqlData = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"entity");
		
		$res = "<Response>";
		if($sqlData){
			$sqlString =" SELECT * FROM his_labmaster where patientId='".$sqlData['patientId']."' and inspectionDate BETWEEN'".$sqlData['admitDate']."' and '".$sqlData['admitDate']." 23:59:59' "; 
			$sqlDataList = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"list");
			if(!$sqlDataList){
				$res .= "<ResultCode>1</ResultCode><ResultContent>查询不到相关数据</ResultContent>";
			}else{  
				$res .= "<ResultCode>0</ResultCode><ResultContent>查询成功</ResultContent>";
				$res .= "<RecordCount>".count($sqlDataList)."</RecordCount>";
				$res .= "<AdmList>";
				foreach($sqlDataList as $key=>$v){
					$res .= "<Report><InspectionId>".$v['inspectionId']."</InspectionId><InspectionName>".$v['inspectionName']."</InspectionName><InspectionDate>".$v['inspectionDate']."</InspectionDate><Status>".$v['status']."</Status><PatientName>".$v['patientName']."</PatientName><PatientAge>".$v['patientAge']."</PatientAge><Gender>".$v['gender']."</Gender><DeptName>".$v['deptName']."</DeptName><ClinicalDiagnosis>".$v['clinicalDiagnosis']."</ClinicalDiagnosis><ReportDoctorName>".$v['reportDoctorName']."</ReportDoctorName><CheckDoctorName></CheckDoctorName><ClinicSeq>".$v['clinicSeq']."</ClinicSeq><InpatientId>".$v['inpatientId']."</InpatientId></Report>";
				}
				$res .= "</AdmList>"; 
			} 
		}else{
			$res .= "<ResultCode>1</ResultCode><ResultContent>查询失败，就诊号不存在</ResultContent>";	
		}
		
		$res .= "</Response>";
		Log::posthis("LISgetReport:res\r\n".$res); 
		return $res; 
		//return "<Response><ResultCode>0</ResultCode><ResultContent>成功</ResultContent><AdmList><Report><InspectionId>800000003087||A083||1</InspectionId><InspectionName>血细胞分析（五分类）</InspectionName><InspectionDate>2016-12-27 00:17:50</InspectionDate><Status>1</Status><PatientName>蔡丽焕</PatientName><PatientAge>24岁</PatientAge><Gender>女</Gender><DeptName>中医内科门诊</DeptName><ClinicalDiagnosis>腰痹病</ClinicalDiagnosis><ReportDoctorName>demo</ReportDoctorName><CheckDoctorName>demo</CheckDoctorName><ClinicSeq>12438</ClinicSeq><InpatientId></InpatientId></Report><Report><InspectionId></InspectionId><InspectionName></InspectionName><InspectionDate></InspectionDate><Status></Status><PatientName></PatientName><PatientAge></PatientAge><Gender></Gender><DeptName></DeptName><ClinicalDiagnosis></ClinicalDiagnosis><ReportDoctorName></ReportDoctorName><CheckDoctorName></CheckDoctorName><ClinicSeq>12438</ClinicSeq><InpatientId></InpatientId></Report></AdmList></Response>";
	}
	
	
	//检验报告列表查询  
	public function LISgetReportItem($input) {
		Log::posthis("LISgetReportItem:req\r\n".$input); 	
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象 
		
		//==============封装HIS请求==============
		$InspectionId = $req->InspectionId;  
		
		
		$sqlString ="SELECT * FROM his_labmaster WHERE inspectionId='$InspectionId'"; 
		$sqlData = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"entity");
		
		$res = "<Response>";
		if($sqlData){
			//$sqlString =" SELECT * FROM his_labreport where itemId='$InspectionId' and labMasterId='".$sqlData['id']."'"; 
			$sqlString =" SELECT * FROM his_labreport where itemId='$InspectionId'"; 
			$sqlDataList = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"list");
			if(!$sqlDataList){
				$res .= "<ResultCode>1</ResultCode><ResultContent>查询不到相关数据</ResultContent>";
			}else{  
				$res .= "<ResultCode>0</ResultCode><ResultContent>查询成功</ResultContent>";
				$res .= "<RecordCount>".count($sqlDataList)."</RecordCount>";				
				foreach($sqlDataList as $key=>$v){
					$res .= "<Item><ItemId>".$v['itemId']."</ItemId><ItemName>".$v['itemName']."</ItemName><OrderNo>".$v['orderNo']."</OrderNo><TestResult>".$v['result']."</TestResult><Unit>".$v['units']."</Unit><ItemRef>".$v['lowerLimit']."~".$v['upperLimit']."</ItemRef><TestDate>".$v['reportTime']."</TestDate><ResultFlag>".$v['abnormal']."</ResultFlag><TestEngName>".$v['testEngName']."</TestEngName><SpecimType>".$v['specimType']."</SpecimType></Item>";
				}
			} 
		}else{
			$res .= "<ResultCode>1</ResultCode><ResultContent>查询失败，检验报告单id号不存在</ResultContent>";	
		}
		
		$res .= "</Response>";
		Log::posthis("LISgetReportItem:res\r\n".$res); 
		return $res; 
		//return "<Response><ResultCode>0</ResultCode><ResultContent>成功</ResultContent><AdmList><Report><InspectionId>800000003087||A083||1</InspectionId><InspectionName>血细胞分析（五分类）</InspectionName><InspectionDate>2016-12-27 00:17:50</InspectionDate><Status>1</Status><PatientName>蔡丽焕</PatientName><PatientAge>24岁</PatientAge><Gender>女</Gender><DeptName>中医内科门诊</DeptName><ClinicalDiagnosis>腰痹病</ClinicalDiagnosis><ReportDoctorName>demo</ReportDoctorName><CheckDoctorName>demo</CheckDoctorName><ClinicSeq>12438</ClinicSeq><InpatientId></InpatientId></Report><Report><InspectionId></InspectionId><InspectionName></InspectionName><InspectionDate></InspectionDate><Status></Status><PatientName></PatientName><PatientAge></PatientAge><Gender></Gender><DeptName></DeptName><ClinicalDiagnosis></ClinicalDiagnosis><ReportDoctorName></ReportDoctorName><CheckDoctorName></CheckDoctorName><ClinicSeq>12438</ClinicSeq><InpatientId></InpatientId></Report></AdmList></Response>";
	}
	
	
	//检查报告列表查询接口 
	public function PACSgetReport($input) {
		Log::posthis("PACSgetReport:req\r\n".$input); 	
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象 
		
		//==============封装HIS请求==============
		$ClinicSeq = $req->ClinicSeq;  
		
		
		$sqlString ="SELECT * FROM his_clinicmaster WHERE regId='$ClinicSeq'"; 
		$sqlData = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"entity");
		
		$res = "<Response>";
		if($sqlData){
			$sqlString =" SELECT * FROM his_exammaster where patientId='".$sqlData['patientId']."' and examDate BETWEEN '".$sqlData['admitDate']."' and '".$sqlData['admitDate']." 23:59:59'"; 
			$sqlDataList = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"list");
			if(!$sqlDataList){
				$res .= "<ResultCode>1</ResultCode><ResultContent>查询不到相关数据</ResultContent>";
			}else{  
				$res .= "<ResultCode>0</ResultCode><ResultContent>查询成功</ResultContent>";
				$res .= "<RecordCount>".count($sqlDataList)."</RecordCount>";
				$res .= "<AdmList>";				
				foreach($sqlDataList as $key=>$v){
					$res .= "<Report><ReportId>".$v['examId']."</ReportId><ReportTitle>".$v['examName']."</ReportTitle><ReportDate>".$v['examDate']."</ReportDate><Status>".$v['status']."</Status><PatientName>".$v['patientName']."</PatientName><PatientAge>".$v['patientAge']."</PatientAge><Gender>".$v['gender']."</Gender><ClinicalDiagnosis>".$v['clinicalDiagnosis']."</ClinicalDiagnosis><ClinicSeq>".$v['clinicSeq']."</ClinicSeq><InpatientId>".$v['inpatientId']."</InpatientId></Report>";
				}
				$res .= "</AdmList>";
			} 
		}else{
			$res .= "<ResultCode>1</ResultCode><ResultContent>查询失败，就诊号不存在</ResultContent>";	
		}
		
		$res .= "</Response>";
		Log::posthis("PACSgetReport:res\r\n".$res); 
		return $res; 
		//return "<Response><ResultCode>0</ResultCode><ResultContent>成功</ResultContent><Report><ReportId>13460</ReportId><ReportTitle>腰椎正侧位片</ReportTitle><ReportDate>2016-09-22</ReportDate><Status>1</Status><PatientName>何春</PatientName><PatientAge>1978-04-02</PatientAge><Gender>2</Gender><ClinicalDiagnosis></ClinicalDiagnosis><ClinicSeq>407</ClinicSeq><InpatientId></InpatientId></Report></Response>";
	}
	
	
	//检查报告明细内容查询接口 
	public function PACSgetReportDetail($input) {
		Log::posthis("PACSgetReportDetail:req\r\n".$input); 	
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象 
		
		//==============封装HIS请求==============
		$ReportId = $req->ReportId;  
		
		
		$sqlString ="SELECT * FROM his_exammaster WHERE examId='$ReportId'"; 
		$sqlData = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"entity");
		
		$res = "<Response>";
		if($sqlData){
			//$sqlString =" SELECT * FROM his_examreport where examId='$ReportId' and examMasterId='".$sqlData['id']."'"; 
			$sqlString =" SELECT * FROM his_examreport where examId='$ReportId'"; 
			$sqlDataList = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"list");
			if(!$sqlDataList){
				$res .= "<ResultCode>1</ResultCode><ResultContent>查询不到相关数据</ResultContent>";
			}else{  
				$res .= "<ResultCode>0</ResultCode><ResultContent>查询成功</ResultContent>";
				foreach($sqlDataList as $key=>$v){
					$res .= "<Item><DeptName>".$v['deptName']."</DeptName><ReportDoctorName>".$v['doctorName']."</ReportDoctorName><CheckParts>".$v['checkPart']."</CheckParts><Examination>".$v['checkSituation']."</Examination><Diagnosis>".$v['diagnosis']."</Diagnosis><CheckDoctorName>".$v['doctorName']."</CheckDoctorName><ExaminationDate>".$v['reportTime']."</ExaminationDate><VerifyDocName>".$v['verifyDocName']."</VerifyDocName><VerifyDate>".$v['verifyDate']."</VerifyDate><AppDocName>".$v['appDocName']."</AppDocName></Item>";
				}
			} 
		}else{
			$res .= "<ResultCode>1</ResultCode><ResultContent>查询失败，检查报告单id号不存在</ResultContent>";	
		}
		
		$res .= "</Response>";
		Log::posthis("PACSgetReportDetail:res\r\n".$res); 
		return $res; 		
		//return "<Response><ResultCode>0</ResultCode><ResultContent>成功</ResultContent><Item><DeptName>惠侨脊柱骨科门诊</DeptName><ReportDoctorName></ReportDoctorName><CheckParts>()</CheckParts><Examination></Examination><Diagnosis>腰椎生理曲度变直；椎列连续；部分腰椎椎体缘见唇状骨质增生影；第五腰椎横突肥大，左侧与骶骨形成假关节；其余椎体、附件及椎间隙未见异常；软组织未见异常；其它：未见异常。</Diagnosis><CheckDoctorName></CheckDoctorName><ExaminationDate>2016-09-22 16:48:31</ExaminationDate><VerifyDocName></VerifyDocName><VerifyDate>2016-09-22 16:48:31</VerifyDate><AppDocName>张耀旋</AppDocName></Item></Response>";
	}
	
	
	
	//查询就诊信息(4002）
	public function AdmInfo($input) {
		Log::posthis("AdmInfo:req\r\n".$input); 	
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象 
		
		//==============封装HIS请求============== 
		$AdmNo = $req->AdmNo; 
		
		$sqlString ="SELECT * FROM his_clinicmaster WHERE regId='$AdmNo' and clinicFlag='0'";
		$sqlData = call_user_func(array($_ENV["dbDao"],"select"),$sqlString,"entity"); 
		
		$res = "<Response>";
		if(!$sqlData){
			$res .= "<ResultCode>1</ResultCode><ResultContent>查询不到相关数据</ResultContent>";
		}else{  
			$res .= "<ResultCode>0</ResultCode><ResultContent>查询成功</ResultContent>"; 
			$res .= "<PatientID>".$sqlData['patientId']."</PatientID><AdmLoc>".$sqlData['admitAddress']."</AdmLoc><AdmDoc>".$sqlData['doctorName']."</AdmDoc><AdmitDate>".$sqlData['admitDate']."</AdmitDate><Diagnosis>".$sqlData['diagnosis']."</Diagnosis>";  
			
		} 
		$res .= "</Response>";
		Log::posthis("AdmInfo:res\r\n".$res); 
		return $res;  
		//return "<Response><ResultCode>0</ResultCode><ResultContent>成功</ResultContent><PatientID>123123</PatientID><AdmLoc>门诊楼5楼一区</AdmLoc><AdmDoc>李可</AdmDoc><AdmitDate>".$this->datenow()."</AdmitDate><Diagnosis>感冒</Diagnosis></Response>";
	}
	
	
	
	//==========================================================以下方法暂无用到=====================================================================
	
	//<req><patientId>91303204391491</patientId><hospitalId>1051</hospitalId></req>  
	//最近一次就诊日期
	private function getLastClinicDate($input){    
		
		Log::posthis("getLastClinicDate:req\r\n".$input); 	
		//==============获得前台请求==============   		
		$req = simplexml_load_string($input); //提取POST数据为simplexml对象 
		
		//<Request><TradeCode>1104</TradeCode><ExtOrgCode>南方医务通</ExtOrgCode><ClientType></ClientType><HospitalId></HospitalId>
		//<ExtUserID>NFYWT</ExtUserID><CardType></CardType><PatientCard></PatientCard><PatientID>33043014</PatientID>
		//<StartDate>2016-09-11</StartDate><EndDate>2016-09-28</EndDate></Request>
		
		//HQTJ 可以查询全部渠道的挂号记录
		//==============封装HIS请求==============
		$TradeCode = "1104";
		$ExtOrgCode ="南方医务通"; 
		$ExtUserID = "HQTJ";  
		$ClientType = ""; 
		$HospitalId = "";    
		$CardType = ""; 
		$PatientCard = "";
		$PatientID = $req->PatientID;
		$StartDate = date("Y-m-d",strtotime("-1 month"));
		$EndDate = date("Y-m-d",time()); 
		
		//================调用自己WS===============
		$postData =  "<Request><TradeCode>$TradeCode</TradeCode><ExtOrgCode>$ExtOrgCode</ExtOrgCode><ClientType>$ClientType</ClientType><HospitalId>$HospitalId</HospitalId><ExtUserID>$ExtUserID</ExtUserID><CardType>$CardType</CardType><PatientCard>$PatientCard</PatientCard><PatientID>$PatientID</PatientID><StartDate>$StartDate</StartDate><EndDate>$EndDate</EndDate></Request>";		
		$postData = str_replace(' ','%20',$postData); 
		$wsdl = "http://yygh2.dept.nfyy.com/csp/oep/DHC.OEP.BS.OEPSTANWebService.cls?soap_method=QueryAdmOPReg&Input=";
		$result = file_get_contents($wsdl.$postData);     
		Log::posthis("getLastClinicDate:res\r\n".$result); 
		//==============处理返回==============    
		
		 
		echo $result;  	
	}
	
	
	
	/**
	* 生成时间
	* @return string
	*/
	private function timenow() { 
		return date("Y-m-d H:i:s", time());
	}
	
	/**
	* 生成时间
	* @return string
	*/
	private function datenow() { 
		return date("Y-m-d", time());
	}
	
	private function datenow1($day) { 
		return date("Y-m-d", strtotime("+".$day." day"));
	}
	/**
	* 生成时间
	* @return string
	*/
	private function dateweek() {  
		$weekarray=array("日","一","二","三","四","五","六");
		return "星期".$weekarray[date("w")];
	}
	
	
	/**
	* 生成随机数字串
	* @param string $lenth 长度
	* @return string 字符串
	*/
	private	function randomnum() {
		
		$str = null;
		$strPol = "0123456789";
		$max = strlen($strPol)-1;

		for($i=0;$i<8;$i++){
			$str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
		}

		return $str;
	} 

}
