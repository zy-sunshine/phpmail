<?php
function postmail_worofo_com($to,$subject = "",$body = ""){
    //$to 表示收件人地址 $subject 表示邮件标题 $body表示邮件正文
    //error_reporting(E_ALL);
    //error_reporting(E_STRICT);
    date_default_timezone_set("Asia/Shanghai");//设定时区东八区
    require_once('class.phpmailer.php');
    include("class.smtp.php");
    $mail             = new PHPMailer(); //new一个PHPMailer对象出来
    $body             = eregi_replace("[\]",'',$body); //对邮件内容进行必要的过滤
    $mail->CharSet ="UTF-8";//设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP(); // 设定使用SMTP服务
    $mail->SMTPDebug  = 1;                     // 启用SMTP调试功能
                                           // 1 = errors and messages
                                           // 2 = messages only
    $mail->SMTPAuth   = true;                  // 启用 SMTP 验证功能
    //$mail->SMTPSecure = "ssl";                 // 安全协议
    $mail->Host       = "mail.worofo.com";      // SMTP 服务器
    $mail->Port       = 25;                   // SMTP服务器的端口号
    //$mail->Port       = 465;                   // SMTP服务器的端口号
    $mail->Username   = "admin";  // SMTP服务器用户名
    $mail->Password   = "oceanko789";            // SMTP服务器密码
    $mail->SetFrom('admin@worofo.com', 'Admin worofo.com');
    $mail->AddReplyTo("admin@worofo.com","Admin worofo.com");
    $mail->Subject    = $subject;
    $mail->AltBody    = "To view the message, please use an HTML compatible email viewer! - From www.worofo.com"; // optional, comment out and test
    $mail->MsgHTML($body);
    $address = $to;
    $mail->AddAddress($address, $address);
    //$mail->AddAttachment("images/phpmailer.gif");      // attachment
    //$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment
    if(!$mail->Send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    } else {
        echo "Message sent success!";
    }
}
if (array_key_exists('address', $_POST)){
    $address = $_POST["address"];
    $subject = $_POST["subject"];
    $content = $_POST["content"];
    postmail_worofo_com($address, $subject, $content);
}
?>
<html>
<head>
<title>post mail</title>
</head>
<body>
    <form method="post" action="">
        <span>Mail To:
        <input id="address" name="address" type="text" />
        <span>Subject:</span>
        <input id="subject" name="subject" type="text" />
        <p>
        <span>Content:</span>
        <textarea id="content" name="content" clos="80" rows="7"></textarea>
        <input type="submit" value="Submit" />
        </p>
    </form>
</body>
</html>

