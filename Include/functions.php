<?php
/**
 * 获取和设置配置参数 支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @return mixed
 */
function config_value($name, $value=null) {
    static $_config = array();
    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtolower($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : null;
            $_config[$name] = $value;
            return;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $name[0]   =  strtolower($name[0]);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : null;
        $_config[$name[0]][$name[1]] = $value;
        return;
    }
    // 批量设置
    if (is_array($name)){
        $_config = array_merge($_config, array_change_key_case($name));
        return;
    }
    return null; // 避免非法参数
}

/**
 * 
 * 列出目录下所有文件及子目录
 * @param string $dirName 目录名称
 * @return 返回列出的所有文件
 */
function listDirTree( $dirName = null ) {
	if( empty( $dirName ) )
		exit( 'IBFileSystem: directory is empty.' );
	if( is_dir( $dirName ) ) {
		if( $dh = opendir($dirName)) {
			$tree = array();
			$files = array();
			while( ( $file = readdir( $dh ) ) !== false ) {
				if( $file != '.' && $file != '..' && $file != '.svn' && $file != '.DS_Store' && $file != '@eaDir' ) {
					$filePath = $dirName . '/' . $file;
					if( is_dir( $filePath ) ) {
				    	//为目录,递归
						$temp = listDirTree( $filePath );
						for ($i=0; $i<count($temp); $i++) {
							$fiels[] = $temp[$i];
						}
						$tree[$file] = listDirTree( $filePath );
					} else {
						$fiels[] = $filePath;
						//为文件,添加到当前数组
						$tree[] = $filePath;
					}
				}
			}
			closedir( $dh );
		} else {
			exit( 'IBFileSystem: can not open directory ' . $dirName);
		}
		return $fiels;
	} else {
		exit( 'IBFileSystem: ' . $dirName . ' is not a directory.');
	}
}


/**
 * 
 * 不判断PageImage PageSound
 * @param string $dirName
 */
function listDirTreeAll( $dirName = null ) {
	if( empty( $dirName ) )
		exit( 'IBFileSystem: directory is empty.' );
	if( is_dir( $dirName ) ) {
		if( $dh = opendir($dirName)) {
			$tree = array();
			$files = array();
			while( ( $file = readdir( $dh ) ) !== false ) {
				if( $file != '.' && $file != '..' && $file != '.svn' && $file != '.DS_Store' && $file != '@eaDir' ) {
					$filePath = $dirName . '/' . $file;
					//echo $filePath . '<br />';
					if( is_dir( $filePath )) {
				    	//为目录,递归
						$temp = listDirTree( $filePath );
						for ($i=0; $i<count($temp); $i++) {
							$fiels[] = $temp[$i];
						}
						$tree[$file] = listDirTree( $filePath );
					} else {
						$fiels[] = $filePath;
						//为文件,添加到当前数组
						$tree[] = $filePath;
					}
				}
			}
			closedir( $dh );
		} else {
			exit( 'IBFileSystem: can not open directory ' . $dirName);
		}
		return $fiels;
	} else {
		exit( 'IBFileSystem: ' . $dirName . ' is not a directory.');
	}
}


/**
 * 格式化XML
 * @param $xml
 * @return string 格式化后的XML字符串
 */
function formatXmlString($xml) {

	// add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
	$xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

	// now indent the tags
	$token      = strtok($xml, "\n");
	$result     = ''; // holds formatted version as it is built
	$pad        = 0; // initial indent
	$matches    = array(); // returns from preg_matches()

	// scan each line and adjust indent based on opening/closing tags
	while ($token !== false) :

	// test for the various tag states

	// 1. open and closing tags on same line - no change
	if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) :
	$indent=0;
	// 2. closing tag - outdent now
	elseif (preg_match('/^<\/\w/', $token, $matches)) :
	$pad--;
	// 3. opening tag - don't pad this one, only subsequent tags
	elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) :
	$indent=1;
	// 4. no indentation needed
	else :
	$indent = 0;
	endif;

	// pad the line with the required number of leading spaces
	$line    = str_pad($token, strlen($token)+$pad, ' ', STR_PAD_LEFT);
	$result .= $line . "\n"; // add to the cumulative result, with linefeed
	$token   = strtok("\n"); // get the next token
	$pad    += $indent; // update the pad size for subsequent lines
	endwhile;

	return $result;
}




