<?php
/**
 * Created by PhpStorm.
 * User: cz
 * Date: 2019/11/1
 * Time: 10:57
 */
class Wxmp
{

    private $username; //开放平台账号
    private $password; //开放平台密码
    private $cookiePath; //cookie存储位置
    private $token;

    public function __construct($username,$password,$cookiePath)
    {
        $this->username = $username;
        $this->password = $password;
        $this->cookiePath = $cookiePath;
    }

    /*获取登录信息*/
    public function bizLogin()
    {
        //模拟用户浏览
        $this->jhCurl('https://mp.weixin.qq.com/',false,0,false,$this->cookiePath);

        $url = 'https://mp.weixin.qq.com/cgi-bin/bizlogin?action=startlogin';

        $header = array(
            'Host: mp.weixin.qq.com',
            'Connection: keep-alive',
            'Accept: */*',
            'Origin: https://mp.weixin.qq.com/',
            'X-Requested-With: XMLHttpRequest',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'Referer: https://mp.weixin.qq.com/',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: zh-CN,zh;q=0.9',
        );

        $requestStr = 'username='.urlencode($this->username).'&pwd='.md5($this->password).'&imgcode=&f=json&userlang=zh_CN&redirect_url=&token=&lang=zh_CN&ajax=1';

        $res = json_decode($this->jhCurl($url,$requestStr,1,$header,$this->cookiePath),true);

        if (isset($res['base_resp']['err_msg']) && $res['base_resp']['err_msg'] == 'ok') {
            return array(
                'code'  => 1, 'msg'   => '登陆成功', 'redirect_url' => 'https://mp.weixin.qq.com'.$res['redirect_url']
            );
        } else {
            return array(
                'code' => 0, 'msg' => '登陆失败'
            );
        }
    }

    /*获取登录二维码*/
    public function getQrCode()
    {
        $qr = 'https://mp.weixin.qq.com/cgi-bin/loginqrcode?action=getqrcode&param=4300&rd='.rand(3,9).rand(1,9).'0';
        $imgHeader = array(
            'Host: mp.weixin.qq.com',
            'Connection: keep-alive',
            'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
            'X-Requested-With: XMLHttpRequest',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'Referer: https://mp.weixin.qq.com/cgi-bin/bizlogin?action=validate&lang=zh_CN&account='.urlencode($this->username).'&token=',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: zh-CN,zh;q=0.9',
        );
        $qrRes = $this->jhCurl($qr,false,0,$imgHeader,$this->cookiePath);
        $path = './login_qr/'.$this->username.'.png';
        file_put_contents($path,$qrRes);
        return $path;
    }

    /*登录二维码检测*/
    public function loginQrCode()
    {
        $url = 'https://mp.weixin.qq.com/cgi-bin/loginqrcode?action=ask&token=&lang=zh_CN&f=json&ajax=1';

        $header = array(
            'Host: mp.weixin.qq.com',
            'Connection: keep-alive',
            'Accept: */*',
            'X-Requested-With: XMLHttpRequest',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'Referer: https://mp.weixin.qq.com/cgi-bin/bizlogin?action=validate&lang=zh_CN&account='.urlencode($this->username).'&token=',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: zh-CN,zh;q=0.9',
        );

        $res = json_decode($this->jhCurl($url,false,0,$header,$this->cookiePath), true);

        return $res;
    }

    /*获取token*/
    public function getToken()
    {
        $url = 'https://mp.weixin.qq.com/cgi-bin/bizlogin?action=login';
        $header = array(
            'Host: mp.weixin.qq.com',
            'Connection: keep-alive',
            'Accept: */*',
            'X-Requested-With: XMLHttpRequest',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'Referer: https://mp.weixin.qq.com/cgi-bin/bizlogin?action=validate&lang=zh_CN&account='.urlencode($this->username).'&token=',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: zh-CN,zh;q=0.9',
        );

        $requestStr = 'userlang=zh_CN&redirect_url=&token=&lang=zh_CN&f=json&ajax=1';

        $res = json_decode($this->jhCurl($url,$requestStr,1,$header,$this->cookiePath),true);

        if (isset($res['base_resp']['err_msg']) && $res['base_resp']['err_msg'] == 'ok') {
            return array(
                'code'  => 1, 'msg'   => '登陆成功', 'redirect_url' => $res['redirect_url']
            );
        } else {
            return array(
                'code' => 0, 'msg' => '登陆失败'
            );
        }
    }

