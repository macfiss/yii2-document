<?php
/**
 * @package   yii2-document
 * @author    Yuri Shekhovtsov <shekhovtsovy@yandex.ru>
 * @copyright Copyright &copy; Yuri Shekhovtsov, lowbase.ru, 2015 - 2016
 * @version   1.0.0
 */

use kartik\widgets\Select2;
use lowbase\document\models\Field;
use lowbase\document\models\Template;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\document\models\Field */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="field-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-lg-12">
            <p>
                <?= Html::submitButton('<i class="glyphicon glyphicon-floppy-disk"></i> '.Yii::t('document', 'Сохранить'), ['class' => 'btn btn-primary']) ?>
                <?php
                if (!$model->isNewRecord) {
                    echo Html::a('<i class="glyphicon glyphicon-trash"></i> '.Yii::t('document', 'Удалить'), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => Yii::t('document', 'Вы уверены, что хотите удалить поле?'),
                            'method' => 'post',
                        ],
                    ]);
                echo " " . Html::a('<i class="glyphicon glyphicon-menu-left"></i> '.Yii::t('document', 'Отмена'), ['template/update', 'id' => $model->template_id], [
                    'class' => 'btn btn-default']);
                } else {
                    echo Html::a('<i class="glyphicon glyphicon-menu-left"></i> '.Yii::t('document', 'Отмена'), ['template/update', 'id' => $template->id], [
                        'class' => 'btn btn-default']);
                } ?>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'template_id')->widget(Select2::classname(), [
                'data' => Template::getAll(),
                'options' => [
                    'placeholder' => '',
                    'id' => 'template_id'
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'type')->dropDownList(Field::getTypes()) ?>
        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'param')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'min')->textInput() ?>
        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'max')->textInput() ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
