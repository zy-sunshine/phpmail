<?php
include_once(dirname(__FILE__).'/../utils/common.php');
class MAILQQ
{
    function __construct()
    {
        $this->httpCurl = new HttpCurl();
    }
    public function login($username, $password)
    {
        $emailobj = new EmailAddress($username);
        $user = $emailobj->getUser();
        $step1 = $this->httpCurl->getNew('http://w39.mail.qq.com/cgi-bin/login');
        $postdata = array('f' => 'xhtmlmp',
            'tfcont' => '',
            'uin' => $user,
            'aliastype' => '@qq.com',
            'pwd' => $password,
            'mss' => '1
'
            );
            #btlogin ç»å½
        $step1->setPostData($postdata);
        $contents = $step1->request();
        //put_contents("content.dbg", $contents);

        //$contents = get_contents("content.dbg");

        preg_match("/url=(http:\/\/.*?)\"/", $contents, $m);
        //var_dump($m[1]);

        $url_redirect = $m[1];

        $urlobj = new Url($url_redirect);
        $params = $urlobj->getParams();
        $this->sid = $params['sid'];
        $this->mailhost = $urlobj->getHost();
        $step2 = $step1->getNew($url_redirect);
        $contents = $step2->request();
        //put_contents("content2.dbg", $contents);
        if (strpos($contents, "退出") !== false){
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

        $step3 = $this->httpCurl->getNew($this->mailhost."/cgi-bin/addr_listall?sid=".$this->sid."&flag=star&s=search&folderid=all&pagesize=10&from=today&fun=slock&page=0&topmails=0&t=addr_listall&loc=today,,,158'
");
        $contents = $step3->request();
        //put_contents("content_contacts.dbg", $contents);
        return $this->standardFormat($contents);
    }
    private function standardFormat($contents)
    {
        $email_contacts = array();
        $html = str_get_html($contents);
        foreach($html->find("a") as $node){
            //var_dump($node->innertext);
            if(preg_match("/(.*?)&lt;(.*?)&gt;/", $node->innertext, $m)){
                //var_dump($m);
                $contact = new EmailContacts($m[1]);
                $contact->appendEmail($m[2]);
                array_push($email_contacts, $contact);
            }
        }
        return $email_contacts;
    }

}

function main(){
    $mailqq = new MAILQQ();
    $mailqq->getAddressList("xxx@qq.com", "xxx");
}
main();
?>