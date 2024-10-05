<?php

namespace App\Facades;

use App\Telegram\Bot\Bot;
use App\Telegram\Bot\File;
use App\Telegram\Bot\Message;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Message message(string|int $chat_id, string|int $text, $reply_id = null)
 * @method static Message forwardMessage(string|int $chat_id, string|int $from_chat_id, string|int $message_id)
 * @method static Message copyMessage(string|int $chat_id, string|int $from_chat_id, string|int $message_id)
 * @method static Message inlineButton(string|int $chat_id, string|int $text, array $buttons)
 * @method static File document(mixed $chat_id, $file, string $filename, $reply_id = null)
 * @method static File photo(mixed $chat_id, $file, string $filename, $reply_id = null)
 * @method static File getFile(int|string $file_id)
 * @method Bot send()
 */

class Telegram extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
     protected static function getFacadeAccessor(): string
     {
         return Telegram::class;
     }
}
