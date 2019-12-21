<?php

use yii\db\Migration;

/**
 * Class m191219_222406_events
 */
class m191219_222406_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }


        $this->createTable('{{%event}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'text' => $this->string()->null(),
            'template' => $this->string()->null(),
        ], $tableOptions);


        $data = [
            ['name'=>'created_task', 'text'=>'Task created', 'template'=>'task'],
            ['name'=>'updated_task', 'text'=>'Task updated', 'template'=>'task'],
            ['name'=>'completed_task', 'text'=>'Task completed', 'template'=>'task'],
            ['name'=>'incoming_sms', 'text'=>'Incoming message', 'template'=>'sms'],
            ['name'=>'outgoing_sms', 'text'=>'Outgoing message', 'template'=>'sms'],
            ['name'=>'incoming_call', 'text'=>'Type changed', 'template'=>'call'],
            ['name'=>'outgoing_call', 'text'=>'Property changed', 'template'=>'call'],
            ['name'=>'incoming_fax', 'text'=>'Outgoing call', 'template'=>'fax'],
            ['name'=>'outgoing_fax', 'text'=>'Incoming call', 'template'=>'fax'],
            ['name'=>'customer_change_type', 'text'=>'Incoming fax', 'template'=>'change_type'],
            ['name'=>'customer_change_quality', 'text'=>'Outgoing fax', 'template'=>'change_quality'],
        ];

        foreach($data as $row) {
            $this->insert('{{%event}}', $row);
        }

        $this->addColumn('{{%history}}', 'event_id', $this->integer()->notNull());


        $items = \app\models\history\History::find()->all();
        foreach($items as $item) {
            $item->event_id = \app\models\history\HistoryEvent::find()->where(['name'=>$item->event])->scalar();
            $item->save();
        }


        $this->dropColumn('{{%history}}', 'event');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%event}}');
        $this->dropColumn('{{%history}}', 'event_id');
    }
}
