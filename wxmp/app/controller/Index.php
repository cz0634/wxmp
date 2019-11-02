<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;
use \Wxmp;

class Index extends BaseController
{

    public $userName = '18537191818@139.com';
    public $passWord = 'lc446763356';
    public $cookiePath;
    public $mp;

    public function initialize()
    {
        $this->cookiePath = './login_cookie/'.date('Y-m-d').$this->userName.'_cookie.txt';
        $this->mp = new Wxmp($this->userName, $this->passWord, $this->cookiePath);
    }

    public function login()
    {

    }

    public function index()
    {
        $info = $this->mp->bizLogin();//登录
        if ($info['code'] == 1) {
            $qrPath = $this->mp->getQrCode();//获取二维码
            View::assign('qrPath',$qrPath);
            return View::fetch('index');
        }
    }

    public function checkLogin()
    {
        $res = $this->mp->loginQrCode();

        if (isset($res['base_resp']['err_msg']) && $res['base_resp']['err_msg'] == 'ok' && $res['status'] == 4) { //已经扫码
            $responseData = ['code'=>3,'msg'=>'扫码未确认。'];
        }elseif (isset($res['base_resp']['err_msg']) && $res['base_resp']['err_msg'] == 'ok' && $res['status'] == 1) { //扫码成功
            $info = $this->mp->getToken();
            if ($info['code'] == 1) {
                $token = substr($info['redirect_url'], 38 + 6);
                $tokenName = md5($this->userName).date('Y-m-d');
                session($tokenName,$token);
                cookie($tokenName,$token);
                $responseData = ['code'=>1,'msg'=>'登录成功。您的token：'.$token];
            } else {
                $responseData = ['code'=>0,'msg'=>'登录失败，msg：获取token失败。'];
            }
        } else { //未扫码
            $responseData = ['code'=>2,'msg'=>'未扫码。'];
        }

        return json($responseData);
    }

    public function createArticle()
    {
        $tokenName = md5($this->userName).date('Y-m-d');
        $token = cookie($tokenName);
        //dump($token);exit;
        $info = [
            'title' => '测试标题',
            'author' => '作者名字',
            'digest' => '测试摘要',
            'content' => 'aaa',
            'sourceurl' => 'https://www.baidua.com'
        ];
        $res = $this->mp->createMaterial($token, $info);

    }

    public function delMaterial()
    {
        $appMsgId = '100000098';
        $token = '82787107';
        $res = $this->mp->delMaterial($token, $appMsgId);
        dump($res);
    }

    public function test()
    {
        dump(cookie(md5($this->userName).date('Y-m-d')));
    }
}
