<?php
require_once (__DIR__).'/raindrops/controller/Authentication.php';
require_once (__DIR__).'/raindrops/controller/Registration.php';
require_once (__DIR__).'/raindrops/controller/SessionHandler.php';
require_once (__DIR__).'/raindrops/controller/MailHandler.php';
require_once (__DIR__).'/raindrops/model/Database.php';
require_once (__DIR__).'/raindrops/router/Router.php';
require_once (__DIR__).'/lib/AppConfig.php';
require_once (__DIR__).'/lib/DebugConfig.php';
require_once (__DIR__).'/controller/Channel.php';
require_once (__DIR__).'/controller/ChannelMember.php';
require_once (__DIR__).'/controller/ChannelMessage.php';

use \Parallax\Channel;
use \Parallax\ChannelMessage;
use \Parallax\ChannelMember;
use \Parallax\AppConfig;
use \Parallax\DebugConfig;

$router = new \Raindrops\Router();
$db = new \Raindrops\Database('sqlite');
$realm = 'parallax';
$id = null;
$sh = null;
$anon_routes = array( // dont check session for these routes
    'recovery-token',
    'verify-session',
    'auth-request',
    'auth-reply',
    'register',
    'lib',
    'controller',
    'view',
    'model',
    'app.js',
    'favicon.ico',
    '',
);

$includes = array(
    'model' => array(
        'storage.js',
    ),
    'controller' => array(
        'client.js',
        'socket.js',
        'ui.js',
        'identity.js',
    ),
    'lib' => array(
        'config.js',
        'object.js',
        'crypto.js',
        'storage.js',
        'ext/jquery.js',
        'ext/jquery-ui.js',
        'ext/socket.io.js'
    ),
    'view' => array(
        'ui.html',
        'ui.css',
        'normalize.css',
        'theme/blu.css',
        'theme/dark.css',
    ),
    'app.js' => array(''),
    'favicon.ico' => array(''),
);

if (sizeof($_GET) > 0) {
    $_POST = $_GET;
}

session_start();

$router->add_route('!',
    $data = array(
        'realm' => $realm,
    ),
    function($data) use (& $db, & $id, & $anon_routes, & $router, & $sh) {
        $response = null;

        if (! $db->connect()) {
            $response = array(
                'status' => 'error',
                'message' => 'Database error',
                'db_log' => $db->log_tail(DebugConfig::DEBUG_LOG_TAIL),
            );
        }

        if (! in_array($router->request_action, $anon_routes)) {
            $session_seed = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
            $sh = new \Raindrops\SessionHandler($db, $data['realm'], null, $session_seed);
            $sh->session_seed = $sh->clean_xff_header($sh->session_seed); // remove ports from xff header IPs

            if ($sh->verify()) {
                $id = $sh->id;
            } else {
                $response = array(
                    'status' => 'error',
                    'message' => 'Not authenticated, please login or register',
                    'db_log' => $sh->db->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                    'log' => $sh->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                );
            }
        }

        return $response;
    }
);

