<?php


namespace app\models;


class ObjectFactory
{
    public static function build($type, $objectId) {
        $objectName = ucfirst($type);
        $className = "app\models\\$type\\$objectName";

        if(class_exists($className)) {
            return $className::find()->where(['id'=>$objectId])->one();
        }

        return null;
    }
}