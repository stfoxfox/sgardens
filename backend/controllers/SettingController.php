<?php

namespace backend\controllers;

use backend\models\forms\SettingForm;
use common\components\controllers\BackendController;
use common\models\BaseModels\SettingBase;
use common\models\Setting;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class SettingController extends  BackendController
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

    public function actionIndex(){
        $this->pageHeader = "Настройки сайта";

        $query = Setting::find();
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('index', [
            'settings' => $models,
            'pages' => $pages,
        ]);
    }

    public function actionAddItem(){
        $addForm = new SettingForm();

        if ($addForm->load(\Yii::$app->request->post())){
            if ($addForm->createItem()){
                return $this->redirect(Url::toRoute('index'));
            }
        }
        return $this->render('add-item',['addForm'=>$addForm]);
    }

    public function actionItemDell(){

        $item_id= \Yii::$app->request->post('item_id');
        $item = Setting::findOne($item_id);
        if (Setting::deleteAll(['id'=>$item_id])>0){
            Setting::dropItemFromCache([$item->key => $item->value]);
            return $this->sendJSONResponse(array('error'=>false,'item_id'=>$item_id));
        }

        return  $this->sendJSONResponse(array('error'=>true));

    }

    public function actionEdit($id){
        $setting = Setting::findOne($id);
        if ($setting){
            $editForm = new SettingForm();
            if ($editForm->load(\Yii::$app->request->post())){
                $editForm->editSetting($setting);
                return $this->redirect(Url::toRoute(['index']));
            }else{
                $editForm->loadFromItem($setting);
                $setting->value_image = $setting->value;
                return $this->render('edit',['editForm'=>$editForm,'item'=>$setting]);
            }
        }
    }

}