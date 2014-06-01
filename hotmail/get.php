<?php
include_once(dirname(__FILE__).'/msn_contact_grab.class.php');
include_once(dirname(__FILE__).'/../utils/common.php');
class HOTMAIL
{
    function __construct()
    {
        $this->msn = new msn;
    }
    public function login($username, $password)
    {
        return $this->msn->connect($username, $password);
    }
    public function getAddressList($username, $password)
    {
        if (!$this->login($username, $password))
        {
            return FALSE;
        }
        $this->msn->rx_data();
        $this->msn->process_emails();
        $returned_emails = $this->msn->email_output;
        return $this->standardFormat($returned_emails);
    }
    private function standardFormat($contents)
    {
        $email_contacts = array();
        foreach($contents as $c){
            $contact = new EmailContacts($c[1]);
            $contact->appendEmail($c[0]);
            array_push($email_contacts, $contact);
        }
        return $email_contacts;
    }

}

function main(){
    $hotmail = new HOTMAIL();
    print_address_list($hotmail->getAddressList("xxx@hotmail.com", "xxx"));
}
?>