    /*创建图文素材*/
    public function createMaterial($token, $info, $type = 10)
    {
        $url = 'https://mp.weixin.qq.com/cgi-bin/operate_appmsg?t=ajax-response&sub=create&type='.$type.'&token='.$token.'&lang=zh_CN';

        $header = array(
            'Host: mp.weixin.qq.com',
            'Connection: keep-alive',
            'Accept: application/json, text/javascript, */*; q=0.01',
            'Origin: https://mp.weixin.qq.com',
            'X-Requested-With: XMLHttpRequest',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'Referer: https://mp.weixin.qq.com/cgi-bin/appmsg?t=media/appmsg_edit_v2&action=edit&isNew=1&type='.$type.'&token='.$token.'&lang=zh_CN',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: zh-CN,zh;q=0.9',
        );

        $requestArr = [
            'token' => $token,
            'lang' => 'zh_CN',
            'f' => 'json',
            'ajax' => '1',
            'random' => '0.55'.rand(1,9).'93161'.rand(1,9).'676466'.rand(1,9),
            'AppMsgId' => '',
            'count' => '1',
            'data_seq' => '0',
            'operate_from' => 'Chrome',
            'isnew' => '0',
            'ad_video_transition0' => '',
            'can_reward0' => '0',
            'related_video0' => '',
            'is_video_recommend0' => '-1',
            'title0' => $info['title'],//标题
            'author0' => $info['author'],//作者
            'writerid0' => '0',
            'fileid0' => '100000200',//图片素材id
            'digest0' => $info['digest'],//摘要
            'auto_gen_digest0' => '0',
            'content0' => $info['content'],//内容
            'sourceurl0' => $info['sourceurl'],//原文链接
            'need_open_comment0' => '0',
            'only_fans_can_comment0' => '0',
            'cdn_url0' => 'http://mmbiz.qpic.cn/mmbiz_jpg/r4pA6porxphXCmqKoD7V5adNEFaeEWb83qBn7JDcqATcMwrZTn5ib6X4k2Tic3Xw6aQ7eqTKia1Z927MvBpTS2jGg/0?wx_fmt=jpeg',
            'cdn_235_1_url0' => 'https://mmbiz.qlogo.cn/mmbiz_jpg/r4pA6porxphXCmqKoD7V5adNEFaeEWb83qBn7JDcqATcMwrZTn5ib6X4k2Tic3Xw6aQ7eqTKia1Z927MvBpTS2jGg/0?wx_fmt=jpeg',
            'cdn_1_1_url0' => 'https://mmbiz.qlogo.cn/mmbiz_jpg/r4pA6porxphXCmqKoD7V5adNEFaeEWb8TPlB6csQHv9HiaWw5PLqtqicIB3gYHJzepqaUSibHnyZicWHg8DutQO42A/0?wx_fmt=jpeg',
            'cdn_url_back0' => 'https://mmbiz.qpic.cn/mmbiz_png/r4pA6porxphBxBLbbuTqGnWD6GPkseX7cMF8Im8yEpxujTjM5sFregRAX2Bk1mBk1Z7S7eBl6NyGD4P5ibibJriaQ/0?wx_fmt=png',
            'crop_list0' => '{"crop_list":[{"ratio":"2.35_1","x1":0,"y1":36,"x2":244,"y2":140},{"ratio":"1_1","x1":53,"y1":0,"x2":193,"y2":140}]}',
            'music_id0' => '',
            'video_id0' => '',
            'voteid0' => '',
            'voteismlt0' => '',
            'supervoteid0' => '',
            'cardid0' => '',
            'cardquantity0' => '',
            'cardlimit0' => '',
            'vid_type0' => '',
            'show_cover_pic0' => '0',
            'shortvideofileid0' => '',
            'copyright_type0' => '0',
            'releasefirst0' => '',
            'platform0' => '',
            'reprint_permit_type0' => '',
            'allow_reprint0' => '',
            'allow_reprint_modify0' => '',
            'original_article_type0' => '',
            'ori_white_list0' => '',
            'free_content0' => '',
            'fee0' => '0',
            'ad_id0' => '',
            'guide_words0' => '',
            'is_share_copyright0' => '0',
            'share_copyright_url0' => '',
            'source_article_type0' => '',
            'reprint_recommend_title0' => '',
            'reprint_recommend_content0' => '',
            'share_page_type0' => '0',
            'share_imageinfo0' => '{"list":[]}',
            'share_video_id0' => '',
            'dot0' => '{}',
            'share_voice_id0' => '',
            'insert_ad_mode0' => '',
            'categories_list0' => '[]'
        ];

        $requestStr = http_build_query($requestArr);
        $res = json_decode($this->jhCurl($url,$requestStr,1,$header,$this->cookiePath),true);

        return $res;


        /*,
        'sections0' => '[{"section_index":1000000,"text_content":"正文1","section_type":9,"ad_available":false},{"section_index":1000001,"text_content":"正文2","section_type":9,"ad_available":false},{"section_index":1000002,"text_content":"正文3","section_type":9,"ad_available":false},{"section_index":1000003,"text_content":"","section_type":9,"ad_available":false},{"section_index":1000004,"text_content":"","section_type":1,"extra_content":"https://mmbiz.qlogo.cn/mmbiz_jpg/r4pA6porxphXCmqKoD7V5adNEFaeEWb8Ms3c7TR2jKURlM7X4kYuDTHHvkO0iaicEglrLtLgyrqnEnUs03EZ4l3w/0?wx_fmt=jpeg","ad_available":false},{"section_index":1000005,"text_content":"","section_type":9,"ad_available":false}]',
            'compose_info0' => '{"list":[{"blockIdx":1,"content":"<p>正文1<mpchecktext id=\"1572674525663_0.21109087336578236\"></mpchecktext><br></p>","width":574,"height":27,"topMargin":0,"blockType":9,"background":"rgba(0, 0, 0, 0)","text":"正文1","textColor":"rgb(51, 51, 51)","textFontSize":"17px","textBackGround":"rgba(0, 0, 0, 0)"},{"blockIdx":2,"content":"<p><strong>正文2<mpchecktext id=\"1572674525665_0.18759796370537352\"></mpchecktext></strong><br></p>","width":574,"height":27,"topMargin":27,"blockType":9,"background":"rgba(0, 0, 0, 0)","text":"正文:#:2","textColor":"rgb(51, 51, 51):#:rgb(51, 51, 51)","textFontSize":"17px:#:17px","textBackGround":"rgba(0, 0, 0, 0):#:rgba(0, 0, 0, 0)"},{"blockIdx":3,"content":"<p><em>正文3<mpchecktext id=\"1572674525666_0.7813447510643841\"></mpchecktext></em></p>","width":574,"height":27,"topMargin":54,"blockType":9,"background":"rgba(0, 0, 0, 0)","text":"正文3","textColor":"rgb(51, 51, 51)","textFontSize":"17px","textBackGround":"rgba(0, 0, 0, 0)"},{"blockIdx":4,"content":"<p><em><br></em></p>","width":574,"height":27,"topMargin":81,"blockType":9,"background":"rgba(0, 0, 0, 0)","text":"","textColor":"","textFontSize":"","textBackGround":""},{"blockIdx":5,"content":"<p style=\"text-align: center\"><img data-s=\"300,640\" class=\"rich_pages js_insertlocalimg\" src=\"https://mmbiz.qlogo.cn/mmbiz_jpg/r4pA6porxphXCmqKoD7V5adNEFaeEWb8Ms3c7TR2jKURlM7X4kYuDTHHvkO0iaicEglrLtLgyrqnEnUs03EZ4l3w/0?wx_fmt=jpeg\" style=\"\" data-ratio=\"0.41641337386018235\" data-w=\"658\" data-type=\"jpeg\"></p>","width":574,"height":246,"topMargin":108,"blockType":1,"background":"rgba(0, 0, 0, 0)","text":"","textColor":"","textFontSize":"","textBackGround":""},{"blockIdx":6,"content":"<p><em></em><br></p>","width":574,"height":27,"topMargin":354,"blockType":9,"background":"rgba(0, 0, 0, 0)","text":"","textColor":"","textFontSize":"","textBackGround":""}]}'*/

        /*$requestStr = 'token='.$token.'&lang=zh_CN&f=json&ajax=1&random=0.8597571453421478&AppMsgId=&count=1&data_seq=0&operate_from=Chrome&isnew=0&ad_video_transition0=&can_reward0=0&related_video0=&is_video_recommend0=-1&title0=%E6%A0%87%E9%A2%98&author0=%E4%BD%9C%E8%80%85&writerid0=0&fileid0=100000081&digest0=%E5%86%85%E5%AE%B9&auto_gen_digest0=1&content0=%3Cp%3E%E5%86%85%E5%AE%B9%3Cbr%3E%3C%2Fp%3E&sourceurl0=https%3A%2F%2Fwww.baidu.com&need_open_comment0=1&only_fans_can_comment0=0&cdn_url0=http%3A%2F%2Fmmbiz.qpic.cn%2Fmmbiz_jpg%2Fr4pA6porxphXCmqKoD7V5adNEFaeEWb8DXsPmpUABX7rLT839TZmeXATQ8zgv6xibibZrhMxPdlKPJJjodibcnZ9w%2F0%3Fwx_fmt%3Djpeg&cdn_235_1_url0=https%3A%2F%2Fmmbiz.qlogo.cn%2Fmmbiz_jpg%2Fr4pA6porxphXCmqKoD7V5adNEFaeEWb8DXsPmpUABX7rLT839TZmeXATQ8zgv6xibibZrhMxPdlKPJJjodibcnZ9w%2F0%3Fwx_fmt%3Djpeg&cdn_1_1_url0=https%3A%2F%2Fmmbiz.qlogo.cn%2Fmmbiz_jpg%2Fr4pA6porxphXCmqKoD7V5adNEFaeEWb8TPlB6csQHv9HiaWw5PLqtqicIB3gYHJzepqaUSibHnyZicWHg8DutQO42A%2F0%3Fwx_fmt%3Djpeg&cdn_url_back0=https%3A%2F%2Fmmbiz.qpic.cn%2Fmmbiz_png%2Fr4pA6porxphBxBLbbuTqGnWD6GPkseX7cMF8Im8yEpxujTjM5sFregRAX2Bk1mBk1Z7S7eBl6NyGD4P5ibibJriaQ%2F0%3Fwx_fmt%3Dpng&crop_list0=%7B%22crop_list%22%3A%5B%7B%22ratio%22%3A%222.35_1%22%2C%22x1%22%3A0%2C%22y1%22%3A18%2C%22x2%22%3A245%2C%22y2%22%3A122%7D%2C%7B%22ratio%22%3A%221_1%22%2C%22x1%22%3A53%2C%22y1%22%3A0%2C%22x2%22%3A193%2C%22y2%22%3A140%7D%5D%7D&music_id0=&video_id0=&voteid0=&voteismlt0=&supervoteid0=&cardid0=&cardquantity0=&cardlimit0=&vid_type0=&show_cover_pic0=0&shortvideofileid0=&copyright_type0=0&releasefirst0=&platform0=&reprint_permit_type0=&allow_reprint0=&allow_reprint_modify0=&original_article_type0=&ori_white_list0=&free_content0=&fee0=0&ad_id0=&guide_words0=&is_share_copyright0=0&share_copyright_url0=&source_article_type0=&reprint_recommend_title0=&reprint_recommend_content0=&share_page_type0=0&share_imageinfo0=%7B%22list%22%3A%5B%5D%7D&share_video_id0=&dot0=%7B%7D&share_voice_id0=&insert_ad_mode0=&categories_list0=%5B%5D&sections0=%5B%7B%22section_index%22%3A1000000%2C%22text_content%22%3A%22%E5%86%85%E5%AE%B9%E2%80%8B%22%2C%22section_type%22%3A9%2C%22ad_available%22%3Afalse%7D%5D&compose_info0=%7B%22list%22%3A%5B%7B%22blockIdx%22%3A1%2C%22content%22%3A%22%3Cp%3E%E5%86%85%E5%AE%B9%3Cmpchecktext+id%3D%5C%221572663815245_0.6791566974024734%5C%22%3E%3C%2Fmpchecktext%3E%E2%80%8B%3Cbr%3E%3C%2Fp%3E%22%2C%22width%22%3A574%2C%22height%22%3A27%2C%22topMargin%22%3A0%2C%22blockType%22%3A9%2C%22background%22%3A%22rgba(0%2C+0%2C+0%2C+0)%22%2C%22text%22%3A%22%E5%86%85%E5%AE%B9%22%2C%22textColor%22%3A%22rgb(51%2C+51%2C+51)%22%2C%22textFontSize%22%3A%2217px%22%2C%22textBackGround%22%3A%22rgba(0%2C+0%2C+0%2C+0)%22%7D%5D%7D';*/
    }

