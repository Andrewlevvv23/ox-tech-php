<?php

namespace App\Telegram\Webhook;

use App\Telegram\Webhook\Commands\Authorization;
use App\Telegram\Webhook\Commands\Lang;
use App\Telegram\Webhook\Commands\LinkingQR;
use App\Telegram\Webhook\Commands\Register;
use App\Telegram\Webhook\Commands\Start;
use App\Telegram\Webhook\Commands\Support;
use App\Telegram\Webhook\Commands\Verification;
use Illuminate\Http\Request;

class Realization
{
    protected const Commands = [
        '/start' => Start::class,
        '/lang' => Lang::class,
        '/register' => Register::class,
        '/authorization' => Authorization::class,
        '/linkingQR' => LinkingQR::class,
        '/verification' => Verification::class,
        '/support' => Support::class,
    ];

    public function take(Request $request): string|bool
    {
        $command = $request->input('message')['entities'][0]['type'] ?? false;

        if ($command == 'bot_command') {
            $text = $request->input('message')['text'] ?? '';
            return self::Commands[$text] ?? false;
        }

        return false;
    }
}
