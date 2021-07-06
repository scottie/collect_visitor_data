<?php 
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include 'db.php';

function getBrowser() 
{ 
    $u_agent = $_SERVER['HTTP_USER_AGENT']; 
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";
 

    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }
    

    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Internet Explorer'; 
        $ub = "MSIE"; 
    } 
    elseif(preg_match('/Firefox/i',$u_agent)) 
    { 
        $bname = 'Mozilla Firefox'; 
        $ub = "Firefox"; 
    } 
    elseif(preg_match('/Chrome/i',$u_agent)) 
    { 
        $bname = 'Google Chrome'; 
        $ub = "Chrome"; 
    } 
    elseif(preg_match('/Safari/i',$u_agent)) 
    { 
        $bname = 'Apple Safari'; 
        $ub = "Safari"; 
    } 
    elseif(preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Opera'; 
        $ub = "Opera"; 
    } 
    elseif(preg_match('/Netscape/i',$u_agent)) 
    { 
        $bname = 'Netscape'; 
        $ub = "Netscape"; 
    } 
    

    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }
    

    $i = count($matches['browser']);
    if ($i != 1) {

        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }
    
    if ($version==null || $version=="") {$version="?";}
    
    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
} 
 
function getUserIP()
{
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];
 
    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }
 
    return $ip;
}
 
function getGeoLocation($freeApikey)
{
    $PublicIP = getUserIP();
    //echo $PublicIP;

    $json = file_get_contents("http://ipinfo.io/$PublicIP/geo" . "?token=" . $freeApikey);
    //echo $json;
    return json_decode($json, true);
}

function main(){

    $GeoLocation = getGeoLocation("YOUR_FREE_API_KEY");
    $BrowserPHP = get_browser(null, true);
    echo $BrowserPHP;
    $Browser=getBrowser(); 
    $RefInfo = Array();
    $timestamp = time();

    try{
        $ref = $_SERVER['HTTP_REFERER'];
    }catch(Exception $ex){
        $ref = "Direct";
    }

    $RefInfo['Name'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $RefInfo['Browser'] = $Browser['name'];
    $RefInfo['BrowserVer'] = $Browser['version'];
    $RefInfo['OpSystem'] = $Browser['platform'];
    $RefInfo['Ref'] = $ref;
    $RefInfo['BrowserAgent'] = $_SERVER['HTTP_USER_AGENT'];
    $RefInfo['BrowserLang'] = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    $RefInfo['IP'] = getUserIP();
    $RefInfo['Platform'] = $BrowserPHP['platform'];
    $RefInfo['Javascript'] = $BrowserPHP['javascript'];
    $RefInfo['City'] = $GeoLocation['city'];
    $RefInfo['Region'] = $GeoLocation['region'];
    $RefInfo['Country'] = $GeoLocation['country'];
    $RefInfo['Loc'] = $GeoLocation['loc'];
    $RefInfo['Postal'] = $GeoLocation['postal'];
    $RefInfo['Timezone'] = $GeoLocation['timezone'];
    $RefInfo['Time'] = $timestamp;
    
    return $RefInfo;
}

$data = main();
//echo json_encode($data);

try{
 
    $db = new DB();
    $conn = $db->Connect();
    if($conn){
        $query =   "INSERT INTO 'leads'(
                        'name',
                        'browser', 
                        'browserVer', 
                        'opSystem',
                        'ref', 
                        'browserAgent', 
                        'browserLang', 
                        'ip', 
                        'platform', 
                        'javascript',  
                        'city', 
                        'region', 
                        'country', 
                        'loc', 
                        'postal', '
                        'timezone', 
                        'timestamp') 
                    VALUES (
                        '".$row["Name"]."',
                        '".$row["Browser"]."',
                        '".$row["BrowserVer"]."',
                        '".$row["OpSystem"]."',
                        '".$row["Ref"]."',
                        '".$row["BrowserAgent"]."',
                        '".$row["BrowserLang"]."',
                        '".$row["IP"]."',
                        '".$row["Platform"]."',
                        '".$row["Javascript"]."',
                        '".$row["City"]."',
                        '".$row["Region"]."',
                        '".$row["Country"]."',
                        '".$row["Loc"]."',
                        '".$row["Postal"]."',
                        '".$row["Timezone"].",
                        '".$row["Time"]."
                    )";
        $conn->query($query);
    }
    else{
        //log connection error / report
        //echo $conn;
    }
}
catch(PDOException $ex){
    echo $ex->getMessage();
}

?>
