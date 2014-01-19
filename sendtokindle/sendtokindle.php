<?php
/*
    方倍工作室 http://www.cnblogs.com/txw1958/
    CopyRight 2013 www.doucube.com  All Rights Reserved
*/

define("TOKEN", "weixin");
$wechatObj = new wechatCallbackapiTest();
if (isset($_GET['echostr'])) {
    $wechatObj->valid();
}else{
    $wechatObj->responseMsg();
}

class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function responseMsg()
	{
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if (!empty($postStr)){
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$RX_TYPE = trim($postObj->MsgType);

			switch ($RX_TYPE)
			{
			/*	case "text":
					$resultStr = $this->receiveText($postObj);
					break;
				case "image":
					$resultStr = $this->receiveImage($postObj);
					break;
				case "location":
					$resultStr = $this->receiveLocation($postObj);
					break;
				case "voice":
					$resultStr = $this->receiveVoice($postObj);
					break;
				case "video":
					$resultStr = $this->receiveVideo($postObj);
					break;
				case "link":
					$resultStr = $this->receiveLink($postObj);
					break;
					*/
				case "event":
					$resultStr = $this->receiveEvent($postObj);
					break;
				default:
					$resultStr = "unknow msg type: ".$RX_TYPE;
					break;
			}
			echo $resultStr;
		}else {
			echo "";
			exit;
		}
	}
	
	private function transmitText($object, $content, $flag = 0)
    {
        $textTpl = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[text]]></MsgType>
		<Content><![CDATA[%s]]></Content>
		<FuncFlag>%d</FuncFlag>
		</xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
    }
	
	private function receiveEvent($object)
    {
        $contentStr = "";
        switch ($object->Event)
        {
            case "subscribe":
                $contentStr = "欢迎关注sendtokindle, 现在是测试版本，我们正在不断完善！";
                break;
            case "unsubscribe":
                $contentStr = "";
                break;
            case "CLICK":
                switch ($object->EventKey)
                {
                    default:
                        $contentStr = "你点击了菜单: ".$object->EventKey;
                        break;
                }
                break;
            default:
                $contentStr = "receive a new event: ".$object->Event;
                break;
        }
        $resultStr = $this->transmitText($object, $contentStr);
        return $resultStr;
    }
}
?>