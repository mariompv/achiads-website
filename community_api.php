<?php
/**
 * ACHIADS - API Backend para Comunidad
 * Manejo de publicaciones, comentarios y funcionalidades de comunidad
 * Versión: 1.0.0
 */

// Configuración de headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Configuración de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Configuración de errores
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Configuración de la aplicación
$config = [
    'data_dir' => 'data/community/',
    'max_post_length' => 5000,
    'max_comment_length' => 1000,
    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'max_file_size' => 2 * 1024 * 1024, // 2MB
    'rate_limit' => 10, // máximo 10 acciones por minuto por IP
    'posts_per_page' => 12
];

// Crear directorio de datos si no existe
if (!file_exists($config['data_dir'])) {
    mkdir($config['data_dir'], 0755, true);
}

// Obtener acción de la URL
$action = $_GET['action'] ?? 'get_posts';
$method = $_SERVER['REQUEST_METHOD'];

// Rate limiting
if (!checkRateLimit()) {
    respondError('Demasiadas solicitudes. Inténtalo más tarde.', 429);
}

// Enrutador principal
try {
    switch ($action) {
        case 'get_posts':
            if ($method === 'GET') {
                getPosts();
            } else {
                respondError('Método no permitido', 405);
            }
            break;
            
        case 'get_post':
            if ($method === 'GET') {
                getPost();
            } else {
                respondError('Método no permitido', 405);
            }
            break;
            
        case 'create_post':
            if ($method === 'POST') {
                createPost();
            } else {
                respondError('Método no permitido', 405);
            }
            break;
            
        case 'update_post':
            if ($method === 'PUT' || $method === 'POST') {
                updatePost();
            } else {
                respondError('Método no permitido', 405);
            }
            break;
            
        case 'delete_post':
            if ($method === 'DELETE' || $method === 'POST') {
                deletePost();
            } else {
                respondError('Método no permitido', 405);
            }
            break;
            
        case 'add_comment':
            if ($method === 'POST') {
                addComment();
            } else {
                respondError('Método no permitido', 405);
            }
            break;
            
        case 'toggle_like':
            if ($method === 'POST') {
                toggleLike();
            } else {
                respondError('Método no permitido', 405);
            }
            break;
            
        case 'upload_image':
            if ($method === 'POST') {
                uploadImage();
            } else {
                respondError('Método no permitido', 405);
            }
            break;
            
        default:
            respondError('Acción no válida', 400);
    }
} catch (Exception $e) {
    error_log('Community API Error: ' . $e->getMessage());
    respondError('Error interno del servidor', 500);
}

/* ================================
   FUNCIONES PRINCIPALES
   ================================ */

function getPosts() {
    global $config;
    
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(50, intval($_GET['limit'] ?? $config['posts_per_page']));
    $type = $_GET['type'] ?? 'all';
    $search = $_GET['search'] ?? '';
    $sort = $_GET['sort'] ?? 'date_desc';
    
    $posts = loadPosts();
    
    // Filtrar por tipo
    if ($type !== 'all') {
        $posts = array_filter($posts, function($post) use ($type) {
            return $post['type'] === $type;
        });
    }
    
    // Filtrar por búsqueda
    if (!empty($search)) {
        $searchLower = strtolower($search);
        $posts = array_filter($posts, function($post) use ($searchLower) {
            return strpos(strtolower($post['title']), $searchLower) !== false ||
                   strpos(strtolower($post['excerpt']), $searchLower) !== false ||
                   strpos(strtolower($post['content']), $searchLower) !== false ||
                   array_reduce($post['tags'], function($carry, $tag) use ($searchLower) {
                       return $carry || strpos(strtolower($tag), $searchLower) !== false;
                   }, false);
        });
    }
    
    // Ordenar
    switch ($sort) {
        case 'date_desc':
            usort($posts, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            break;
        case 'date_asc':
            usort($posts, function($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });
            break;
        case 'likes_desc':
            usort($posts, function($a, $b) {
                return $b['likes'] - $a['likes'];
            });
            break;
        case 'comments_desc':
            usort($posts, function($a, $b) {
                return count($b['comments']) - count($a['comments']);
            });
            break;
    }
    
    // Paginar
    $total = count($posts);
    $offset = ($page - 1) * $limit;
    $posts = array_slice($posts, $offset, $limit);
    
    // Preparar respuesta
    $response = [
        'success' => true,
        'data' => [
            'posts' => array_values($posts),
            'pagination' => [
                'current_page' => $page,
                'total_posts' => $total,
                'total_pages' => ceil($total / $limit),
                'posts_per_page' => $limit
            ]
        ]
    ];
    
    respondSuccess($response);
}

function getPost() {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        respondError('ID de post requerido', 400);
    }
    
    $posts = loadPosts();
    $post = array_filter($posts, function($p) use ($id) {
        return $p['id'] == $id;
    });
    
    if (empty($post)) {
        respondError('Post no encontrado', 404);
    }
    
    respondSuccess(['post' => array_values($post)[0]]);
}

