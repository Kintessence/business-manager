<?php
declare(strict_types=1);

namespace BusinessEngine\Ingredients\Admin;

use BusinessEngine\Ingredients\Repositories\IngredientRepository;
use BusinessEngine\Ingredients\DTOs\IngredientDTO;

final class IngredientController
{
    private IngredientRepository $repository;

    public function __construct()
    {
        $this->repository = new IngredientRepository();
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenu']);
        add_action('wp_ajax_be_save_bulk_ingredients', [$this, 'ajaxSaveBulk']);
        add_action('wp_ajax_be_delete_ingredient', [$this, 'ajaxDelete']);
    }

    public function addMenu(): void
    {
        $menuTitle = function_exists('be_term') ? be_term('supplies_plural') : 'Insumos & Embalagens';
        add_submenu_page(
            'business-engine',
            $menuTitle,
            $menuTitle,
            'manage_options',
            'be-ingredients',
            [$this, 'render']
        );
    }

    public function render(): void
    {
        global $wpdb;
        $search = sanitize_text_field($_GET['s'] ?? '');
        $category = sanitize_text_field($_GET['category'] ?? '');
        $ingredients = $this->repository->getAll($search, $category);
        $categories = $this->repository->getCategories();

        $recipesCount = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}be_recipes");

        require BE_PLUGIN_DIR . 'modules/Ingredients/Views/ingredients-list.php';
    }

    public function ajaxSaveBulk(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissão negada.'], 403);
        }

        $items = $_POST['items'] ?? [];
        if (!is_array($items)) {
            wp_send_json_error(['message' => 'Nenhum dado enviado.']);
            return;
        }

        $dtos = [];
        foreach ($items as $itemData) {
            $name = trim($itemData['name'] ?? '');
            if (empty($name)) continue;

            $dtos[] = IngredientDTO::fromArray($itemData);
        }

        $this->repository->saveBulk($dtos);
        wp_send_json_success(['message' => 'Insumos salvos com sucesso!']);
    }

    public function ajaxDelete(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0 && $this->repository->delete($id)) {
            wp_send_json_success(['message' => 'Insumo removido com sucesso.']);
        } else {
            wp_send_json_error(['message' => 'Erro ao remover insumo.']);
        }
    }
}