$router->add_route('verify-session',
	$data = array(
		'realm' => $realm,
		'session_id' => $_POST['session_id'],
		'session_seed' => (isset($_POST['session_seed']) ? $_POST['session_seed'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'])),
        'identity' => $_POST['identity'],
	),
	function($data) use (& $db) {
		$vs = new \Raindrops\SessionHandler($db, $data['realm'], $data['session_id'], $data['session_seed'], $data['identity']);
        $vs->session_seed = $vs->clean_xff_header($vs->session_seed); // remove ports from xff header IPs

		if ($vs->verify($read_only = true)) {
			$response = array(
				'status' => 'success',
				'message' => 'Session verified',
                'session_id' => $vs->session_id,
                'session_seed' => $vs->session_seed,
                'identity' => $vs->identity,
                'db_log' => $vs->db->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                'log' => $vs->log_tail(DebugConfig::DEBUG_LOG_TAIL),
			);
		} else {
			$response = array(
				'status' => 'error',
				'message' => 'Session did not verify',
                'session_id' => $vs->session_id,
                'session_seed' => $vs->session_seed,
                'db_log' => $vs->db->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                'log' => $vs->log_tail(DebugConfig::DEBUG_LOG_TAIL),
			);
		}
        return $response;
	}
);

$router->add_route('auth-reply',
    $data = array(
		/**
		 * POST:
		 * nonce_identity = string
		 * nonce = hash string
		 * nonce_signature = base64 signature
		 * device = device assoc for pubkey
		 */
        'nonce_identity' => $_POST['nonce_identity'],
        'nonce' => $_POST['nonce'],
        'nonce_signature' => $_POST['nonce_signature'],
        'device' => $_POST['device'],
        'realm' => $realm,
    ),
    function($data) use (& $db) {
        $crypto = new \Raindrops\Crypto();
        $sfa = new \Raindrops\Authentication($db, $data['nonce_identity'], $data['realm']);
        if ($sfa->get_identity()) {
            if ($sfa->verify_challenge_response($data)) {
                $seed = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
                $seed = $sfa->clean_xff_header($seed);
                $sfa->generate_auth_token(array($seed));

                $_SESSION['rd_auth_token'] = $sfa->token;
                $_SESSION['rd_auth_identity'] = $sfa->identity;

                $response = array(
                    'status' => 'success',
                    'message' => 'Authentication successful',
                    'identity' => $sfa->identity,
                    'device' => $data['device'],
					'session_id' => session_id(),
                    'db_log' => $sfa->db->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                    'log' => $sfa->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                );
            } else {
                $response = array(
                    'status' => 'error',
                    'message' => 'Challenge verification failed',
                    'nonce_identity' => $sfa->identity,
                    'device' => $data['device'],
                    'nonce_signature' => $data['nonce_signature'],
                    'nonce' => $data['nonce'],
                    'db_log' => $sfa->db->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                    'log' => $sfa->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                );
            }
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Identity retrieval failed',
                'nonce_identity' => $sfa->identity,
                'device' => $data['device'],
                'nonce_signature' => $data['nonce_signature'],
                'nonce' => $data['nonce'],
                'db_log' => $sfa->db->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                'log' => $sfa->log_tail(DebugConfig::DEBUG_LOG_TAIL),
            );
        }

        return $response;
    }
);

$router->add_route('auth-request',
    $data = array(
		/**
		 * POST:
		 * identity = string
		 * device = string
		 */
        'identity' => $_POST['identity'],
        'device' => $_POST['device'],
        'realm' => $realm,
    ),
    function($data) use (& $db) {
        $sfa = new \Raindrops\Authentication($db, $data['identity'], $data['realm']);
        if ($sfa->get_identity() && $sfa->create_challenge($data['device'])) {
            $response = array(
                'status' => 'success',
                'nonce' => $sfa->challenge,
                'identity' => $sfa->identity,
                'device' => $data['device'],
                'db_log' => $sfa->db->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                'log' => $sfa->log_tail(DebugConfig::DEBUG_LOG_TAIL),
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Authentication request failed',
                'identity' => $sfa->identity,
                'device' => $data['device'],
                'db_log' => $sfa->db->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                'log' => $sfa->log_tail(DebugConfig::DEBUG_LOG_TAIL),
            );
        }
        return $response;
    }
);

$router->add_route('register',
    $data = array(
		/**
		 * POST:
		 * identity = string
		 * pubkey = string
		 * device = string
		 * email = string
		 * recovery_token = (optional) string
		 */
        'identity' => $_POST['identity'],
        'pubkey' => $_POST['pubkey'],
        'device' => $_POST['device'],
        'email' => $_POST['email'],
        'recovery_token' => $_POST['recovery_token'],
        'realm' => $realm,
    ),
    function($data) use (& $db) {
        $sfr = new \Raindrops\Registration($db, $data['identity'], $data['realm']);

        $identity_data = array(
            'pubkey' => $data['pubkey'],
            'device' => $data['device'],
            'email' => $data['email'],
            'recovery_token' => $data['recovery_token'],
        );
        if ($sfr->create_identity($identity_data)) {
            $response = array(
                'status' => 'success',
                'message' => 'Identity created',
                'identity' => $sfr->identity,
                'email' => $sfr->email,
                'device' => $data['device'],
                'pubkey' => $data['pubkey'],
                'recovery_token' => $data['recovery_token'],
                'db_log' => $sfr->db->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                'log' => $sfr->log_tail(DebugConfig::DEBUG_LOG_TAIL),
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Registration failed',
                'identity' => $data['identity'],
                'email' => $data['email'],
                'device' => $data['device'],
                'pubkey' => $data['pubkey'],
                'recovery_token' => $data['recovery_token'],
                'db_log' => $sfr->db->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                'log' => $sfr->log_tail(DebugConfig::DEBUG_LOG_TAIL),
            );
        }
        return $response;
    }
);

$router->add_route('recovery-token',
    $data = array(
		/**
		 * POST:
		 * identity = string
		 * email = string
		 * device = string
		 */
        'identity' => $_POST['identity'],
        'email' => $_POST['email'],
        'device' => $_POST['device'],
        'realm' => $realm,
    ),
    function($data) use (& $db) {
        $sfr = new \Raindrops\Registration($db, $data['identity'], $data['realm']);

        if ($sfr->generate_recovery_token($data['device'], $data['email'])) {
            $mh = new \Raindrops\MailHandler();
            $mh->to = $sfr->email;
            $mh->from = 'no-reply@echoes.im';
            $mh->from_name = 'Parallax Identity';
            $mh->subject = 'Identity Recovery Token';
            $mh->message = 'Please use the following token when registering '. $sfr->identity .', to recover the identity: '. $sfr->recovery_token;

            $mail_sent = $mh->send($as_html = true);

            $response = array(
                'status' => 'success',
                'message' => 'Recovery token request successful',
                'identity' => $sfr->identity,
                'email' => $sfr->email,
                'device' => $data['device'],
                'mail_sent' => $mail_sent,
                'db_log' => $sfr->db->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                'log' => $sfr->log_tail(DebugConfig::DEBUG_LOG_TAIL),
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Recovery token request failed',
                'identity' => $data['identity'],
                'email' => $data['email'],
                'device' => $data['device'],
                'db_log' => $sfr->db->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                'log' => $sfr->log_tail(DebugConfig::DEBUG_LOG_TAIL),
            );
        }
        return $response;
    }
);

$router->add_route('delete-identity',
    $data = array(
		/**
		 * POST:
		 * identity = string
		 */
        'identity' => $_POST['identity'],
        'realm' => $realm,
    ),
    function($data) use (& $db, & $id, & $sh) {
        if ($id->identity !== $data['identity']) {
            $response = array(
                'status' => 'error',
                'message' => 'Please confirm deletion by posting your identity',
                'identity' => $data['identity'],
                'auth_identity' => $id->identity,
                'db_log' => $id->db->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                'log' => $id->log_tail(DebugConfig::DEBUG_LOG_TAIL),
            );
            return $response;
        }

        $sfr = new \Raindrops\Registration($db, $data['identity'], $data['realm']);

        if ($sfr->delete_identity()) {
            $sh->logout();
            $response = array(
                'status' => 'success',
                'message' => 'Identity deleted',
                'identity' => $sfr->identity,
                'db_log' => $sfr->db->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                'log' => $sfr->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                'sh_log' => $sh->log_tail(DebugConfig::DEBUG_LOG_TAIL),
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Identity deletion failed',
                'identity' => $sfr->identity,
                'pubkeys' => $sfr->pubkeys,
                'db_log' => $sfr->db->log_tail(DebugConfig::DEBUG_LOG_TAIL),
                'log' => $sfr->log_tail(DebugConfig::DEBUG_LOG_TAIL),
            );
        }
        return $response;
    }
);

$router->add_route('channel',
    $data = array(
		/**
		 * POST data:
		 * channel_id = int
		 * channel_name = string
		 * command = string
		 * message = string
		 * message_data = ban reason, kick reason, public key broadcast?
		 * message_type = int
		 * last_message_id = int - for get command
		 */
        'incoming' => str_replace(array("\n", "\r"), "\\n", $_POST['data']),
        'realm' => $realm,
    ),
    function($data) use (& $db, & $id) {
		if (! isset($id->id)) {
            $response = array(
                'status' => 'error',
                'message' => 'Unauthorized, please login first',
            );
			return $response;
		}
        $json_incoming = json_decode($data['incoming']);
        $json_error = json_last_error();

		$channel = new Channel($db, (int)$json_incoming->{'channel_id'}, $json_incoming->{'channel_name'});

		switch ($json_incoming->{'command'}) {
			case 'join':
				if ($channel->join($id->id)) {
					$response = array(
						'status' => 'success',
						'message' => "Successfully joined channel '". $channel->name ."'",
					);
				} else {
					$response = array(
						'status' => 'error',
						'message' => "Failed to join channel: ". json_encode($channel->log_tail(5)),
					);
				}
			break;
			case 'part':
				if ($channel->part($id->id)) {
					$response = array(
						'status' => 'success',
						'message' => "Successfully parted channel '". $channel->name ."'",
					);
				} else {
					$response = array(
						'status' => 'error',
						'message' => "Failed to part channel: ". json_encode($channel->log_tail(5)),
					);
				}
			break;
			case 'msg':
				$message = $json_incoming->{'message'};
				$message_data = $json_incoming->{'message_data'};
				$message_type = (int)$json_incoming->{'message_type'};

				$allowed_msg_command_types = array(
					AppConfig::MESSAGE_TYPE_MESSAGE,
					AppConfig::MESSAGE_TYPE_ACTION
				);

				if (in_array($message_type, $allowed_msg_command_types)) {
					$channel_message = new ChannelMessage($db, $id->id, $channel->id);

					if ($channel_message->send($message, $message_type, $message_data)) {
						$response = array(
							'status' => 'success',
							'message' => "Message sent to '". $channel->name ."'",
						);
					} else {
						$response = array(
							'status' => 'error',
							'message' => "Failed to send message: ". json_encode($channel_message->log_tail(5)),
						);
					}
				} else {
					$response = array(
						'status' => 'error',
						'message' => "Failed to send message, invalid message_type. Allowed: ". join(', ', $allowed_msg_command_types),
					);
				}
			break;
			case 'get':
				$since_last_message_id = (int)$json_incoming->{'last_message_id'};

				$channel_message = new ChannelMessage($db, $id->id, $channel->id);

				if ($channel_message->get($since_last_message_id)) {
					$response = $channel_message->messages;
				} else {
					$response = array(
						'status' => 'error',
						'message' => "Failed to get messages: ". json_encode($channel_message->log_tail(5)),
					);
				}
			break;
			default:
				$response = array(
					'status' => 'error',
					'message' => "Invalid channel command '". $json_incoming->{'command'} ."'",
				);
			break;

		}
        return $response;
    }
);

foreach (array_keys($includes) as $action) {
    $router->add_route($action,
        $secondaries = $includes[$action],
        function($secondaries) use(& $router) {
            if (sizeof($secondaries) == 0
                || in_array($router->secondary_action, $secondaries)) {
                $response = array(
                    'include' => (__DIR__) .'/client/'. $router->request_action . ($router->secondary_action ? '/'. $router->secondary_action : '')
                );
            }
            return $response;
        }
    );
}

$router->add_route('*',
    $data = array(
    ),
    function($data) {
        $response = array(
            'include' => (__DIR__).'/client/view/ui.html',
        );

        return $response;
    }
);

$view = $router->process();
if (isset($view['include'])) {
    $content_types = array(
        'css' => 'text/css',
        'js' => 'text/javascript',
        'html' => 'text/html'
    );

    $ext = pathinfo($view['include'], PATHINFO_EXTENSION);
    header('Content-type: '. $content_types[$ext]);

	include $view['include'];
    //echo $view['include'];
} else {
    if (! DebugConfig::DEBUG) {
        $view['log'] = [];
        $view['db_log'] = [];
        $view['sh_log'] = [];
    }
	echo json_encode($view);
}
?>
