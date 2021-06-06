<?php

namespace Weirin\Wechat;

/**
 * Class Emoji
 * @package common\helpers
 */
class Emoji
{
    /*
      * 字节转Emoji表情
      * http://www.cnblogs.com/zhengwk/p/5828843.html
      * @param $cp
      * @return string
      */
    public static function bytes2emoji($cp)
    {
        if ($cp > 0x10000) { // 4 bytes
            return chr(0xF0 | (($cp & 0x1C0000) >> 18)) . chr(0x80 | (($cp & 0x3F000) >> 12)) . chr(0x80 | (($cp & 0xFC0) >> 6)) . chr(0x80 | ($cp & 0x3F));
        } else
            if ($cp > 0x800) { // 3 bytes
                return chr(0xE0 | (($cp & 0xF000) >> 12)) . chr(0x80 | (($cp & 0xFC0) >> 6)) . chr(0x80 | ($cp & 0x3F));
            } else
                if ($cp > 0x80) { // 2 bytes
                    return chr(0xC0 | (($cp & 0x7C0) >> 6)) . chr(0x80 | ($cp & 0x3F));
                } else { // 1 byte
                    return chr($cp);
                }
    }

    /*
     * 99个微信表情
     */
    public static function codes()
    {
        return [
            "/::)",	  //微笑
            "/::~",	 //撇嘴
            "/::B",	 //色
            "/::|",	 //发呆
            "/:8-)",	 //得意
            "/::<",	 //流泪
            "/::$",	 //害羞
            "/::X",	 //闭嘴
            "/::Z",	 //睡
            "/::'(",	 //大哭
            "/::-|",	 //尴尬
            "/::@",	 //发怒
            "/::P",	 //调皮
            "/::D",	 //呲牙
            "/::O",	 //惊讶
            "/::(",	 //难过

            "/:--b",	 //冷汗
            "/::Q",	 //抓狂
            "/::T",	 //吐
            "/:,@P",	 //偷笑
            "/:,@-D",	 //可爱
            "/::d",	 //白眼
            "/:,@o",	 //傲慢
            "/:|-)",	 //困
            "/::!",	 //惊恐
            "/::L",	 //流汗
            "/::>",	 //憨笑
            "/::,@",	 //大兵
            "/:,@f",	 //努力
            "/::-S",	 //咒骂
            "/:?",	     //疑问
            "/:,@x",	 //嘘

            "/:,@@",	 //晕
            "/:,@!",	 //衰
            "/:!!!",	 //骷髅
            "/:xx",	 //敲打
            "/:bye",	 //再见
            "/:wipe",	 //擦汗
            "/:dig",	 //抠鼻
            "/:handclap",	 //鼓掌
            "/:B-)",	 //坏笑
            "/:<@",	 //左哼哼
            "/:@>",	 //右哼哼
            "/::-O",	 //哈欠
            "/:>-|",	 //鄙视
            "/:P-(",	 //委屈
            "/::'|",	 //快哭了
            "/:X-)",	 //阴险

            "/::*",	 //亲亲
            "/:8*",	 //可怜
            "/:pd",	 //菜刀
            "/:<W>",	 //西瓜
            "/:beer",	 //啤酒
            "/:coffee",	 //咖啡
            "/:pig",	 //猪头
            "/:rose",	 //玫瑰
            "/:fade",	 //凋谢
            "/:showlove",	 //示爱
            "/:heart",	 //爱心
            "/:break",	 //心碎
            "/:cake",	 //蛋糕
            "/:bome",	 //炸弹
            "/:shit",	 //便便
            "/:moon",	 //月亮

            "/:sun",	 //太阳
            "/:hug",	 //拥抱
            "/:strong",	 //强
            "/:weak",	 //弱
            "/:share",	 //握手
            "/:v",	 //胜利
            "/:@)",	 //抱拳
            "/:jj",	 //勾引
            "/:@@",	 //拳头
            "/:ok",	 //Ok
            "/:jump",	 //跳舞
            "/:shake",	 //发抖
            "/:<O>",	 //怄火
            "/:circle",	 //转圈
            "\\ue415",  // 笑脸
            "\\ue40c" ,  // 生病

            "\\ue412", // 破涕为笑
            "\\ue40d", // 吐舌
            "\\ue40d", // 脸红
            "\\ue107", // 恐惧
            "\\ue403", // 失望
            "\\ue40e", // 无语
            "", // 嘿哈
            "", // 捂脸
            "", // 奸笑
            "", // 机智
            "",  // 皱眉
            "", // 耶
            "\\ue11b", //鬼魂
            "\\ue41d", //合十
            "\\ue14c", // 强壮
            "\\ue312", // 庆祝

            "\\ue112", // 礼物
            "", // 红包
            "", // 鸡
        ];
    }

    /*
     * 将表情图片转换成{{n}}占位符
     * @param string $html
     * @return string
     */
    public static function image2placeHolder($html)
    {
        return preg_replace("/<img class=\"icon_emotion_single icon_smiley_(.*?)\" [^>]+>/i", '{{$1}}', $html);
    }


    /*
     * 将{{n}}占位符转换成表情图片
     * @param string $html
     * @param string $baseUrl
     * @return string
     */
    public static function placeHolder2Image($html, $blankImg)
    {
        $img = '<img class="icon_emotion_single icon_smiley_' . '$1' . '" src="' . $blankImg . ' >';

        return preg_replace("/\{\{(.*?)\}\}/", $img, $html);
    }

    /*
     * 将{{n}}占位符转换成表情编码
     * @param string $html
     * @return string
     */
    public static function placeHolder2Code($html)
    {
        return preg_replace_callback(
            '/\{\{(.*?)\}\}/',
            function ($matches) {
                $codes = self::codes();
                return $codes[$matches[1]];
            },
            $html
        );
    }
}
