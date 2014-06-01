<?php
include_once(dirname(__FILE__).'/../utils/common.php');
class MAIL126
{
    function __construct()
    {
        $this->httpCurl = new HttpCurl();
    }
    public function login($username, $password)
    {
        $step1 = $this->httpCurl->getNew('https://reg.163.com/logins.jsp?type=1&product=mail126&url=http://entry.mail.126.com/cgi/ntesdoor?hid%3D10010102%26lightweight%3D1%26verifycookie%3D1%26language%3D0%26style%3D-1');
        $postdata = array('username'=> $username, 'password'=> $password);
        $step1->setPostData($postdata);
        $contents = $step1->request();
        //put_contents("content.dbg", $contents);
        preg_match("/replace\(\"(.*?)\"\)\;/", $contents, $m);
        $url_redirect = $m[1];
        $step2 = $step1->getNew($url_redirect);
        $contents = $step2->request();
        //put_contents("content2.dbg", $contents);
        if (strpos($contents, "登录成功，正在跳转...") !== false){
            //echo "Login Success!";
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

        $step3 = $this->httpCurl->getNew("http://entry.mail.126.com/cgi/ntesdoor?hid=10010102&lightweight=1&verifycookie=1&language=0&style=-1&username=".$username);
        $step3->request();
        $locurl = $step3->getLocation();
        $locurl = new Url($locurl);
        $sid = $locurl->getParams();
        $sid = $sid["sid"];
        //var_dump($sid);
        $headerstr = $step3->getHeaderString();
        preg_match_all('/Location:\s*(.*?)\r\n/i', $headerstr, $regs);
        $refer = $regs[1][0];
        preg_match_all('/http\:\/\/(.*?)\//i', $headerstr, $regs);
        $host = $regs[1][0];
        //var_dump("sid: $sid refer: $refer host: $host");
        if (!$sid || !$refer || !$host){
            return -1;
        }
        $url = "http://tg1a64.mail.126.com/jy3/address/addrlist.jsp?sid=".$sid."&gid=all";
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
    $mail126 = new MAIL126();
    $mail126->getAddressList("xxx@126.com", "xxx");
}
?>