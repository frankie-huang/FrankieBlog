<?php
    //10>设置session,必须处于脚本最顶部
    session_start();

    $image = imagecreatetruecolor(100, 30);        //1>设置验证码图片大小的函数
    //5>设置验证码颜色 imagecolorallocate(int im, int red, int green, int blue);
    $bgcolor = imagecolorallocate($image,255,255,255); //#ffffff
    //6>区域填充 int imagefill(int im, int x, int y, int col)  (x,y) 所在的区域着色,col 表示欲涂上的颜色
    imagefill($image, 0, 0, $bgcolor);
    //10>设置变量
    $captcha_code = "";
    //7>生成随机数字
    for($i=0;$i<4;$i++){
        //设置字体大小
        $fontsize = 6;        
        //设置字体颜色，随机颜色
        $fontcolor = imagecolorallocate($image, rand(0,2),rand(0,2), rand(0,2));            //0-120深颜色
        //设置数字
        // $fontcontent = rand(0,9);

        
        //设置需要随机取的值,去掉容易出错的值如0和o
        $data ='abcdefghigkmnpqrstuvwxy3456789';
        //取出值，字符串截取方法   strlen获取字符串长度
        $fontcontent = substr($data, rand(0,strlen($data)-1),1);
        

        //10>.=连续定义变量
        $captcha_code .= $fontcontent;    
        //设置坐标
        $x = ($i*100/4)+rand(5,10);
        $y = rand(5,10);

        imagestring($image,$fontsize,$x,$y,$fontcontent,$fontcolor);
    }
    //10>存到session
    $_SESSION['authcode'] = $captcha_code;
    //8>增加干扰元素，设置雪花点
    for($i=0;$i<200;$i++){
        //设置点的颜色，50-200颜色比数字浅，不干扰阅读
        $pointcolor = imagecolorallocate($image,rand(50,200), rand(50,200), rand(50,200));        
        //imagesetpixel — 画一个单一像素
        imagesetpixel($image, rand(1,99), rand(1,29), $pointcolor);
    }
    //9>增加干扰元素，设置横线
    for($i=0;$i<4;$i++){
        //设置线的颜色
        $linecolor = imagecolorallocate($image,rand(80,220), rand(80,220),rand(80,220));
        //设置线，两点一线
        imageline($image,rand(1,99), rand(1,29),rand(1,99), rand(1,29),$linecolor);
    }

    //2>设置头部，image/png
    header('Content-Type: image/png');
    //3>imagepng() 建立png图形函数
    imagepng($image);
    //4>imagedestroy() 结束图形函数  销毁$image
    imagedestroy($image);
?>

<!--使用中文验证码：
    //7>设置ttf字体
    $fontface = './FZYTK.TTF';
    //7>设置字库，实现简单的数字储备
    $str='天地不仁以万物为刍狗圣人不仁以百姓为刍狗这句经常出现在控诉暴君暴政上地残暴不仁把万物都当成低贱的猪狗来看待而那些高高在上的所谓圣人们也没两样还不是把我们老百姓也当成猪狗不如的东西但实在正取的解读是地不情感用事对万物一视同仁圣人不情感用事对百姓一视同仁执子之手与子偕老当男女主人公含情脉脉看着对方说了句执子之手与子偕老女方泪眼朦胧含羞地回一句讨厌啦这样的情节我们是不是见过很多但是我们来看看这句的原句死生契阔与子成说执子之手与子偕老于嗟阔兮不我活兮于嗟洵兮不我信兮意思是说战士之间的约定说要一起死现在和我约定的人都走了我怎么活啊赤裸裸的兄弟江湖战友友谊啊形容好基友的基情比男女之间的爱情要合适很多吧';
    //str_split()切割字符串为一个数组,一个中文在utf_8为3个字符
    $strdb = str_split($str,3);

    ...

    //随机选取中文
    $in = rand(0,count($strdb)-1);
    $cn = $strdb[$in];

    /*imagettftext (resource $image ,float $size ,float $angle ,int $x ,int $y,int $color,
    string $fontfile ,string $text ) 幕布 ，尺寸，角度，坐标，颜色，字体路径，文本字符串
    mt_rand()生成更好的随机数,比rand()快四倍*/
    imagettftext($image, mt_rand(20,24),mt_rand(-60,60),(40*$i+20),mt_rand(30,35),$fontcolor,$fontface,$cn);

-->
<!--摘自文章http://www.php100.com/html/dujia/2015/0919/8975.html-->