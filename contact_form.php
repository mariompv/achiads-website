<?php
/**
 * ACHIADS - Formulario de Contacto
 * Asociación Chilena de IA para el Desarrollo Sustentable
 * Versión: 1.0.0
 */

// Configuración de headers de seguridad
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Configuración de errores (desactivar en producción)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Configuración del formulario
$config = [
    'email_to' => 'info@achiads.cl',
    'email_from' => 'noreply@achiads.cl',
    'subject_prefix' => '[ACHIADS] ',
    'max_file_size' => 5 * 1024 * 1024, // 5MB
    'allowed_file_types' => ['pdf', 'doc', 'docx', 'txt'],
    'rate_limit' => 5, // máximo 5 envíos por hora por IP
    'honeypot_field' => 'website' // campo trampa para bots
];

// Función para limpiar y validar entrada
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Función para validar email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Función para validar longitud de texto
function validate_length($text, $min = 1, $max = 1000) {
    $length = strlen($text);
    return $length >= $min && $length <= $max;
}

// Función de rate limiting
function check_rate_limit($ip, $limit) {
    $log_file = 'logs/contact_log.txt';
    $current_time = time();
    $hour_ago = $current_time - 3600;
    
    // Crear directorio de logs si no existe
    if (!file_exists('logs')) {
        mkdir('logs', 0755, true);
    }
    
    // Leer log existente
    $attempts = [];
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
        $lines = explode("\n", $log_content);
        
        foreach ($lines as $line) {
            $parts = explode('|', $line);
            if (count($parts) >= 2) {
                $timestamp = intval($parts[0]);
                $logged_ip = $parts[1];
                
                // Solo considerar intentos de la última hora
                if ($timestamp >= $hour_ago && $logged_ip === $ip) {
                    $attempts[] = $timestamp;
                }
            }
        }
    }
    
    // Verificar si excede el límite
    if (count($attempts) >= $limit) {
        return false;
    }
    
    // Registrar este intento
    $log_entry = $current_time . '|' . $ip . '|' . date('Y-m-d H:i:s') . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    
    return true;
}

// Función para detectar spam
function is_spam($data) {
    // Palabras clave de spam comunes
    $spam_keywords = [
        'viagra', 'cialis', 'lottery', 'winner', 'casino', 'poker',
        'credit card', 'loan', 'mortgage', 'investment', 'bitcoin',
        'cryptocurrency', 'forex', 'trading', 'click here', 'urgent'
    ];
    
    $content = strtolower($data['name'] . ' ' . $data['email'] . ' ' . $data['message']);
    
    foreach ($spam_keywords as $keyword) {
        if (strpos($content, $keyword) !== false) {
            return true;
        }
    }
    
    // Verificar enlaces excesivos
    $link_count = preg_match_all('/https?:\/\//', $data['message']);
    if ($link_count > 2) {
        return true;
    }
    
    // Verificar campo honeypot
    if (!empty($data['website'])) {
        return true;
    }
    
    return false;
}

// Función para enviar email
function send_email($to, $subject, $body, $from, $reply_to) {
    $headers = [
        'From: ' . $from,
        'Reply-To: ' . $reply_to,
        'Content-Type: text/html; charset=UTF-8',
        'X-Mailer: PHP/' . phpversion(),
        'X-Priority: 3'
    ];
    
    return mail($to, $subject, $body, implode("\r\n", $headers));
}

// Función para generar respuesta JSON
function json_response($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('c')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Método no permitido');
}

// Verificar CSRF token (implementar en versión futura)
// if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
//     json_response(false, 'Token de seguridad inválido');
// }

// Obtener IP del usuario
$user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
           $_SERVER['HTTP_X_REAL_IP'] ?? 
           $_SERVER['REMOTE_ADDR'] ?? 
           'unknown';

// Verificar rate limiting
if (!check_rate_limit($user_ip, $config['rate_limit'])) {
    json_response(false, 'Demasiados intentos. Inténtalo más tarde.');
}

// Obtener y limpiar datos del formulario
$form_data = [
    'name' => clean_input($_POST['name'] ?? ''),
    'email' => clean_input($_POST['email'] ?? ''),
    'organization' => clean_input($_POST['organization'] ?? ''),
    'type' => clean_input($_POST['type'] ?? 'individual'),
    'subject' => clean_input($_POST['subject'] ?? ''),
    'message' => clean_input($_POST['message'] ?? ''),
    'website' => clean_input($_POST['website'] ?? ''), // honeypot
    'privacy_accepted' => isset($_POST['privacy_accepted'])
];

// Validaciones
$errors = [];

// Validar nombre
if (empty($form_data['name'])) {
    $errors[] = 'El nombre es obligatorio';
} elseif (!validate_length($form_data['name'], 2, 100)) {
    $errors[] = 'El nombre debe tener entre 2 y 100 caracteres';
}

// Validar email
if (empty($form_data['email'])) {
    $errors[] = 'El email es obligatorio';
} elseif (!validate_email($form_data['email'])) {
    $errors[] = 'El formato del email no es válido';
}

// Validar tipo de membresía
if (!in_array($form_data['type'], ['individual', 'organization'])) {
    $errors[] = 'Tipo de membresía no válido';
}

