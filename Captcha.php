<?php
/**
 * Created By PHPStorm
 * User: SteffenKong(Konghy)
 * Date: 2020/4/7
 * Time: 21:19
 */

namespace Steffen\Captcha;

class Captcha {

//配置文件格式
//[
//    'fontSize' => 25,
//    'height' => 50,
//    'width' => 200,
//    'length' => 5,
//    'pixel' => 4,
//    'snow' => 4,
//    'line' => 4,
//    'fontFile' => __DIR__.'/Fonts/segoeprb.ttf'
//]



    //字体大小
    private $fontSize;

    //画布高度
    private $height;

    //字体文件
    private $fontFile;

    //画布宽度
    private $width;

    //字体长度
    private $length;

    //像素点的个数
    private $pixel;

    //雪花个数
    private $snow;

    //线条数
    private $line;

    //资源句柄
    private $imageObj;

    private static $sigle = null;


    private function __clone(){}


    /**
     * @param $config
     * @return Captcha|null
     * @throws \Exception
     * 单例操作
     */
    public static function instance($config) {
        return self::$sigle ?? new self($config);
    }


    /**
     * Captcha constructor.
     * @param $config
     * @throws \Exception
     */
    private function __construct($config)
    {
        $this->checkConfig($config);
        $this->fontSize = isset($config['fontSize']) ? $config['fontSize'] : 16;
        $this->width = isset($config['width']) ? $config['width'] : 100;
        $this->height = isset($config['height']) ? $config['height'] : 200;
        $this->length = isset($config['length']) ? $config['length'] : 4;
        $this->pixel = isset($config['pixel']) ? $config['pixel'] : 0;
        $this->snow = isset($config['snow']) ? $config['snow'] : 0;
        $this->line = isset($config['line']) ? $config['line'] : 0;
        $this->fontFile = isset($config['fontFile']) ? $config['fontFile'] : __DIR__.'/Fonts/calibril.ttf';
        $this->imageObj = imagecreatetruecolor($this->width,$this->height);
        $this->getCaptcha();
    }


    /**
     * @param $config
     * @return void
     * @throws \Exception
     * 检测配置项
     */
    private function checkConfig($config) {
        if (!is_array($config) || empty($config)) {
            throw new \Exception('配置项格式错误或为空!');
        }

        if (!isset($config['fontSize']) || !is_numeric($config['fontSize'])) {
            throw new \Exception('fontSize参数不对!');
        }

        if (!isset($config['width']) || !is_numeric($config['width'])) {
            throw new \Exception('width参数不对!');
        }

        if (!isset($config['height']) || !is_numeric($config['height'])) {
            throw new \Exception('height参数不对!');
        }

        if (!isset($config['length']) || !is_numeric($config['length'])) {
            throw new \Exception('length参数不对!');
        }

        if (!isset($config['pixel']) || !is_numeric($config['pixel'])) {
            throw new \Exception('pixel参数不对!');
        }

        if (!isset($config['snow']) || !is_numeric($config['snow'])) {
            throw new \Exception('snow参数不对!');
        }

        if (!isset($config['line']) || !is_numeric($config['line'])) {
            throw new \Exception('line参数不对!');
        }
    }


    /**
     * 获取图像
     */
    public function getCaptcha() {
        $bgColor = $this->getRandColor();
        //填充矩形
        imagefilledrectangle($this->imageObj,0,0,$this->width,$this->height,$bgColor);
        $codeStr = $this->getCodeStr($this->length);
        $x = ($this->width / $this->length) + mt_rand(5,10);
        $y = $this->height / 1.5;
        //旋转角度
//        $angle = mt_rand(-30,30);
        $randColor = $this->getRandColor();
        imagettftext($this->imageObj,$this->fontSize,$angle,$x,$y,$randColor,$this->fontFile,$codeStr);

        /* 干扰项 */

        if(!empty($this->snow) && $this->snow > 0) {
            $this->setSnow();
        }

        if(!empty($this->line) && $this->line > 0) {
            $this->setLine();
        }

        if(!empty($this->pixel) && $this->pixel > 0) {
            $this->setPixel();
        }


        //输出图像
        $this->output();
    }


    /**
     * @return false|int
     */
    public function getRandColor() {
        return imagecolorallocate($this->imageObj,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
    }


    /**
     * 输出图像
     */
    private function output() {
        header('Content-type:image/png');
        imagepng($this->imageObj);
        imagedestroy($this->imageObj);
    }


    /**
     * @param $length
     * @return string
     */
    private function getCodeStr($length) {
        $randArray = array_merge(range(0,9),range('A','Z'),range('a','z'));
        $index = array_rand($randArray,$length);
        shuffle($index);
        $randStr = '';
        foreach ($index as $i) {
            $randStr .= $randArray[$i];
        }
        $_SESSION['captchaStr'] = $randStr;
        return $randStr;
    }


    /**
     * 设置像素点
     */
    private function setPixel() {
        for ($i = 1;$i <= $this->pixel; ++$i) {
            imagesetpixel($this->imageObj,mt_rand(0,$this->width),mt_rand(0,$this->height),$this->getRandColor());
        }
    }


    /**
     * 设置干扰线
     */
    private function setLine() {
        for ($i = 1;$i <= $this->line; ++$i) {
            imageline($this->imageObj,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$this->getRandColor());
        }
    }


    /**
     * 设置雪花
     */
    private function setSnow() {
        for ($i = 1;$i <= $this->snow; ++$i) {
            imagestring($this->imageObj,mt_rand(1,5),mt_rand(0,$this->width),mt_rand(0,$this->height),'*',$this->getRandColor());
        }
    }


    /**
     * @param $input
     * @return bool
     * 校验
     */
    public function validate($input) {
        if(!empty($input) && isset($_SESSION['captchaStr']) && strcasecmp($input,strtolower($_SESSION['captchaStr'])) == 0) {
            return true;
        }
        return false;
    }
}