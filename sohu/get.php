<?php
include_once(dirname(__FILE__).'/../utils/common.php');
Class SOHU
{
    function __construct()
    {
        $this->httpCurl = new HttpCurl();
    }
    private function login($username, $password)
    {
        $url = "https://passport.sohu.com/sso/login.jsp?userid=".$username."&password=".md5($password)."&appid=1000&persistentcookie=0&s=".time()."&b=2&w=1440&pwdtype=1";
        $step1 = $this->httpCurl->getNew($url);
        $contents = $step1->request();
        //put_contents("content.dbg", $contents);
        if ( strpos( $contents, "success" ) === false )
        {
            echo "Login Failed!";
            return false;
        }else{
             //echo "Login Success!";
            return true;
        }
    }
    public function getAddressList($username, $password)
    {
        if (!$this->login($username, $password))
        {
            return FALSE;
        }
        // Get contacts list

        $url = 'http://mail.sohu.com/bapp/97/main#addressList';
        $step2 = $this->httpCurl->getNew($url);
        $contents = $step2->request();
        
        //$contents = get_contents("content.dbg");
        return $this->standardFormat($contents);
    }
    public function standardFormat($contents)
    {
        $email_contacts = array();
        preg_match('/var ADDRESSES = \'.*"contact": (\[.*?\]),/', $contents, $m);
        //var_dump($m[1]);
        //var_dump(json_decode( '[ {"pinyin": "liu", "nickname": "\u5218\u535a\u8d85", "id": 22106478, "email": "liubochao988@163.com"}]'));
        $jsonobj = json_decode($m[1]);
        foreach($jsonobj as $elem){
            //print "$elem->nickname <br />";
            //print "$elem->email <br />";
            $contact = new EmailContacts($elem->nickname);
            $contact->appendEmail($elem->email);
            array_push($email_contacts, $contact);
        }
        return $email_contacts;
    }
}
function main()
{
    $sohu = new SOHU();
    $sohu->getAddressList("xxx@sohu.com", "xxx");
}

main();
?>
