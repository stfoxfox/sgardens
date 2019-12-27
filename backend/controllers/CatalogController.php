<?php
/**
 * Created by PhpStorm.
 * User: anatoliypopov
 * Date: 23/11/2016
 * Time: 00:47
 */

namespace backend\controllers;


use backend\models\forms\AddCatalogCategoryForm;
use backend\models\forms\CatalogItemForm;
use backend\models\forms\EditCatalogCategoryForm;
use common\components\controllers\BackendController;
use common\models\CatalogCategory;
use common\models\CatalogItem;
use yii\filters\AccessControl;
use yii\helpers\Url;

class CatalogController extends BackendController
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


    public function actionView($id=null){


        $this->pageHeader="Управление Меню";


        if(isset($id)){

            $categoryObj=CatalogCategory::findOne($id);

            if($categoryObj)
                return $this->render('index',['category'=>$categoryObj, 'CatalogCategories' =>CatalogCategory::find()->orderBy('sort')->all()]);
            else
                return $this->redirect(Url::toRoute(['catalog/view']));
        }
        else {

            $category = CatalogCategory::find()->limit(1)->orderBy('sort')->one();

            if($category){

                return $this->redirect(Url::toRoute(['catalog/view','id'=>$category->id]));
            }
        }

        $addCatalogCategoryForm  = new  AddCatalogCategoryForm();

        if($addCatalogCategoryForm->load(\Yii::$app->request->post())){


            if($newCategory= $addCatalogCategoryForm->createCategory()){

                return $this->redirect(Url::toRoute(['catalog/view','id'=>$newCategory->id]));
            }
        }



        return $this->render('add-category',['addForm'=>$addCatalogCategoryForm]);






    }


    public function actionEditItem($id){


        if ($item = CatalogItem::findOne($id)){


            $editForm = new CatalogItemForm();


            if (\Yii::$app->request->isPost && $editForm ->load(\Yii::$app->request->post())){

                if ($editForm->saveItem($item)){
                    //var_dump($f);
                    //return;                
                    return $this->redirect(Url::toRoute(['edit-item','id'=>$item->id]));
                }
            }
            else{

                $editForm->loadFromItem($item);

                return $this->render('edit',['editForm'=>$editForm,'item'=>$item]);

            }




        }

        else
        {
            throw new \yii\web\NotFoundHttpException("Блюда не существует");
        }

    }

    public function actionItemDell(){



        $item_id = \Yii::$app->request->post('item_id');

        if($item_id){


            if($item = CatalogItem::findOne(['id'=>$item_id])){

                $image_path = false;
                if ($item->file_name){

                    $image_path = $item->uploadTo('file_name') ;
                }

                if($item->delete()){

                    if ($image_path && file_exists($image_path)){

                        unlink($image_path);

                    }
                    return $this->sendJSONResponse(array('error'=>false,'item_id'=>$item_id));
                }
            }
        }



        return $this->sendJSONResponse(array('error'=>true));

    }

    public function actionAddItem($id){

        $this->pageHeader="Добавить блюдо";

        if ($category = CatalogCategory::findOne($id)){




            $addForm = new CatalogItemForm();



            if ($addForm->load(\Yii::$app->request->post())){

                if ($item = $addForm->createItem()){

                    return $this->redirect(Url::toRoute(['edit-item','id'=>$item->id]));
                }
            }

            return $this->render('add-item',['addForm'=>$addForm]);


        }        else{

            throw new \yii\web\NotFoundHttpException("Категории не существует");
        }

    }


    public function actionEditCategory($id){


        $this->pageHeader="Изменить категорию";


        if ($category = CatalogCategory::findOne($id)){

            $editForm = new EditCatalogCategoryForm();

            if ($editForm->load(\Yii::$app->request->post())){


                if($editForm->editCategory($category)){


                    \Yii::$app->getSession()->setFlash('success', 'Категория изменена');

                }else{

                    throw new \yii\web\ServerErrorHttpException("Ошибка сохранения обратитесь к администратору");
                }
            }
            else{
                $editForm->title = $category->title;
                $editForm->alias = $category->alias;
                $editForm->show_in_app = $category->show_in_app;
                $editForm->is_active = $category->is_active;
                $editForm->is_main_page = $category->is_main_page;
            }

            return $this->render('edit-category',['editForm'=>$editForm]);

        }
        else{

            throw new \yii\web\NotFoundHttpException("Этой категории не существует");
        }

    }



    public function actionItemSort(){


        $sort = \Yii::$app->request->post('item_order');

        if($sort){
            $i=0;
            foreach($sort as $cat_id){
                $i++;
                CatalogItem::updateAll(['sort'=>$i],['id'=>$cat_id]);


            }

            return  $this->sendJSONResponse(array('error'=>false));


        }


        return  $this->sendJSONResponse(array('error'=>true));



    }


    public function actionCategorySort(){


        $sort = \Yii::$app->request->post('category_order');

        if($sort){
            $i=0;
            foreach($sort as $cat_id){
                $i++;
                CatalogCategory::updateAll(['sort'=>$i],['id'=>$cat_id]);


            }

            return  $this->sendJSONResponse(array('error'=>false));


        }


        return  $this->sendJSONResponse(array('error'=>true));



    }


    public function actionCategoryDell(){

        $category_id = \Yii::$app->request->post('category_id');

        if($category_id){

            /** @var CatalogCategory $category */
            if($category = CatalogCategory::findOne(['id'=>$category_id])){
                if($category->delete()){
                    return $this->sendJSONResponse(array('error'=>false,'category_id'=>$category_id));
                }
            }
        }



        return $this->sendJSONResponse(array('error'=>true));

    }



    public function actionAddCategory(){


        $title = \Yii::$app->request->post('title');
        $alais = \Yii::$app->request->post('alias');
        if($title){

                $category = new CatalogCategory();
                $category->title=$title;
                if(isset($alais))$category->alias=$alias;

                if($category->save())
                    return $this->sendJSONResponse(array('error'=>false,'category_title'=>$category->title,'category_alias'=>$category->alias,'category_id'=>$category->id));


        }

        return $this->sendJSONResponse(array('error'=>true));

    }

}