// Validar organización (solo si es tipo organización)
if ($form_data['type'] === 'organization' && empty($form_data['organization'])) {
    $errors[] = 'El nombre de la organización es obligatorio';
}

// Validar asunto
if (empty($form_data['subject'])) {
    $errors[] = 'El asunto es obligatorio';
} elseif (!validate_length($form_data['subject'], 5, 200)) {
    $errors[] = 'El asunto debe tener entre 5 y 200 caracteres';
}

// Validar mensaje
if (empty($form_data['message'])) {
    $errors[] = 'El mensaje es obligatorio';
} elseif (!validate_length($form_data['message'], 20, 5000)) {
    $errors[] = 'El mensaje debe tener entre 20 y 5000 caracteres';
}

// Validar aceptación de privacidad
if (!$form_data['privacy_accepted']) {
    $errors[] = 'Debes aceptar la política de privacidad';
}

// Si hay errores, devolver respuesta de error
if (!empty($errors)) {
    json_response(false, 'Por favor corrige los siguientes errores:', $errors);
}

// Verificar spam
if (is_spam($form_data)) {
    // Log del intento de spam
    error_log("Intento de spam detectado desde IP: $user_ip");
    json_response(false, 'Mensaje rechazado por filtros de seguridad');
}

// Preparar email para ACHIADS
$email_subject = $config['subject_prefix'] . $form_data['subject'];

$email_body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Nuevo mensaje desde achiads.cl</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2E8B57; color: white; padding: 20px; text-align: center; }
        .content { background: #f9f9f9; padding: 20px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #2E8B57; }
        .value { margin-top: 5px; }
        .footer { background: #f1f1f1; padding: 15px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>ACHIADS - Nuevo Mensaje</h1>
        </div>
        <div class='content'>
            <div class='field'>
                <div class='label'>Nombre:</div>
                <div class='value'>" . htmlspecialchars($form_data['name']) . "</div>
            </div>
            
            <div class='field'>
                <div class='label'>Email:</div>
                <div class='value'>" . htmlspecialchars($form_data['email']) . "</div>
            </div>
            
            <div class='field'>
                <div class='label'>Tipo de membresía:</div>
                <div class='value'>" . ($form_data['type'] === 'individual' ? 'Individual' : 'Organización') . "</div>
            </div>
            
            " . ($form_data['organization'] ? "
            <div class='field'>
                <div class='label'>Organización:</div>
                <div class='value'>" . htmlspecialchars($form_data['organization']) . "</div>
            </div>
            " : "") . "
            
            <div class='field'>
                <div class='label'>Asunto:</div>
                <div class='value'>" . htmlspecialchars($form_data['subject']) . "</div>
            </div>
            
            <div class='field'>
                <div class='label'>Mensaje:</div>
                <div class='value'>" . nl2br(htmlspecialchars($form_data['message'])) . "</div>
            </div>
        </div>
        <div class='footer'>
            <p>
                <strong>Información técnica:</strong><br>
                IP: $user_ip<br>
                Navegador: " . htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido') . "<br>
                Fecha: " . date('Y-m-d H:i:s') . "
            </p>
        </div>
    </div>
</body>
</html>
";

// Intentar enviar el email
$email_sent = send_email(
    $config['email_to'],
    $email_subject,
    $email_body,
    $config['email_from'],
    $form_data['email']
);

if ($email_sent) {
    // Enviar email de confirmación al usuario
    $confirmation_subject = 'Confirmación - Tu mensaje ha sido recibido - ACHIADS';
    $confirmation_body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Confirmación - ACHIADS</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2E8B57; color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; }
            .footer { background: #f1f1f1; padding: 15px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>¡Gracias por contactarnos!</h1>
            </div>
            <div class='content'>
                <p>Estimado/a " . htmlspecialchars($form_data['name']) . ",</p>
                
                <p>Hemos recibido tu mensaje con el asunto: <strong>" . htmlspecialchars($form_data['subject']) . "</strong></p>
                
                <p>Nuestro equipo revisará tu consulta y te responderemos a la brevedad posible, generalmente dentro de 2-3 días hábiles.</p>
                
                <p>Si tu consulta es urgente, puedes contactarnos directamente a info@achiads.cl</p>
                
                <p>¡Gracias por tu interés en ACHIADS!</p>
                
                <p>Atentamente,<br>
                <strong>Equipo ACHIADS</strong><br>
                Asociación Chilena de IA para el Desarrollo Sustentable</p>
            </div>
            <div class='footer'>
                <p>
                    ACHIADS - Santiago, Chile<br>
                    <a href='https://achiads.cl'>www.achiads.cl</a>
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    send_email(
        $form_data['email'],
        $confirmation_subject,
        $confirmation_body,
        $config['email_from'],
        $config['email_to']
    );
    
    // Guardar en base de datos (implementar en futuro)
    // save_to_database($form_data);
    
    json_response(true, 'Mensaje enviado correctamente. Te responderemos pronto.');
    
} else {
    // Log del error
    error_log("Error al enviar email desde formulario de contacto. IP: $user_ip");
    json_response(false, 'Hubo un error al enviar el mensaje. Inténtalo más tarde.');
}

?>