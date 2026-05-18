<?php
abstract class BaseController {

    protected function view(string $template, array $data = []): void {
        extract($data);
        $file = ROOT . '/app/views/' . $template . '.php';
        if (!file_exists($file)) {
            throw new RuntimeException("View not found: $template");
        }
        require $file;
    }

    protected function redirect(string $path): void {
        $base = defined('APP_BASE') ? APP_BASE : '';
        // Only prepend if path doesn't already start with base
        if ($base !== '' && !str_starts_with($path, $base)) {
            $path = $base . $path;
        }
        header("Location: $path");
        exit;
    }

    protected function requireAuth(): void {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    protected function requireAdmin(): void {
        $this->requireAuth();
        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo "<h1>403 Forbidden</h1>";
            exit;
        }
    }

    protected function json(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function currentUserId(): ?int {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    // Upload helper: validates MIME + size, moves to destination
    protected function handleUpload(
        string $inputName,
        string $destDir,
        int    $maxBytes,
        array  $allowedMimes = ['image/jpeg','image/png']
    ): ?string {
        if (empty($_FILES[$inputName]['tmp_name'])) return null;

        $file = $_FILES[$inputName];
        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > $maxBytes) return null;

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowedMimes, true)) return null;

        $ext      = $mime === 'image/png' ? 'png' : 'jpg';
        $filename = bin2hex(random_bytes(10)) . '.' . $ext;
        $destPath = ROOT . '/public/' . $destDir . $filename;

        if (!is_dir(ROOT . '/public/' . $destDir)) {
            mkdir(ROOT . '/public/' . $destDir, 0755, true);
        }

        move_uploaded_file($file['tmp_name'], $destPath);
        return $destDir . $filename;
    }
}