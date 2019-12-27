<?php
/**
 * Created by PhpStorm.
 * User: anatoliypopov
 * Date: 27/11/2016
 * Time: 20:11
 */

namespace common\models;


use common\models\BaseModels\CatalogItemModificatorBase;
use yii\helpers\ArrayHelper;

class CatalogItemModificator extends CatalogItemModificatorBase
{

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatalogItems()
    {
        return $this->hasMany(CatalogItem::className(), ['id' => 'catalog_item_id'])->viaTable('catalog_item_modificator_link', ['catalog_item_modificator_id' => 'id']);
    }


    public static function getItemsForSelect(){


        return ArrayHelper::map(self::find()->all(), 'id', 'title');
    }

    public function getJson(){

        $returnObj =  array(
            'id'=>$this->id,
            'title'=>$this->title,
            'price'=>$this->price,
        );

        if ($this->icon){

            $returnObj['icon']=$this->icon;
        }


        return $returnObj;
    }

    public function uploadTo($attribute){
        if($this->$attribute)
            return \Yii::getAlias('@common')."/uploads/modificator/{$this->$attribute}";
        else
            return null;
    }


}