<?php
include_once(dirname(__FILE__).'/../utils/common.php');
class TOM
{
    function __construct()
    {
        $this->httpCurl = new HttpCurl();
    }
    public function login($username, $password)
    {
        $emailobj = new EmailAddress($username);
        $user = $emailobj->getUser();
        $step1 = $this->httpCurl->getNew('http://login.mail.tom.com/cgi/login');
        $postdata = array('type' => 0,
            'user' => $user,
            'in_username' => $username,
            'pass' => $password,
            'style' => 21,
            'verifycookie' => 'y'
        );

        $step1->setPostData($postdata);
        $step1->setOptFollowLocation(true);
        $contents = $step1->request();

        //put_contents("content.dbg", $contents);
        
        $locurl = $step1->getLocation();
        $locurl = new Url($locurl);
        $sid = $locurl->getParams();
        $sid = $sid["sid"];
        $this->url_contacts = $locurl->getHost().'/cgi/ldvcapp?funcid=prtsearchres&sid='.$sid.'&showlist=all&tempname=address%2Faddress.htm';

        return true;
    }
    public function getAddressList($username, $password)
    {
        if (!$this->login($username, $password))
        {
            return FALSE;
        }

        $step2 = $this->httpCurl->getNew($this->url_contacts);

        $contents = $step2->request();

        //put_contents("contacts.html.dbg", $contents);
        //$contents = get_contents("contacts.html.dbg");

        return $this->standardFormat($contents);
    }
    private function standardFormat($contents)
    {
        $email_contacts = array();
        $name_arr = array();
        $mail_arr = array();
        $html = str_get_html($contents);
//<td class="Addr_Td_Checkbox"><input type="checkbox" name="chk-_0_0_" value="xwolfx%40gmail.com"  ></td><td class="Addr_Td_Name"><a href="ldvcapp?funcid=prtsearchres&sid=YAZvcUGubqUAYaFy&iid=0&mode=1&emptymode=1&print.x=1&tempname=address/add.htm">xwolfx</a></td><td class="Addr_Td_Address"><a href="ldvcapp?funcid=prtsearchres&sid=YAZvcUGubqUAYaFy&iid=0&mode=1&emptymode=1&print.x=1&tempname=address/add.htm">xwolfx@gmail.com</a></td><td class="Addr_Td_Opt">[<a href="/coremail/fcg/ldmmapp?sid=YAZvcUGubqUAYaFy&amp;funcid=compose&amp;to=%22xwolfx%22%20%3Cxwolfx%40gmail.com%3E" target="_parent">写信</a>] [<a href="ldsrchapp?funcid=srchhand&sid=YAZvcUGubqUAYaFy&word=xwolfx%40gmail.com&fromonly=yes&fid=0&subfolder=yes&perfectmatch=1&rtnurl=" target="_parent">列出来信</a>] [<a href="ldvcapp?funcid=loadiadd&iid=0&sid=YAZvcUGubqUAYaFy&ifirstv=&lid=&modify.x=1">编辑</a>]</td>

        foreach($html->find("td[class=Addr_Td_Name]") as $node){
            array_push($name_arr, $node->children(0)->innertext);
            //echo $node->children(0)->innertext."<br />";
        }
        foreach($html->find("td[class=Addr_Td_Address]") as $node){
            array_push($mail_arr, $node->children(0)->innertext);
            //echo $node->children(0)->innertext."<br />";
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
    $tom = new TOM();
    $tom->getAddressList("xxx@tom.com", "xxx");
}
?>
