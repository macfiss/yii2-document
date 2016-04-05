<?php

use app\modules\document\DocumentAsset;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\document\models\Template */

$this->title = Yii::t('document', 'Новый шаблон');
$this->params['breadcrumbs'][] = ['label' => Yii::t('document', 'Шаблоны'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
DocumentAsset::register($this);
?>

<div class="template-create">

    <div class="row">
        <div class="col-lg-12">
            <?= $this->render('../default/alert');?>
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
