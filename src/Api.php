<?php

namespace Mdeskorg\ViberBotApi;

use Exception;
use Throwable;

class Api
{
  public $token;
  public $api = 'https://chatapi.viber.com/pa/';

  function __construct($token)
  {
    $this->token = $token;
  }

  private function request($type, $data = [])
  {
    $url = $this->api . $type;

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
      "X-Viber-Auth-Token: " . $this->token,
      "Content-Type: application/json",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    if (count($data) > 0) {
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $resp = collect(json_decode(curl_exec($curl), true));
    curl_close($curl);

    if ($resp->get('status') === 0) {
      return $resp;
    } else {
      throw new Exception((string) $resp->get('status_message', 'undefined'));
    }
  }

  public function getMe()
  {
    return $this->request('get_account_info');
  }

  public function setWebhook($url)
  {
    return $this->request('set_webhook', [
      'url' => $url,
      'event_types' => ['delivered', 'seen', 'failed', 'subscribed', 'unsubscribed', 'conversation_started'],
      'send_name' => true,
      'send_photo' => true
    ]);
  }

  public function getUser($id)
  {
     try {
            $response = $this->request('get_user_details', ['id' => $id]);
            return collect($response->get('user'));
        } catch (Throwable $th) {
            return collect([]);
        }
  }

  public function sendMessage($user_id, $text)
  {
    return collect($this->request('send_message', [
      'receiver' => $user_id,
      'type' => 'text', 'text' => $text
    ]));
  }

  public function sendPhoto($user_id,  $photo, $caption = '')
  {
    return collect($this->request('send_message', [
      'receiver' => $user_id,
      'type' => 'picture',
      'text' => $caption,
      'media' => $photo
    ]));
  }
}
