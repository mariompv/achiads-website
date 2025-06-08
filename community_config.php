<?php
/**
 * ACHIADS - Configuración de Comunidad
 * Archivo de configuración para funcionalidades de comunidad
 * Versión: 1.0.0
 */

// Prevenir acceso directo
if (!defined('ACHIADS_COMMUNITY')) {
    define('ACHIADS_COMMUNITY', true);
}

// Configuración de la base de datos (para futuras implementaciones)
define('DB_HOST', 'localhost');
define('DB_NAME', 'achiads_community');
define('DB_USER', 'achiads_user');
define('DB_PASS', 'secure_password_here');

// Configuración de archivos y directorios
define('COMMUNITY_DATA_DIR', __DIR__ . '/data/community/');
define('COMMUNITY_UPLOADS_DIR', __DIR__ . '/assets/images/community/');
define('COMMUNITY_LOGS_DIR', __DIR__ . '/logs/community/');

// Configuración de límites
define('MAX_POST_LENGTH', 5000);
define('MAX_COMMENT_LENGTH', 1000);
define('MAX_TITLE_LENGTH', 200);
define('MAX_EXCERPT_LENGTH', 300);
define('MAX_TAGS', 10);
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('POSTS_PER_PAGE', 12);

// Configuración de moderación
define('AUTO_APPROVE_POSTS', true);
define('AUTO_APPROVE_COMMENTS', true);
define('REQUIRE_MODERATION_KEYWORDS', ['spam', 'promoción', 'venta']);
define('BLOCKED_IPS', []);
define('BLOCKED_WORDS', ['spam', 'viagra', 'casino']);

// Configuración de notificaciones
define('ADMIN_EMAIL', 'admin@achiads.cl');
define('NOTIFY_NEW_POSTS', true);
define('NOTIFY_NEW_COMMENTS', true);
define('NOTIFY_REPORTED_CONTENT', true);

// Configuración de redes sociales
$social_config = [
    'facebook' => [
        'app_id' => '', // Agregar tu Facebook App ID
        'share_url' => 'https://www.facebook.com/sharer/sharer.php'
    ],
    'twitter' => [
        'share_url' => 'https://twitter.com/intent/tweet',
        'via' => 'ACHIADS_Chile' // Tu handle de Twitter
    ],
    'linkedin' => [
        'share_url' => 'https://www.linkedin.com/sharing/share-offsite/'
    ],
    'instagram' => [
        'handle' => '@achiads.chile' // Tu handle de Instagram
    ]
];

// Configuración de tipos de contenido
$content_types = [
    'news' => [
        'label' => 'Noticia',
        'icon' => '📰',
        'color' => '#2E8B57',
        'description' => 'Noticias sobre IA y sustentabilidad'
    ],
    'research' => [
        'label' => 'Investigación',
        'icon' => '🔬',
        'color' => '#4A90E2',
        'description' => 'Papers, estudios y investigaciones'
    ],
    'video' => [
        'label' => 'Video',
        'icon' => '🎥',
        'color' => '#FF6B35',
        'description' => 'Videos educativos y presentaciones'
    ],
    'event' => [
        'label' => 'Evento',
        'icon' => '🎓',
        'color' => '#9B59B6',
        'description' => 'Eventos, webinars y conferencias'
    ],
    'discussion' => [
        'label' => 'Discusión',
        'icon' => '💬',
        'color' => '#E67E22',
        'description' => 'Debates y discusiones abiertas'
    ]
];

// Configuración de etiquetas predefinidas
$predefined_tags = [
    'IA', 'Machine Learning', 'Deep Learning', 'Sustentabilidad', 'Medio Ambiente',
    'Agricultura', 'Ciudades Inteligentes', 'Energía Renovable', 'Ética',
    'Gobierno', 'Educación', 'Salud', 'Investigación', 'Chile', 'Latinoamérica',
    'Python', 'TensorFlow', 'PyTorch', 'Datos', 'Algoritmos', 'Innovación'
];