function createPost() {
    global $config;
    
    // Validar datos de entrada
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $errors = validatePostData($data);
    if (!empty($errors)) {
        respondError('Datos inválidos', 400, $errors);
    }
    
    // Crear nuevo post
    $post = [
        'id' => generateId(),
        'title' => sanitizeInput($data['title']),
        'type' => $data['type'],
        'excerpt' => sanitizeInput($data['excerpt'] ?? ''),
        'content' => sanitizeInput($data['content']),
        'author' => sanitizeInput($data['author'] ?? 'Usuario Anónimo'),
        'author_email' => filter_var($data['author_email'] ?? '', FILTER_VALIDATE_EMAIL),
        'date' => date('Y-m-d H:i:s'),
        'tags' => array_map('trim', explode(',', $data['tags'] ?? '')),
        'likes' => 0,
        'comments' => [],
        'status' => 'published', // En el futuro: pending, published, rejected
        'featured_image' => $data['featured_image'] ?? '',
        'metadata' => [
            'ip' => getUserIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]
    ];
    
    // Generar excerpt si no se proporciona
    if (empty($post['excerpt'])) {
        $post['excerpt'] = substr(strip_tags($post['content']), 0, 150) . '...';
    }
    
    // Guardar post
    $posts = loadPosts();
    array_unshift($posts, $post);
    savePosts($posts);
    
    // Log de la acción
    logAction('create_post', $post['id'], getUserIP());
    
    respondSuccess(['message' => 'Post creado exitosamente', 'post' => $post]);
}

function addComment() {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $postId = $data['post_id'] ?? null;
    $content = $data['content'] ?? '';
    $author = $data['author'] ?? 'Usuario Anónimo';
    $authorEmail = $data['author_email'] ?? '';
    
    if (!$postId || empty($content)) {
        respondError('Datos requeridos faltantes', 400);
    }
    
    if (strlen($content) > 1000) {
        respondError('Comentario demasiado largo', 400);
    }
    
    // Verificar que el post existe
    $posts = loadPosts();
    $postIndex = null;
    foreach ($posts as $index => $post) {
        if ($post['id'] == $postId) {
            $postIndex = $index;
            break;
        }
    }
    
    if ($postIndex === null) {
        respondError('Post no encontrado', 404);
    }
    
    // Crear comentario
    $comment = [
        'id' => generateId(),
        'author' => sanitizeInput($author),
        'author_email' => filter_var($authorEmail, FILTER_VALIDATE_EMAIL),
        'content' => sanitizeInput($content),
        'date' => date('Y-m-d H:i:s'),
        'ip' => getUserIP()
    ];
    
    // Agregar comentario al post
    $posts[$postIndex]['comments'][] = $comment;
    savePosts($posts);
    
    // Log de la acción
    logAction('add_comment', $postId, getUserIP());
    
    respondSuccess(['message' => 'Comentario agregado exitosamente', 'comment' => $comment]);
}

function toggleLike() {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $postId = $data['post_id'] ?? null;
    $userIP = getUserIP();
    
    if (!$postId) {
        respondError('ID de post requerido', 400);
    }
    
    // Cargar likes existentes
    $likesFile = "data/community/likes_{$postId}.json";
    $likes = [];
    if (file_exists($likesFile)) {
        $likes = json_decode(file_get_contents($likesFile), true) ?? [];
    }
    
    $isLiked = in_array($userIP, $likes);
    
    if ($isLiked) {
        // Quitar like
        $likes = array_filter($likes, function($ip) use ($userIP) {
            return $ip !== $userIP;
        });
    } else {
        // Agregar like
        $likes[] = $userIP;
    }
    
    // Guardar likes
    file_put_contents($likesFile, json_encode(array_values($likes)));
    
    // Actualizar contador en el post
    $posts = loadPosts();
    foreach ($posts as &$post) {
        if ($post['id'] == $postId) {
            $post['likes'] = count($likes);
            break;
        }
    }
    savePosts($posts);
    
    respondSuccess([
        'liked' => !$isLiked,
        'total_likes' => count($likes)
    ]);
}

