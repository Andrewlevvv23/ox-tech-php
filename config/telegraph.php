<?php

return [
    /*
     * Telegram api base url, it can be overridden
     * for self-hosted servers
     */
    'telegram_api_url' => 'https://api.telegram.org/',

    /*
     * Sets the handler to be used when Telegraph
     * receives a new webhook call.
     */
    'webhook_handler' => OXTechPHP\Telegram\Handlers\EmptyWebhookHandler::class,

    /*
     * Sets the webhook URL that will be exposed by the server,
     * this can be customized or entirely disabled (by setting it to NULL)
     */
    'webhook_url' => '/telegraph/{token}/webhook',

    'bot_token' => '0000000000:AAEdIepawh_1NOTkkQFNNL0IBRL6xjla1qM',

    'group_chat_id' => "-10000000000000",

    /*
     * If enabled, Telegraph dumps received
     * webhook messages to logs
     */
    'debug_mode' => false,

    /*
     * Set model class for both TelegraphBot and TelegraphChat,
     * to allow more customization.
     *
     * Bot model must be or extend `OXTelegram\Telegram\Models\Bot::class`
     * Chat model must be or extend `OXTelegram\Telegram\Models\Chat::class`
     */
    'models' => [
        'bot' => OXTechPHP\Telegram\Models\Bot::class,
        'chat' => OXTechPHP\Telegram\Models\Chat::class,
    ],

    'storage' => [
        /**
         * Default storage driver to be used for Telegraph data
         */
        'default' => 'file',
    ],

    /**
     * Attachment validation rules, Telegram bot API defaults are set
     * can be changed to match higher limits when using a local bot
     * API server (ref. https://core.telegram.org/bots/api#using-a-local-bot-api-server)
     */
    'attachments' => [
        'thumbnail' => [
            'max_size_kb' => 200,
            'max_height_px' => 320,
            'max_width_px' => 320,
            'allowed_ext' => ['jpg'],
        ],
        'photo' => [
            'max_size_mb' => 10,
            'max_ratio' => 20,
            'height_width_sum_px' => 10000,
        ],
        'animation' => [
            'max_size_mb' => 50,
        ],
        'video' => [
            'max_size_mb' => 50,
        ],
        'audio' => [
            'max_size_mb' => 50,
        ],
        'document' => [
            'max_size_mb' => 50,
        ],
    ],
];
