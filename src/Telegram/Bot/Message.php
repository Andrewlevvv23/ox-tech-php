<?php

namespace App\Telegram\Bot;

class Message extends Bot
{
    protected array $data;
    protected string $method;

    public function message(string|int $chat_id, string|int $text): static
    {
        $this->method = 'sendMessage';
        $this->data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => "HTML",
        ];
        return $this;
    }

    public function forwardMessage(string|int $chat_id, string|int $from_chat_id, string|int $message_id): static
    {
        $this->method = 'forwardMessage';
        $this->data = [
            'chat_id' => $chat_id,
            'from_chat_id' => $from_chat_id,
            'message_id' => $message_id,
        ];
        return $this;
    }

    public function copyMessage(string|int $chat_id, string|int $from_chat_id, string|int $message_id): static
    {
        $this->method = 'copyMessage';
        $this->data = [
            'chat_id' => $chat_id,
            'from_chat_id' => $from_chat_id,
            'message_id' => $message_id,
        ];
        return $this;
    }

    public function inlineButton(string|int $chat_id, string|int $text, array $buttons): static
    {
        $this->method = 'sendMessage';
        $this->data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => "HTML",
            'reply_markup' => $buttons,
        ];
        return $this;
    }
}
