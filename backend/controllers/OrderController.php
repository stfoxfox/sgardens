<?php
/**
 * Created by PhpStorm.
 * User: anatoliypopov
 * Date: 22/06/2017
 * Time: 13:18
 */

namespace backend\controllers;


use common\components\controllers\BackendController;
use common\models\Order;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class OrderController extends  BackendController
{


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [

                    [

                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }



    public function actionChangeStatus($id,$status){



        $order = Order::findOne($id);

        if ($order){
            $order->status= $status;

            $order->save();



        }


        return $this->redirect(Url::toRoute(['order/view','id'=>$id]));


    }

    public function actionIndex(){


        $this->pageHeader = "Список заказов";



        $query = Order::find();
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('created_at DESC')
            ->all();

        return $this->render('index', [
            'orders' => $models,
            'pages' => $pages,
        ]);



    }

    public function actionView($id){



         if ($order= Order::findOne($id)){

             $this->pageHeader = "Заказ номер ". $order->id;


             return $this->render('view',['order'=>$order]);
        }

        throw new NotFoundHttpException('Заказ не найден');
    }

}