// 获得后缀名
function get_file_type($filename){
    $type = substr($filename, strrpos($filename, ".")+1);
    return $type;
}


$array_Option_EnableDisable = array(
	0=>'Disable',
	1=>'Enable'
);
$array_Option_AlarmType = array(
	0=>'Email',
	1=>'SMS'
);
$array_Option_DataVersion = array(
	0=>'V2.2.4',
	1=>'V2.2.5'
);
$array_Option_RoadSystemType = array(
	0=>'Aquatrak',
	1=>'Roadtrak'
);


//query data from table and make a 
//$textName
//<select name=$textName id=$textName>
//	<option value="xxx">
//	<option value="xxx">
//	<option value="xxx">
//</select>
function OptionSelectLabel_FromTableRow($textName,$table,$idField,$nameField,$selectValue)
{
	$sql = "SELECT `".$idField."`,`".$nameField."` from `".$table."`";
	$rs = mysql_query($sql);
	if (!$rs){
		$ret = "Query error! sql=".$sql;
		return $ret;
	}
	$ret='';
	while($array = mysql_fetch_array($rs))
	{
		$ret = $ret."<tr><td width=\"60\" height=\"35\">".$textName.":</td>";
		$ret = $ret. "<td><select name=\"input".$textName."\" id=\"input".$textName."\">";
		$selected = ($selectValue==$array[$idField])?'selected="selected"' : '';
		$ret = $ret. "<option value=\"".$array[$idField]."\" " . $selected . ">" . $array[$nameField] . "</option>";
		$ret = $ret.  "</select></td></tr>";
	}
	return $ret;
}



//query data from table and make a 
//$textName
//<select name=$textName id=$textName>
//	<option value="xxx">
//	<option value="xxx">
//	<option value="xxx">
//</select>
function OptionSelectLabel_FromArray($textName,$optionArray,$selectValue)
{

	$ret = $ret."<tr><td width=\"60\" height=\"35\">".$textName.":</td>";
	$ret = $ret. "<td><select name=\"input".$textName."\" id=\"input".$textName."\">";

	foreach($optionArray as $id => $value)
	{
		$selected = ($selectValue==$id)?'selected="selected"' : '';
		$ret = $ret. "<option value=\"".$id."\" " . $selected . ">" . $value . "</option>";
	}
	$ret = $ret.  "</select></td></tr>";

	return $ret;
}


function OptionSelect_GetDescFromArray($array,$value_id)
{
	foreach($array as $id => $desc)
	{
		if ($value_id = $id)	return $desc;
	}
	return '';
}

function OptionSelect_GetDescFromTable($table,$idField,$DescField,$value_id)
{
	$sql = "SELECT `".$idField."`,`".$DescField."` from `".$table."`";
	$rs = mysql_query($sql);
	if (!$rs){
		$ret = "Query error! sql=".$sql;
		return $ret;
	}
	$ret='';
	while($array = mysql_fetch_array($rs))
	{
		if ($array[$idField]==$value_id)	return $array[$DescField];
	}
	return '';
}
function now()
{
	return date("Y-m-d H:i:s");
}



//update database from array
//
//row("field1"=>"aaa","field2"=>"bbb")
//
function update_db_row($table,$row,$condition)
{
	$updates = "";
	$where = "";
	foreach($row as $key=>$value)
	{
		if(preg_match("/^[0-9]*$/",$key))	continue;
		if ($condition!='')
		{
			$where = " WHERE ".$condition;
		}
		
		$update="`".$key."`"."=\"".$value."\" ";

		if ($updates!="") $update = ",".$update;
		$updates = $updates.$update;
	}
	$sql = "UPDATE `".$table."` SET ".$updates.$where;
	$ret = mysql_query($sql);
	if (!$ret)
	{
		//echo "SQL error! ".$sql."\r\n";
		LOG::write("SQL error! {$sql}",LOG::ERR);
	}
	return $ret;
}


