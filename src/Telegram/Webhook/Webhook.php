<?php

namespace App\Telegram\Webhook;

use App\Facades\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Webhook
{
    protected Request $request;
    protected ?string $language = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->setLanguage();
    }

    protected function setLanguage(): void
    {
        $chatId = $this->request->input('message.chat.id') ?? $this->request->input('callback_query.message.chat.id');
        if ($chatId) {
            $this->language = DB::table('clients')
                ->where('chat_id', $chatId)
                ->value('language_code');
            if (!$this->language) $this->language = 'en';
        } else {
            $this->language = 'en';
        }
    }

    public function run(): true|\Illuminate\Http\Client\Response
    {
        $chatId = $this->request->input('message.chat.id');
        if(!$chatId || $chatId == env('TELEGRAM_IRC_CHAT')) {Log::error("Chat ID is missing in callback query: $chatId"); return true;}

        $text = $this->language === 'ru' ? 'Не известная команда, проверьте введенные данные✍️' : 'Unknown command, check the entered data✍️';
        return Telegram::message($chatId, $text)->send();
    }
}
