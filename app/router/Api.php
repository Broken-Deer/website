<?php

namespace App\Router;

require_once __DIR__ . '/../auth/Auth.php';
require_once __DIR__ . '/../config/LoadConfigs.php';
use App\Database\DatabaseApp;
use App\Auth\Auth;

/* error_reporting(0); */

class Api
{
    function __construct()
    {
        $config = include __DIR__ . '/../../config/session.php';
        session_name($config['session_name']);
        session_start();
    }

    public static function user()
    {
        $_POST;
    }

    public static function auth($parameter)
    {
        switch ($parameter['type']) {
            case 'login':
                if (self::recaptcha($parameter['token'])) {
                    Auth::login(
                        $parameter['email'],
                        $parameter['password']
                    );
                } else {
                    self::throw_http_error('403');
                }
                break;

            case 'remember':
                if (self::recaptcha($parameter['token'])) {
                } else {
                    self::throw_http_error('403');
                }
                break;

            case 'reg':
                if (self::recaptcha($parameter['token'])) {
                    Auth::register(
                        $parameter['email'],
                        $parameter['password'],
                        $parameter['username']
                    );
                } else {
                    self::throw_http_error('403');
                }
                break;

            case 'qid':
                if (self::recaptcha($parameter['token'])) {
                    Auth::save_qid($parameter['code']);
                } else {
                    self::throw_http_error('403');
                }
                break;
            default:
                self::throw_http_error('400');
                break;
        }
    }

    public static function get_user_info()
    {
        return [
            'uid'      => 1,
            'username' => 'Old_Driver__',
        ];
    }

    public static function recaptcha($token)
    {
        $get = "https://recaptcha.net/recaptcha/api/siteverify?secret=6LfwxPcgAAAAAIZofwKMwLx16ztK8JAN5NMSBbiu&response=" . $token;
        $return_data = json_decode(file_get_contents(
            $get,
            false,
            stream_context_create(
                array(
                    'https' => array(
                        'method' => "GET",
                        'timeout' => 20
                    )
                )
            )
        ), true);
        if (!($return_data && $return_data['success'])) {
            return false;
        } else {
            return true;
        }
    }

    public static function throw_http_error($code)
    {
        $config = include __DIR__ . '/../../config/error.php';
        $context = DatabaseApp::load_custom_text();
        $context['error_message'] = $config[$code]['message'];
        $context['error_description'] = $config[$code]['description'];

        header('HTTP/1.1 ' . $context['error_message']);
    }
}
