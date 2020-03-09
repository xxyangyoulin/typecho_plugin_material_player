<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * MaterialPlayer
 *
 * @package MaterialPlayer
 * @author xxyangyoulin
 * @version 1.0.0
 * @link http://typecho.org
 */
class MaterialPlayer_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->header = array('MaterialPlayer_Plugin', 'header');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('MaterialPlayer_Plugin', 'footer');
    }

    public static function header()
    {
        if (Typecho_Widget::widget('Widget_Options')
                ->plugin('MaterialPlayer')->hasJquery == "no") {
            echo "<link href=\"https://fonts.googleapis.com/icon?family=Material+Icons\" rel=\"stylesheet\">";
        }
        echo "<link rel=\"stylesheet\" href=\"" . Helper::options()->pluginUrl . "/MaterialPlayer/static/mp.min.css\" />";
    }

    public
    static function footer()
    {
        if (Typecho_Widget::widget('Widget_Options')->plugin('MaterialPlayer')->hasIcons == "no") {
            echo '<script  src="' . Helper::options()->pluginUrl . '/MaterialPlayer/static/jquery.2.2.4.min.js"></script>' . "\n";
        }
        echo '<script  src="' . Helper::options()->pluginUrl . '/MaterialPlayer/static/mp.min.js"></script>' . "\n";
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public
    static function deactivate()
    {
    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public
    static function config(Typecho_Widget_Helper_Form $form)
    {
        $item = new Typecho_Widget_Helper_Form_Element_Radio('autoPlay',
            array('yes' => '是', 'no' => '否'), 'no', _t('自动播放背景音乐(不建议)：'));
        $form->addInput($item);
        $item = new Typecho_Widget_Helper_Form_Element_Radio('autoPlayNext',
            array('yes' => '是', 'no' => '否'), 'yes', _t('自动播放下一曲：'));
        $form->addInput($item);

        $musicList = new Typecho_Widget_Helper_Form_Element_Textarea('mpMusicList',
            NULL,
            "5276818 #*# 小星星变奏曲 - 莫扎特,\nhttp://music.163.com/song/media/outer/url?id=2177197.mp3 #*# Moon River - Audrey Hepburn,\n",
            _t('背景音乐列表'),
            _t('<span style="font-size: 18px">说明:</span><br>支持网易云音乐ID和直接音乐URL资源。
<br><span style="font-size: 18px">格式:</span>
<br>使用符号（<b style="color: #467B96">#*#</b>）分割音乐代码与音乐标题，使用逗号（<b style="color: #467B96">,</b>）分割不同音乐。写完一条可以换一行。
<br><br><span style="font-size: 18px">例如:</span>
<br>5276818 <b style="color: #467B96">#*#</b> 小星星变奏曲 - 莫扎特<b style="color: #467B96">,</b>
<br>http://music.163.com/song/media/outer/url?id=2177197.mp3 <b style="color: #467B96">#*#</b> Moon River - Audrey Hepburn<b style="color: #467B96">,</b><br>'));
        $form->addInput($musicList);

        $item = new Typecho_Widget_Helper_Form_Element_Radio('hasJquery',
            array('yes' => '是', 'no' => '否'), 'no', _t('网站已有JQuery资源？'));
        $form->addInput($item);
        $item = new Typecho_Widget_Helper_Form_Element_Radio('hasIcons',
            array('yes' => '是', 'no' => '否'), 'no', _t('网站已有Google Material Icons资源？'));
        $form->addInput($item);
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public
    static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    private
    static function getMusicList()
    {
        $result = [];

        $list_str = trim(Typecho_Widget::widget('Widget_Options')
            ->plugin('MaterialPlayer')->mpMusicList);
        if (empty($list_str)) {
            return $result;
        }

        $list_arr = explode(',', $list_str);
        foreach ($list_arr as $list_item) {
            $list_item = trim($list_item);
            if (!$list_item && strlen($list_item) < 5) {
                continue;
            }
            $item_info = explode("#*#", $list_item);
            if (count($item_info) < 2) {
                continue;
            }

            $result_item['src'] = trim($item_info[0]);
            if (is_numeric($result_item['src'])) {
                $result_item['src'] = 'https://music.163.com/song/media/outer/url?id=' . $result_item['src'];
            }
            $result_item['name'] = trim($item_info[1]);

            $result[] = $result_item;
        }
        return $result;
    }


    public static function fixed($top = null, $right = null, $left = null)
    {
        if (!array_key_exists('MaterialPlayer', Typecho_Plugin::export()['activated'])) {
            return;
        }

        $top = $top ? "top:" . $top . ";" : "";
        $right = $right ? "right:" . $right . ";" : "";
        $left = $left ? "left:" . $left . ";" : "";

        echo '<div style="position: fixed;' . $top . $right . $left . '">';
        MaterialPlayer_Plugin::insert();
        echo '</div>';
    }

    public static function insert()
    {
        if (!array_key_exists('MaterialPlayer', Typecho_Plugin::export()['activated'])) {
            return;
        }

        $musicList = MaterialPlayer_Plugin::getMusicList();
        if (sizeof($musicList) == 0) {
            echo "<!--Material Player： 没有歌曲-->";
            return;
        }

        $liHtml = "";
        $autoPlay = Typecho_Widget::widget('Widget_Options')->plugin('MaterialPlayer')->autoPlay;
        $autoPlayNext = Typecho_Widget::widget('Widget_Options')->plugin('MaterialPlayer')->autoPlayNext;

        foreach ($musicList as $item) {
            $liHtml .= "<li data-src='" . htmlspecialchars($item['src']) . "'>"
                . htmlspecialchars($item['name']) . "</li>";
        }

        echo '<!--Material Player-->
    <div id="mp-music" data-autoplay="' . $autoPlay . '"  data-autoplaynext="' . $autoPlayNext . '">
        <div id="mp-music-wrapper">
            <div id="mp-list">
                <ul>
                    ' . $liHtml . '
                </ul>
            </div>
            <div id="mp-ctrl-group">
                <button id="mp-music-album" class=" mp-mdl-button  mp-mdl-button--icon"><i
                        class="material-icons">music_note</i>
                </button>
                <div id="mp-hide-panel">
                    <div id="mp-ctrl-panel">
                        <button id="mp-music-volume" class=" mp-mdl-button mp-mdl-button--icon"><i
                                class="material-icons">volume_up</i></button>
                        <button id="mp-music-list" class=" mp-mdl-button mp-mdl-button--icon"><i
                                class="material-icons">playlist_play</i></button>
                        <button id="mp-music-prev" class=" mp-mdl-button mp-mdl-button--icon"><i
                                class="material-icons">skip_previous</i></button>
                        <button id="mp-music-play" class=" mp-mdl-button mp-mdl-button--icon"><i
                                class="material-icons">play_circle_filled</i></button>
                        <button id="mp-music-next" class=" mp-mdl-button mp-mdl-button--icon"><i
                                class="material-icons">skip_next</i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>';

    }
}
