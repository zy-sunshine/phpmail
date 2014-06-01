<?php
include_once(dirname(__FILE__).'/../utils/common.php');
class YAHOO
{
    function __construct()
    {
        $this->httpCurl = new HttpCurl();
    }
    public function login($username, $password)
    {
        $step1 = $this->httpCurl->getNew('https://mlogin.yahoo.com/w/login');
        $contents = $step1->request();

        preg_match('<form action="(.*?)" method="post" id="LoginModel">', $contents, $m);
        $post_url = $m[1];

        $html = new HtmlParser($contents);

        $name = array('_authurl','_done','_sig', '_src', '_ts', '_crumb', '_pc', '_send_userhash', '_appdata', '_partner_ts', '_is_ysid', '_page', '_next', '__submit');
        foreach($name as $v) {
            $attrs = $html->getTagValueByName('input', $v);
            $$v = $attrs['value'];
            //echo "|".$v." -> ".$$v."|<br />";
        }
        $postdata = array("id" => $username, "password" => $password);
        foreach($name as $v){
            $postdata[$v] = $$v;
        }
        $step2 = $step1->getNew($post_url);
        $step2->setPostData($postdata);
        $step2->setOptFollowLocation(true);
        $contents = $step2->request();
        //put_contents("content.dbg", $contents);
        return true;
    }
    public function getAddressList($username, $password)
    {
//         $contents = get_contents("content.dbg");
//         $this->standardFormat($contents);
//         die("");
        if (!$this->login($username, $password))
        {
            return FALSE;
        }
        //$url = 'http://hk.m.yahoo.com/w/ygo-addressbook/contacts?.ts='.$_ts.'&.intl=hk&.lang=zh-hant-hk';
        //$step3 = $step2->getNew($url);
        //$contents = $step3->request();
        $url = 'http://mail.yahoo.com';
        $step3 = $this->httpCurl->getNew($url);
        $step3->setOptFollowLocation(true);
        $contents = $step3->request();

        //put_contents("content.dbg", $contents);

        //$contents = get_contents("content.dbg");
        preg_match('/servername:"(.*?)",.*wssid:"(.*?)",/', $contents, $m);
        $servername = $m[1];
        $wssid = $m[2];
        $url = 'http://'.$servername.'/yab-fe/mu/ContactListView.json';
        $step4 = $step3->getNew($url);
        $contents = $step4->request();
        //put_contents("content.dbg", $contents);
        return $this->standardFormat($contents);
    }
    private function standardFormat($contents)
    {
        $email_contacts = array();
        $jsonobj = json_decode($contents);
        //var_dump($jsonobj);
        //var_dump($jsonobj->contacts->contacts->contact);
        foreach($jsonobj->contacts->contacts->contact as $c){
            foreach($c->fields as $f){
                if($f->type == 'name'){
                    $name = $f->value->givenName.' '.$f->value->middleName.' '.$f->value->familyName;
                }else if($f->type == 'nickname'){
                    $nickname = $f->value;
                }else if($f->type == 'email'){
                    $email = $f->value;
                }
            }
            if(trim($name) == '') $name = $nickname;
//             echo "$name<br />";
//             echo "$email<br />";
            $contact = new EmailContacts($name);
            $contact->appendEmail($email);
            array_push($email_contacts, $contact);
        }
        return $email_contacts;
    }

}

function main(){
    $yahoo = new YAHOO();
    $yahoo->getAddressList("xxx@yahoo.com", "xxx");
}
?>