function insert_db_row($table,$array)
{
	$fields="";
	$values="";
	foreach($array as $key=>$value)
	{
		if(preg_match("/^[0-9]*$/",$key))	continue;

		$key = "`".$key."`";
		if ($fields!="") $key = ",".$key;		
		$fields=$fields.$key;

		if ($values!="") $value = "\",\"".$value;
		$values = $values.$value;
	}
	$sql = "INSERT INTO `".$table."` (".$fields." ) VALUES(\"".$values."\")";
	$ret = mysql_query($sql);
	if (!$ret)
	{
		//echo "SQL error! ".$sql."\r\n";
		LOG::write("SQL error! {$sql}",LOG::ERR);
	}
	return $ret;
}

/*get a single row from table*/
/*if error, write log and return null*/
function get_single_row_sql($sql)
{
	$ret = mysql_query($sql);
	if (!$ret)
	{
		//echo "SQL error! ".$sql."\r\n";
		LOG::write("SQL error! {$sql}",LOG::ERR);
		return null;
	}
	else
	{
		$row = mysql_fetch_array($ret,true);
		return $row;
	}

}

/*get multi row from table*/
/*if error, write log and return null*/
function get_multi_row_sql($sql)
{
	$array = array();
  	$rs = mysql_query($sql);
	if (!$rs)
	{
		//echo "SQL error! ".$sql."\r\n";
		LOG::write("SQL error! {$sql}",LOG::ERR);
		return null;
	}

  	$k = 0;
  	while($fields= mysql_fetch_array($rs,true))
  	{
   		$array[$k++] = $fields;
  	}
  	return $array;  
}



/*
 	extion with get_multi_row_sql
	if some rows not often modify , we keep them in memory, 
	This function check wether or not changed,if changed read them again.

	$key   for save timestamp
	$sql   sql for refresh  rows from table
	$rows  fill with data 
 */

function refresh_multi_row($key,$sql,&$rows)
{
	$tableUpdate =get_single_row_sql("SELECT * FROM table_update");
	if ( isset($GLOBALS['table_update'][$key]) && !empty($rows) )
	{
		echo $tableUpdate['table_update'][$key]."\r\n";
		if ($tableUpdate[$key] == $GLOBALS['table_update'][$key])  return;
	}

	$list = get_multi_row_sql($sql);
	if ($list){
		$GLOBALS['table_update'][$key] = 	$tableUpdate[$key];
		$rows = $list;
		unset($list);
	}
}



/*execute sql */
/*if error, write log and return false*/
function execute_sql($sql)
{
	$ret = mysql_query($sql);
	if (!$ret)
	{
		//echo "SQL error! ".$sql."\r\n";
		LOG::write("SQL error! {$sql}",LOG::ERR);
	}
	return $ret;
}






/********************************/
/* end mysql function */
/********************************/






//make sql "field in (xx1,xx2,xx2,xx4)"
function gene_sql_condition_in($field,$arr,$arry_field='')
{
    $condition = $field." in (";
    $cn = 0;


    for($i=0; $i<count($arr); $i++)
    {
        if ($cn>0)
            $condition = $condition.",";

        if ($arry_field=='')
            $condition = $condition."'".$arr[$i]."'";
        else
            $condition = $condition."'".$arr[$i][$arry_field]."'";


        $cn++;
    }
    $condition = $condition.")";

    return $condition;
}

function gene_sql_condition_not_in($field,$arr,$arry_field='')
{
    $condition = $field." not in (";
    $cn = 0;


    for($i=0; $i<count($arr); $i++)
    {
        if ($cn>0)
            $condition = $condition.",";

        if ($arry_field=='')
            $condition = $condition."'".$arr[$i]."'";
        else
            $condition = $condition."'".$arr[$i][$arry_field]."'";


        $cn++;
    }
    $condition = $condition.")";

    return $condition;
}


