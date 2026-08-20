<?php

namespace FMSS\Services;

class TelegramService {
    private string $botToken;
    private string $adminChatId;

    public function __construct() {
        $config = require __DIR__ . '/../../config/config.php';
        $this->botToken = trim(str_replace(' ', '', $config['telegram']['bot_token'] ?? ''));
        $this->adminChatId = trim($config['telegram']['admin_chat_id'] ?? '');
    }

    /**
     * Send a message to a Telegram Chat ID
     */
    public function sendMessage(string $chatId, string $message): bool {
        if (empty($this->botToken) || $this->botToken === 'YOUR_TELEGRAM_BOT_TOKEN_HERE') {
            // Log error in production. Returning false for now.
            return false;
        }
        
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
                'ignore_errors' => true // Prevents fatal stream errors on 400/500 responses
            ]
        ];
        
        $context  = stream_context_create($options);
        $result = @file_get_contents($url, false, $context); // @ suppresses the PHP warning
        
        if ($result === false) {
            return false;
        }
        
        $response = json_decode($result, true);
        return isset($response['ok']) && $response['ok'] === true;
    }

    /**
     * Polls the Telegram API to check if a specific connection token was received.
     * Returns the Chat ID if found, otherwise returns null.
     */
    public function checkForConnection(string $token): ?string {
        if (empty($this->botToken) || $this->botToken === 'YOUR_TELEGRAM_BOT_TOKEN_HERE') {
            return null;
        }

        $url = "https://api.telegram.org/bot{$this->botToken}/getUpdates";
        
        $options = [
            'http' => [
                'method' => 'GET',
                'ignore_errors' => true
            ]
        ];
        
        $context  = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        if ($result === false) {
            return null;
        }
        
        $response = json_decode($result, true);
        
        if (isset($response['ok']) && $response['ok'] === true && !empty($response['result'])) {
            foreach ($response['result'] as $update) {
                if (isset($update['message']['text']) && trim($update['message']['text']) === '/start ' . $token) {
                    return (string) $update['message']['chat']['id'];
                }
            }
        }
        
        return null;
    }

    /**
     * Send a notification to the configured Admin Chat ID
     */
    public function sendAdminNotification(string $message): bool {
        if (empty($this->adminChatId)) {
            return false;
        }
        return $this->sendMessage($this->adminChatId, $message);
    }
}