function uploadImage() {
    global $config;
    
    if (!isset($_FILES['image'])) {
        respondError('No se envió ninguna imagen', 400);
    }
    
    $file = $_FILES['image'];
    
    // Validar archivo
    if ($file['error'] !== UPLOAD_ERR_OK) {
        respondError('Error al subir archivo', 400);
    }
    
    if ($file['size'] > $config['max_file_size']) {
        respondError('Archivo demasiado grande', 400);
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $config['allowed_file_types'])) {
        respondError('Tipo de archivo no permitido', 400);
    }
    
    // Generar nombre único
    $filename = 'community_' . generateId() . '.' . $extension;
    $uploadPath = 'assets/images/community/';
    
    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    $fullPath = $uploadPath . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $fullPath)) {
        respondSuccess([
            'message' => 'Imagen subida exitosamente',
            'url' => '/' . $fullPath
        ]);
    } else {
        respondError('Error al guardar archivo', 500);
    }
}

/* ================================
   FUNCIONES DE UTILIDAD
   ================================ */

function loadPosts() {
    global $config;
    $file = $config['data_dir'] . 'posts.json';
    
    if (!file_exists($file)) {
        return [];
    }
    
    $content = file_get_contents($file);
    return json_decode($content, true) ?? [];
}

function savePosts($posts) {
    global $config;
    $file = $config['data_dir'] . 'posts.json';
    
    $result = file_put_contents($file, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if ($result === false) {
        throw new Exception('No se pudo guardar los posts');
    }
    
    return true;
}

function validatePostData($data) {
    global $config;
    $errors = [];
    
    if (empty($data['title']) || strlen($data['title']) < 5) {
        $errors[] = 'Título requerido (mínimo 5 caracteres)';
    }
    
    if (empty($data['content']) || strlen($data['content']) < 20) {
        $errors[] = 'Contenido requerido (mínimo 20 caracteres)';
    }
    
    if (strlen($data['content']) > $config['max_post_length']) {
        $errors[] = 'Contenido demasiado largo';
    }
    
    $validTypes = ['news', 'research', 'video', 'event', 'discussion'];
    if (!in_array($data['type'] ?? '', $validTypes)) {
        $errors[] = 'Tipo de post inválido';
    }
    
    return $errors;
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateId() {
    return time() . '_' . bin2hex(random_bytes(8));
}

function getUserIP() {
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
           $_SERVER['HTTP_X_REAL_IP'] ?? 
           $_SERVER['REMOTE_ADDR'] ?? 
           'unknown';
}

function checkRateLimit() {
    global $config;
    
    $ip = getUserIP();
    $logFile = 'data/community/rate_limit.log';
    $currentTime = time();
    $timeWindow = 60; // 1 minuto
    
    // Crear directorio si no existe
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    
    // Leer log existente
    $attempts = [];
    if (file_exists($logFile)) {
        $lines = explode("\n", file_get_contents($logFile));
        foreach ($lines as $line) {
            $parts = explode('|', $line);
            if (count($parts) >= 2) {
                $timestamp = intval($parts[0]);
                $loggedIP = $parts[1];
                
                // Solo considerar intentos dentro de la ventana de tiempo
                if ($timestamp >= ($currentTime - $timeWindow) && $loggedIP === $ip) {
                    $attempts[] = $timestamp;
                }
            }
        }
    }
    
    // Verificar límite
    if (count($attempts) >= $config['rate_limit']) {
        return false;
    }
    
    // Registrar este intento
    $logEntry = $currentTime . '|' . $ip . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    return true;
}

function logAction($action, $resource_id, $ip) {
    $logFile = 'data/community/actions.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$action} | Resource: {$resource_id} | IP: {$ip}\n";
    
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

function respondSuccess($data = null) {
    $response = [
        'success' => true,
        'timestamp' => date('c')
    ];
    
    if ($data !== null) {
        if (isset($data['data'])) {
            $response['data'] = $data['data'];
        } else {
            $response = array_merge($response, $data);
        }
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

function respondError($message, $code = 400, $details = null) {
    http_response_code($code);
    
    $response = [
        'success' => false,
        'error' => $message,
        'timestamp' => date('c')
    ];
    
    if ($details !== null) {
        $response['details'] = $details;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

?>