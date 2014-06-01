<html>
<head>
<link rel="stylesheet" href="" type="text/css" />
<script type="text/javascript" src=""></script>
<title></title>
</head>
<body>
    <p>
    Get Contacts:
    </p>
    <form action="" method="get">
        <span>Username:</span>
        <input id="username" name="username" type="text" />
        <span>Password:</span>
        <input id="password" name="password" type="password" />
        <input type="submit" value="Submit" />
    </form>
    <?php
    include_once(dirname(__FILE__).'/ContactsManager.php');
    if(array_key_exists('username', $_GET) && array_key_exists('password', $_GET)){
        $res = get_contacts($_GET["username"], $_GET["password"]);
        print_address_list($res);
    }
    ?>
    <div id="description">
        <h3>Current support mail type: </h3>
        <ul>
        <?php
        include(dirname(__FILE__).'/utils/config.php');
        foreach($CLASS_DICT as $key => $value){
            echo '<li>'.$key;
            if($value[1] != ''){
                echo '&nbsp&nbsp&nbspTest Account: '. $value[1] . "&nbsp&nbsp&nbsp&nbsp password: ". $value[2];
            }
            echo '</li>';
        }
        ?>
        </ul>
    </div>
</body>
</html>