function DateDiff($part, $begin, $end)
{
	$diff = strtotime($end) - strtotime($begin);
	switch($part)
	{
		case "y": $retval = bcdiv($diff, (60 * 60 * 24 * 365)); break;
		case "m": $retval = bcdiv($diff, (60 * 60 * 24 * 30)); break;
		case "w": $retval = bcdiv($diff, (60 * 60 * 24 * 7)); break;
		case "d": $retval = bcdiv($diff, (60 * 60 * 24)); break;
		case "h": $retval = bcdiv($diff, (60 * 60)); break;
		case "n": $retval = bcdiv($diff, 60); break;
		case "s": $retval = $diff; break;
	}
	return $retval;
}
function DateAdd($part, $number, $date)
{
	$date_array = getdate(strtotime($date));
	$hor = $date_array["hours"];
	$min = $date_array["minutes"];
	$sec = $date_array["seconds"];
	$mon = $date_array["mon"];
	$day = $date_array["mday"];
	$yar = $date_array["year"];
	switch($part)
	{
		case "y": $yar += $number; break;
		case "q": $mon += ($number * 3); break;
		case "m": $mon += $number; break;
		case "w": $day += ($number * 7); break;
		case "d": $day += $number; break;
		case "h": $hor += $number; break;
		case "n": $min += $number; break;
		case "s": $sec += $number; break;
	}
	return date("Y-m-d H:i:s", mktime($hor, $min, $sec, $mon, $day, $yar));
}

function get_time($datetime,$onlyHourMin = true)
{
	$date_time = explode(" ", $datetime);
	if ( !empty($date_time[1]) ){
		if ($onlyHourMin){
			return substr($date_time[1],0,5);
		}
		else{
			return $date_time[1];
		}
	}
		
	return '';
}
function get_date($datetime)
{
	$date_time = explode(" ", $datetime);
	if (!empty($date_time[0]))
		return $date_time[0];
	return '';
}


/*在Array和String类型之间转换，转换为字符串的数组可以直接在URL上传递*/
// convert a multidimensional array to url save and encoded string
// usage: string Array2String( array Array )
function Array2String($Array)
{
    $Return='';
    $NullValue="^^^";
    foreach ($Array as $Key => $Value) {
        if(is_array($Value))
            $ReturnValue='^^array^'.Array2String($Value);
        else
            $ReturnValue=(strlen($Value)>0)?$Value:$NullValue;
        $Return.=urlencode(base64_encode($Key)) . '|' . urlencode(base64_encode($ReturnValue)).'||';
    }
    return urlencode(substr($Return,0,-2));
}

// convert a string generated with Array2String() back to the original (multidimensional) array
// usage: array String2Array ( string String)
function String2Array($String)
{
    $Return=array();
    $String=urldecode($String);
    $TempArray=explode('||',$String);
    $NullValue=urlencode(base64_encode("^^^"));
    foreach ($TempArray as $TempValue) {
        list($Key,$Value)=explode('|',$TempValue);
        $DecodedKey=base64_decode(urldecode($Key));
        if($Value!=$NullValue) {
            $ReturnValue=base64_decode(urldecode($Value));
            if(substr($ReturnValue,0,8)=='^^array^')
                $ReturnValue=String2Array(substr($ReturnValue,8));
            $Return[$DecodedKey]=$ReturnValue;
        }
        else
        $Return[$DecodedKey]=NULL;
    }
    return $Return;
}


// checks for multiarray to defined depth level recursively
// original $level must be 2 or more, else will instantly return true
function is_multi_array($multiarray, $level = 2) {  // default is simple multiarray
    if (is_array($multiarray)) {  // confirms array
        if ($level == 1) {  // $level reaches 1 after specified # of recursions  
            return true;  // returns true to recursive function conditional
        }  // end conditional
        foreach ($multiarray as $array) {  // goes one level deeper into array
            if (is_multi_array($array, $level - 1)) {  // check subarray
                return true;  // best if $message = true so function returns boolean
            }  // end recursive function
        }  // end loop
    } else {  // not an array at specified level
    	return false;  // is also used recursively so can't change to message
    } 
}



