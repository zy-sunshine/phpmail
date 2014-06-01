<?php
include_once(dirname(__FILE__).'/../utils/common.php');

class MAIL21CN
{
    function __construct()
    {
        $this->httpCurl = new HttpCurl();
    }
    public function login($username, $password)
    {
        $emailobj = new EmailAddress($username);
        $user = $emailobj->getUser();
        $this->user = $user;
        $step1 = $this->httpCurl->getNew('http://mail.21cn.com');
        $contents = $step1->request();
        
        $step2 = $step1->getNew('http://fmail.21cn.com/freeinterface/maillogin.jsp?jsoncallback=jsonp1331411595185&loginName='.$user.'&UserName='.$user.'&passwd='.$password);
        $contents = $step2->request();
        
        $step3 = $step2->getNew('http://hermes.webmail.21cn.com/webmail/login.perform?key=1');
        $step3->setOptFollowLocation(true);
        $contents = $step3->request();
        $location = $step3->getLocation();
        
        $urlobj = new Url($location);
        $this->mailhost = $urlobj->getHost();
        $foward_url = $this->mailhost.'/webmail/logon.do?uud=1';
        $step4 = $step3->getNew($foward_url);
        $contents = $step3->request();
        //put_contents("content.dbg", $contents);
        /*
        if (strpos($contents, 'iframe') !== false){
            echo "Login Success!";
            return true;
        }else{
            echo "Login Failed!";
            return false;
        }
        */
        return true;
    }
    public function getAddressList($username, $password)
    {
        if (!$this->login($username, $password))
        {
            return FALSE;
        }
        $url = $this->mailhost.'/webmail/addressBookList.do?groupId=-1&page=1';

        $contents = $this->httpCurl->getNew($url)->request();
        //put_contents("result.dbg", $contents);
        //$contents = get_contents("result.dbg");
        
        return $this->standardFormat($contents);
    }
    private function standardFormat($contents)
    {
        #$contents = 'GetUserAddrDataResp={"ResultCode":"0","ResultMsg":"successful","TotalRecord":"1","UserNumber":"8618601785004","TotalRecordGroup":"5","TotalRecordRelation":"0","Group":[{"gd":"825043521","gn":"\u4eb2\u4eba","cn":"0"},{"gd":"825043524","gn":"\u540c\u4e8b","cn":"0"},{"gd":"825043522","gn":"\u540c\u5b66","cn":"0"},{"gd":"825043525","gn":"\u5ba2\u6237","cn":"0"},{"gd":"825043523","gn":"\u670b\u53cb","cn":"0"}],"Contacts":[{"sd":"825043543","c":"\u0078\u0077\u006f\u006c\u0066","y":"xwolfx@gmail.com","b3":"X","d2":"xwolf","d3":"xwolf"}, {"sd":"825043543","c":"\u0078\u0077\u006f\u006c\u0066","y":"xwolfx1@gmail.com","b3":"X","d2":"xwolf","d3":"xwolf"}],GroupList:[],LastContacts:[],CloseContacts:[],BirthdayContacts:[]';
        $html = str_get_html($contents);
        $email_contacts = array();
        foreach($html->find("tr[class=TRxg]") as $node){
            $name = $node->children(1)->children(0)->innertext;
            $email = $node->children(2)->children(0)->innertext;
            
            $contact = new EmailContacts($name);
            $contact->appendEmail($email);
            array_push($email_contacts, $contact);
        }
        return $email_contacts;
    }
}

function main(){
    $mail21cn = new MAIL21CN();
    $mail21cn->getAddressList("xxx@21cn.com", "xxx");
}
?>