    /*删除图文素材*/
    public function delMaterial($token, $appMsgId)
    {
        $url = 'https://mp.weixin.qq.com/cgi-bin/operate_appmsg?sub=del&t=ajax-response';

        $header = array(
            'Host: mp.weixin.qq.com',
            'Connection: keep-alive',
            'Accept: application/json, text/javascript, */*; q=0.01',
            'Origin: https://mp.weixin.qq.com',
            'X-Requested-With: XMLHttpRequest',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'Referer: https://mp.weixin.qq.com/cgi-bin/appmsg?begin=0&count=10&t=media/appmsg_list&type=10&action=list_card&lang=zh_CN&token='.$token,
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: zh-CN,zh;q=0.9',
        );

        $requestStr = 'AppMsgId='.$appMsgId.'&token='.$token.'&lang=zh_CN&f=json&ajax=1';

        $res = json_decode($this->jhCurl($url,$requestStr,1,$header,$this->cookiePath),true);
        return $res;
    }
















    public function jhCurl($url , $params = false , $ispost = 0 ,$header = false , $cookie_file = '', $timeout = 10 , $needHeader = 0){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT , $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER , true );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($needHeader) {
            // 返回 response_header, 该选项非常重要,如果不为 true, 只会获得响应的正文
            curl_setopt($ch, CURLOPT_HEADER, true);
        }

        if (is_numeric(strpos($url,'https'))) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }

        if ($cookie_file) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        }

        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        if( $ispost ) {
            curl_setopt($ch , CURLOPT_POST , true );
            curl_setopt($ch , CURLOPT_POSTFIELDS , $params );
            curl_setopt($ch , CURLOPT_URL , $url );
        } else {
            if($params){
                curl_setopt($ch , CURLOPT_URL , $url.'?'.$params );
            }else{
                curl_setopt($ch , CURLOPT_URL , $url);
            }
        }

        $response = curl_exec($ch );

        if ($response === FALSE) {
            echo "cURL Error: " . curl_error($ch);
            return false;
        }

        if ($needHeader) {
            // 获得响应结果里的：头大小
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            // 根据头大小去获取头信息内容
            $header = substr($response, 0, $headerSize);
            $response = substr($response, $headerSize);
            $res = json_encode(['response' => $response,'header'   => $header]);
            return $res;
        }
        curl_close($ch);
        return $response;
    }

}