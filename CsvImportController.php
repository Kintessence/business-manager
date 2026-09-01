<?php
declare(strict_types=1);

namespace BusinessEngine\CsvImport\Controllers;

use BusinessEngine\CsvImport\Services\MayaImporterService;

final class CsvImportController
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'registerSubmenu']);
    }

    public static function registerSubmenu(): void
    {
        add_submenu_page(
            'business-engine',
            'Importar Dados Maya',
            '📥 Importar Maya',
            'manage_options',
            'be-csv-import',
            [self::class, 'renderImportPage']
        );
    }

    public static function renderImportPage(): void
    {
        $status = '';
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('be_import_nonce', '_nonce')) {
            $type = sanitize_key($_POST['import_type'] ?? '');
            
            if (empty($_FILES['csv_file']['tmp_name']) || !is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
                $status = 'error';
                $message = 'Nenhum arquivo foi selecionado para upload.';
            } else {
                $result = MayaImporterService::importFile($_FILES['csv_file']['tmp_name'], $type);
                
                if ($result['count'] > 0) {
                    $status = 'success';
                    $message = "Sucesso! Foram importados/atualizados {$result['count']} registros de {$type}.";
                } else {
                    $status = 'error';
                    $message = "Insucesso: 0 registros importados. Motivo: " . ($result['error'] ?? 'Cabeçalho incompatível ou arquivo vazio.');
                }
            }
        }

        include BE_PLUGIN_DIR . 'modules/CsvImport/Views/import-view.php';
    }
}