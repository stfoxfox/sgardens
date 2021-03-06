<?php

use yii\db\Migration;
use common\models\Setting;

/**
 * Class m180302_095423_add_site_setting
 */
class m180302_095423_add_site_setting extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $setting = new Setting([
            'title' => 'Тег H1',
            'key' => 'text_footer',
            'type' => 'text',
            'value' => '
            C 2001 года мы занимаемся доставкой цветов по Москве. Бренд «Сады Сальвадора» по праву считается одним из старейших и уважаемых среди ценителей флористики. Во всех уголках нашего большого и любимого города знают, что «Сады Сальвадора» всегда ассоциируются с авторским подходом к букетам, чуткому отношению к клиентам, надежностью в доставке цветов.
            
            «Сады Сальвадора» за эти годы стали настоящей кузницей кадров. Флористы «Садов Сальвадора» становились призерами и победителями не только национальных, но и многих международных конкурсов. Работая только с качественной и свежей цветочной срезкой, они продолжают традиции бренда и радуют своими букетами их получателей.
            
            Никогда еще не было так удобно и просто заказать услугу доставки букетов по Москве как сейчас. Используя для этого наш онлайн сервис, Вы можете не только выбрать из широкого каталога букетов понравившуюся вам цветочную работу, но и бесплатно добавить к ней элегантный подарок – кулон с полудрагоценным камнем. Уникальная застежка к нему, которую мы разработали сами, явится еще одним приятным сюрпризом для получателя букета. Собирая камни-кулоны вы сохраняете ваши эмоции, связанные с самыми приятными моментами в жизни.
            '
        ]);
        $setting->save();
        Setting::setItemInCache([$setting->key => $setting->value]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
     
    }
}
