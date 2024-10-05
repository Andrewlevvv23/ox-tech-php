<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Telegram\Webhook\Webhook;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Verification extends Webhook
{
    /**
     * @throws ConnectionException
     */
    public function run(): \Illuminate\Http\Client\Response
    {
        $chatId = $this->request->input('message.chat.id');

        $textStart = $this->language === 'ru'
            ? "📷🪪 Верификация. \n Пожалуйста, сделайте и отправьте в этот чат селфи с паспортом: "
            : "📷🪪 Verification. \n Please take and send a selfie with your passport to this chat: ";
        $textSample = $this->language === 'ru'
            ? "Пример фотографии для верификации аккаунта"
            : "Sample photo for verification";

        $photo = Storage::path('public/images/verification.jpg');

        Telegram::message($chatId, $textStart)->send();
        return Telegram::photo($chatId, $photo, $textSample)->send();
    }

    /**
     * @throws ConnectionException
     */
    public function handle(Request $request)
    {
        $chatId = $request->input('message.chat.id');
        $pictures = $request->input('message.photo');

        $file = Telegram::getFile($pictures[count($pictures) - 1]['file_id'])->send()->json();
        $file_path = $file['result']['file_path'];
        $ext = explode('.', $file_path);
        $ext = $ext[count($ext) - 1];
        $file_name = uniqid().'.'.$ext;
        $textSuccess = $this->language === 'ru'
            ? "Спасибо, ожадайте, идет проверка ваших данных 🕑"
            : "Thank you, please wait, we are checking your data 🕑";

        Storage::putFileAs('public/telegram/images', env('TELEGRAM_FILE_PATH').env('TELEGRAM_BOT_TOKEN').'/'.$file_path, $file_name);
        Telegram::message($chatId, $textSuccess)->send();

        $file_url = Storage::url('public/telegram/images/' . $file_name);
        $sendPhoto = DB::table('clients')->where('chat_id', $chatId)->update([
            'path_image_verification' => $file_url,
            'verification' => 1,
        ]);
        if($sendPhoto) Log::info('Successfully saved photo verification client for chat_id: ' . $chatId);
        else Log::error('Failed to saved photo verification client for chat_id: ' . $chatId);


        $userFirstName = $request->input('message.chat.first_name') ?? 'Hidden first name';
        $userNickName = $request->input('message.chat.username') ?? 'Hidden username';
        $marker = time();
        Telegram::inlineButton(env('TELEGRAM_IRC_CHAT'),
            "📩 New client has left his details:
            \n User ID: <b>$chatId</b>
            \n User name: <b>$userFirstName</b>
            \n User nickname: <b>$userNickName</b>",
            [
                'inline_keyboard' => [
                    [
                        ['text' => 'Detailed information 🗂', 'callback_data' => "reply_$chatId-$marker"],
                    ],
                ],
                'resize_keyboard' => true,
            ])->send();



        return app(Support::class, ['request' => $request])->run();
    }
}
