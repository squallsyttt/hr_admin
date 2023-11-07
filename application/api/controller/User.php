<?php

namespace app\api\controller;

use app\common\model\User as ModelUser;
use app\common\model\UserIDcard;
use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use fast\Http;
use fast\Random;
use Overtrue\Pinyin\Pinyin;
use think\Config;
use think\Validate;
use think\Db;

/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['login', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third', 'wechatLogin','organizationJobMap','contacts'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }
    }

    /**
     * 会员中心
     */
    public function index()
    {
        $this->success('', ['welcome' => $this->auth->nickname]);
    }

    /**
     * 会员登录
     *
     * @ApiMethod (POST)
     * @param string $account  账号
     * @param string $password 密码
     */
    public function login()
    {
        $account = $this->request->post('account');
        $password = $this->request->post('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 手机验证码登录
     *
     * @ApiMethod (POST)
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    public function mobilelogin()
    {
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注册会员
     *
     * @ApiMethod (POST)
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email    邮箱
     * @param string $mobile   手机号
     * @param string $code     验证码
     */
    public function register()
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        $email = $this->request->post('email');
        $mobile = $this->request->post('mobile');
        $code = $this->request->post('code');
        if (!$username || !$password) {
            $this->error(__('Invalid parameters'));
        }
        if ($email && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $ret = Sms::check($mobile, $code, 'register');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }
        $ret = $this->auth->register($username, $password, $email, $mobile, []);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 退出登录
     * @ApiMethod (POST)
     */
    public function logout()
    {
        // $this->success(__('Logout successful'));

        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     *
     * @ApiMethod (POST)
     * @param string $avatar   头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $bio      个人简介
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->post('username');
        $nickname = $this->request->post('nickname');
        $bio = $this->request->post('bio');
        $avatar = $this->request->post('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Username already exists'));
            }
            $user->username = $username;
        }
        if ($nickname) {
            $exists = \app\common\model\User::where('nickname', $nickname)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Nickname already exists'));
            }
            $user->nickname = $nickname;
        }
        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->save();
        $this->success();
    }

    /**
     * 修改邮箱
     *
     * @ApiMethod (POST)
     * @param string $email   邮箱
     * @param string $captcha 验证码
     */
    public function changeemail()
    {
        $user = $this->auth->getUser();
        $email = $this->request->post('email');
        $captcha = $this->request->post('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号
     *
     * @ApiMethod (POST)
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    public function changemobile()
    {
        $user = $this->auth->getUser();
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
     * 第三方登录
     *
     * @ApiMethod (POST)
     * @param string $platform 平台名称
     * @param string $code     Code码
     */
    public function third()
    {
        $url = url('user/index');
        $platform = $this->request->post("platform");
        $code = $this->request->post("code");
        $config = get_addon_config('third');
        if (!$config || !isset($config[$platform])) {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定会员
        $result = $app->{$platform}->getUserInfo(['code' => $code]);
        if ($result) {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret) {
                $data = [
                    'userinfo'  => $this->auth->getUserinfo(),
                    'thirdinfo' => $result
                ];
                $this->success(__('Logged in successful'), $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }

    /**
     * 重置密码
     *
     * @ApiMethod (POST)
     * @param string $mobile      手机号
     * @param string $newpassword 新密码
     * @param string $captcha     验证码
     */
    public function resetpwd()
    {
        $type = $this->request->post("type");
        $mobile = $this->request->post("mobile");
        $email = $this->request->post("email");
        $newpassword = $this->request->post("newpassword");
        $captcha = $this->request->post("captcha");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        //验证Token
        if (!Validate::make()->check(['newpassword' => $newpassword], ['newpassword' => 'require|regex:\S{6,30}'])) {
            $this->error(__('Password must be 6 to 30 characters'));
        }
        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'resetpwd');
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 获取组织架构人员
     */
    public function organization()
    {
        $params = $this->request->param();
        $where = ['audit_status' => ['=', 1]];
        $order = [];
        $where['job'] = ['<>', 99];
        $order = ['job' => 'asc', 'nickname' => 'asc'];

        if (($params['job'] ?? '') != 0) {
            $where['job'] = ['=', $params['job']];
        }

        $results = ModelUser::where($where)->order($order)->select();
        $restructured = [];
        $currentJob = null;
        $jobName = config("site.organizationJob");;
        foreach ($results as $key => $result) {
            $job = $result['job'];

            // 检查当前job是否与前job一个不同
            if ($job !== $currentJob) {
                // 创建新的记录
                $restructured[] = [
                    'job' => $job,
                    'job_name' => $jobName[$job],
                    'list' => [$result],
                ];
                $currentJob = $job;
            } else {
                // 在当前工作的记录中添加新的项
                $restructured[count($restructured) - 1]['list'][] = $result;
            }
        }

        $this->success('请求成功', $restructured);
    }

    public function contacts()
    {
        $params = $this->request->param();
        $where = ['audit_status' => ['=', 1]];
        $order = ['nickname' => 'asc'];

        if (($params['job'] ?? '') != 0) {
            $where['job'] = ['=', $params['job']];
        }

        if (!empty(($params['search'] ?? '') ?: '')) {
            $where['nickname'] = ['like', '%' . $params['search'] . '%'];
        }

        $list = ModelUser::field('id,nickname,job,avatar,company_name')->where($where)->order($order)->select();

        $sortedUsers = [];

        $pinyin = new Pinyin();
        $jobName = config("site.organizationJob");
        foreach ($list as $user) {
            $firstLetter = strtoupper($pinyin->abbr(mb_substr($user['nickname'], 0, 1))); // 获取拼音的首字母并转换为大写

            if (!isset($sortedUsers[$firstLetter])) {
                $sortedUsers[$firstLetter] = [
                    'title' => $firstLetter,
                    'list' => []
                ];
            }

            $user['job_name'] = $user['job'] < 99 ? $jobName[$user['job']] ?? '' : '';
            $sortedUsers[$firstLetter]['list'][] = $user;
        }

        // 对 $sortedUsers 数组按字母顺序排序
        ksort($sortedUsers);
        $this->success('请求成功', array_values($sortedUsers));
    }

    /**
     * 查看用户详情
     */
    public function detail()
    {
        $params = $this->request->param();
        $id = ($params['id'] ?? '') ?: $this->auth->id;
        if (empty($id)) {
            $is_login = $this->auth->id ? true : false;
            $this->error($is_login ? '该用户不存在!!!' : '未登录！', [], $is_login ? 0 : -1);
        }

        $detail = ModelUser::find($id);
        if (empty($detail)) {
            $this->error('该用户不存在!!!');
        }
        $jobName = config("site.organizationJob");
        $detail->job_name = $jobName[$detail->job] ?? '';
        $this->success('请求成功', $detail);
    }

    /**
     * 提交入会申请
     */
    public function apply()
    {
        $params = $this->request->param();
        $email = ($params['email'] ?? '') ?: '';
        $mobile =  ($params['mobile'] ?? '') == '' ? '' : intval($params['mobile']);
        // $mobile =  ($params['mobile']??'') == '' ?: '';

        if (empty($params['name'] ?? '')) {
            $this->error("姓名未填写!!!!!");
        }
        // var_dump($mobile);
        if (!Validate::regex($mobile ?? '', "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }

        if (!empty($email) && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }

        if (empty($params['ID_card_front'] ?? '') || empty($params['ID_card_back'] ?? '')) {
            $this->error("身份证信息未上传!!!!");
        }
        $Data = [
            'nickname'      =>  $params['name'],
            'mobile'        =>  $mobile,
            'position'      => $params['position'] ?? '',
            'wechat'        => ($params['wechat'] ?? '') ?: '',
            'email'         =>  $email,
            'bio'           => ($params['bio'] ?? '') ?: '',
            'company_name'  => ($params['company_name'] ?? '') ?: '',
            'company_type'  => ($params['company_type'] ?? '') ?: '',
            'area'          => ($params['area'] ?? '') ?: '',
            'area_id'       => ($params['area_id'] ?? '') ?: '',
            'address'       => ($params['address'] ?? '') ?: '',
            'is_apply'      =>  '1',
        ];
        try {
            $model = new ModelUser();

            $ret = $model->allowField(true)->save($Data, ['id' => $this->auth->id]);
            $userinfo = $this->auth->getUserinfo();
            $user_id = $userinfo['id'];

            $id_card_insert = [
                'ID_card_front' => $params['ID_card_front'],
                'ID_card_back'  => $params['ID_card_back'],
                'user_id'       => $user_id
            ];
            UserIDcard::where('user_id', $this->auth->id)->delete();
            UserIDcard::create($id_card_insert);
        } catch (\Exception $e) {
            $this->error('提交失败!!!', $e->getMessage());
        }
        $this->success("已提交入会申请", $userinfo);
    }


    /**
     * 编辑用户信息
     */
    public function edit()
    {
        $id = $this->auth->id;

        $params = $this->request->param();
        $email = ($params['email'] ?? '') ?: '';

        if (empty($params['name'] ?? '')) {
            $this->error("姓名未填写!!!!!");
        }
        if (!Validate::regex($params['mobile'] ?? '', "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }

        if (!empty($email) && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }

        // try{
        $data = [
            'username'      =>  $params['name'],
            'nickname'      =>  $params['name'],
            'mobile'        =>  $params['mobile'],
            'wechat'        => ($params['wechat'] ?? '') ?: '',
            'email'         =>  $email,
            'position'      => $params['position'] ?? '',
            'bio'           => ($params['bio'] ?? '') ?: '',
            'company_name'  => ($params['company_name'] ?? '') ?: '',
            'company_type'  => ($params['company_type'] ?? '') ?: '',
            'area'          => ($params['area'] ?? '') ?: '',
            'area_id'       => ($params['area_id'] ?? '') ?: '',
            'address'       => ($params['address'] ?? '') ?: '',
        ];
        $model = new ModelUser();
        $model->allowField(true)->save($data, ['id' => $id]);
        $this->success("编辑成功", []);
    }

    /**
     * 组织架构职位
     */
    public function organizationJobMap()
    {
        $result[] = [
            'name' => '全部',
            'key' => 0
        ];
        $jobName = config("site.organizationJob");
        foreach ($jobName as $key => $value) {
            if ($key < 99) {
                $result[] = [
                    'name' => $value,
                    'key' => $key
                ];
            }
        }
        return $this->success('成功!', $result);
    }

    public function getOpenId($code = '')
    {
        $code = $this->request->param('code');
        if (empty($code)) {
            $this->error('未获取到用户授权信息！！！');
        }
        $appid = 'wxe63c298b4eec57f0';  // 小程序的appid
        $secret = '53aa558bed39d599bcac41ec2a31eb82'; // 小程序的secret
        $grant_type = 'authorization_code'; //授权类型，此处只需填写 authorization_code
        $url = "https://api.weixin.qq.com/sns/jscode2session";
        // $url = "https://api.weixin.qq.com/sns/oauth2/access_token";
        $params = [
            'appid' => $appid,
            'secret' => $secret,
            'js_code' => $code,
            // 'code' => $code,
            'grant_type' => $grant_type
        ];
        $result = Http::get($url, $params);
        return $result;
    }

    public function wechatLogin()
    {
        $code = $this->request->param('code');

        $result = json_decode($this->getOpenId($code), true);
        if (isset($result['openid'])) {
            $user = ModelUser::get(['open_id' => $result['openid']]);

            if ($user) {
                $loginret = $this->auth->direct($user->id);
                if ($loginret) {
                    $data = [
                        'userinfo'  => $this->auth->getUserinfo(),
                        'thirdinfo' => $result
                    ];
                    $this->success(__('Logged in successful'), $data);
                }
            } else {

                $insertData = [
                    'username'      =>  'admin' . time(),
                    'nickname'      =>  'admin' . time(),
                    'open_id'       =>  $result['openid']
                ];

                $ret = $this->auth->register($insertData['username'], '', '', '', $insertData);
                $data = [
                    'userinfo'  => $this->auth->getUserinfo(),
                    'thirdinfo' => $result
                ];
                $this->success(__('Logged in successful'), $data);
            }
        } else {
            $this->error('用户未授权登录!!!');
        }
    }

    public function sync(){
        $params = $this->request->param();

        $currentId = ($params['id'] ?? '') ? $params['id'] : $this->auth->id;
        $syncMobile = $params['mobile'] ?? '';

        if (empty($currentId)) {
            $this->error("获取用户身份失败，请退出后重新授权登录");
        }

        if (!Validate::regex($syncMobile ?? '', "^1\d{10}$")) {
            $this->error("请检查号码格式",['mobile' => $syncMobile]);
        }

        $targetAccount = Db("user")
            ->where('mobile',$syncMobile)
            ->where('open_id',"")
            ->select();


        if(empty($targetAccount)){
            $this->error("目标同步账户不存在，需要您主动提交入会申请。");
        }
        $targetAccount = $targetAccount[0];
        $userModel = new ModelUser();
        //对预录入数据的操作
        $updateData = [
            'audit_status' => 0
        ];
        $userModel->allowField(true)->save($updateData,['id' => $targetAccount['id']]);

        $syncData = [
            'group_id' => $targetAccount['group_id'],
            'username' => $targetAccount['username'],
            'nickname' => $targetAccount['nickname'],
            'password' => $targetAccount['password'],
            'salt' => $targetAccount['salt'],
            'email' => $targetAccount['email'],
            'mobile' => $targetAccount['mobile'],
            'wechat' => $targetAccount['wechat'],
            'level' => $targetAccount['level'],
            'position' => $targetAccount['position'],
            'job' => $targetAccount['job'],
            'company_type' => $targetAccount['company_type'],
            'company_name' => $targetAccount['company_name'],
            'area_id' => $targetAccount['area_id'],
            'area' => $targetAccount['area'],
            'address' => $targetAccount['address'],
            'gender' => $targetAccount['gender'],
            'birthday' => $targetAccount['birthday'],
            'bio' => $targetAccount['bio'],
            'money' => $targetAccount['money'],
            'score' => $targetAccount['score'],
            'status' => $targetAccount['status'],
            'verification' => $targetAccount['verification'],
            'audit_status' => 1,//能同步的话就直接通过 0未通过 1审核通过
            'is_apply' => $targetAccount['is_apply'],
        ];
        $userModel->allowField(true)->save($syncData,['id' => $currentId]);
        $syncAccount = $syncData;
        $syncAccount['id'] = $currentId;
        $this->success("同步成功",['account' => $syncAccount]);
    }
}
