<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;

class MessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Ariza qabul qilindi',
                'type' => 'general',
                'body_uz' => "Hurmatli {ism}!\n\nSizning {vakansiya} vakansiyasiga arizangiz qabul qilindi.\nKompaniya: {kompaniya}\n\nNatijani kuting. Omad!",
                'body_ru' => "Уважаемый {ism}!\n\nВаша заявка на вакансию {vakansiya} принята.\nКомпания: {kompaniya}\n\nОжидайте результатов. Удачи!",
                'variables' => ['ism', 'vakansiya', 'kompaniya'],
                'is_system' => true,
            ],
            [
                'name' => 'Intervyuga taklif',
                'type' => 'invite',
                'body_uz' => "Hurmatli {ism}!\n\nSiz {vakansiya} vakansiyasi bo'yicha intervyuga taklif etilasiz.\n\n📅 Sana: {sana}\n⏰ Vaqt: {vaqt}\n📍 Manzil: {manzil}\n\nIltimos, o'z vaqtida keling.",
                'body_ru' => "Уважаемый {ism}!\n\nВы приглашены на собеседование по вакансии {vakansiya}.\n\n📅 Дата: {sana}\n⏰ Время: {vaqt}\n📍 Адрес: {manzil}\n\nПожалуйста, приходите вовремя.",
                'variables' => ['ism', 'vakansiya', 'sana', 'vaqt', 'manzil'],
                'is_system' => true,
            ],
            [
                'name' => 'Afsuski rad etildi',
                'type' => 'reject',
                'body_uz' => "Hurmatli {ism}!\n\nAfsuski, {vakansiya} vakansiyasi bo'yicha arizangiz rad etildi.\nSabab: {sabab}\n\nBoshqa vakansiyalarga ariza berishingiz mumkin. Omad!",
                'body_ru' => "Уважаемый {ism}!\n\nК сожалению, ваша заявка на вакансию {vakansiya} отклонена.\nПричина: {sabab}\n\nВы можете подать заявку на другие вакансии. Удачи!",
                'variables' => ['ism', 'vakansiya', 'sabab'],
                'is_system' => true,
            ],
        ];

        foreach ($templates as $template) {
            MessageTemplate::updateOrCreate(
                ['name' => $template['name'], 'is_system' => true],
                $template
            );
        }
    }
}
