<?php
/** @var $this \yii\web\View */

foreach ($page['styles'] as $style) {
    $this->registerCssFile($style);
}
foreach ($page['scripts'] as $script) {
    $this->registerJsFile($script,['depends' => [yii\web\JqueryAsset::className()]]);
}

?>
