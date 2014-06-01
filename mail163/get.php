<?php
include_once(dirname(__FILE__).'/../utils/common.php');
Class MAIL163
{
    function __construct()
    {
        $this->httpCurl = new HttpCurl();
    }
    private function login($username, $password)
    {
        $step1 = $this->httpCurl->getNew('http://reg.163.com/logins.jsp?type=1&product=mail163&url=http://entry.mail.163.com/coremail/fcg/ntesdoor2?lightweight%3D1%26verifycookie%3D1%26language%3D-1%26style%3D1');
        $postdata = array('username'=> $username, 'password'=> $password, 'type'=> 1);
        $step1->setPostData($postdata);
        $contents = $step1->request();
        //put_contents("content.dbg", $contents);
        if (strpos($contents, "登录成功，正在跳转...") !== false){
            //echo "Login Success!";
            return 1;
        }else{
            //echo "Login Failed!";
            return 0;
        }
    }
    public function getAddressList($username, $password)
    {
        if (!$this->login($username, $password))
        {
            return FALSE;
        }
        // Get contacts list
        $mailaddr = new EmailAddress($username);
        $user = $mailaddr->getUser();
        $url = 'http://entry.mail.163.com/coremail/fcg/ntesdoor2?username='.$user.'&lightweight=1&verifycookie=1&language=-1&style=1';
        $step2 = $this->httpCurl->getNew($url);
        $step2->request();
        $locurl = new Url($step2->getLocation());
        $sid = $locurl->getParams();
        $sid = $sid["sid"];

        //$cookiedict = Cookie::parseFromFile($step2->getCookieFile());  // Notice this sid is not useful for next request.
        //print_r($cookiedict);

        $url = 'http://twebmail.mail.163.com/js4/s?sid='.$sid.'&func=global:sequential&showAd=false&userType=browser&uid='.$username;
        $step3 = $this->httpCurl->getNew($url);
        $postdata = array(
            'func' => 'global:sequential',
            'showAd' => 'false',
            'sid' => 'qACVwiwOfuumHPdcYqOOUTAjEXNbBeAr',
            'uid' => $username,
            'userType' => 'browser',
            'var' => urlencode('<!--?xml version="1.0"?--><object><array name="items"><object><string name="func">pab:searchContacts</string><object name="var"><array name="order"><object><string name="field">FN</string><boolean name="desc">false</boolean><boolean name="ignoreCase">true</boolean></object></array></object></object><object><string name="func">pab:getAllGroups</string></object></array></object>')
            );
        $step3->setPostData($postdata);
        $res = $step3->request();
        ////put_contents("content1.dbg", $res);

        //$res = get_contents("content1.dbg");
        //print $res;
        return $this->standardFormat($res);
    }
    public function standardFormat($res)
    {
        $email_contacts = array();
        $html = str_get_html($res);
        foreach(array_slice($html->find("object"), 1, -1) as $object){
            //print "".$array;
            //print "<br />==========<br />";
            $email = "";
            $name = "";
            foreach($object->children() as $objchild){
                //print $objchild->name;
                if($objchild->name == "EMAIL;PREF"){
                    //print "<br />".$objchild->innertext."<br />";
                    $email = $objchild->innertext;
                }else if($objchild->name == "FN"){
                    //print "<br />".$objchild->innertext."<br />";
                    $name = $objchild->innertext;
                }
            }
            $contact = new EmailContacts($name);
            $contact->appendEmail($email);
            array_push($email_contacts, $contact);
        }
        return $email_contacts;
    }
}
function main()
{
    $mail163 = new MAIL163();
    $mail163->getAddressList("xxx@163.com", "xxxx");
}

main();
?>
