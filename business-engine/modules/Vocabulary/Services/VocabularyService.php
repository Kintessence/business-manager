<?php
declare(strict_types=1);

namespace BusinessEngine\Vocabulary\Services;

final class VocabularyService
{
    private const OPTION_KEY = 'be_system_vocabulary';

    public static function getDefaults(): array
    {
        return [
            // Módulo 2: Insumos
            'supplies_singular'       => 'Insumo',
            'supplies_plural'         => 'Insumos & Embalagens',
            'search_supplies_ph'      => 'Buscar insumos pelo nome...',

            // Módulo 3: Fichas Técnicas
            'recipes_singular'        => 'Ficha Técnica',
            'recipes_plural'          => 'Fichas Técnicas',
            'search_recipes_ph'       => 'Buscar fichas técnicas pelo nome...',

            // Módulo 4: Catálogo de Produtos
            'products_singular'       => 'Produto Comercial',
            'products_plural'         => 'Catálogo de Produtos',
            'search_products_ph'      => 'Buscar produtos comerciais...',

            // Módulo 5: Clientes (Pronto para Uso)
            'customers_singular'      => 'Cliente',
            'customers_plural'        => 'Clientes & CRM',
            'search_customers_ph'     => 'Buscar clientes por nome ou telefone...',

            // Módulo 6: Pedidos (Pronto para Uso)
            'orders_singular'         => 'Pedido',
            'orders_plural'           => 'Histórico de Pedidos',
            'search_orders_ph'        => 'Buscar pedidos...',

            // Módulo 7: Venda de Rua (Pronto para Uso)
            'street_sales_title'      => 'Venda de Rua / Pronta Entrega',
            'street_sales_btn'        => '+ Registrar Venda Avulsa',

            // Filtros e Tabelas Universais
            'all_categories_filter'   => 'Todas as Categorias',
            'all_roles_filter'        => 'Todas as Funções Estratégicas',
            'col_name'                => 'Item / Descrição',
            'col_category'            => 'Categoria',
            'col_yield'               => 'Rendimento',
            'col_prep_time'           => 'Tempo de Execução',
            'col_direct_cost'         => 'Custo Direto (CMV)',
            'col_sale_price'          => 'Preço de Venda',
            'col_margin'              => 'Margem Efetiva',
            'col_actions'             => 'Ações',
        ];
    }

    public static function getAll(): array
    {
        $saved = get_option(self::OPTION_KEY, []);
        return array_merge(self::getDefaults(), is_array($saved) ? $saved : []);
    }

    public static function get(string $key): string
    {
        $all = self::getAll();
        return $all[$key] ?? (self::getDefaults()[$key] ?? $key);
    }

    public static function update(array $terms): void
    {
        $clean = [];
        $defaults = self::getDefaults();
        foreach ($defaults as $k => $v) {
            $clean[$k] = sanitize_text_field($terms[$k] ?? $v);
        }
        update_option(self::OPTION_KEY, $clean);
    }
}