//convert array each value to string separater with delimiter
//from array[0] --- end  , order 
//for example, delimiter='!'   array('aa','bb') return : aa!bb
//
///////////
function Array2StringDelimiter($inArray , $delimiter = '!')
{
	if (!is_array($inArray) || empty($inArray))	return '';
	$keys = array_keys($inArray);
	$maxIndex = max($keys);
	$return_str = '';
	for($i=0;$i<=$maxIndex;$i++)
	{
		$value = isset($inArray[$i])?$inArray[$i]:'';
		$return_str	=	$return_str.$value;

		if ($i!=$maxIndex)
			$return_str = $return_str.$delimiter;

	}
	return $return_str;
}

function String2ArrayDelimiter($string , $delimiter = '!')
{
	return explode($delimiter, $string);
}


/**
 * [aasort]
 * sort array with key
 * @param  [type] $array [sort array, after sorted array changed]
 * @param  [type] $key   [which key will be sorted]
 * 
 */
function aasort (&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

/////////////////////////////////
//sign a url 
//echo signUrl("http://maps.googleapis.com/maps/api/geocode/json?latlng=40.7,-73.96&client=gme-traktroniclimited&sensor=true&", 'GMlL28fgH41B45WlG4ojDhc2IrM=);
////////////////////////////////////////////
// Encode a string to URL-safe base64
function encodeBase64UrlSafe($value)
{
  return str_replace(array('+', '/'), array('-', '_'),
    base64_encode($value));
}

// Decode a string from URL-safe base64
function decodeBase64UrlSafe($value)
{
  return base64_decode(str_replace(array('-', '_'), array('+', '/'),
    $value));
}

// Sign a URL with a given crypto key
// Note that this URL must be properly URL-encoded
function signUrl($myUrlToSign, $privateKey)
{
  // parse the url
  $url = parse_url($myUrlToSign);

  $urlPartToSign = $url['path'] . "?" . $url['query'];

  // Decode the private key into its binary format
  $decodedKey = decodeBase64UrlSafe($privateKey);

  // Create a signature using the private key and the URL-encoded
  // string using HMAC SHA1. This signature will be binary.
  $signature = hash_hmac("sha1",$urlPartToSign, $decodedKey,  true);

  $encodedSignature = encodeBase64UrlSafe($signature);

  return $myUrlToSign."&signature=".$encodedSignature;
}
/////////////////////////////////

function multi_implode(array $glues, array $array){
    $out = "";
    $g = array_shift($glues);
    $c = count($array);
    $i = 0;
    foreach ($array as $val){
        if (is_array($val)){
            $out .= multi_implode($glues,$val);
        } else {
            $out .= (string)$val;
        }
        $i++;
        if ($i<$c){
            $out .= $g;
        }
    }
    return $out;
}
function multi_explode(array $delimiter,$string){
    $d = array_shift($delimiter);
    if ($d!=NULL){
        $tmp = explode($d,$string);
        foreach ($tmp as $key => $o){
            $out[$key] = multi_explode($delimiter,$o);
        }
    } else {
        return $string;
    }
    return $out;
}

function MYDUMP($var)
{
  if ( isset($var['Has-Error']) ){
  	echo "<font color=\"red\">";
  }
  print_r('<pre>');
  print_r($var);
  print_r('</pre>');
  if ( isset($var['Has-Error']) ){
  	echo "</font>";
  }
}


//out put log
define('LOG_TYPE_Terminal',1);
define('LOG_TYPE_WebPage',2);

define('LOG_LEVEL_High',1);
define('LOG_LEVEL_Mid',2);
define('LOG_LEVEL_Lowest',3);

function MYLOG($log,$type = LOG_TYPE_Terminal, $log_level = LOG_LEVEL_Lowest)
{
	if($type == LOG_TYPE_Terminal)
	{
		$log = $log."\r\n";
	}
	else if ($type == LOG_TYPE_WebPage)
	{
		$log = $log."<br>";
	}
	echo $log;
	
}

function MYLOG_TOFILE($file, $log,$type = LOG_TYPE_Terminal, $log_level = LOG_LEVEL_Lowest)
{
	$fh = fopen($file,"a");
	fputs($fh,now()."-".$log."\r\n");
	fclose($fh);
}




/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function getClientIP($type = 0) {
	$type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
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
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

function generateRandomString($length = 9) {
	return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}


function array_sort($array, $on, $order=SORT_ASC)
{
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }
        
        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }
        
        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}

