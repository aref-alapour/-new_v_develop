<?php

class DB {

    private $conn;

    public function __construct() {
        $servername = "localhost";
        $dbname     = "tlescape_bot";
        $username   = "tlescape_bot";
        $password   = "Saeed12349!@#$(";

        $this->conn = new mysqli($servername, $username, $password, $dbname);
        if ($this->conn->connect_error)
            die("Connection failed: " . $this->conn->connect_error);
    }

    public function add_state ($chat_id, $state, $duplicate = false) {

        if ( !$duplicate )
            if ( count($this->get_state($chat_id)) > 0 ) return;

        $sql = "INSERT INTO states (    chat_id, state) VALUES ($chat_id, " . "'" . $state . "')";

        if ($this->conn->query($sql) === TRUE)
            return true;
        else
            return false;
    }

    public function get_state ($chat_id, $single = false) {

        $sql = "SELECT * FROM states WHERE chat_id LIKE $chat_id";

        $result = $this->conn->query($sql);

        $temp = array();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $temp[] = $row;
            }
        }

        if( $single )
            return $temp[0]['state'];

        return $temp;
    }

    public function update_state ($chat_id, $state) {
        $sql = "UPDATE states SET state= " . "'" . $state . "' WHERE chat_id LIKE $chat_id";

        if ($this->conn->query($sql) === TRUE)
            return true;
        else
            return false;
    }

    public function delete_state ($chat_id) {
        $sql = "SELECT FROM states WHERE chat_id LIKE  " . "'" . $chat_id . "' ";

        if ($this->conn->query($sql) === TRUE)
            return true;
        else
            return false;
    }

} $db_obj = new DB();
/***********************************************************************************************************/
class ezTelegramBot
{
    const BOT_TOKEN         = "5808885324:AAH91yTlyITByTlv7eMPQdLDKmvBJ_9iAfc";
    const TELEGRAM_API_URL  = "https://api.telegram.org/bot";

    public $url;

    public function __construct() {
        $this->url = SELF::TELEGRAM_API_URL . SELF::BOT_TOKEN;
    }

    private function run_script($method) {
        return file_get_contents($this->url . '/'. $method);
    }

    public function web_hook() {
        return json_decode(file_get_contents("php://input"), true);
    }

    public function send_message($chatId, $text) {
        $url = "sendmessage?text=$text&chat_id=$chatId";
        return $this->run_script($url);
    }

    public function validateMobile($mobile) {

        if (ctype_digit($mobile)) {

            if (strlen($mobile) == 11 && substr($mobile, 0, 2) == "09") {
                return array( 'success' => true, 'data' => substr($mobile, 1) );

            } elseif (strlen($mobile) == 10 && substr($mobile, 0, 1) == "9") {
                return array( 'success' => true, 'data' => $mobile );

            } else {
                return array( 'success' => false, 'data' => 'خطا: شماره موبایل باید 11 رقم باشد!' );
            }

        } else {
            return array( 'success' => false, 'data' => 'خطا: شماره موبایل باید عدد باشد!' );
        }
    }

    public function api_request($url, $data) {
        $response = '';

        $i = 5;
        while ( empty($response) && $i ) { $i --;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
            curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            $response = curl_exec($ch);
            curl_close($ch);

            sleep(1);
        }

        return $response;
    }

}  $bot_obj = new ezTelegramBot();
/***********************************************************************************************************/
$server = 'https://escapezoom.ir';

$message = $bot_obj->web_hook();

$chat_id = $message['message']['from']['id'];
$command = $message['message']['text'];

$state = $db_obj->get_state($chat_id, true);

if ( $state == 'end' ) {
    $bot_obj->send_message($chat_id, "این حساب تلگرامی قبلا ثبت شده است. اگر مشکلی دارید لطفا تماس بگیرید.");
    return;
}

if ( $command == '/start' ) {
    $db_obj->add_state($chat_id, 'start');
    $bot_obj->send_message($chat_id, "مجموعه دار گرامی، لطفآً شماره همراه خود را که در سایت بعنوان مدیر بازی ثبت شده است را وارد نمایید. اگر از شماره اصلی بازی خود مطمئن نیستید ابتدا از پشتیبانی استعلام کنید. ");

} elseif ( $state == 'start' ) { // the bot is waiting for a phone number

    $phone_validation = $bot_obj->validateMobile( $command );
    if ( $phone_validation['success'] ) {

        $response = $bot_obj->api_request($server . '/api/v1/telegram/send_code/', array("phone" => $command, "chat_id" => $chat_id));
        if ( !empty( $response ) ) {
            $response = json_decode($response);

            if ( $response->success ) {
                $db_obj->update_state($chat_id, 'phone');
                $bot_obj->send_message($chat_id, "کد پیامک شده را وارد کنید: ");

            } else
                $bot_obj->send_message($chat_id, $response->data);

        } else {
            $bot_obj->send_message($chat_id, "مشکلی پیش آمده! لطفا چند ساعت بعد مجددا امتحان کنید. در صورت حل نشدن مشکل با پشتیبانی در ارتباط باشید.");
        }

    } else {
        $bot_obj->send_message($chat_id, $phone_validation['data']);
    }

} elseif ( $state == 'phone' ) { // the bot is waiting for a sms code
    if ( strlen( $command ) == 4 ) {

        $response = $bot_obj->api_request($server . '/api/v1/telegram/verify_code/', array("code" => $command, "chat_id" => $chat_id));
        $response = json_decode($response);

        if ( $response->success ) {
            $db_obj->update_state($chat_id, 'end');
            $bot_obj->send_message($chat_id, $response->data);

        } else
            $bot_obj->send_message($chat_id, $response->data);

    } else {
        $bot_obj->send_message($chat_id, "خطا: کد تایید 4 رقمی می باشد. ");
    }
} else
    $bot_obj->send_message($chat_id, "مشکلی پیش آمده! لطفا بات را دوباره /start کنید.");
/*******************************************/
if ( isset( $_GET['chat_id'] ) && isset( $_GET['chat_id'] ) ) {

    // Log all incoming headers and $_GET data for debugging
    file_put_contents('request_log.txt', print_r([
        'headers' => getallheaders(),
        'get' => $_GET,
        'remote_addr' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
    ], true), FILE_APPEND);

// Your existing check fixed:
    if (isset($_GET['chat_id']) && isset($_GET['message'])) {
        $chat_id = $_GET['chat_id'];
        $message = $_GET['message'];

        // You can skip bot call temporarily to check connectivity
        // $bot_obj->send_message($chat_id, $message);

        // Simple debug response
        echo json_encode([
            'status' => 'success',
            'chat_id' => $chat_id,
            'message' => $message,
            'remote_addr' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
        ]);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Missing parameters']);
    }


    $bot_obj->send_message($_GET['chat_id'], $_GET['message']);
}
/*******************************************/
if ( isset( $_GET['saeed'] ) ) {
    $server = 'https://escapezoom.ir';

    $response = $bot_obj->api_request($server . '/api/v1/telegram/send_code/', array("phone" => '09353316152', "chat_id" => 97720589));
    $bot_obj->send_message(97720589, $response);
}


