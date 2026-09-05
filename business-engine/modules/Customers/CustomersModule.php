<?php
declare(strict_types=1);

namespace BusinessEngine\Customers;

use BusinessEngine\ModuleInterface;
use BusinessEngine\Customers\Admin\CustomerController;

final class CustomersModule implements ModuleInterface
{
    public function init(): void
    {
        $controller = new CustomerController();
        $controller->register();
    }
}