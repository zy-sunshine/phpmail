<?php
include_once(dirname(__FILE__).'/../utils/common.php');
class MAILSINA
{
    function __construct()
    {
        $this->httpCurl = new HttpCurl();
    }
    public function login($username, $password)
    {
        $url = 'https://login.sina.com.cn/sso/login.php';
        $step1= $this->httpCurl->getNew($url);
        $postdata = array(
            'username' => $username,
            'password' => $password,
            'entry' => 'freemail',
            'gateway' => 0,
            'encoding' => 'UTF-8',
            'url' => 'http://mail.sina.com.cn/',
            'returntype' => 'META',
            );
        $step1->setPostData($postdata);
        $step1->setOptFollowLocation(true);
        $c = $step1->request();
        preg_match("/replace\(\"(.*?)\"\)\;/", $c, $m);
        $url = $m[1];
        $step2 = $step1->getNew($url);
        $step2->setOptFollowLocation(true);
        $c = $step2->request();
        
        $step3 = $step2->getNew('http://mail.sina.com.cn/cgi-bin/login.php');
        $step3->setOptFollowLocation(true);
        $c = $step3->request();
        
        $urlobj = new Url($step3->getLocation());
        $host = $urlobj->getHost();
        
        $url = $host.'/classic/addr_member.php?act=list&sort_item=letter&sort_type=desc';
        $step4 = $step3->getNew($url);
        $this->contents = $step4->request();
        
        //put_contents("content.dbg", $c);
        //die($step3->getHeaderString());
        return true;
    }
    public function getAddressList($username, $password)
    {
        if (!$this->login($username, $password))
        {
            return FALSE;
        }

        return $this->standardFormat($this->contents);
    }
    private function standardFormat($contents)
    {
        $email_contacts = array();
        $jsonobj = json_decode($contents);
        foreach($jsonobj->data->contact as $c){
            //echo "$c->name => $c->email <br />";
            $contact = new EmailContacts($c->name);
            $contact->appendEmail($c->email);
            array_push($email_contacts, $contact);
        }
        return $email_contacts;
    }

}

function main(){
    $a=new MAILSINA();
    $tmp=$a->getAddressList("xxx@sina.com","xxx");
    var_dump($tmp);
}

?>