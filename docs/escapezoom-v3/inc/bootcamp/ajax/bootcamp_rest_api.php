<?php
/**
 * Bootcamp Ultra-Fast AJAX Handler
 * Uses global Medoo function for database operations
 */

// Set headers for JSON response
header('Content-Type: application/json; charset=utf-8');

// Calculate theme path without WordPress
$current_file = __FILE__;
$theme_dir = dirname(dirname(dirname(dirname($current_file)))); // From inc/bootcamp/ajax/ to theme root

// Define get_template_directory() stub for init.php (before requiring it)
if (!function_exists('get_template_directory')) {
    function get_template_directory() {
        global $theme_dir;
        return $theme_dir;
    }
}

// Load Medoo init.php to use global medoo() function (reuses existing connection)
$medoo_init_path = $theme_dir . '/inc/medoo/init.php';
if (!file_exists($medoo_init_path)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'data' => ['message' => 'Medoo init not found']], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once $medoo_init_path;

// Get global Medoo instance (reuses connection from init.php)
$medoo = medoo();
if (!$medoo) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'data' => ['message' => 'Database connection failed']], JSON_UNESCAPED_UNICODE);
    exit;
}

// Simple nonce verification (without WordPress)
function verify_nonce($nonce, $action) {
    if (empty($nonce)) return false;
    // Simple hash-based verification
    $expected = hash('sha256', $action . 'v2-ajax-secret-key');
    return hash_equals($expected, $nonce);
}

