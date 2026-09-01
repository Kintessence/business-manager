<?php
declare(strict_types=1);

namespace BusinessEngine\Backup\Controllers;

use BusinessEngine\Backup\Services\BackupService;

final class BackupController
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'registerMenu']);
        add_action('admin_post_be_export_backup', [self::class, 'handleExport']);
        add_action('admin_post_be_restore_backup', [self::class, 'handleRestore']);
    }

    public static function registerMenu(): void
    {
        add_submenu_page(
            'business-engine',
            'Backup & Restauração Completa',
            '💾 Backup do Banco',
            'manage_options',
            'be-backup-restore',
            [self::class, 'renderPage']
        );
    }

    public static function renderPage(): void
    {
        $status = sanitize_key($_GET['status'] ?? '');
        $msg = sanitize_text_field($_GET['msg'] ?? '');

        include BE_PLUGIN_DIR . 'modules/Backup/Views/backup-view.php';
    }

    public static function handleExport(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Acesso negado.');
        }

        check_admin_referer('be_export_nonce', '_nonce');

        $backupData = BackupService::exportData();
        $json = json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'backup-business-engine-' . current_time('Y-m-d-His') . '.json';

        header('Content-Description: File Transfer');
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($json));

        echo $json;
        exit;
    }

    public static function handleRestore(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Acesso negado.');
        }

        check_admin_referer('be_restore_nonce', '_nonce');

        if (empty($_FILES['backup_file']['tmp_name'])) {
            wp_redirect(admin_url('admin.php?page=be-backup-restore&status=error&msg=' . urlencode('Nenhum arquivo enviado.')));
            exit;
        }

        $content = file_get_contents($_FILES['backup_file']['tmp_name']);
        $result = BackupService::restoreData($content);

        if ($result['success']) {
            wp_redirect(admin_url('admin.php?page=be-backup-restore&status=success&msg=' . urlencode($result['message'])));
        } else {
            wp_redirect(admin_url('admin.php?page=be-backup-restore&status=error&msg=' . urlencode($result['message'])));
        }
        exit;
    }
}