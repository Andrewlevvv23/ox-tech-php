<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Telegram\Webhook\Webhook;
use Illuminate\Support\Facades\Log;

class Start extends Webhook
{
    public function run(): true|\Illuminate\Http\Client\Response
    {
        $chatId = $this->request->input('message.chat.id');
        if(!$chatId) {Log::error("Chat ID is missing in callback query: $chatId"); return true;}

        Telegram::message($chatId, 'Welcome to the ATOMIC 👋')->send();
        return app(Lang::class)->run();
    }
}