// Simple sanitize function
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Send JSON response
function send_json($success, $data = [], $message = '') {
    $response = [
        'success' => $success,
        'data' => $data
    ];
    if ($message) {
        $response['data']['message'] = $message;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// Security check - simple nonce verification
$nonce = $_POST['nonce'] ?? '';
if (!verify_nonce($nonce, 'v2-ajax-nonce')) {
    send_json(false, [], 'Invalid nonce');
}

$action = sanitize_input($_POST['action_type'] ?? '');

// Get puzzle HTML functions path (without WordPress)
$theme_path = $theme_dir;
$puzzle_functions_path = $theme_path . '/inc/bootcamp/ajax/bootcamp_check_answer.php';

// Helper function to load puzzle functions only when needed
function load_puzzle_functions() {
    global $theme_path, $puzzle_functions_path;
    
    // Define helper functions before requiring puzzle functions
    if (!function_exists('get_template_directory_uri')) {
        function get_template_directory_uri() {
            global $theme_dir;
            $theme_name = basename($theme_dir);
            // Get site URL from server
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            return $protocol . $host . '/wp-content/themes/' . $theme_name;
        }
    }

    // Define WordPress function stubs to prevent errors
    if (!function_exists('wp_send_json_success')) {
        function wp_send_json_success($data = null) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    if (!function_exists('wp_send_json_error')) {
        function wp_send_json_error($data = null) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'data' => $data], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    if (!function_exists('wp_verify_nonce')) {
        function wp_verify_nonce($nonce, $action) {
            // Stub function - not used when BOOTCAMP_SKIP_SECURITY_CHECK is defined
            return true;
        }
    }

    if (!function_exists('sanitize_text_field')) {
        function sanitize_text_field($str) {
            return sanitize_input($str);
        }
    }

    // Define flag to skip security check in bootcamp_check_answer.php
    define('BOOTCAMP_SKIP_SECURITY_CHECK', true);

    if (file_exists($puzzle_functions_path)) {
        // Define a minimal Theme_PATH for compatibility
        if (!defined('Theme_PATH')) {
            define('Theme_PATH', $theme_path . DIRECTORY_SEPARATOR);
        }
        require_once $puzzle_functions_path;
    }
}

if ($action === 'check_envelopes_all') {
    // Check all three envelope answers at once
    // Don't use sanitize_input for Persian text as it may corrupt the characters
    $answer1 = trim($_POST['answer1'] ?? '');
    $answer2 = trim($_POST['answer2'] ?? '');
    $answer3 = trim($_POST['answer3'] ?? '');
    
    // Basic validation - just check if not empty
    if (empty($answer1) || empty($answer2) || empty($answer3)) {
        send_json(false, [], 'لطفا همه رمزها را وارد کنید');
    }
    
    // Define correct answers for envelopes
    $envelope_answers = [
        1 => 'رزرو',
        2 => 'بازی',
        3 => 'خاطره سازی',
    ];
    
    $all_correct = (
        $answer1 === $envelope_answers[1] &&
        $answer2 === $envelope_answers[2] &&
        $answer3 === $envelope_answers[3]
    );
    
    if ($all_correct) {
        send_json(true, [
            'correct' => true,
            'results' => [
                'envelope1' => true,
                'envelope2' => true,
                'envelope3' => true
            ]
        ]);
    } else {
        // Return which ones are correct
        $results = [
            'envelope1' => ($answer1 === $envelope_answers[1]),
            'envelope2' => ($answer2 === $envelope_answers[2]),
            'envelope3' => ($answer3 === $envelope_answers[3])
        ];
        send_json(false, [
            'correct' => false,
            'results' => $results
        ], 'برخی رمزها اشتباه است');
    }
    
} else if ($action === 'check_answer') {
    // Load puzzle functions only when needed
    load_puzzle_functions();
    
    $puzzle_num = $_POST['puzzle_num'] ?? 0;
    if ($puzzle_num == '4Final') {
        // Handle string puzzle number
    } else {
        $puzzle_num = intval($puzzle_num);
    }
    
    $answer = sanitize_input($_POST['answer'] ?? '');
    
    if (empty($puzzle_num)) {
        send_json(false, [], 'داده‌های ناقص');
    }
    
    // Define correct answers
    $correct_answers = [
        1 => 'بازی',
        2 => 'رزرو',
        3 => 'خاطره سازی',
        4 => ['رزرو', 'بازی', 'خاطره سازی'],
        '4Final' => 'المپیک',
    ];
    
    // Check answer
    $is_correct = false;
    
    if ($puzzle_num == 4) {
        $box1 = trim(sanitize_input($_POST['box1'] ?? ''));
        $box2 = trim(sanitize_input($_POST['box2'] ?? ''));
        $box3 = trim(sanitize_input($_POST['box3'] ?? ''));
        
        if ($box1 === $correct_answers[4][0] && 
            $box2 === $correct_answers[4][1] && 
            $box3 === $correct_answers[4][2]) {
            $is_correct = true;
        }
    } else if ($puzzle_num == '4Final') {
        // For 4Final, always accept any answer (we want to see what user guessed)
        $is_correct = true;
        // Store the guessed answer for analysis
        $guessed_answer = trim($answer);
    } else {
        $is_correct = (trim($answer) === $correct_answers[$puzzle_num]);
    }
    
    if ($is_correct) {
        $next_html = '';
        $puzzle_4_html = '';
        
        if ($puzzle_num == 1 && function_exists('get_puzzle_2_html')) {
            $next_html = get_puzzle_2_html();
        } else if ($puzzle_num == 2 && function_exists('get_puzzle_3_html')) {
            $next_html = get_puzzle_3_html();
        } else if ($puzzle_num == 3) {
            if (function_exists('get_puzzle_3_success_html')) {
                $next_html = get_puzzle_3_success_html();
            }
            if (function_exists('get_puzzle_4_html')) {
                $puzzle_4_html = get_puzzle_4_html();
            }
        }
        
        $response_data = [
            'message' => 'جواب صحیح است',
            'next_html' => $next_html
        ];
        
        if ($puzzle_num == 3) {
            $response_data['puzzle_4_html'] = $puzzle_4_html;
        }
        
        // For 4Final, also include the guessed answer
        if ($puzzle_num == '4Final') {
            $response_data['guessed_answer'] = $guessed_answer;
        }
        
        send_json(true, $response_data);
    } else {
        send_json(false, [], 'جواب اشتباه است');
    }
    
} else if ($action === 'submit_form') {
    // Don't sanitize for Persian text - just trim
    $fullname = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $idnumber = trim($_POST['student_id'] ?? '');
    $duration = intval($_POST['duration'] ?? 0);
    $final_answer = trim($_POST['final_answer'] ?? ''); // Store the guessed answer from 4Final
    
    if (empty($fullname) || empty($phone)) {
        send_json(false, [], 'لطفا نام و شماره تماس را وارد کنید');
    }
    
    // Check if phone already exists using global Medoo
    $existing = $medoo->get('wp_bootcamp', 'id', [
        'phone' => $phone
    ]);
    
    if ($existing) {
        send_json(false, [], 'این شماره تماس قبلا ثبت شده است');
    }
    
    // Calculate solved_at as time only (m:ss format, e.g., 2:44)
    $minutes = floor($duration / 60);
    $seconds = $duration % 60;
    $solved_at = $minutes . ':' . str_pad($seconds, 2, '0', STR_PAD_LEFT);
    
    // Insert into database using global Medoo
    try {
        $insert_data = [
            'fullname' => $fullname,
            'idnumber' => $idnumber ? $idnumber : '',
            'phone' => $phone,
            'solved_at' => $solved_at,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Store final_answer in idnumber field if idnumber is empty, otherwise append it
        // This way we can see what the user guessed for the final answer
        if (!empty($final_answer)) {
            if (empty($idnumber)) {
                $insert_data['idnumber'] = 'GUESS:' . $final_answer;
            } else {
                $insert_data['idnumber'] = $idnumber . '|GUESS:' . $final_answer;
            }
        }
        
        $result = $medoo->insert('wp_bootcamp', $insert_data);
        
        if ($result) {
            send_json(true, [], 'اطلاعات با موفقیت ثبت شد');
        } else {
            send_json(false, [], 'خطا در ثبت اطلاعات');
        }
    } catch (Exception $e) {
        send_json(false, [], 'خطا در ثبت اطلاعات: ' . $e->getMessage());
    }
    
} else {
    send_json(false, [], 'Invalid action');
}

exit;

