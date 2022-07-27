<?php
/*
 * *************************************************************************
 *  * Copyright (C) Cyril BOCHET - All Rights Reserved
 *  * @project     YousignApiClient
 *  * @file        YousignApiClient.php
 *  * @author      Cyril BOCHET
 *  * @site        https://www.linkedin.com/in/cyril-bochet
 *  * @date        26/07/2022 10:15
 *
 */

namespace YousignApiClient;


use Exception;
use JsonException;

class YousignApiClient
{
    private string $apikey;
    private string $apiBaseUrl;
    private string $apiBaseUrlWslash;
    private string $fileId;
    private string $idAdvancedProcedure;
    private string $member;
    private string $fileobject;

    /**
     * @param $apikey
     * @param $env
     */
    public function __construct($apikey, $env)
    {
        $this->setApikey($apikey);
        if ($env === 'prod') {
            $this->apiBaseUrl = 'https://api.yousign.com/';
            $this->apiBaseUrlWslash = 'https://api.yousign.com';
        } else {
            $this->apiBaseUrl = 'https://staging-api.yousign.com/';
            $this->apiBaseUrlWslash = 'https://staging-api.yousign.com';
        }
    }

    /**
     * @param $apikey
     */
    public function setApikey($apikey): void
    {
        $this->apikey = $apikey;
    }

    /**
     * @return string
     */
    public function getApikey(): string
    {
        return $this->apikey;
    }

    /**
     * @param $member
     */
    public function setMember($member)
    {
        $this->member = $member;
    }

    /**
     * @return string
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @return string
     */
    public function getfileId(): string
    {
        return $this->fileId;
    }

    /**
     * @param $fileId
     */
    public function setfileId($fileId)
    {
        $this->fileId = $fileId;
    }

    /**
     * @param string $fileId
     * @param bool $binary
     * @return bool|Exception|string
     */
    public function downloadFile(string $fileId, bool $binary = true)
    {
        $curl = curl_init();
        if ($binary) {
            // download the file in binary
            $url = $this->apiBaseUrlWslash . $fileId . "/download?alt=media";
        } else {
            // base64
            $url = $this->apiBaseUrlWslash . $fileId . "/download";
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return new Exception("cURL error  : " . $error);
        }

        return $response;
    }

