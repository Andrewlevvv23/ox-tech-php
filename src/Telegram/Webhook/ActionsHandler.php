<?php

namespace App\Telegram\Webhook;

use App\Facades\Telegram;
use App\Telegram\Webhook\Commands\Authorization;
use App\Telegram\Webhook\Commands\Lang;
use App\Telegram\Webhook\Commands\LinkingQR;
use App\Telegram\Webhook\Commands\Register;
use App\Telegram\Webhook\Commands\Support;
use App\Telegram\Webhook\Commands\Verification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActionsHandler
{
    /**
     * @throws Exception
     */
    public function handle(Request $request): string
    {
        $chatId = $request->input('message.chat.id') ?? $request->input('callback_query.from.id');

        if (!$chatId) {
            Log::error("Chat ID is missing in callback query: $chatId");
            return true;
        }

        $chatType = $this->getChatType($chatId);

        return match ($chatType) {
            'supergroup' => $this->handleSupergroupMessage($request),
            'private' => $this->handlePrivateChatMessage($request, $chatId),
            default => $this->handleUnknownChat($request),
        };
    }

    /**
     * @throws Exception
     */
    protected function handleSupergroupMessage(Request $request): string|true
    {
        if ($request->has('message.reply_to_message')) return app(Support::class)->handleReply($request);
        return true;
    }

    /**
     * @throws Exception
     */
    protected function handlePrivateChatMessage(Request $request, $chatId = ''): string
    {
        if ($request->has('callback_query')) return $this->handleCallback($request);
        if ($request->has('message.contact')) return app(Authorization::class)->handle($request);
        if ($request->has('message.photo')) return $this->handlePhoto($request, $chatId);
        if ($request->has('message.text') && !$request->has('message.entities')) return $this->handleText($request, $chatId);
       return $this->handleCommand($request);
    }

    protected function handleUnknownChat($request): string
    {
        Log::error("Chat ID is missing in callback query: " . $request->all());
        return true;
    }

    protected function getChatType($chatId): string
    {
        return $chatId == env('TELEGRAM_IRC_CHAT') ? 'supergroup' : 'private';
    }

    protected function handleCommand(Request $request): string
    {
        $path = app(Realization::class)->take($request);
        return $path
            ? app($path)->run()
            : app(Webhook::class)->run();
    }

     /**
     * @throws Exception
     */
    protected function handleCallback(Request $request): string
    {
        $callbackData = $request->input('callback_query.data');
        return match (true) {
            str_starts_with($callbackData, 'register_') => app(Register::class)->handle($request),
            str_starts_with($callbackData, 'lang_') => app(Lang::class)->handle($request),
            str_starts_with($callbackData, 'authorization_') => app(Authorization::class)->handle($request),
            str_starts_with($callbackData, 'support_') => app(Support::class)->handle($request),
            str_starts_with($callbackData, 'reply_') => app(Support::class)->handleReplyBtn($request),
            default => app(Webhook::class)->run(),
        };
    }

    /**
     * @throws Exception
     */
    protected function handlePhoto(Request $request, $chatId): string
    {
        $client = DB::table('clients')->where('chat_id', $chatId)->first() ?? '';

        if (empty($client->path_image_qr_code)) {
            app(LinkingQR::class)->handle($request);
        } elseif (empty($client->path_image_verification)){
            app(Verification::class)->handle($request);
        }

        Telegram::message($chatId, 'Photos have already been added, please contact the support service')->send();
        return true;
    }

    protected function handleText(Request $request, $chatId): string
    {
        $clientMessage = $request->input('message.text');
        $clientName =  $request->input('message.chat.first_name');
        $clientUserName =  $request->input('message.chat.username');

        Telegram::message(env('TELEGRAM_IRC_CHAT'),"Response from the client $chatId: \nName: $clientName \nUser-name: $clientUserName \n <b>$clientMessage</b>")->send();
        return true;
    }

}
