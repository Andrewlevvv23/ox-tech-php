<?php

namespace App\Telegram\Helpers;

class InlineButton
{
    private static int $button_number = 1;
    public static array $buttons = [
        'inline_keyboard' => []
    ];

    public static function add(mixed $text, string $action, array $data, int $row = 1): void
    {
        $data['action'] = $action;
        $data['button_number'] = self::$button_number;
        self::$button_number++;
        self::$buttons['inline_keyboard'][$row -1 ][] = [
            'text' => $text,
            'callback_data' => json_encode($data)
        ];
    }

    public static function link(mixed $text, string $url, int $row = 1): void
    {
        self::$buttons['inline_keyboard'][$row -1 ][] = [
            'text' => $text,
            'url' => $url
        ];
    }
}
