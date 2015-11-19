//  Created by 邢俊劼 on 15/11/19.
//  Copyright © 2015年 邢俊劼. All rights reserved.

<?php

class wechatCallbackapiTest
{

    public function responseMsg()
    {

        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        libxml_disable_entity_loader(true);
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $time = time();
        $msgType = $postObj->MsgType;


        //extract post data
        if (!empty($postStr)){

            if ($msgType == "event") {
                $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
                    $msgType = "text";
                    $contentStr = "欢迎关注本微信公众账号！\n请发送[注册]以注册成为用户！";
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
            }

            if ($msgType == "text"){
                
                $keyword = trim($postObj->Content);
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
                if ($this->getInform('WeChatID',$fromUsername,'STATUS','Users') == "setWordList") {
                    if ($keyword == '1') {
                        $this->updateInform('WeChatID',$fromUsername,'ListName','WordList','Users');
                    }
                    elseif ($keyword == '2') {
                        $this->updateInform('WeChatID',$fromUsername,'ListName','tofel','Users');
                    }
                    else {
                        $contentStr = "无目标选项，请重新选择！";
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                        exit; 
                    }
                    $this->updateInform('WeChatID',$fromUsername,'STATUS','normal','Users');
                    $contentStr = "词汇书设置完成～\n回复[默写]以开始！";
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                    exit;                     
                }

                if ($this->getInform('WeChatID',$fromUsername,'STATUS','Users') == "dictation") {
                    //若status＝"dictation",判断答案，出题
                    if ($keyword == "0") {
                        $this->updateInform('WeChatID',$fromUsername,'STATUS','normal','Users');
                        $this->updateInform('WeChatID',$fromUsername,'DONE',0,'Users');
                        $this->updateInform('WeChatID',$fromUsername,'CORRECT',0,'Users');
                        $this->updateInform('WeChatID',$fromUsername,'NowID',0,'Users');
                        $name = $this->getInform('WeChatID',$fromUsername,'Name','Users');
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
                elseif ($this->getInform('WeChatID',$fromUsername,'STATUS','Users') == "unnamed") {
                    //set name
                $this->updateInform('WeChatID',$fromUsername,'Name',$keyword,'Users');
                $this->updateInform('WeChatID',$fromUsername,'STATUS','normal','Users');

                $contentStr = "$keyword:\n\t\t\t恭喜你，注册完毕！\n输入cmd以查看功能！";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
                exit;
                }

                if ($keyword == "默写") {
                    $this->updateInform('WeChatID',$fromUsername,'STATUS','dictation','Users');
                    $contentStr = $this->judgeAndSet($fromUsername,$keyword);
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                    exit;
                }
                elseif ($keyword == "词汇表") {
                    $this->updateInform('WeChatID',$fromUsername,'STATUS','setWordList','Users');
                    $contentStr = "词汇表列表:\n[1]默认;\n[2]托福\n请回复数字以更改设置！";
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                    exit;
                }

                $contentStr = $this->makeContent($keyword,$fromUsername);
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
                exit;
             }                               
        }
    }

    private function judgeAndSet($id,$keyword){
            $content = "";
            $done = $this->getInform('WeChatID',$id,'DONE','Users');
            $correct = $this->getInform('WeChatID',$id,'CORRECT','Users');
            $nowID = $this->getInform('WeChatID',$id,'NowID','Users');
            $ListName = $this->getInform('WeChatID',$id,'ListName','Users');
            if ($done >= 0 && $done <= 9){
            if ($done != 0) {
                if ($keyword == $this->getWord($ListName,$nowID,'English')) {
                    $content = "恭喜您答对了第".$done."题！\n\n";
                    $this->updateInform('WeChatID',$id,'CORRECT',$correct+1,'Users');
                }
                else {
                    $word = $this->getWord($ListName,$nowID,'English');
                    $content = "第".$done."题答错了哟～\n答案:\n".$word."\n\n";
                }
            }
            $tiid = rand($min = 1,$max = 3644);
            $chinese = $this->getWord($ListName,$tiid,'Chinese');
            $done = $done+1;
            $content = $content."第".$done."题:\n";
            $content = $content.$chinese."请回答！(or input [0] to exit)";
            $this->updateInform('WeChatID',$id,'NowID',$tiid,'Users');
            $this->updateInform('WeChatID',$id,'DONE',$done,'Users');
            return $content;
        }
        elseif ($done == 10) {
            if ($keyword == $this->getWord($ListName,$nowID,'English')) {
                    $content = "恭喜您答对了第".$done."题！\n";
                    $this->updateInform('WeChatID',$id,'CORRECT',$correct+1,'Users');
                }
                else {
                    $word = $this->getWord($ListName,$nowID,'English');
                    $content = "第".$done."题答错了哟～\n答案：\n".$word."\n";
                }
                $correct = $this->getInform('WeChatID',$id,'CORRECT','Users');
                $name = $this->getInform('WeChatID',$id,'Name','Users');
                $content = $content."\n".$name."\n\t\t\t您已完成此次默写！\n正确率：".$correct."/10\n";
                $this->updateInform('WeChatID',$id,'STATUS','normal','Users');
                $this->updateInform('WeChatID',$id,'DONE',0,'Users');
                $this->updateInform('WeChatID',$id,'CORRECT',0,'Users');
                $this->updateInform('WeChatID',$id,'NowID',0,'Users');
                return $content;
        }

            

    }
   
   function updateInform($idName,$id,$key,$content,$table){
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
            mysql_query("UPDATE {$table} SET {$key} = '{$content}' WHERE {$idName} = '{$id}'",$link);
            mysql_close($link);
    }

    private function getInform($idName,$id,$key,$table){
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
            $result = mysql_query("SELECT * FROM {$table} WHERE {$idName}='{$id}'",$link);
            $row = mysql_fetch_array($result);
            mysql_close($link);
            return $row[$key];
    }

    private function getWord($ListName,$id,$key){
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
            $result = mysql_query("SELECT * FROM {$ListName} WHERE id='{$id}'",$link);
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
            mysql_query("INSERT INTO Users values('{$id}','unnamed',0,0,'',0,'WordList')",$link);
            mysql_close($link);
            return "wechatID:\n$id\n请设置您的用户名.";
    }

    private function makeContent($keyword,$id) {
       if ($keyword == "cmd") {
          return "命令：\n[默写]:进入默写系统；\n[注销]:注销帐号;\n[词汇表]:选择词汇手册";
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
