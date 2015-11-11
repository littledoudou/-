//  Created by 邢俊劼 on 15/11/4.
//  Copyright © 2015年 邢俊劼. All rights reserved.

<?php

class wechatCallbackapiTest
{

    public function responseMsg()
    {

        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){
                libxml_disable_entity_loader(true);
                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $time = time();
                $msgType = $postObj->MsgType;
                $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
                 //检测是否是已注册用户
                if (!$this->checkRegister($fromUsername)) {//未注册
                    if ($keyword == "注册"){
                        $contentStr = $this->register($fromUsername);
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                        exit;
                    }
                    $contentStr = "系统发现您尚未注册，请发送[注册]以开启功能！";
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                    exit;
                }
                //已注册用户
                //检测status
                if ($this->checkInform($fromUsername,'STATUS') == "dictation") {
                    //若status＝"dictation",判断答案，出题
                    if ($keyword == "0") {
                        $this->updateInform($fromUsername,'STATUS','normal');
                        $this->updateInform($fromUsername,'DONE',0);
                        $this->updateInform($fromUsername,'CORRECT',0);
                        $this->updateInform($fromUsername,'NowID',0);
                        $name = $this->checkInform($fromUsername,'Name');
                        $contentStr = $name.":\n\t\t\t您已退出此次默写！";
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                        exit; 
                    }
                    //call function
                $contentStr = $this->judgeAndSet($fromUsername,$keyword);
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
                exit;   
                }
                elseif ($this->checkInform($fromUsername,'STATUS') == "unnamed") {
                    //set name
                $this->updateInform($fromUsername,'Name',$keyword);
                $this->updateInform($fromUsername,'STATUS','normal');

                $contentStr = "$keyword:\n\t\t\t恭喜你，注册完毕！\n输入cmd以查看功能！";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
                exit;
                }

                if ($keyword == "默写") {
                    $this->updateInform($fromUsername,'STATUS','dictation');
                    $contentStr = $this->judgeAndSet($fromUsername,$keyword);
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                    exit;
                }


                if(!empty( $keyword ))
                {
                    if ($msgType == "text") {                        
                        $contentStr = $this->makeContent($keyword,$fromUsername);
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                        exit;
                        }                    
                }                      
        }
    }

    private function judgeAndSet($id,$keyword){
            $content = "";
            $done = $this->checkInform($id,'DONE');
            $correct = $this->checkInform($id,'CORRECT');
            $nowID = $this->checkInform($id,'NowID');
            if ($done >= 0 && $done <= 9){
            if ($done != 0) {
                if ($keyword == $this->getWord($nowID,'English')) {
                    $content = "恭喜您答对了第".$done."题！\n";
                    $this->updateInform($id,'CORRECT',$correct+1);
                }
                else {
                    $word = $this->getWord($nowID,'English');
                    $content = "第".$done."题答错了哟～\n答案:\n".$word."\n";
                }
            }
            $tiid = rand($min = 1,$max = 3644);
            $chinese = $this->getWord($tiid,'Chinese');
            $done = $done+1;
            $content = $content."第".$done."题:\n";
            $content = $content.$chinese."请回答！(or input [0] to exit)";
            $this->updateInform($id,'NowID',$tiid);
            $this->updateInform($id,'DONE',$done);
            return $content;
        }
        elseif ($done == 10) {
            if ($keyword == $this->getWord($nowID,'English')) {
                    $content = "恭喜您答对了第".$done."题！\n";
                    $this->updateInform($id,'CORRECT',$correct+1);
                }
                else {
                    $word = $this->getWord($nowID,'English');
                    $content = "第".$done."题答错了哟～\n答案：\n".$word."\n";
                }
                $correct = $this->checkInform($id,'CORRECT');
                $name = $this->checkInform($id,'Name');
                $content = $content."\n".$name."\n\t\t\t您已完成此次默写！\n正确率：".$correct."/10\n";
                $this->updateInform($id,'STATUS','normal');
                $this->updateInform($id,'DONE',0);
                $this->updateInform($id,'CORRECT',0);
                $this->updateInform($id,'NowID',0);
                return $content;
        }

            

    }

    private function updateInform($id,$key,$content){
        //连接数据库
            $hostname = SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT;
            $dbuser = SAE_MYSQL_USER;
            $dbpass = SAE_MYSQL_PASS;
            $dbname = SAE_MYSQL_DB;
            $link = mysql_connect($hostname, $dbuser, $dbpass);
            if (!$link) {
                die('Could not connect: ' . mysql_error());
            }

            mysql_select_db("app_sjturg",$link);
            mysql_query("set names utf8",$link);
            if ($key == "Name"){
            mysql_query("UPDATE Users SET Name = '{$content}' WHERE WeChatID = '{$id}'",$link);
            }
            elseif ($key == "DONE"){
            mysql_query("UPDATE Users SET DONE = '{$content}' WHERE WeChatID = '{$id}'",$link);
            }
            elseif ($key == "CORRECT"){
            mysql_query("UPDATE Users SET CORRECT = '{$content}' WHERE WeChatID = '{$id}'",$link);
            }
            elseif ($key == "NowID"){
            mysql_query("UPDATE Users SET NowID = '{$content}' WHERE WeChatID = '{$id}'",$link);
            }
            elseif ($key == "STATUS"){
            mysql_query("UPDATE Users SET STATUS = '{$content}' WHERE WeChatID = '{$id}'",$link);
            }
            mysql_close($link);
    }

    private function checkInform($id,$key){
        //连接数据库
            $hostname = SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT;
            $dbuser = SAE_MYSQL_USER;
            $dbpass = SAE_MYSQL_PASS;
            $dbname = SAE_MYSQL_DB;
            $link = mysql_connect($hostname, $dbuser, $dbpass);
            if (!$link) {
                die('Could not connect: ' . mysql_error());
            }

            mysql_select_db("app_sjturg",$link);
            mysql_query("set names utf8",$link);
            $result = mysql_query("SELECT * FROM `Users` WHERE WeChatID='{$id}'",$link);
            $row = mysql_fetch_array($result);
            return $row[$key];
    }

    private function getWord($id,$key){
        //连接数据库
            $hostname = SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT;
            $dbuser = SAE_MYSQL_USER;
            $dbpass = SAE_MYSQL_PASS;
            $dbname = SAE_MYSQL_DB;
            $link = mysql_connect($hostname, $dbuser, $dbpass);
            if (!$link) {
                die('Could not connect: ' . mysql_error());
            }

            mysql_select_db("app_sjturg",$link);
            mysql_query("set names utf8",$link);
            $result = mysql_query("SELECT * FROM `WordList` WHERE id='{$id}'",$link);
            $row = mysql_fetch_array($result);
            mysql_close($link);
            return $row[$key];
    }



    private function checkRegister($id){
        //连接数据库
            $hostname = SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT;
            $dbuser = SAE_MYSQL_USER;
            $dbpass = SAE_MYSQL_PASS;
            $dbname = SAE_MYSQL_DB;
            $link = mysql_connect($hostname, $dbuser, $dbpass);
            if (!$link) {
                die('Could not connect: ' . mysql_error());
            }

            mysql_select_db("app_sjturg",$link);
        $result = mysql_query("SELECT * FROM `Users` WHERE WeChatID='{$id}'",$link);
            if(!mysql_num_rows($result))
            {
                mysql_close($link);
                return false;
            }
            else{
                mysql_close($link);
                return true;
            }

    }

    private function register($id){
        //连接数据库
            $hostname = SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT;
            $dbuser = SAE_MYSQL_USER;
            $dbpass = SAE_MYSQL_PASS;
            $dbname = SAE_MYSQL_DB;
            $link = mysql_connect($hostname, $dbuser, $dbpass);
            if (!$link) {
                die('Could not connect: ' . mysql_error());
            }

            mysql_select_db("app_sjturg",$link);
            mysql_query("INSERT INTO Users values('{$id}','unnamed',0,0,'',0)",$link);
            mysql_close($link);
            return "wechatID:\n$id\n请设置您的用户名.";
    }

    private function makeContent($keyword,$id) {
       if ($keyword == "cmd") {
          return "命令：\n[默写]:进入默写系统；\n[注销]:注销帐号";
       }
       elseif ($keyword == "注销") {
           //连接数据库
            $hostname = SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT;
            $dbuser = SAE_MYSQL_USER;
            $dbpass = SAE_MYSQL_PASS;
            $dbname = SAE_MYSQL_DB;
            $link = mysql_connect($hostname, $dbuser, $dbpass);
            if (!$link) {
                die('Could not connect: ' . mysql_error());
            }

            mysql_select_db("app_sjturg",$link);
            mysql_query("DELETE FROM Users WHERE WeChatID = '{$id}'",$link);
            mysql_close($link);
            return "WeChatID:\n".$id."\n已注销！";
       }
       else {
        return "输入cmd以获取命令";
       }
    }

        private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
                
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}


?>
