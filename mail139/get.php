<?php
include_once(dirname(__FILE__).'/../utils/common.php');

class MAIL139
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
        $step1 = $this->httpCurl->getNew('https://mail.10086.cn/Login/Login.ashx');
        $postdata = array("UserName" => $user,"Password" => $password, 'VerifyCode' => '');
        $step1->setPostData($postdata);
        $step1->setOptFollowLocation(true);
        $contents = $step1->request();
        //die($step1->getHeaderString());
        preg_match('/<META HTTP-EQUIV=REFRESH CONTENT="0;url=(.*?)">/',$contents,$m);
        $url = $m[1];
        $urlobj = new Url($url);
        $params = $urlobj->getParams();
        $this->sid = $params['sid'];
        return true;
    }
    public function getAddressList($username, $password)
    {
        if (!$this->login($username, $password))
        {
            return FALSE;
        }
        $cookie = Cookie::parseFromFile($this->httpCurl->getCookieFile());
        #$url = 'http://g3.mail.10086.cn/ServiceAPI/getmaindatarm.ashx?sid='.$this->sid.'&uid='.$this->user.'&rnd=0.7252342673805435';
        #$this->httpCurl->getNew($url)->request();
        #$url = 'http://g3.mail.10086.cn/serviceapi/GetShuokeCount.ashx?sid='.$this->sid.'&r=0.7254430882814118&mobile='.$this->user.'&lasttime=';
        #$this->httpCurl->getNew($url)->request();
        $url = "http://g3.mail.10086.cn/addr/apiserver/GetContactsDataByJs.ashx?sid=".$this->sid."&rnd=0.7460640215841737";
             //'http://g3.mail.10086.cn/addr/apiserver/GetContactsDataByJs.ashx?sid=MTMzMTQwNzM3OTAwMDY4MjQzODM0MAAA000003&rnd=0.7460640215841737'
        $step3 = $this->httpCurl->getNew($url);
        $contents = $step3->request();
        return $this->standardFormat($contents);
    }
    private function standardFormat($contents)
    {
        #$contents = 'GetUserAddrDataResp={"ResultCode":"0","ResultMsg":"successful","TotalRecord":"1","UserNumber":"8618601785004","TotalRecordGroup":"5","TotalRecordRelation":"0","Group":[{"gd":"825043521","gn":"\u4eb2\u4eba","cn":"0"},{"gd":"825043524","gn":"\u540c\u4e8b","cn":"0"},{"gd":"825043522","gn":"\u540c\u5b66","cn":"0"},{"gd":"825043525","gn":"\u5ba2\u6237","cn":"0"},{"gd":"825043523","gn":"\u670b\u53cb","cn":"0"}],"Contacts":[{"sd":"825043543","c":"\u0078\u0077\u006f\u006c\u0066","y":"xwolfx@gmail.com","b3":"X","d2":"xwolf","d3":"xwolf"}, {"sd":"825043543","c":"\u0078\u0077\u006f\u006c\u0066","y":"xwolfx1@gmail.com","b3":"X","d2":"xwolf","d3":"xwolf"}],GroupList:[],LastContacts:[],CloseContacts:[],BirthdayContacts:[]';
        $email_contacts = array();
        
        preg_match_all('/,"c":"(.*?)","y":"(.*?)"/', $contents, $m);
        for($i=0; $i < count($m[1]); $i++){
            //print $m[1][$i]."<br />";
            //print $m[2][$i]."<br />";
            
            $contact = new EmailContacts(unicode2string($m[1][$i]));
            $contact->appendEmail($m[2][$i]);
            array_push($email_contacts, $contact);

        }
        return $email_contacts;
    }

}

function main(){
    $mail139 = new MAIL139();
    $mail139->getAddressList("xxx@139.com", "xxx");
    #$mail139->standardFormat("");
}
?>