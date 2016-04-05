<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\modules\document\DocumentAsset;

/* @var $this yii\web\View */
/* @var $model app\modules\document\models\Template */

$this->title = Yii::t('document', 'Просмотр шаблона');
$this->params['breadcrumbs'][] = ['label' => Yii::t('document', 'Шаблоны'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
DocumentAsset::register($this);
?>

<div class="template-view">

    <div class="row">
        <div class="col-lg-12">
            <?= $this->render('../default/alert');?>
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
    </div>

    <p>
        <?= Html::a('<i class="glyphicon glyphicon-pencil"></i> '.Yii::t('document', 'Редактировать'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="glyphicon glyphicon-trash"></i> '.Yii::t('document', 'Удалить'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('document', 'Вы уверены, что хотите удалить шаблон?'),
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('<i class="glyphicon glyphicon-menu-left"></i> '.Yii::t('document', 'Отмена'), ['index'], [
            'class' => 'btn btn-default',
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'description:ntext',
            'path',
        ],
    ]) ?>

</div>
