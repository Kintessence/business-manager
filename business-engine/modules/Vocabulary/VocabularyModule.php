<?php
declare(strict_types=1);

namespace BusinessEngine\Vocabulary;

use BusinessEngine\ModuleInterface;
use BusinessEngine\Vocabulary\Admin\VocabularyController;

final class VocabularyModule implements ModuleInterface
{
    public function init(): void
    {
        require_once BE_PLUGIN_DIR . 'includes/helpers.php';
        $controller = new VocabularyController();
        $controller->register();
    }
}