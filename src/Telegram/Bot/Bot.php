<?php

namespace App\Telegram\Bot;

use Illuminate\Support\Facades\Http;

abstract class Bot
{
    protected array $data;
    protected string $method;

    public function send(): \Illuminate\Http\Client\Response
    {
       return Http::post("https://api.telegram.org/bot".env('TELEGRAM_BOT_TOKEN')."/$this->method", $this->data);
    }
}
