<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Telegram\Webhook\Webhook;
use Illuminate\Http\Request;

class Register extends Webhook
{
    public function run(): \Illuminate\Http\Client\Response
    {
       $chatId = $this->request->input('message.chat.id');
       $textStart = $this->language === 'ru' ? '🪪 Виберете вариант регистрации:' : '🪪 Select registration option:';

       return Telegram::inlineButton($chatId, $textStart, [
           'inline_keyboard' => [
               [
                   ['text' => 'Google account', 'callback_data' => 'register_google'],
                   ['text' => 'Apple ID', 'callback_data' => 'register_apple'],
               ],
           ],
           'resize_keyboard' => true,
           'one_time_keyboard' => true,
        ])->send();
    }

    public function handle(Request $request): void
    {
        $callbackQuery = $request->input('callback_query');
        $textGoogle = $this->language === 'ru' ? 'Регистрация через Google Account...' : 'Registration via Google Account...';
        $textApple = $this->language === 'ru' ? 'Регистрация через Apple ID...' : 'Registration via Apple ID...';

        if ($callbackQuery) {
            $chatId = $callbackQuery['message']['chat']['id'];
            $data = $callbackQuery['data'];

            if ($data === 'register_google') Telegram::message($chatId, $textGoogle)->send();
            elseif ($data === 'register_apple') Telegram::message($chatId, $textApple)->send();
        }
    }
}
