<?php

//include_once(dirname(__FILE__).'/utils/common.php');

function get_contacts($username, $password)
{
    include_once(dirname(__FILE__).'/utils/config.php');
    list($name, $domain) = explode('@', $username);
    $class = $CLASS_DICT[strtolower($domain)][0];
    //$class != NULL or die("Sorry Not Support $domain Domain Now.");
    if($class == NULL) return array();
    
    /// Start process
    include_once($class."/get.php");
    
    $class_name = strtoupper($class);
    $obj = new $class_name;
    return $obj->getAddressList($username, $password);
}

?>
