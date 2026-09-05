<?php
declare(strict_types=1);

namespace BusinessEngine\Recipes\Admin;

use BusinessEngine\Recipes\Repositories\RecipeRepository;
use BusinessEngine\Recipes\DTOs\RecipeDTO;
use BusinessEngine\BusinessProfile\Repositories\BusinessProfileRepository;
use BusinessEngine\Ingredients\Repositories\IngredientRepository;

final class RecipeController
{
    private RecipeRepository $repository;
    private BusinessProfileRepository $profileRepository;
    private IngredientRepository $ingredientRepository;

    public function __construct()
    {
        $this->repository = new RecipeRepository();
        $this->profileRepository = new BusinessProfileRepository();
        $this->ingredientRepository = new IngredientRepository();
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenu']);
        add_action('wp_ajax_be_save_recipe', [$this, 'ajaxSave']);
        add_action('wp_ajax_be_quick_update_recipe', [$this, 'ajaxQuickUpdate']);
        add_action('wp_ajax_be_get_recipe_details', [$this, 'ajaxGetDetails']);
        add_action('wp_ajax_be_delete_recipe', [$this, 'ajaxDelete']);
    }

    public function addMenu(): void
    {
        add_submenu_page(
            'business-engine',
            'Fichas Técnicas & Receitas',
            'Fichas Técnicas',
            'manage_options',
            'be-recipes',
            [$this, 'render']
        );
    }

    public function render(): void
    {
        global $wpdb;
        $search = sanitize_text_field($_GET['s'] ?? '');
        $category = sanitize_text_field($_GET['category'] ?? '');
        $recipes = $this->repository->getAll($search, $category);
        $categories = $this->repository->getCategories();
        $profile = $this->profileRepository->get();
        $supplies = $this->ingredientRepository->getAll();

        $productsCount = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}be_products");

        require BE_PLUGIN_DIR . 'modules/Recipes/Views/recipes-list.php';
    }

    public function ajaxGetDetails(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        $id = (int)($_GET['id'] ?? 0);
        $recipe = $this->repository->findById($id);

        if (!$recipe) {
            wp_send_json_error(['message' => 'Ficha técnica não encontrada.']);
            return;
        }

        wp_send_json_success([
            'recipe' => $recipe->toArray(),
            'items'  => $recipe->items,
        ]);
    }

    public function ajaxQuickUpdate(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissão negada.'], 403);
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? 'Geral');
        $yieldQty = max(0.0001, (float)($_POST['yield_qty'] ?? 1.0));
        $yieldUnit = sanitize_text_field($_POST['yield_unit'] ?? 'porções');

        if ($id <= 0 || empty($name)) {
            wp_send_json_error(['message' => 'Dados inválidos.'], 400);
        }

        $recipe = $this->repository->findById($id);
        if (!$recipe) {
            wp_send_json_error(['message' => 'Receita não encontrada.'], 404);
        }

        $newUnitCost = $recipe->totalCost / $yieldQty;

        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'be_recipes',
            [
                'name'       => $name,
                'category'   => $category,
                'yield_qty'  => $yieldQty,
                'yield_unit' => $yieldUnit,
                'unit_cost'  => $newUnitCost,
            ],
            ['id' => $id]
        );

        wp_send_json_success([
            'unit_cost_formatted' => 'R$ ' . number_format($newUnitCost, 4, ',', '.'),
        ]);
    }

    public function ajaxSave(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissão negada.'], 403);
        }

        $raw = $_POST['recipe'] ?? [];
        $items = $_POST['items'] ?? [];

        if (empty(trim($raw['name'] ?? ''))) {
            wp_send_json_error(['message' => 'O nome da ficha técnica é obrigatório.']);
            return;
        }

        $profile = $this->profileRepository->get();
        $prepMin = max(0, (int)($raw['prep_time_minutes'] ?? 0));
        $laborCost = round($prepMin * $profile->costPerMinute, 4);

        $suppliesCost = 0.0;
        foreach ($items as $item) {
            $suppliesCost += (float)($item['subtotal_cost'] ?? 0.0);
        }

        $yieldQty = max(0.0001, (float)($raw['yield_qty'] ?? 1.0));
        $totalCost = $suppliesCost + $laborCost;
        $unitCost = round($totalCost / $yieldQty, 6);

        $raw['labor_cost_calculated'] = $laborCost;
        $raw['supplies_cost_calculated'] = $suppliesCost;
        $raw['total_cost'] = $totalCost;
        $raw['unit_cost'] = $unitCost;

        $dto = RecipeDTO::fromArray($raw);
        $id = $this->repository->save($dto, $items);

        wp_send_json_success([
            'id'      => $id,
            'message' => 'Ficha técnica salva com sucesso!',
        ]);
    }

    public function ajaxDelete(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0 && $this->repository->delete($id)) {
            wp_send_json_success(['message' => 'Ficha técnica removida com sucesso.']);
        } else {
            wp_send_json_error(['message' => 'Erro ao remover ficha técnica.']);
        }
    }
}