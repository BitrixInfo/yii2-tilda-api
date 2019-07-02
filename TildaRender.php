<?php
/**
 * Created by PhpStorm.
 * User: wintermute
 * Date: 02-Jul-19
 * Time: 21:14
 */

namespace daccess1\tilda;


use daccess1\tilda\models\TildaPage;
use daccess1\tilda\models\TildaScript;
use daccess1\tilda\models\TildaStyle;

class TildaRender
{
    public static function pageHtml($pageID) {
        $page = TildaPage::findOne(['id' => $pageID]);
    }

    public static function loadPage($pageID) {
        $page['html'] = TildaPage::findOne(['id' => $pageID])->html;

        $styles = TildaStyle::find()->where(['tilda_page_id' => $page->id])->all();
        foreach ($styles as $style) {
            $page['styles'][] = $style->path;
        }
        $scripts = TildaScript::find()->where(['tilda_page_id' => $page->id])->all();
        foreach ($scripts as $script) {
            $page['scripts'][] = $script->path;
        }

        return $page;
    }
}