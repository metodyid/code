<?php

namespace app\modules\admin\controllers;

use app\forms\PhotosForm;
use app\models\Characteristics;
use app\models\ProductsHasCharacteristicsItems;
use Yii;
use app\models\Products;
use app\models\search\ProductsSearch;
use app\models\Photos;
use app\forms\ProductsForm;
use app\forms\MetaForm;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProductsController implements the CRUD actions for Products model.
 */
class ProductsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Products models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Products model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionCreate()
    {
        throw new NotFoundHttpException('The requested page does not exist.');

        /*$model = new Products();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);*/

        $form = new ProductsForm();
        $products = new Products();
        $photos = new Photos();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $products->name_rus = $form->name_rus;
                $products->name_eng = $form->name_eng;
                $products->slug = $form->slug;
                $products->description = $form->description;
                $products->category_id = $form->category_id;
                $products->country_id = $form->country_id;
                $products->region_id = $form->region_id;
                $products->status = $form->status;

                $formMeta = new MetaForm();
                if ($formMeta->load(Yii::$app->request->post()) && $formMeta->validate()) {
                    $products->meta = $formMeta;
                }

                $formPhotos = new PhotosForm();
                if ($formPhotos->load(Yii::$app->request->post()) && $formPhotos->validate()) {
                    foreach ($formPhotos->files as $file) {
                        $products->addPhoto($file);
                    }
                }
                $products->save();
                if(isset($_POST['ProductsForm']['characteristics']) && Yii::$app->request->post('ProductsForm')['characteristics']){
                    $productsCharacteristicsItems = Yii::$app->request->post('ProductsForm')['characteristics'];
                    foreach ($productsCharacteristicsItems as $value){
                        $PHCI = new ProductsHasCharacteristicsItems();
                        $PHCI->products_id=$products->id;
                        $PHCI->characteristics_items_id=$value;
                        $PHCI->save();
                    }
                }

                return $this->redirect(['view', 'id' => $products->id]);
            } catch (\DomainException $e) {
                Yii::$app->errorHandler->logException($e);
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $form,
            'products' => $products,
        ]);
    }

    /**
     * Updates an existing Products model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $form = new ProductsForm();
        $products = $this->findModel($id);
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $products->name_rus = $form->name_rus;
                $products->name_eng = $form->name_eng;
                $products->slug = $form->slug;
                $products->description = $form->description;
                $products->category_id = $form->category_id;
                $products->country_id = $form->country_id;
                $products->region_id = $form->region_id;
                $products->manufacturer_id = $form->manufacturer_id;
                $products->status = $form->status;
                $formMeta = new MetaForm();
                if ($formMeta->load(Yii::$app->request->post()) && $formMeta->validate()) {
                    $products->meta = $formMeta;
                }
                $formPhotos = new PhotosForm();
                if ($formPhotos->load(Yii::$app->request->post()) && $formPhotos->validate()) {
                    foreach ($formPhotos->files as $file) {
                        $products->addPhoto($file);
                    }
                }
                if(isset($_POST['ProductsForm']['characteristics']) && Yii::$app->request->post('ProductsForm')['characteristics']){
                    $productsCharacteristicsItems = Yii::$app->request->post('ProductsForm')['characteristics'];
                    ProductsHasCharacteristicsItems::deleteAll(['products_id' => $id]);
                    foreach ($productsCharacteristicsItems as $value){
                        if(is_array($value)){
                            foreach ($value as $v){
                                if($v){
                                    $PHCI = new ProductsHasCharacteristicsItems();
                                    $PHCI->products_id=$id;
                                    $PHCI->characteristics_items_id=$v;
                                    $PHCI->save();
                                }
                            }
                        }else{
                            if($value){
                                $PHCI = new ProductsHasCharacteristicsItems();
                                $PHCI->products_id=$id;
                                $PHCI->characteristics_items_id=$value;
                                $PHCI->save();
                            }
                        }
                    }
                }
                $products->save();
                return $this->redirect(['view', 'id' => $products->id]);
            } catch (\DomainException $e) {
                Yii::$app->errorHandler->logException($e);
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }
        $form->name_rus = $products->name_rus;
        $form->name_eng = $products->name_eng;
        $form->slug = $products->slug;
        $form->description = $products->description;
        $form->category_id = $products->category_id;
        $form->country_id = $products->country_id;
        $form->region_id = $products->region_id;
        $form->manufacturer_id = $products->manufacturer_id;
        $form->status = $products->status;
        $form->meta = new MetaForm($products->meta);
        /*$a = $products->category->characteristics;
        foreach ($a as $v){
            var_dump($v->characteristicsItems);
        }
        exit;*/
        return $this->render('update', [
            'model' => $form,
            'products' => $products,
        ]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        throw new NotFoundHttpException('The requested page does not exist.');

        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Products model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Products the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Products::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @param $id
     * @param $photo_id
     * @return \yii\web\Response
     */
    public function actionDeletePhoto($id, $photo_id)
    {
        $model = Products::findOne($id);
        try {
            $model->removePhoto($photo_id);
            $model->save();
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['update', 'id' => $id, '#' => 'photos']);
    }

    /**
     * @param integer $id
     * @param $photo_id
     * @return mixed
     */
    public function actionMovePhotoUp($id, $photo_id)
    {
        $model = Products::findOne($id);
        $model->movePhotoUp($photo_id);
        $model->save();
        return $this->redirect(['update', 'id' => $id, '#' => 'photos']);
    }

    /**
     * @param integer $id
     * @param $photo_id
     * @return mixed
     */
    public function actionMovePhotoDown($id, $photo_id)
    {
        $model = Products::findOne($id);
        $model->movePhotoDown($photo_id);
        $model->save();
        return $this->redirect(['update', 'id' => $id, '#' => 'photos']);
    }

    public function actionGet_characteristics_items()
    {
        $characteristics = Characteristics::find()
            ->joinWith('category')
            ->where(['category_id' => Yii::$app->request->post('id')])
            ->all();
        if($characteristics){
            $html = $this->renderPartial('_form_characteristics', [
                'characteristics' => $characteristics,
            ]);
        }else{
            $html = '';
        }

        echo Json::encode(['html' => $html]);
        exit;
    }
}
