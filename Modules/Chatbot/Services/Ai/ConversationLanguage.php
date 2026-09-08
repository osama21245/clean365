<?php

namespace Modules\Chatbot\Services\Ai;

class ConversationLanguage
{
    /**
     * @param  array<int, array{role?: string, content?: string}>  $history
     */
    public static function detect(array $history, string $userMessage): string
    {
        $texts = [trim($userMessage)];

        foreach (array_reverse($history) as $message) {
            if (($message['role'] ?? '') !== 'user') {
                continue;
            }

            $content = trim((string) ($message['content'] ?? ''));
            if ($content !== '') {
                $texts[] = $content;
            }

            if (count($texts) >= 6) {
                break;
            }
        }

        $arabic = 0;
        $latin = 0;

        foreach ($texts as $text) {
            $arabic += (int) preg_match_all('/\p{Arabic}/u', $text, $matches);
            $latin += (int) preg_match_all('/\p{Latin}/u', $text, $matches);
        }

        if ($latin >= 4 && $latin > $arabic) {
            return 'en';
        }

        return 'ar';
    }

    public static function isEnglish(string $lang): bool
    {
        return $lang === 'en';
    }

    public static function llmReplyRule(string $lang): string
    {
        if (self::isEnglish($lang)) {
            return '- Always reply in the same language the user uses. The user is writing in English — respond in clear, natural English only. Do not switch to Arabic unless the user does.';
        }

        return '- رد دائمًا بنفس لغة المستخدم. المستخدم يكتب بالعربي — استخدم العربي الفصيح الواضح المناسب للسوق السعودي. لا تتحول للإنجليزي إلا إذا المستخدم كتب بالإنجليزي.';
    }
}
