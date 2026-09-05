<?php
declare(strict_types=1);

if (!function_exists('be_term')) {
    function be_term(string $key): string
    {
        return \BusinessEngine\Vocabulary\Services\VocabularyService::get($key);
    }
}