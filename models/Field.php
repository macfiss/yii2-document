<?php
/**
 * @package   yii2-document
 * @author    Yuri Shekhovtsov <shekhovtsovy@yandex.ru>
 * @copyright Copyright &copy; Yuri Shekhovtsov, lowbase.ru, 2015 - 2016
 * @version   1.0.0
 */

namespace lowbase\document\models;

use Yii;

/**
 * Дополнительные поля шаблона
 *
 * @property integer $id
 * @property string $name
 * @property integer $template_id
 * @property integer $type
 * @property string $param
 * @property integer $min
 * @property integer $max
 *
 * @property Template $template
 */
class Field extends \yii\db\ActiveRecord
{
    /**
     * Наименование таблицы
     * @return string
     */
    public static function tableName()
    {
        return 'lb_field';
    }

    /**
     * Типы дополнительных полей
     * @return array
     */
    public static function getTypes()
    {
        return [
            1 => 'Целое число',
            2 => 'Число',
            3 => 'Флажок',
            4 => 'Строка',
            5 => 'Текст',
            6 => 'Список (дочерние документы)',
            7 => 'Список (документы-потомки)',
            8 => 'Файл (выбор с сервера)',
            9 => 'Регулярное выражение',
        ];
    }

    /**
     * Правила валидации
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'template_id', 'type'], 'required'],  // Обязательны для заполнения
            [['template_id', 'type', 'min', 'max'], 'integer'], // Целочисленные значения
            [['name', 'param'], 'string', 'max' => 255],    // Текстовая строка (максимум 255 символов)
            [['template_id'], 'exist', 'skipOnError' => true, 'targetClass' => Template::className(), 'targetAttribute' => ['template_id' => 'id']],
            [['name', 'param'], 'filter', 'filter' => 'trim'],    // Обрезаем строки по краям
            [['param'], 'default', 'value' => null],   // По умолчанию = null
            [['min', 'max'], 'default', 'value' => 1],   // По умолчанию = 1
        ];
    }

    /**
     * Наименования полей аттрибутов
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('document', 'ID'),
            'name' =>  Yii::t('document', 'Наименование'),
            'template_id' => Yii::t('document', 'Шаблон'),
            'type' => Yii::t('document', 'Тип'),
            'param' => Yii::t('document', 'Параметры'),
            'min' => Yii::t('document', 'Минимум значений'),
            'max' => Yii::t('document', 'Максимум значений'),
        ];
    }

    /**
     * Шаблон, которому принадлежит поле
     * @return \yii\db\ActiveQuery
     */
    public function getTemplate()
    {
        return $this->hasOne(Template::className(), ['id' => 'template_id']);
    }
}