// Configuración de idiomas
$language_config = [
    'default' => 'es',
    'available' => ['es', 'en'],
    'strings' => [
        'es' => [
            'new_post' => 'Nueva publicación',
            'add_comment' => 'Agregar comentario',
            'share' => 'Compartir',
            'like' => 'Me gusta',
            'read_more' => 'Leer más',
            'load_more' => 'Cargar más',
            'no_posts' => 'No hay publicaciones',
            'search_placeholder' => 'Buscar publicaciones...',
            'required_field' => 'Campo requerido',
            'invalid_email' => 'Email inválido',
            'post_created' => 'Publicación creada exitosamente',
            'comment_added' => 'Comentario agregado',
            'error_occurred' => 'Ocurrió un error'
        ],
        'en' => [
            'new_post' => 'New post',
            'add_comment' => 'Add comment',
            'share' => 'Share',
            'like' => 'Like',
            'read_more' => 'Read more',
            'load_more' => 'Load more',
            'no_posts' => 'No posts',
            'search_placeholder' => 'Search posts...',
            'required_field' => 'Required field',
            'invalid_email' => 'Invalid email',
            'post_created' => 'Post created successfully',
            'comment_added' => 'Comment added',
            'error_occurred' => 'An error occurred'
        ]
    ]
];

// Configuración de cache
define('ENABLE_CACHE', true);
define('CACHE_DURATION', 300); // 5 minutos
define('CACHE_DIR', __DIR__ . '/cache/community/');

// Configuración de API externa (para futuras integraciones)
$api_config = [
    'analytics' => [
        'enabled' => true,
        'google_analytics_id' => 'GA_MEASUREMENT_ID'
    ],
    'recaptcha' => [
        'enabled' => false,
        'site_key' => '',
        'secret_key' => ''
    ],
    'mailchimp' => [
        'enabled' => false,
        'api_key' => '',
        'list_id' => ''
    ]
];

// Configuración de seguridad
$security_config = [
    'csrf_protection' => true,
    'rate_limit_enabled' => true,
    'rate_limit_requests' => 60, // por minuto
    'rate_limit_window' => 60, // segundos
    'ip_whitelist' => [],
    'ip_blacklist' => [],
    'content_filtering' => true,
    'auto_ban_threshold' => 5, // reportes antes de ban automático
    'session_timeout' => 3600 // 1 hora
];

// Funciones de utilidad
function getCommunityConfig($key = null) {
    global $content_types, $predefined_tags, $language_config, $social_config, $api_config, $security_config;
    
    $config = [
        'content_types' => $content_types,
        'predefined_tags' => $predefined_tags,
        'language' => $language_config,
        'social' => $social_config,
        'api' => $api_config,
        'security' => $security_config
    ];
    
    if ($key) {
        return $config[$key] ?? null;
    }
    
    return $config;
}

function getContentTypeConfig($type) {
    global $content_types;
    return $content_types[$type] ?? null;
}

function getLanguageString($key, $lang = 'es') {
    global $language_config;
    return $language_config['strings'][$lang][$key] ?? $key;
}

function isValidContentType($type) {
    global $content_types;
    return isset($content_types[$type]);
}

function sanitizeTag($tag) {
    $tag = trim($tag);
    $tag = preg_replace('/[^a-zA-Z0-9\s\-\_áéíóúñü]/i', '', $tag);
    $tag = ucfirst(strtolower($tag));
    return $tag;
}

function generateSlug($title) {
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug);
    $slug = preg_replace('/[\s\-]+/', '-', $slug);
    $slug = trim($slug, '-');
    return substr($slug, 0, 100);
}

function isSpamContent($content) {
    global $security_config;
    
    if (!$security_config['content_filtering']) {
        return false;
    }
    
    $content_lower = strtolower($content);
    $spam_patterns = [
        '/viagra|cialis|casino|lottery|winner/i',
        '/click here|urgent|act now/i',
        '/\$\d+|\d+\$|money|cash|prize/i',
        '/http[s]?:\/\/[^\s]{3,}/i' // Links externos excesivos
    ];
    
    foreach ($spam_patterns as $pattern) {
        if (preg_match($pattern, $content_lower)) {
            return true;
        }
    }
    
    // Verificar palabras bloqueadas
    foreach (BLOCKED_WORDS as $word) {
        if (strpos($content_lower, strtolower($word)) !== false) {
            return true;
        }
    }
    
    return false;
}

function createRequiredDirectories() {
    $directories = [
        COMMUNITY_DATA_DIR,
        COMMUNITY_UPLOADS_DIR,
        COMMUNITY_LOGS_DIR,
        CACHE_DIR
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

function logCommunityActivity($action, $details = []) {
    $log_file = COMMUNITY_LOGS_DIR . 'activity.log';
    
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'details' => $details
    ];
    
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
}

// Inicialización
if (!defined('ACHIADS_COMMUNITY_INIT')) {
    define('ACHIADS_COMMUNITY_INIT', true);
    createRequiredDirectories();
}

?>