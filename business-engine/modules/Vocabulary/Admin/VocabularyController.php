<?php
declare(strict_types=1);

namespace BusinessEngine\Vocabulary\Admin;

use BusinessEngine\Vocabulary\Services\VocabularyService;

final class VocabularyController
{
    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenu'], 99);
        add_action('wp_ajax_be_save_vocabulary', [$this, 'ajaxSave']);
    }

    public function addMenu(): void
    {
        add_submenu_page(
            'business-engine',
            'Dicionário & Personalização',
            'Dicionário de Termos',
            'manage_options',
            'be-vocabulary',
            [$this, 'render']
        );
    }

    public function render(): void
    {
        $terms = VocabularyService::getAll();
        require BE_PLUGIN_DIR . 'modules/Vocabulary/Views/vocabulary-settings.php';
    }

    public function ajaxSave(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissão negada.'], 403);
        }

        $posted = $_POST['terms'] ?? [];
        VocabularyService::update($posted);

        wp_send_json_success(['message' => 'Termos e vocabulário atualizados com sucesso!']);
    }
}