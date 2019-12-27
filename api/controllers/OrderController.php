<?php
/**
 * Created by PhpStorm.
 * User: anatoliypopov
 * Date: 12/01/2017
 * Time: 23:37
 */

namespace api\controllers;


use common\components\controllers\ApiController;
use common\components\MyExtensions\MyError;
use common\components\MyExtensions\MyHelper;
use common\models\Address;
use common\models\Cart;
use common\models\CartItem;
use common\models\CartItemJson;
use common\models\CatalogItem;
use common\models\CatalogItemModificator;
use common\models\Order;
use common\models\Organisation;
use common\models\Promo;
use common\models\Restaurant;
use common\models\RestaurantZone;
use common\models\StopListElement;
use common\models\User;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class OrderController extends ApiController
{



    public function actionCheckPromo(){

        $orderJson = json_decode( \Yii::$app->request->getRawBody(),true);


        $cart =        ArrayHelper::getValue($orderJson,'cart',false);


        if ($cart && is_array($cart)){


            $cartArray = array();
            $cartItemsIds = array();
            $modificatorsIds = array();
            foreach ($cart as $cartItemJson){

                $cItem = new CartItemJson();
                $cItem->getFromJson($cartItemJson,$modificatorsIds);
                $cartItemsIds[]=$cItem->catalog_item_id;
                $cartArray[]=$cItem;


            }


            $modificatorsIds = array_unique($modificatorsIds,SORT_NUMERIC);



            $catalogItems = CatalogItem::find()->where(['id'=>$cartItemsIds])->all();

            $modificators = CatalogItemModificator::find()->where(['id'=>$modificatorsIds])->all();


            $sum =0;
            /**
             * @var CartItemJson $loadedCartItem
             */

            foreach ($cartArray as $loadedCartItem){

                $loadedCartItem->loadWithData($catalogItems,$modificators);

                $sum+=$loadedCartItem->getSum();

            }



            $promo = Promo::find()->where("min_order<{$sum}")->one();

            if ($promo){

                $returnObj = array(
                    'has_promo'=>true,
                    'promo'=>$promo->getFullJson()
                );


                if ($promo->action_type==Promo::ACTION_TYPE_DISCOUNT){
                    $returnObj['discount']=$promo->discount;
                }
                if ($promo->action_type==Promo::ACTION_TYPE_GIFT){

                    $gifts_array = array();

                        foreach ($promo->catalogItems as $item){

                            $gifts_array[]=$item->getJson();
                        }
                  

                    $returnObj['gifts']=$gifts_array;
                }

                return $this->sendResponse($returnObj);


            }else{
                return $this->sendResponse(['has_promo'=>false]);
            }

            return $this->sendError();
        }

        else
            return $this->sendError();


        /**
         * @var Promo $promo
         */
        $promo = Promo::find()->where(['action_type'=>$act])->one();


        if ($gift_id!=0 && $promo){

             $returnObj = array(
                 'has_promo'=>true,
                 'promo'=>$promo->getFullJson()
             );


            if ($promo->action_type==Promo::ACTION_TYPE_DISCOUNT){
                $returnObj['discount']=$promo->discount;
            }
            if ($promo->action_type==Promo::ACTION_TYPE_GIFT){

                $gifts_array = array();
                if ($gift_id==2){


                foreach ($promo->catalogItems as $item){

                    $gifts_array[]=$item->getJson();
                }
                }
                if ($gift_id==3){

                    $item= $promo->catalogItems[0];
                    $gifts_array[]=$item->getJson();

                }

                $returnObj['gifts']=$gifts_array;
            }

            return $this->sendResponse($returnObj);
        }
        else{

            return $this->sendResponse(['has_promo'=>false]);
        }




    }


    public function actionCreate(){

        $payment_link =false;
        $orderJson = json_decode( \Yii::$app->request->getRawBody(),true);

        $name =        ArrayHelper::getValue($orderJson,'name');
        $phone =        ArrayHelper::getValue($orderJson,'phone');
        $address =        ArrayHelper::getValue($orderJson,'address');
        $full_address =        ArrayHelper::getValue($orderJson,'full_address');

        $entrance =        ArrayHelper::getValue($orderJson,'entrance');
        $floor =        ArrayHelper::getValue($orderJson,'floor');
        $flat =        ArrayHelper::getValue($orderJson,'flat');

        $points =        ArrayHelper::getValue($orderJson,'points',0);



        $gift_id = ArrayHelper::getValue($orderJson,'gift_id');
        $promo_id = ArrayHelper::getValue($orderJson,'promo_id');

        $lat  =  ArrayHelper::getValue($orderJson,'lat');
        $lng  =  ArrayHelper::getValue($orderJson,'lng');


        $payment_method =        ArrayHelper::getValue($orderJson,'payment_method',0);



        $address_id =        ArrayHelper::getValue($orderJson,'address_id');
        $cart =        ArrayHelper::getValue($orderJson,'cart');


        $orderSum=0;



        if ($name && $phone && $address && $cart && $lat && $lng){

            $order  = new Order();

            $phone= MyHelper::preparePhone($phone);


            $address_string = $address;
            if ($full_address){
                $address_string = $full_address;
            }

            $order->name=$name;
            $order->phone=$phone;
            $order->address=$address_string;
            $order->lat=$lat;
            $order->lng=$lng;
            $order->order_source=Order::TYPE_APP;


            $order->promo_id= $promo_id;
            if($gift_id && $promo_id && $promo = Promo::find()->where(['id'=>$promo_id])->one() && $gift = CatalogItem::find()->where(['id'=>$gift_id])->one() ){



                $order->gift_id=$gift->id;
            }

            $update_address= false;
            $user_address = new Address();
            if ($address_id && !\Yii::$app->user->isGuest && $user_address = Address::findOne($address_id)){


                if( $user_address->user_id == \Yii::$app->user->id){
                    $update_address= true;
                }
            }

            if ($full_address){

                $user_address->full_address=$full_address;
            }
            if ($floor){

                $order->floor= $floor;
                if ($update_address){

                    $user_address->floor= $floor;
                }
            }

            if ($entrance){

                $order->entrance=$entrance;

                if ($update_address){
                    $user_address->entrance=$entrance;
                }
            }

            if ($flat){

                $order->flat=$flat;

                if ($update_address){
                    $user_address->flat=$flat;
                }
            }

            $user_address->save();

            //if ($points){
                $order->points_number=$points;
           // }


            $order->payment_type=$payment_method;


            $cartItemsArray = array();
            foreach ($cart as $cartJson){


                $cItem = CartItem::getFromArray($cartJson);

                $cartItemsArray[]=$cItem ;

            }



            if ($restaurantZone = self::CheckZone($lat,$lng)) {

                $restaurant = $restaurantZone->restaurant;
                if (count($cartItemsArray) > 0) {

                    /**
                     * @var User $user
                     */
                    if (\Yii::$app->user->isGuest) {


                        $user = User::find()->where(['username' => $phone])->one();

                        if (!$user) {
                            $user = new User();
                            $user->username = $phone;
                            $user->name=$order->name;
                            $password = self::randomString(4);
                            $user->setPassword($password);
                            $user->generateAuthKey();
                            $user->status=User::STATUS_NOT_CONFIRMED;
                            $user->save();
                        }


                    } else {

                        $user = \Yii::$app->user->getIdentity();

                        if (!$user->name){
                            $user->name =  $order->name;
                            $user->save();
                            $user->generateXml();

                        }elseif (strlen($user->name)==0){

                            $user->name =  $order->name;
                            $user->save();
                            $user->generateXml();
                        }
                    }

                    $order->user_id = $user->id;

                    $order->restaurant_id=$restaurant->id;

                    $order->restaurant_zone_id=$restaurantZone->zone_external_id;
                    if ($order->save()) {

                        $cart = new Cart();
                        $cart->order_id = $order->id;
                        $cart->user_id = $user->id;

                        if ($cart->save()) {
                            /**
                             * @var CartItem $cartItemToSave
                             */


                            foreach ($cartItemsArray as $cartItemToSave) {
                                $cartItemToSave->cart_id = $cart->id;
                                if ($cartItemToSave->save()) {


                                    foreach ($cartItemToSave->tempModificatorsArray as $modificator) {

                                        $modificator->cart_item_id = $cartItemToSave->id;
                                        $modificator->save();
                                    }
                                }
                            }


                            /**
                             * @var Order $newOrder
                             */
                            $newOrder = Order::find()->where(['id'=>$order->id])->with(['carts.cartItems','carts.cartItems.catalogItem','carts.cartItems.cartItemModificators'])->one();

                            $newOrder->updateSum();


                            if ($payment_method==Order::PAYMENT_TYPE_CARD){

                                /**
                                 * @var Organisation $organisation
                                 */



                                if ($organisation =$restaurant->organisation){


                                    $merchantUser = $organisation->sber_user;
                                    $merchantPass = $organisation->sber_pass;
                                    $arrReq = array();

                                    $arrReq['userName'] = $merchantUser;
                                    $arrReq['password'] = $merchantPass;// Идентификатор магазина

                                    $arrReq['orderNumber'] = "PA".$order->id; // Идентификатор заказа в системе магазина
                                    $arrReq['amount'] = $newOrder->payment_summ*100; // Сумма заказа
//$arrReq['pg_lifetime'] = 3600 * 24; // Время жизни счёта (в секундах)
                                    $arrReq['description'] = 'Оплата заказа №' . $order->id; // Описание заказа (показывается в Платёжной системе)
                                    $arrReq['returnUrl'] = 'ru.pronto24.app://order';
                                    $arrReq['failUrl'] = 'ru.pronto24.app://fail';
                                    /* Параметры безопасности сообщения. Необходима генерация pg_salt и подписи сообщения. */
                                    $query = http_build_query($arrReq);
                                    $orderUrl = 'https://securepayments.sberbank.ru/payment/rest/register.do?'.$query;
                                   //$orderUrl = 'https://3dsec.sberbank.ru/payment/rest/register.do?'.$query;


                                    $orderJ = json_decode(file_get_contents($orderUrl),true);
                                    $payment_link=ArrayHelper::getValue($orderJ,'formUrl',$orderJ);
                                    $sber_id=ArrayHelper::getValue($orderJ,'orderId',false);

                                    if ($sber_id){

                                        $newOrder->payment_type=Order::PAYMENT_TYPE_CARD;
                                        $newOrder->payment_status=Order::PAYMENT_STATUS_WAIT;
                                        $newOrder->sber_id=$sber_id;
                                        $newOrder->save();

                                    }
                                   // return $this->sendResponse([$orderJ]);
                                }
                            }

                            $returnArr=
                           [
                                'order_id' => $order->id,
                                'restaurant' => $restaurant->getDetailedJson(),
                                'success'=>true,
                                'points_count'=>floor($newOrder->payment_summ*\Yii::$app->params['points_rate']/100),

                            ];

                            if ($payment_link){

                                $returnArr['payment_url']=$payment_link;
                            }



                            $newOrder->generateXML();

                            return $this->sendResponse($returnArr);
                        }
                    }
                }

            }
            else{

                return $this->sendResponse([
                    'success'=>false,
                    'error_message'=>"Не удалось найти ресторан. Попробуйте сменить адрес"
                ]);
            }
            

        }


        return $this->sendError(MyError::BAD_REQUEST);


    }



   static function randomString($length = 6) {
        $str = "";
        $characters = array_merge(  range('0','9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }

    /**
     * @param $lat
     * @param $lng
     * @return bool|RestaurantZone
     */
    public static function  CheckZone($lat,$lng){


        $returnObj = false;

        if ($lat && $lng){

            $zone  = RestaurantZone::find()->where(["ST_Contains(restaurant_zone.zone,ST_GeomFromText('POINT({$lng}  {$lat} )',4326))"=>true])->all();

            if (count($zone)>0){



                /**
                 * @var RestaurantZone $found_zone
                 * @var  Restaurant $restaurant
                 */
                $found_zone = $zone[0];

                $restaurant = $found_zone->restaurant;


                //$hours_info = $restaurant->getHoursInfo();


                if ($restaurant->checkHoursInfo()){

                    $returnObj =  $found_zone;
                }




            }


        }


        return $returnObj;

    }

}