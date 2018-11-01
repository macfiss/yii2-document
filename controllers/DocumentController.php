<?php
/**
 * @package   yii2-document
 * @author    Yuri Shekhovtsov <shekhovtsovy@yandex.ru>
 * @copyright Copyright &copy; Yuri Shekhovtsov, lowbase.ru, 2015 - 2016
 * @version   1.0.0
 */
 
namespace lowbase\document\controllers;

use lowbase\document\models\Field;
use lowbase\document\models\Like;
use lowbase\document\models\Visit;
use Yii;
use lowbase\document\models\Template;
use lowbase\document\models\Document;
use lowbase\document\models\DocumentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
//use yii\filters\AccessControl;

/**
 * Документы
 *
 * Абсолютные пути Views использованы, чтобы при наследовании
 * происходила связь с отображениями модуля родителя.
 *
 * Class DocumentController
 * @package lowbase\document\controllers
 */
class DocumentController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
// Ограничение доступа к операциям, связанным с документами
// Активировать при подключении пользователей и разделений прав
//            'access' => [
//                'class' => AccessControl::className(),
//                'only' => ['index', 'view', 'create', 'update', 'delete', 'multidelete', 'multiactive', 'multiblock', 'move', 'show', 'like', 'change', 'field'],
//                'rules' => [
//                ],
//            ],
        ];
    }

    /**
     * Менеджер документов (список таблицей)
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new DocumentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('@vendor/macfiss/yii2-document/views/document/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Просмотр карточки документа
     * @param $id - ID документа
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model =  $this->findModel($id);
        $views = Visit::getAll($model->id); // Считаем просмотры
        $likes = Like::getAll($model->id);  // Считаем лайки
        return $this->render('@vendor/macfiss/yii2-document/views/document/view', [
            'model' => $model,
            'views' => ($views) ?  $views[0]->count : 0,
            'likes' => ($likes) ?  $likes[0]->count : 0
        ]);
    }

    /**
     * Создание документа
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Document();
        // Устанавливаем родительский документ если пришло значение из $_GET
        $model->parent_id = Yii::$app->request->get('parent_id');
        // Заполняем необходимые для заполнения поля
        $model->fillFields();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('document', 'Новый документ создан.'));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('@vendor/macfiss/yii2-document/views/document/create', [
            'model' => $model,
        ]);
    }

    /**
     * Редактирование документа
     * @param $id - ID документа
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        // Заполняем необходимые для заполнения поля
        $model->fillFields();
//        exit(print_r($model->fields));
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('document', 'Документ отредактирован.'));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('@vendor/macfiss/yii2-document/views/document/update', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление документа
     * @param $id - ID документа
     * @return bool|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        if (Yii::$app->request->isAjax) { // Если пришел Ajax-запрос
            return true;
        } else {
            Yii::$app->getSession()->setFlash('success', Yii::t('document', 'Документ удален.'));
            return $this->redirect(['index']);
        }
    }

    /**
     * Множественное удаление документов
     * @return bool
     * @throws NotFoundHttpException
     */
    public function actionMultidelete()
    {
        $models = Yii::$app->request->post('keys');
        if ($models) {
            foreach ($models as $id) {
                    $this->findModel($id)->delete();
            }
            Yii::$app->getSession()->setFlash('success', Yii::t('document', 'Документы удалены.'));
        }
        return true;
    }

    /**
     * Множественная публикация документов
     * @return bool
     * @throws NotFoundHttpException
     */
    public function actionMultiactive()
    {
        $models = Yii::$app->request->post('keys');
        if ($models) {
            foreach ($models as $id) {
                $model = $this->findModel($id);
                $model->status = Document::STATUS_ACTIVE;
                $model->save();
            }
            Yii::$app->getSession()->setFlash('success', Yii::t('document', 'Документы опубликованы.'));
        }
        return true;
    }

    /**
     * Множественное снятие с публикации документов
     * @return bool
     * @throws NotFoundHttpException
     */
    public function actionMultiblock()
    {
        $models = Yii::$app->request->post('keys');
        if ($models) {
            foreach ($models as $id) {
                $model = $this->findModel($id);
                $model->status = Document::STATUS_BLOCKED;
                $model->save();
            }
            Yii::$app->getSession()->setFlash('success', Yii::t('document', 'Документы сняты с публикации.'));
        }
        return true;
    }

    /**
     * Перемещение документа
     * Используется компонентом JSTree
     * @return bool
     * @throws NotFoundHttpException
     * @throws \Exception
     */
     public function actionMove()
    {
        // Получаем данные необходимые для перемещения
        $data = Yii::$app->request->post();
        $model = $this->findModel($data['id']);
        // Запоминаем прошлый родительский документ
        $old_parent_id = $model->parent_id;
        // # - означает, что документ первого уровня (нет родителя)
        $model->parent_id = ($data['new_parent_id'] == '#') ? null : $data['new_parent_id'];

        // Если указан документ после которого надо поместить документ

        if ($data['new_prev_id'] && $data['new_prev_id'] !== 'false') {
            $prev_model = $this->findModel($data['new_prev_id']);
            $model->position = $prev_model->position+1;
        } else {
            $model->position = 0;
        }
        // Если указан документ перед которым надо поместить документ
        if ($data['new_next_id'] && $data['new_next_id'] !== 'false' && (!$data['new_prev_id'] || $data['new_prev_id'] == 'false')) {
            // Документов впереди на уровне нет
            $model->position = 0;
        }
        // Пересчитываем позиции остальных документов текущего уровня
        $db = $model->getDb();
        $transaction = $db->beginTransaction();
        try {
            $db->createCommand("set @i:=". $model->position)->execute();
            if ($model->parent_id) {
                $db->createCommand('UPDATE lb_document SET position=(@i:=@i+1) WHERE (parent_id='.$model->parent_id.' && `position`>='.$model->position.') ORDER BY position')->execute();
            } else {
                $db->createCommand('UPDATE lb_document SET position=(@i:=@i+1) WHERE (parent_id IS NULL && `position`>='.$model->position.') ORDER BY position')->execute();
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        $model->save();

        // Пересматриваем пометку "Папка" если произошло изменение
        // родительского документа
        if ($old_parent_id <> $model->parent_id) {
            if ($old_parent_id !== '#') {
                // Проверяем необходимость снять пометки "Папка"
                // с прошлого родителя
                Document::folder($old_parent_id);
            }
            if ($old_parent_id !== null) {
                // Устанавлием значение "Папка" на нового родителя
                // если не был установлен до этого
                Document::folder($model->parent_id);
            }
        }
        return true;
    }

    /**
     * Поиск документа по ID
     * @param integer $id - ID документа
     * @return Document the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Document::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('document', 'Запрашиваемая страница не найдена.'));
        }
    }

    /**
     * Публичное отображение документа
     * @param $alias - Url-адрес документа
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionShow($alias)
    {
        // Отображаем только опубликованные документы
        $model = Document::find()->where(['alias' => $alias, 'status' => Document::STATUS_ACTIVE])->one();
        if ($model == null) {
            throw new NotFoundHttpException(Yii::t('document', 'Запрашиваемая страница не найдена.'));
        }
        Visit::check($model->id);           // Фиксируем просмотр
        $views = Visit::getAll($model->id); // Считаем просмотры
        $likes = Like::getAll($model->id);  // Считаем лайки
        // Если задан шаблон отображения, то отображаем согласно нему, иначе стандартное отображение статьи
        $template = (isset($model->template) && $model->template->path) ? $model->template->path : '@vendor/macfiss/yii2-document/views/document/template/default';
        return $this->render($template, [
            'model' => $model,
            'views' => ($views) ?  $views[0]->count : 0,
            'likes' => ($likes) ?  $likes[0]->count : 0
        ]);
    }

    /**
     * Лайк документа
     * @param $id - ID документа
     * Отображает количество лайков статьи
     */
    public function actionLike($id)
    {
        Like::check($id);
        $likes = Like::getAll($id);
        echo ($likes) ? $likes[0]->count : 0;
    }

    /**
     * Изменение шаблона докуменат
     */
    public function actionChange()
    {
        $id = Yii::$app->request->post('id');
        $model = ($id) ? Document::findOne($id) : new Document();
        $model->template_id = Yii::$app->request->post('template_id');
        $model->fillFields();   // Заполняем поля согласно новому шаблону

        return $this->renderAjax('@vendor/macfiss/yii2-document/views/document/_fields', [
            'model' => $model,
        ]);
    }

    /**
     * Добавление значения мультиполя
     */
    public function actionField()
    {
        $document_id = Yii::$app->request->post('document_id');
        $model = ($document_id) ? Document::findOne($document_id) : new Document();
        $field_id = Yii::$app->request->post('field_id');
        $field = Field::findOne($field_id);
        if (!$field) {
            return false;
        }
        $data_id = Yii::$app->request->post('data_id');
        $data_id = 'new_multi_' . $data_id; // 0 перед id нового поля свидетельствует о добавлении мультизначения
        $data = [
            'value' => '',
            'position' => ''
        ];
        return $this->renderAjax('@vendor/macfiss/yii2-document/views/document/_field', [
            'model' => $model,
            'field' => $field,
            'field_id' => $field_id,
            'data' => $data,
            'data_id' => $data_id,
        ]);
    }
}
