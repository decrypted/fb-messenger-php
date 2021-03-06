<?php

namespace pimax;

use pimax\Messages\Message;
use pimax\Messages\MessageButton;

class FbBotApp
{
    /**
     * Request type GET
     */
    const TYPE_GET = "get";

    /**
     * Request type POST
     */
    const TYPE_POST = "post";

    /**
     * Request type DELETE
     */
    const TYPE_DELETE = "delete";

    /**
     * FB Messenger API Url
     *
     * @var string
     */
    protected $apiUrl = 'https://graph.facebook.com/';

    /**
     * @var null|string
     */
    protected $token = null;

    /**
     * FbBotApp constructor.
     * @param string $token
     */
    public function __construct($token, $version = 2.7)
    {
        $this->token = $token;
        $this->apiUrl .= "v{$version}/";
    }

    /**
     * Send Message
     *
     * @param Message|array $message
     * @return array
     */
    public function send($message)
    {
        if (!is_array($message))
            $message = $message->getData();
        return $this->call('me/messages', $message);
    }

    /**
     * Get User Profile Info
     *
     * @param int    $id
     * @param string $fields
     * @return UserProfile
     */
    public function userProfile($id, $fields = 'first_name,last_name,profile_pic,locale,timezone,gender')
    {
        return new UserProfile($this->call($id, [
            'fields' => $fields
        ], self::TYPE_GET));
    }

    /**
     * Set Persistent Menu
     *
     * @see https://developers.facebook.com/docs/messenger-platform/thread-settings/persistent-menu
     * @param MessageButton[] $buttons
     * @return array
     */
    public function setPersistentMenu($buttons)
    {
        $elements = [];

        foreach ($buttons as $btn) {
            $elements[] = $btn->getData();
        }

        return $this->call('me/thread_settings', [
            'setting_type' => 'call_to_actions',
            'thread_state' => 'existing_thread',
            'call_to_actions' => $elements
        ], self::TYPE_POST);
    }

    /**
     * Remove Persistent Menu
     *
     * @see https://developers.facebook.com/docs/messenger-platform/thread-settings/persistent-menu
     * @return array
     */
    public function deletePersistentMenu()
    {
        return $this->call('me/thread_settings', [
            'setting_type' => 'call_to_actions',
            'thread_state' => 'existing_thread'
        ], self::TYPE_DELETE);
    }

    /**
     * Request to API
     *
     * @param string $url
     * @param array  $data
     * @param string $type Type of request (GET|POST|DELETE)
     * @return array
     */
    protected function call($url, $data, $type = self::TYPE_POST)
    {
        $query = [];
        $query['access_token'] = $this->token;

        $headers = [
            'Content-Type: application/json',
        ];

        $url .= "?" . http_build_query($query);

        if ($type == self::TYPE_GET) {
            $url .= '&' . http_build_query($data);
        }

        $process = curl_init($this->apiUrl.$url);
        curl_setopt($process, CURLOPT_HEADER, false);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);

        if ($type == self::TYPE_POST || $type == self::TYPE_DELETE) {
            curl_setopt($process, CURLOPT_POST, 1);
            if (isset($data["filedata"])) {
                curl_setopt($process, CURLOPT_POSTFIELDS, $data);
                $headers = ["Content-Type: multipart/form-data"];
            } else {
                curl_setopt($process, CURLOPT_POSTFIELDS, http_build_query($data));
                $headers = ["Content-Type: application/json"];
            }
        }

        curl_setopt($process, CURLOPT_HTTPHEADER, $headers);

        if ($type == self::TYPE_DELETE) {
            curl_setopt($process, CURLOPT_CUSTOMREQUEST, "DELETE");
        }

        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($process);
        curl_close($process);

        return json_decode($return, true);
    }
}
