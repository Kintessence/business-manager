<?php
declare(strict_types=1);

namespace BusinessEngine\Products\Admin;

use BusinessEngine\Products\Repositories\ProductRepository;
use BusinessEngine\Products\DTOs\ProductDTO;
use BusinessEngine\BusinessProfile\Repositories\BusinessProfileRepository;
use BusinessEngine\Recipes\Repositories\RecipeRepository;
use BusinessEngine\Ingredients\Repositories\IngredientRepository;

final class ProductController
{
    private ProductRepository $repository;
    private BusinessProfileRepository $profileRepository;
    private RecipeRepository $recipeRepository;
    private IngredientRepository $ingredientRepository;

    public function __construct()
    {
        $this->repository = new ProductRepository();
        $this->profileRepository = new BusinessProfileRepository();
        $this->recipeRepository = new RecipeRepository();
        $this->ingredientRepository = new IngredientRepository();
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenu']);
        add_action('wp_ajax_be_save_product', [$this, 'ajaxSave']);
        add_action('wp_ajax_be_quick_update_product', [$this, 'ajaxQuickUpdate']);
        add_action('wp_ajax_be_get_product_details', [$this, 'ajaxGetDetails']);
        add_action('wp_ajax_be_delete_product', [$this, 'ajaxDelete']);
    }

    public function addMenu(): void
    {
        add_submenu_page(
            'business-engine',
            'Produtos Comerciais & Cardápio',
            'Catálogo de Produtos',
            'manage_options',
            'be-products',
            [$this, 'render']
        );
    }

    public function render(): void
    {
        global $wpdb;
        $search = sanitize_text_field($_GET['s'] ?? '');
        $role = sanitize_text_field($_GET['role'] ?? '');
        $products = $this->repository->getAll($search, $role);
        $profile = $this->profileRepository->get();
        $recipes = $this->recipeRepository->getAll();
        
        // Filtra apenas suprimentos do tipo Embalagem ou Acabamento
        $allSupplies = $this->ingredientRepository->getAll();
        $packagingSupplies = array_values(array_filter($allSupplies, function($s) {
            return in_array($s->category, ['Embalagem', 'Acabamento', 'Decoração', 'Outros'], true);
        }));

        $ordersCount = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}be_orders");

        require BE_PLUGIN_DIR . 'modules/Products/Views/products-list.php';
    }

    public function ajaxGetDetails(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        $id = (int)($_GET['id'] ?? 0);
        $product = $this->repository->findById($id);

        if (!$product) {
            wp_send_json_error(['message' => 'Produto não encontrado.']);
            return;
        }

        wp_send_json_success([
            'product' => $product->toArray(),
            'items'   => $product->items,
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
        $role = sanitize_text_field($_POST['strategic_role'] ?? 'carro_chefe');
        $finalPrice = max(0.0, (float)($_POST['final_price'] ?? 0.0));

        if ($id <= 0 || empty($name)) {
            wp_send_json_error(['message' => 'Dados inválidos.'], 400);
        }

        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'be_products',
            [
                'name'           => $name,
                'strategic_role' => $role,
                'final_price'    => $finalPrice,
            ],
            ['id' => $id]
        );

        wp_send_json_success(['message' => 'Atualizado com sucesso!']);
    }

    public function ajaxSave(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissão negada.'], 403);
        }

        $raw = $_POST['product'] ?? [];
        $items = $_POST['items'] ?? [];

        if (empty(trim($raw['name'] ?? ''))) {
            wp_send_json_error(['message' => 'O nome do produto é obrigatório.']);
            return;
        }

        $directCost = 0.0;
        foreach ($items as $item) {
            $directCost += (float)($item['subtotal_cost'] ?? 0.0);
        }

        $raw['direct_cost'] = $directCost;
        $dto = ProductDTO::fromArray($raw);
        $id = $this->repository->save($dto, $items);

        wp_send_json_success([
            'id'      => $id,
            'message' => 'Produto comercial salvo com sucesso!',
        ]);
    }

    public function ajaxDelete(): void
    {
        check_ajax_referer('be_secure_nonce', 'nonce');
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0 && $this->repository->delete($id)) {
            wp_send_json_success(['message' => 'Produto removido com sucesso.']);
        } else {
            wp_send_json_error(['message' => 'Erro ao remover produto.']);
        }
    }
}