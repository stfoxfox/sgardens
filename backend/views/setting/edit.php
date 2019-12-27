<?php
/**
 * Created by PhpStorm.
 * User: anatoliypopov
 * Date: 27/11/2016
 * Time: 20:29
 */

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use \yii\helpers\Url;

$this->params['breadcrumbs'][] = "Добавить настройку";
$this->title ='Добавить настройку';

$asset = \backend\assets\custom\SettingAsset::register($this);
?>

<div class="row">
    <div class="col-lg-8 col-lg-offset-2">
        <div class="ibox float-e-margins">
            <div class="ibox-content text-left p-md">

                <h2 class="text-center"><span class="text-success text-center">Добавить настройку</span>
                </h2>

                <p>
                    <?php
                    $form = ActiveForm::begin(['id' => 'add-spot', 'class'=>"m-t",'options' => ['enctype' => 'multipart/form-data']]); ?>


                    <?= $form->field($editForm, 'title',array())->textInput() ?>
                    <?= $form->field($editForm, 'key',array())->textInput() ?>
                    <?= $form->field($editForm, 'type',array())->dropDownList(\common\widgets\SettingEnum::values()) ?>
                    <?= $form->field($editForm, 'value',array())->textInput() ?>
                    
                    

                    
                    
                    <div class="row crop-value" style = "display: none;">
                        <div class="col-md-12">
                            <?=\backend\widgets\crop\CropImageWidget::widget([
                                    'form'=>$editForm,
                                    'fileAttribute'=>'value_image',
                                    'model'=> $item->type == "image" ? $item : '',
                                ]
                            )?>
                        </div>
                    </div>

                    <?= Html::submitButton('Изменить', ['class' => 'btn btn-outline btn-success', 'name' => 'add-type-button']) ?>

                    <?php ActiveForm::end(); ?>



            </div>
        </div>
    </div>

</div>
