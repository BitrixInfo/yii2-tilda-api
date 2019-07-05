<?php
 /* @var $pages */
 /* @var $model */
 /* @var $modelName */
 /* @var field */
 ?>

<div class="form-group">
    <label for="<?= mb_strtolower($modelName) ?>-<?= $field ?>">
        <?= $model->attributeLabels()[$field] ?>
    </label>
    <select id="<?= mb_strtolower($modelName) ?>-<?= $field ?>" name="<?= $modelName ?>[<?= $field ?>]" class="form-control">
        <?php foreach ($pages as $id => $page): ?>
            <option
                    <?= ($model['attributes'][$field] == $id) ? "selected" : "" ?>
                    value="<?= $id ?>">
                <?=$page ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

