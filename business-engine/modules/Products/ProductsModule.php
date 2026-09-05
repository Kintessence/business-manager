<?php
declare(strict_types=1);

namespace BusinessEngine\Products;

use BusinessEngine\ModuleInterface;
use BusinessEngine\Products\Admin\ProductController;

final class ProductsModule implements ModuleInterface
{
    public function init(): void
    {
        $controller = new ProductController();
        $controller->register();
    }
}