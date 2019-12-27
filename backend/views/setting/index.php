<?php
/**
 * Created by PhpStorm.
 * User: anatoliypopov
 * Date: 16/12/2016
 * Time: 01:42
 */
use common\components\MyExtensions\MyImagePublisher;
use yii\helpers\Url;
use common\models\Setting;


/* @var $this yii\web\View */


$this->title ='Настройки сайта';

$this->params['breadcrumbs'][] = "Настройки сайта";

$asset = \backend\assets\custom\SettingAsset::register($this);

?>

<div class="row">
    <div class="col-lg-12 animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5>Список настроек</h5>
                        <div class="pull-right">
                            <a  class="btn btn-outline btn-primary btn-xs" href="<?=Url::toRoute(['add-item'])?>">
                                Добавить настройку
                            </a>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <table class="table">
                            <thead>
                            <tr><th>#</th>
                                <th>Заголовок</th>
                                <th>Ключ</th>
                                <th>Значение</th>
                                <th>Действие</th>
                            </tr>
                            </thead>
                            <tbody id="sortable">

                            <?php
                            /** @var \common\models\Promo $item */
                            foreach($settings as $item)  { 
                                $item->file_name = $item->value;    
                            ?>
                                <tr id="item_<?=$item->id?>" sort-id="<?=$item->id?>">
                                    <td><?= $item->id ?></td>
                                    <td><?= $item->title ?></td>
                                    <td><?= $item->key ?></td>
                                    <td>
                                    <?php
                                    if($item->type == 'image'){
                                        if($item->uploadTo('value')) { ?><img width="50" height="50" src="<?=(new MyImagePublisher($item))->thumbnail(100,100)?>"> <?php } else echo "-"; 
                                    }else{
                                        echo $item->value;
                                    }
                                    ?>
                                    </td>
                                    <td>
                                        <a href="#" class="dell-catalog-item" data-item-id="<?=$item->id?>" data-item-name="<?=$item->title?>">Удалить</a> | <a href="<?=Url::toRoute(['edit','id'=>$item->id])?>">Изменить</a>
                                    </td>

                                </tr>
                            <?php  } ?>

                            </tbody>
                        </table>




                    </div>
                </div>


            </div>
        </div>



    </div>
</div>