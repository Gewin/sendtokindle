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
			
			  $myString = $this->debugFuc($postObj, $RX_TYPE);
            //echo $myString;

			  switch ($RX_TYPE)
				{
					case "text":
						$resultStr = $this->language($postObj);
						break;
					/*case "image":
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
						break;*/
					case "link":
						$resultStr = $this->receiveLink($postObj);
						break; 
						
					case "event":
						$resultStr = $this->receiveEvent($postObj);
						break;
					default:
						$resultStr = "unknow msg type: ".$RX_TYPE;
						break;
				}
			  echo $resultStr;
		}else {
			  echo "Wrong message!";
			  exit;
		}
	}
	
	private function language($object){
        $value = urlencode($object->Content);
		$qurl = 'http://fanyi.youdao.com/openapi.do?keyfrom=Liujiawei&key=2017678959&type=data&doctype=json&version=1.1&q='.$value;
		$f = new SaeFetchurl(); 
		$content = $f->fetch($qurl); 
		$sina = json_decode($content,true); 
		$errorcode = $sina['errorCode'];
		$phonetic = $sina['basic']['phonetic']; 
		$explains = $sina['basic']['explains']['0'];
		$interpret = $sina['basic']['explains']['1'];
		$interprets = $sina['basic']['explains']['2'];
		$trans = '';
		if (isset($errorcode)){
			switch ($errorcode){
				case 0:
						$trans = $sina['translation']['0'];
				break;
				case 20:
						$trans = '要翻译的文本过长';
				break;
				case 30:
						$trans = '无法进行有效的翻译';
				break;
				case 40:
						$trans = '不支持的语言类型';
				break;
				case 50:
						$trans = '无效的key';
				break;
				default:
						$trans = '出现异常';
				break;
			}
		}
		$contentStr = $trans."\n".$phonetic."\n".$explain."\n".$interpret."\n".$interprets;
		$resultStr = $this->transmitText($object, $contentStr);
        return $resultStr; 
    }
    
	private function articleAndPic($object, $title, $desription, $image, $turl)
	{
		$picTpl = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[news]]></MsgType>
		<ArticleCount>1</ArticleCount>
		<Articles>
		<item>
		<Title><![CDATA[%s]]></Title>
		<Description><![CDATA[%s]]></Description>
		<PicUrl><![CDATA[%s]]></PicUrl>
		<Url><![CDATA[%s]]></Url>
		</item>
		</Articles>
		<FuncFlag>1</FuncFlag>
		</xml> ";
		$resultStr = sprintf($picTpl, $object->FromUserName, $object->ToUserName, time(), $title, $desription, $image, $turl);
		return $resultStr;
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
	
	private function debugFuc($object, $msgType)
	{
		$contentStr = "Current type is ".$msgType;
		
		$resultStr = $this->transmitText($object, $contentStr);
        return $resultStr;
	}	
	
	private function receiveText($object)
    {
        $funcFlag = 0;
        $contentStr = "你发送的是文本，内容为：".$object->Content; // .<a href="http://blog.csdn.net/lyq8479">柳峰的博客</a>;
        $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
        //$desription = "刘佳炜测试";
		//$image = "http://image.baidu.com/i?ct=503316480&tn=baiduimagedetail&cg=art&ipn=d&ic=0&lm=-1&word=%E9%A3%8E%E6%99%AF&ie=utf-8&in=3354&cl=2&st=&pn=4&rn=1&di=&fr=&&fmq=1378374347070_R&se=&sme=0&tab=&face=&&istype=&ist=&jit=&objurl=http%3A%2F%2Fpica.nipic.com%2F2007-12-23%2F200712231523651_2.jpg#pn4&-1&di&objURLhttp%3A%2F%2Fpica.nipic.com%2F2007-12-23%2F200712231523651_2.jpg&fromURLippr_z2C%24qAzdH3FAzdH3Fooo_z%26e3Bgtrtv_z%26e3Bv54AzdH3Ffi5oAzdH3FdAzdH3FbAzdH3F0jamdjdvw1nljdaw_z%26e3Bip4s&W1024&H640&T&S&TP0";
		//$turl = $object->Content;
		//$resultStr = $this->articleAndPic($object, $contentStr, $desription, $image, $turl);
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
	
	private function receiveLink($object)
    {
        $funcFlag = 0;
        $contentStr = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
        $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
        return $resultStr;
    }
}
?>