    /**
     * @param $post
     * @param $action
     * @param $method
     * @return mixed|string
     */
    public function apiRequest($post, $action, $method)
    {

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiBaseUrl . $action,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->getApikey(),
                "Content-Type: application/json"
            ],
        ]);

        if ($method === 'POST') {
            $post = $this->json_encode($post);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }

        $result = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return new Exception("cURL error  : " . $error);
        }
        return $this->json_decode($result);

    }

    /**
     * @return mixed|string
     */
    public function getUsers()
    {
        return $this->apiRequest(array(), 'users', 'GET');
    }

    /**
     * @param $filePath
     * @return Exception|YousignApiClient
     */
    public function newProcedure($filePath)
    {
        $curl = curl_init();

        $data = file_get_contents($filePath);
        $b64Doc = base64_encode($data);

        $names = explode('/', $filePath);
        $filename = $names[count($names) - 1];

        $post = array(
            'name' => $filename,
            'content' => $b64Doc
        );
        $post = $this->json_encode($post);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiBaseUrl . "files",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);
        if ($error) {
            return new Exception("cURL error  : " . $error);
        }
        $response = $this->json_decode($response);

        $this->fileId = $response['id'];
        return $this;

    }

    /**
     * @param $members
     * @param $procedureName
     * @param $description
     * @return bool|string
     */
    public function addMembersToProcedure($members, $procedureName, $description)
    {
        $post = array(
            'name' => $procedureName,
            'description' => $description,
            'members' => $members
        );

        $post = $this->json_encode($post);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiBaseUrl . "procedures",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);
        if ($error) {
            return new Exception("cURL error  : " . $error);
        }

        return $response;
    }

    /**
     * @param $parameters
     * @param array|null $emails
     * @param array $webhooks
     * @return bool|string
     */
    public function advancedProcedureCreate($parameters, array $emails = [], array $webhooks = [])
    {
        $config = array();

        if (!empty($emails)) {
            foreach ($emails as $trigger => $email) {

                $config['email'][$trigger][] = array(
                    "subject" => $email["subject"],
                    "message" => $email["message"],
                    "to" => $email["to"]
                );
            }
        }

        if (!empty($webhooks)) {
            foreach ($webhooks as $trigger => $webhook) {
                $config['webhook'][$trigger][] = array(
                    "url" => $webhook["url"],
                    "method" => $webhook["method"],
                    "headers" => $webhook["headers"],
                );
            }
        }

        if (!empty($config)) {
            $parameters['config'] = $config;
        }

        $curl = curl_init();

        $params = $this->json_encode($parameters);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiBaseUrl . "procedures",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return new Exception("cURL error  : " . $error);
        }

        $response = $this->json_decode($response);
        $this->idAdvancedProcedure = $response['id'];
        return $response;
    }

    /**
     * $type = 'signable' or 'attachment'
     * @param string $filePath
     * @param string $fileName
     * @param string $type
     * @return bool|string
     */
    public function advancedProcedureAddFile(string $filePath, string $fileName, string $type = 'signable')
    {
        $data = file_get_contents($filePath);
        $b64Doc = base64_encode($data);

        $parameters = array(
            'name' => $fileName,
            'content' => $b64Doc,
            'procedure' => $this->idAdvancedProcedure,
            'type' => $type
        );


        $curl = curl_init();
        $params = $this->json_encode($parameters);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiBaseUrl . "files",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return new Exception("cURL error  : " . $error);
        }

        $response = $this->json_decode($response);
        $this->fileId = $response['id'];
        return $response;
    }

    /**
     * $type = 'signer' or 'validator'
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @param string $phone
     * @param string $type
     * @return bool|string
     */
    public function advancedProcedureAddMember(string $firstname, string $lastname, string $email, string $phone, string $type = 'signer')
    {

        $member = array(
            "firstname" => $firstname,
            "lastname" => $lastname,
            "email" => $email,
            "phone" => $phone,
            "type" => $type,
            "procedure" => $this->idAdvancedProcedure
        );

        $curl = curl_init();

        $param = $this->json_encode($member);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiBaseUrl . "members",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $param,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return new Exception("cURL error  : " . $error);
        }

        $response = $this->json_decode($response);
        $this->member = $response['id'];
        return $response;
    }

    /**
     * @param string $position
     * @param int $page
     * @param string $mention
     * @param string $mention2
     * @param string $reason
     * @return bool|string
     */
    public function advancedProcedureFileObject(string $position, int $page, string $mention = '', string $mention2 = '', string $reason = '')
    {
        $parameter = array(
            "file" => $this->fileId,
            "member" => $this->member,
            "position" => $position,
            "page" => $page,
            "mention" => $mention,
            "mention2" => $mention2,
            "reason" => $reason
        );

        $param = $this->json_encode($parameter);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiBaseUrl . "file_objects",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $param,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return new Exception("cURL error  : " . $error);
        }

        $response = $this->json_decode($response);
        $this->fileobject = $response['id'];
        return $response;

    }

    /**
     * @return bool|Exception|string
     */
    public function advancedProcedureStart()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiBaseUrlWslash . $this->idAdvancedProcedure,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => "{\n   \"start\": true\n}",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return new Exception("cURL error  : " . $error);
        }

        return $response;

    }

    /**
     * @param array $members
     * @param string $procedureName
     * @param string $procedureDescription
     * @param string $mailSubject
     * @param string $mailMessage
     * @param array $mailTo
     * @return bool|string
     */
    public function addMemberAndTriggerMailAlert(array $members = [], string $procedureName = '', string $procedureDescription = '', string $mailSubject, string $mailMessage, array $mailTo = array("@creator", "@members"))
    {
        $curl = curl_init();

        $config = array();

        $config["email"] =
            array(
                "member.started" => array(
                    array(
                        "subject" => $mailSubject,
                        "message" => $mailMessage,
                        "to" => array("@member")
                    )
                ),
                "procedure.started" => array(
                    array(
                        "subject" => $mailSubject,
                        "message" => $mailMessage,
                        "to" => $mailTo
                    )
                )
            );

        $body = array(
            "name" => $procedureName,
            "description" => $procedureDescription,
            "members" => $members,
            "config" => $config

        );

        $param = $this->json_encode($body);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiBaseUrl . "procedures",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $param,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return new Exception("cURL error  : " . $error);
        }

        return $response;
    }

    /**
     * @param $json
     * @return Exception|mixed
     */
    private function json_decode($json)
    {
        try {
            $json = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return new Exception("JsonException error  : " . $exception->getMessage());
        }

        return $json;
    }

    /**
     * @param $json
     * @return Exception|false|string
     */
    private function json_encode($json)
    {
        try {
            $json = json_encode($json, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return new Exception("JsonException error  : " . $exception->getMessage());
        }

        return $json;
    }
}
