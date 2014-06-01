<?php
include_once(dirname(__FILE__).'/../utils/common.php');
class MAILYEAH
{
    function __construct()
    {
        $this->httpCurl = new HttpCurl();
    }
    public function login($username, $password)
    {
        $step1 = $this->httpCurl->getNew('https://reg.163.com/logins.jsp?type=1&product=mailyeah&url=http://entry.mail.yeah.net/cgi/ntesdoor?lightweight%3D1%26verifycookie%3D1%26style%3D-1');
        $postdata = array('username'=> $username, 'password'=> $password);
        $step1->setPostData($postdata);
        $contents = $step1->request();
        preg_match("/replace\(\"(.*?)\"\)\;/", $contents, $m);
        $url_redirect = $m[1];
        $step2 = $step1->getNew($url_redirect);
        $contents = $step2->request();
        //put_contents("content2.dbg", $contents);
        preg_match('/window.location.replace\("(.*?)"\);/', $contents, $m);
        if (strpos($contents, "登录成功，正在跳转...") !== false){
            //echo "Login Success!";
            $this->url_redirect = $m[1];
            return true;
        }else{
            //echo "Login Failed!";
            return false;
        }
    }
    public function getAddressList($username, $password)
    {
        if (!$this->login($username, $password))
        {
            return FALSE;
        }
        $step3 = $this->httpCurl->getNew($this->url_redirect);
        $step3->request();
        $locurl = $step3->getLocation();
        $locurl = new Url($locurl);
        $sid = $locurl->getParams();
        $sid = $sid["sid"];
        $headerstr = $step3->getHeaderString();
        preg_match_all('/Location:\s*(.*?)\r\n/i', $headerstr, $regs);
        $refer = $regs[1][0];
        preg_match_all('/http\:\/\/(.*?)\//i', $headerstr, $regs);
        $host = $regs[1][0];
        //var_dump("sid: $sid refer: $refer host: $host");
        if (!$sid || !$refer || !$host){
            return -1;
        }
        $url = "http://g1a8.mail.yeah.net/jy3/address/addrlist.jsp?sid=".$sid."&gid=all";
        $step4 = $step3->getNew($url);
        $contents = $step4->request();
        ////put_contents("contacts.html.dbg", $contents);
        //$contents = get_contents("contacts.html.dbg");

        return $this->standardFormat($contents);
    }
    private function standardFormat($contents)
    {
        $email_contacts = array();
        $name_arr = array();
        $mail_arr = array();
        $html = str_get_html($contents);
        foreach($html->find("td[class=Ibx_Td_addrName]") as $node){
            array_push($name_arr, $node->children(0)->innertext);
        }
        foreach($html->find("td[class=Ibx_Td_addrEmail]") as $node){
            array_push($mail_arr, $node->children(0)->innertext);
        }
        for($i=0; $i < count($name_arr); $i++){
            //echo "$name_arr[$i] => $mail_arr[$i] <br />";
            $contact = new EmailContacts($name_arr[$i]);
            $contact->appendEmail($mail_arr[$i]);
            array_push($email_contacts, $contact);
        }
        return $email_contacts;
    }

}

function main(){
    $mailyeah = new MAILYEAH();
    $mailyeah->getAddressList("xxx@yeah.net", "xxx");
}

?>