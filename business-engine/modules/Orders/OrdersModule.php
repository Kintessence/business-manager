<?php
declare(strict_types=1);

namespace BusinessEngine\Orders;

use BusinessEngine\ModuleInterface;
use BusinessEngine\Orders\Admin\OrderController;

final class OrdersModule implements ModuleInterface
{
    public function init(): void
    {
        $controller = new OrderController();
        $controller->register();
    }
}