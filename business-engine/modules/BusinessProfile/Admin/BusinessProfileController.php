<?php
declare(strict_types=1);

namespace BusinessEngine\BusinessProfile\Admin;

use BusinessEngine\BusinessProfile\Repositories\BusinessProfileRepository;

final class BusinessProfileController
{
    private BusinessProfileRepository $repository;

    public function __construct()
    {
        $this->repository = new BusinessProfileRepository();
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenuPages']);
        add_action('wp_ajax_be_save_profile', [$this, 'handleAjaxSave']);
    }

    public function addMenuPages(): void
    {
        add_menu_page(
            'Business Engine',
            'Business Engine',
            'manage_options',
            'business-engine',
            [$this, 'renderSettings'],
            'dashicons-chart-area',
            30
        );

        add_submenu_page(
            'business-engine',
            'Configuração Financeira',
            '⚙️ Motor Financeiro',
            'manage_options',
            'business-engine',
            [$this, 'renderSettings']
        );
    }

    public function renderSettings(): void
    {
        $profile = $this->repository->get();
        require BE_PLUGIN_DIR . 'modules/BusinessProfile/Views/onboarding-wizard.php';
    }

    public function handleAjaxSave(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissão negada.'], 403);
        }

        $input = $_POST['profile'] ?? [];
        $dto = $this->repository->save($input);

        wp_send_json_success([
            'message' => 'Parâmetros financeiros calibrados com sucesso!',
            'profile' => $dto->toArray(),
        ]);
    }
}