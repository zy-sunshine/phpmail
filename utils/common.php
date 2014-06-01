<?php
include_once(dirname(__FILE__).'/../simplehtmldom/simple_html_dom.php');
class EmailAddress
{
    function __construct($addr)
    {
        $this->addr = $addr;
        //list($user, $domain) = split('@', $this->addr);
        preg_match("/^([[:alnum:]][a-z0-9_.-]*)@([a-z0-9.-]+\.[a-z]{2,4})$/i", $this->addr, $m);
        $this->user = $m[1];
        $this->domain = $m[2];
    }
    static public function isValid($email){
        if (!preg_match("/^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$/i", $email))
        {
            return false;
        } else {
            return true;
        }
    }
    public function valid()
    {
        return self::isValid($this->addr);
    }
    public function getUser()
    {
        return $this->user;
    }
    public function getDomain()
    {
        return $this->domain;
    }
}
class Url
{
    function __construct($url)
    {
        $this->url = $url;
        $this->params = $this->parseParams($url);
    }
    private function parseParams($url)
    {

        $param = array();
        if(!$url) return array();
        $pos = strpos($url, '/', $offset = 7);
        $this->host = substr($url, 0, $pos);

        $pos = strpos($url, '?');
        $this->urlmain = substr($url, 0, $pos);

        $this->urlparams = substr($url, $pos + 1);
        $arr = explode('&', $this->urlparams);
        foreach($arr as $k => $v){
            //print "$k => $v <br />";
            list($pk, $pv) = explode('=', $v);
            //print "$pk => $pv <br />";
            $param[$pk] = $pv;
        }
        return $param;
    }
    public function getParams()
    {
        return $this->params;
    }
    public function getHost()
    {
        return $this->host;
    }
    public function setParams($params)
    {
        $this->params = $params;
    }
    public function getNewUrl()
    {
        $paramstr = '';
        foreach($this->params as $key => $value){
            $paramstr .= "$key=$value&";
        }
        $paramstr = substr($paramstr, 0, -1);
        $this->urlparams = $paramstr;
        return $this->urlmain.'?'.$this->urlparams;
    }
    static function curPageURL() 
    {
        $pageURL = 'http';

        if ($_SERVER["HTTPS"] == "on") 
        {
            $pageURL .= "s";
        }
        $pageURL .= "://";

        if ($_SERVER["SERVER_PORT"] != "80") 
        {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["PHP_SELF"];
        } 
        else 
        {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["PHP_SELF"];
        }
        return $pageURL;
    }
//     private Test()
//     {
//         $t = new Url("http://webmail.mail.163.com/js4/main.jsp?sid=QCadZiiLZVKTcxbTeeLLDoHCZcylngFS&test=1&test2=tstring");
//         var_dump($t->getParams());
//     }
}
class Cookie
{
    function __construct()
    {
    }
    static function parseFromFile($cookieFile)
    {
        $cookiedict = array();
        $cookie_content = file_get_contents ( $cookieFile );
        $citem = explode("\n",$cookie_content);

        foreach( array_slice($citem, 3, -1) as $c )
        {
            if(strlen($c) == 0){// || substr($c, 0, 1) == '#'
                continue;
            }
            $arr = explode("\t",$c);
            $value = end($arr);
            $key = prev($arr);
            $cookiedict[$key] = $value;
    //         echo "Key $key <br />";
    //         echo "Value $value <br />";
        }
        return $cookiedict;
    }
    static function joinFromDict($cookiedict)
    {
        foreach( $cookiedict as $k=>$v )
        {
            $d[] = $k."=".$v;
        }
        $data = implode(";",$d);
        return $data;
    }
//     static function Test()
//     {
//         $cookieDict = Cookie::parseFromFile("/tmp/cookie68Vbmg");
//         print_r($cookieDict);
//         echo Cookie::joinFromDict($cookieDict);
//     }
}
class EmailContacts{
    public function __construct($name){
        $this->name = $name;
        $this->emails = array();
    }

