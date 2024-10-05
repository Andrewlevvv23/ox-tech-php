<?php

namespace App\Telegram\Bot;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class File extends Bot
{
    protected array $data;
    protected string $method;
    protected mixed $file;
    protected string $filename;
    protected string $type;

    public function document(mixed $chat_id, $file, string $filename, $reply_id = null): static
    {
        $this->method = 'sendDocument';
        $this->type = 'document';
        $this->file = $file;
        $this->filename = $filename;
        $this->data = [
            'chat_id' => $chat_id,
        ];
        if($reply_id)
        {
            $this->data['reply_parameters'] = [
                'message_id' => $reply_id
            ];
        }
        return $this;
    }

    public function getFile(int|string $file_id): static
    {
        $this->method = 'getFile';
        $this->data = [
            'file_id' => $file_id,
        ];
        return $this;
    }

    public function photo(mixed $chat_id, string $filePath, string $caption = '', $reply_id = null): static
    {
        $this->method = 'sendPhoto';
        $this->type = 'photo';
        $this->file = $filePath;
        $this->data = [
            'chat_id' => $chat_id,
            'caption' => $caption,
        ];
        if($reply_id)
        {
            $this->data['reply_parameters'] = [
                'message_id' => $reply_id
            ];
        }
        return $this;
    }

    public function album(mixed $chat_id, array $file_url, $reply_id = null): static
    {
        $this->method = 'sendMediaGroup';
        $this->type = 'media';
        foreach ($file_url as $key => $url)
        {
            $this->data['media'][] = [
                'media' => $url,
                'type' => 'photo'
            ];
        }
        $this->data['chat_id'] = $chat_id;
        if($reply_id)
        {
            $this->data['reply_parameters'] = [
                'message_id' => $reply_id
            ];
        }
        return $this;
    }

    /**
     * @throws ConnectionException
     */
    public function send(): \Illuminate\Http\Client\Response
    {
        if ($this->method === 'getFile') return Http::post('https://api.telegram.org/bot' . env('TELEGRAM_BOT_TOKEN') . '/' . $this->method, $this->data);

        if (!empty($this->file)) return Http::attach($this->type, file_get_contents($this->file), basename($this->file))->post('https://api.telegram.org/bot' . env('TELEGRAM_BOT_TOKEN') . '/' . $this->method, $this->data);

        return Http::post('https://api.telegram.org/bot' . env('TELEGRAM_BOT_TOKEN') . '/' . $this->method, $this->data);
    }
}
