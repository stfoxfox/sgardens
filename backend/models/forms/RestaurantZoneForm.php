<?php
/**
 * Created by PhpStorm.
 * User: anatoliypopov
 * Date: 02/12/2016
 * Time: 11:29
 */

namespace backend\models\forms;


use common\models\RestaurantZone;
use common\models\Tag;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\db\Expression;

class RestaurantZoneForm extends Model
{

    public $restaurant_id;
    public $zone;
    public $min_order;
    public $min_time;
    public $max_time;
    public $created_at;
    public $updated_at;
    public $zone_external_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['zone', 'restaurant_id'], 'required'],
            [['restaurant_id'], 'integer'],
            [['zone'], 'safe'],
            [['created_at', 'updated_at'], 'safe'],
            [['min_order', 'min_time', 'max_time', 'zone_external_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'restaurant_id' => 'Restaurant ID',
            'zone' => 'Zone',
            'min_order' => 'Min Order',
            'min_time' => 'Min Time',
            'max_time' => 'Max Time',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'zone_external_id' => 'Zone External ID',
        ];
    }

    /**
     * @return bool|RestaurantZone
     */

    public  function createRestaurantZone(){
        if ($this->validate()){
            $restaurantZone = new RestaurantZone();

            $restaurantZone->restaurant_id = $this->restaurant_id;
            $restaurantZone->min_time = $this->min_time;
            $restaurantZone->max_time = $this->max_time;
            $restaurantZone->min_order = $this->min_order;
            $restaurantZone->zone_external_id = $this->zone_external_id;
            $restaurantZone->created_at = date('Y-m-d H:i:s');
            $restaurantZone->updated_at = date('Y-m-d H:i:s');

            $coords = explode(',', $this->zone);
            $zone = [];
            for($i = 0; $i < count($coords); $i+=2){
                $zone[] = $coords[$i+1]." ".$coords[$i];
            }
            $zoneString = implode(',', $zone);

            $restaurantZone->zone =new Expression("ST_GeomFromText('POLYGON(({$zoneString}))',4326)");

            if($restaurantZone->save()){
                return $restaurantZone;
            }else{
                var_dump($restaurantZone->getErrors());
            }
        } 
        return false;
    }
}