    public function appendEmail($email){
        if(EmailAddress::isValid($email)){
            array_push($this->emails, $email);
        }
    }
    public function emailCounts(){
        return count($this->emails);
    }
    public function printString(){
        print "=================<br />";
        print "Name: $this->name<br />";
        print "Emails: <br />";
        foreach($this->emails as $email){
            print "&nbsp;&nbsp;$email<br />";
        }
        print "=================<br />";
    }
}
class HttpHeader
{
    public function __construct($header_str)
    {
        $this->header_str = $header_str;
        //http_parse_headers("");
    }
    public function getlocation()
    {
        preg_match('/Location:\s+(.*?)\s+/is', $this->header_str, $m);
        return $m[1];
    }
}
class HttpCurl
{
    function __construct($url = "")
    {
        $this->url = $url;
        $this->ch = NULL;
        $this->cookieFile = tempnam( ini_get( "upload_tmp_dir" ), "cookie" );
        $this->timeout = 1000;
        $this->useragent = "Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:10.0.2) Gecko/20100101 Firefox/10.0.2";
        // variables
        $this->fields = "";
        $this->method = "get";
        $this->ret = "";
        $this->contentstr = "";
        $this->info = NULL;
        $this->headerstr = "";
        $this->headerobj = NULL;
        $this->options = array(CURLOPT_URL => $this->url,
            CURLOPT_HEADER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_USERAGENT => $this->useragent,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_RETURNTRANSFER => true,
        );
    }
    function __destruct()
    {
        if(file_exists($this->cookieFile)){
            unlink($this->cookieFile);
        }
    }
    public function getNew($url)
    {
        $this->url = $url;
        $this->setOpt(CURLOPT_URL, $this->url);

        // reset variables
        $this->fields = "";
        $this->method = "get";
        $this->ret = "";
        $this->contentstr = "";
        $this->info = NULL;
        $this->headerstr = "";
        $this->headerobj = NULL;

        return $this;
    }
    public function setOpt($k, $v)
    {
        $this->options[$k] = $v;
    }
    public function setCookieFile($cookieFile)
    {
        $this->cookieFile = $cookieFile;
        $this->setOpt(CURLOPT_COOKIEFILE, $this->cookieFile);
        $this->setOpt(CURLOPT_COOKIEJAR, $this->cookieFile);
    }
    public function setOptReferer($referer)
    {
        $this->setOpt(CURLOPT_REFERER, $referer);
    }
    private function requestPost()
    {
        $this->setOpt(CURLOPT_POST, true);
        curl_setopt_array($this->ch, $this->options);
        //$this->setOpt(CURLOPT_SSL_VERIFYPEER, true);
        //$this->setOpt(CURLOPT_SSL_VERIFYHOST, 2);
        //$this->setOpt(CURLOPT_CAINFO, getcwd()."/../VeriSignClass3ExtendedValidationSSLCA");
        return curl_exec($this->ch);
    }
    private function requestGet()
    {
        curl_setopt_array($this->ch, $this->options);
        return curl_exec($this->ch);
    }
    public function setOptPostFields($fields)
    {
        $this->fields = $fields;
        $this->setOpt(CURLOPT_POSTFIELDS, $this->fields);
        $this->method = "post";
    }
    public function setPostData($postdata){
        $fields = "";
        foreach($postdata as $key => $value){
            $fields .= "$key=$value&";
        }
        $fields = rtrim($fields, '&');
        //var_dump($fields);
        $this->setOptPostFields($fields);
    }
    public function request()
    {
        $this->ch = curl_init();
        if($this->method == "post"){
            $this->ret = $this->requestPost();
        }else{
            $this->ret = $this->requestGet();
        }
        if(!$this->ret){
            curl_close($this->ch);
            $this->ch = NULL;
            return false;
        }
        $this->info = curl_getinfo($this->ch);
        $this->headerstr = substr($this->ret, 0, $this->info['header_size']);
        //put_contents("header.dbg", $this->headerstr);
        $this->contentstr = substr($this->ret, $this->info['header_size']);

        curl_close($this->ch);
        $this->ch = NULL;

        return $this->contentstr;
    }
    public function setOptFollowLocation($flag)
    {
        $this->setOpt(CURLOPT_FOLLOWLOCATION, $flag);
    }
    public function setOptHeader($flag)
    {
        $this->setOpt(CURLOPT_HEADER, $flag);
    }
    public function setOptHttpHeader($flag)
    {
        $this->setOpt(CURLOPT_HTTPHEADER, $flag);
    }
    public function getLocation()
    {
        if(!$this->headerobj){
            $this->headerobj = new HttpHeader($this->headerstr);
        }
        return $this->headerobj->getlocation();
    }
    public function getCookieFile()
    {
        return $this->cookieFile;
    }
    public function getHeaderString()
    {
        return $this->headerstr;
    }
}
class HtmlParser
{
    public function __construct($html_str){
        $this->html= str_get_html($html_str);
    }
    
    public function getTagValueById($tag, $id){

        $ret_arr = array();
        foreach($this->html->find($tag) as $tagObj){
            if($tagObj->id == $id){
                foreach($tagObj->attr as $key=>$value){
                    $ret_arr[$key] = $value;
                }
                return $ret_arr;
            }
        }
        return array();
    }
    
    public function getTagValueByName($tag, $name){

        $ret_arr = array();
        foreach($this->html->find($tag) as $tagObj){
            if($tagObj->name == $name){
                foreach($tagObj->attr as $key=>$value){
                    $ret_arr[$key] = $value;
                }
                return $ret_arr;
            }
        }
        return array();
    }
}
function put_contents($file, $content) {
    $f = fopen("$file", "w");
    fwrite($f, $content);
    fclose($f);
}
function get_contents($file) {
    return file_get_contents("$file");
    /*
    $f = fopen($file,"r");
    $content = fread($f);
    fclose($f);
     */
}

function replace_unicode_escape_sequence($match) {
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
}
function unicode2string($str){
    $str = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $str);
    return $str;
}
function print_address_list($contacts)
{
    print count($contacts)." Contacts: <br />";
    // Debug Print
    foreach($contacts as $c){
        $c->printString();
    }
}
?>
