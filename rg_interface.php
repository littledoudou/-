<?php
require 'rg_responsfunc.php';

define("TOKEN", "rgsjtu");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();

?>
