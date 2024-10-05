<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Telegram\Webhook\Webhook;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Support extends Webhook
{
    public function run(): true|\Illuminate\Http\Client\Response
    {
        $chatId = $this->request->input('message.chat.id') ?? $this->request->input('callback_query.from.id');
        if(!$chatId) {Log::error("Chat ID is missing in callback query: $chatId"); return true;}

        $textTitle = $this->language === 'ru'
            ? "Желаете связаться с менеджером службы поддержки?"
            : "Would you like to contact a support manager?";
        $textAnswer = $this->language === 'ru'
            ? "Да, отправить заявку менеджеру 💬"
            : "Yes, send a request to the manager 💬";

        return Telegram::inlineButton($chatId, $textTitle, [
            'inline_keyboard' => [
                [
                    ['text' => $textAnswer, 'callback_data' => 'support_request'],
                ],
            ],
            'resize_keyboard' => true,
        ])->send();
    }

    public function handle(Request $request): true
    {
        $chatId = $this->request->input('callback_query.from.id');
        $marker = time();
        $supportChatId = env('TELEGRAM_IRC_CHAT');
        $userID = $request->input('callback_query.from.id') ?? 'Hidden';
        $userFirstName = $request->input('callback_query.from.first_name') ?? 'undefiled';
        $userNickName = $request->input('callback_query.from.username') ?? 'undefiled';

        if(!$chatId) {Log::error("Chat ID is missing in callback query: $chatId"); return true;}

        Telegram::inlineButton($supportChatId,
      "📩 New request from a client:
            \n User ID: <b>$userID</b>
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

        $textAnswer = $this->language === 'ru'
            ? "Ожидайте, с вами свяжется ближайший свободный менеджер..."
            : "Wait for the nearest available manager to contact you...";
        Telegram::message($chatId, $textAnswer)
            ->send();

        return true;
    }

    public function handleReply(Request $request): true
    {
        $textAnswer = $this->language === 'ru'
            ? "Ответ от менеджера службы поддержки ATOMIC:"
            : "Response from ATOMIC Help Desk Manager:";

        if($callbackData = $request->input('message.reply_to_message.reply_markup.inline_keyboard.0.0.callback_data'))
        {
            preg_match('/^reply_(\d+)-(\d+)$/', $callbackData, $matches);
            $userChatId = $matches[1] ?? false;
            $replyText = $request->input('message.text');
            if(!$userChatId) {Log::error("Chat ID is missing in callback query: $userChatId"); return true;}

            Telegram::message($userChatId, "$textAnswer \n <b>$replyText</b>")->send();

            if($request->has('message.photo') || $request->has('message.document')) {
                $message_id = $request->input('message.message_id');
                Telegram::copyMessage($userChatId, env('TELEGRAM_IRC_CHAT'), $message_id)->send();
            }

            return true;
        }

        if($replyData = $request->input('message.reply_to_message.text')) {
            preg_match('/Response from the client (\d+):/', $replyData, $matches);
            $clientId = $matches[1] ?? false;
            $replyText = $request->input('message.text');
            if(!$clientId) {Log::error("Chat ID is missing in callback query: $clientId"); return true;}

            Telegram::message($clientId, "$textAnswer \n <b>$replyText</b>")->send();

            if($request->has('message.photo') || $request->has('message.document')) {
                $message_id = $request->input('message.message_id');
                Telegram::copyMessage($clientId, env('TELEGRAM_IRC_CHAT'), $message_id)->send();
            }
        }

        return true;
    }

    /**
     * @throws ConnectionException
     */
    public function handleReplyBtn(Request $request): true
    {
        $callbackData = $request->input('callback_query.data');
        preg_match('/^reply_(\d+)-(\d+)$/', $callbackData, $matches);
        $userChatId = $matches[1];

        $client = DB::table('clients')->where('chat_id', $userChatId)->first();

        Telegram::message(env('TELEGRAM_IRC_CHAT'),
            "👤 More details about the client:
            \n User ID: <b>$userChatId</b>
            \n User name: <b>$client->first_name</b>
            \n User nickname: <b>$client->user_name</b>
            \n User phone: <b>$client->phone</b>
            \n Language: <b>$client->language_code</b>
            \n Created date: <b>$client->created_at (UTC)</b>
           ")->send();

        $fileQrCode = ltrim($client->path_image_qr_code, '/');
        $fileVerification = ltrim($client->path_image_verification, '/');

        if($fileQrCode && $fileVerification) {
            Telegram::photo(env('TELEGRAM_IRC_CHAT'), $fileQrCode, 'FileQrCode')->send();
            Telegram::photo(env('TELEGRAM_IRC_CHAT'), $fileVerification, 'FileVerification')->send();
        }

        return true;
    }
}
