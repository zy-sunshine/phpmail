<?php
include_once(dirname(__FILE__).'/../utils/common.php');

class GMAIL
{

    function __construct()
    {
        $this->httpCurl = new HttpCurl();
    }

    public function login($username, $password)
    {
        $step1 = $this->httpCurl->getNew("https://accounts.google.com/ServiceLoginAuth");        
        $contents = $step1->request();

        $name = array('dsh','timeStmp','secTok');
        $html = new HtmlParser($contents);
        //$html->getTagValueById('input', 'dsh');
        //return;

        foreach($name as $v) {
            $attrs = $html->getTagValueById('input', $v);
            $$v = $attrs['value'];
        }
        $server = 'mail';

        $attrs = $html->getTagValueByName('input', "GALX");
        $GALX = $attrs['value'];

        $timeStmp = time();

        $step2 = $step1->getNew("https://accounts.google.com/ServiceLoginAuth");
        $fields = "dsh=$dsh&GALX=$GALX&pstMsg=1&dnConn=&checkConnection=&checkedDomains=youtube&timeStmp=&secTok=$secTok&Email=".$username."&Passwd={$password}&signIn=Sign in&rmShown=1";
        //            timeStmp=$timeStmp&asts=&PersistentCookie=yes";
        $step2->setOptPostFields($fields);
        $str = $step2->request();
        //die($str);
        $check_cookie_url = $step2->getLocation();

        $step3 = $step2->getNew($check_cookie_url);
        
        $step3->setOptFollowLocation(true);
        $str2 = $step3->request();

        return TRUE;
    }
    
    public function getAddressList($username, $password)
    {
        if (!$this->login($username, $password))
        {
            return FALSE;
        }

        $step4 = $this->httpCurl->getNew("https://mail.google.com/mail/contacts/data/contacts?thumb=true&groups=true&show=ALL&enums=true&psort=Name&max=300&out=js&rf=&jsx=true");

        $step4->setOptFollowLocation(true);
        $contents = $step4->request();

        $contents = substr($contents, strlen('while (true); &&&START&&&'), -strlen('&&&END&&& '));
        return $this->standardFormat($contents);
    }
    public function standardFormat($res){
        $resObj = json_decode($res);

        $contacts = $resObj->Body->Contacts;
        $len = count($contacts);
        //var_dump($resObj->Body->Contacts[0]->Emails);
        $email_contacts = array();
        //var_dump($resObj);
        if($resObj->Success){
            for($i = 0; $i < $len; $i++){
                $emails = $contacts[$i]->Emails;
                $len_e = count($emails);
                if($len_e > 0){
                    $contact = new EmailContacts($contacts[$i]->DisplayName);
                    for($j = 0; $j < $len_e; $j++){
                        $contact->appendEmail($emails[$j]->Address);
                    }
                    if($contact->emailCounts()){
                        array_push($email_contacts, $contact);
                    }
                }
            }
        }
        return $email_contacts;
    }
}

function main()
{
    $gmail = new GMAIL;
    $name = 'xxx@gmail.com';
    $pass = 'xxxxx';
    //$gmail->login($name, $pass);
    $res = $gmail->getAddressList($name, $pass);
}

?>
