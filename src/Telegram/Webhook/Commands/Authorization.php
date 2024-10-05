<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Telegram\Webhook\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Authorization extends Webhook
{
    public function run(): true|\Illuminate\Http\Client\Response
    {
        $chatId = $this->request->input('callback_query.message.chat.id') ?? $this->request->input('message.chat.id');
        if(!$chatId) {Log::error("Chat ID is missing in callback query: $chatId"); return true;}

        $textStart = $this->language === 'ru' ? '📞 Поделитесь своим номером телефона для авторизации: ' : '📞 Share your phone number for authorization: ';

        return Telegram::inlineButton($chatId, $textStart, [
            'keyboard' => [
                [
                    ['text' => 'Share your phone', 'request_contact' => true],
                ],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ])->send();
    }

    public function handle(Request $request)
    {
        $contact = $request->input('message.contact') ?? false;
        $chatId = $request->input('message.chat.id') ?? false;
        if(!$chatId) {Log::error("Chat ID is missing in callback query: $chatId"); return true;}

        $text = $this->language === 'ru' ? '📞 Вы успешно авторизированы в системе с номером: ' : '📞 You are successfully logged in to the system with the number: ';
        $textError = $this->language === 'ru' ? '📞 Произошла ошибка при отравке номера телефона...' : '📞 An error occurred while sending the phone number...';

        if ($contact) {
            $phoneNumber = $contact['phone_number'];
            Telegram::message($chatId, ($text . $phoneNumber))->send();
            $addedPhone = DB::table('clients')->updateOrInsert(
                ['chat_id' => $chatId], ['phone' => $phoneNumber, 'updated_at' => now()]
            );
            if($addedPhone) Log::info('Successfully saved phone client for chat_id: ' . $chatId);
            else Log::error('Failed to saved phone client for chat_id: ' . $chatId);
        } else {
            Telegram::message($chatId, $textError)->send();
        }

        return app(LinkingQR::class, ['request' => $request])->run();
    }
}
