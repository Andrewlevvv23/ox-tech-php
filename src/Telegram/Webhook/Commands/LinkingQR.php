<?php

namespace App\Telegram\Webhook\Commands;

use App\Facades\Telegram;
use App\Telegram\Webhook\Webhook;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LinkingQR extends Webhook
{
    public function run(): \Illuminate\Http\Client\Response
    {
        $chatId = $this->request->input('message.chat.id');

        $textStart = $this->language === 'ru'
            ? '✏️ Что бы привязать Ваш счет, предоставьте фото QR код вашего кошелька: '
            : '✏️ To link your account, provide the QR code of your wallet: ';

        return Telegram::message($chatId, $textStart)->send();
    }

    /**
     * @throws ConnectionException
     */
    public function handle(Request $request)
    {
        $chatId = $request->input('message.chat.id');
        $pictures = $request->input('message.photo');

        $text = $this->language === 'ru' ? '🤝Спасибо, вас счет передан на рассмотрение. ' : '🤝Thank you, your invoice has been submitted for review. ';

        $file = Telegram::getFile($pictures[count($pictures) - 1]['file_id'])->send()->json();
        $file_path = $file['result']['file_path'];
        $ext = explode('.', $file_path);
        $ext = $ext[count($ext) - 1];
        $file_name = uniqid().'.'.$ext;
        Storage::putFileAs('public/telegram/images', env('TELEGRAM_FILE_PATH').env('TELEGRAM_BOT_TOKEN').'/'.$file_path, $file_name);
        Telegram::message($chatId, ($text))->send();

        $file_url = Storage::url('public/telegram/images/' . $file_name);
        $sendPhoto = DB::table('clients')->where('chat_id', $chatId)->update([
            'path_image_qr_code' => $file_url
        ]);
        if($sendPhoto) Log::info('Successfully saved photo QR-code client for chat_id: ' . $chatId);
        else Log::error('Failed to saved photo QR-code client for chat_id: ' . $chatId);

      return app(Verification::class, ['request' => $request])->run();
    }
}
