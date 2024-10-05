<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Telegram\Webhook\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Lang extends Webhook
{
    public function run(): true|\Illuminate\Http\Client\Response
    {
       $chatId = $this->request->input('message.chat.id');
       if(!$chatId) {Log::error("Chat ID is missing in callback query: $chatId"); return true;}

       return Telegram::inlineButton($chatId, '🌐 Choose a language convenient for you:', [
           'inline_keyboard' => [
               [
                   ['text' => 'English', 'callback_data' => 'lang_en'],
                   ['text' => 'Русский', 'callback_data' => 'lang_ru'],
               ],
           ],
           'resize_keyboard' => true,
        ])->send();
    }

    public function handle(Request $request)
    {
        $callbackQuery = $request->input('callback_query');

        if ($callbackQuery) {
            $chatId = Arr::get($callbackQuery, 'message.chat.id', '');
            $userName = Arr::get($callbackQuery, 'message.chat.username', 'undefined');
            $firstName = Arr::get($callbackQuery, 'message.chat.first_name', 'undefined');
            $languageCode = Arr::get($callbackQuery, 'data', 'en') === 'lang_ru' ? 'ru' : 'en';
            $data = $callbackQuery['data'];

            if(!$chatId) {Log::error("Chat ID is missing in callback query: $chatId"); return true;}

            if ($data === 'lang_en') Telegram::message($chatId, 'You have successfully selected English!')->send();
             elseif ($data === 'lang_ru') Telegram::message($chatId, 'Вы успешно выбрали русский язык!')->send();

            $updated = DB::table('clients')->updateOrInsert(
                ['chat_id' => $chatId],
                [
                    'chat_id' => $chatId,
                    'user_name' => $userName,
                    'first_name' => $firstName,
                    'language_code' => $languageCode,
                    'created_at' => now(),
                ]
            );

            if($updated) Log::info('Successfully saved or updated client data for chat_id: ' . $chatId);
             else Log::error('Failed to save or update client data for chat_id: ' . $chatId);
        }

       return app(Authorization::class, ['request' => $request])->run();
    }
}
