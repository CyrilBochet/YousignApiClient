<?php
/*
 * *************************************************************************
 *  * Copyright (C) Cyril BOCHET - All Rights Reserved
 *  * @project     YousignApiClient
 *  * @file        yousign.php
 *  * @author      Cyril BOCHET
 *  * @site        https://www.linkedin.com/in/cyril-bochet
 *  * @date        25/07/2022 21:50
 *
 */

require_once __DIR__ . '/../vendor/autoload.php';

use YousignApiClient\YousignApiClient;

$apikey = getenv('API_KEY');

function procedureSimple($apikey)
{
    $client = new YousignApiClient($apikey, 'dev');

    $client->newProcedure('test.pdf');

    $members = array(
        array(
            'firstname' => 'Cyril',
            'lastname' => 'Bochet',
            'email' => getenv('EMAIL'),
            'phone' => getenv('TEL'),
            'fileObjects' => array(
                array(
                    'file' => $client->getfileId(),
                    'page' => 1,
                    'position' => "202,205,389,284",
                    'mention' => "Lu et approuvé",
                    "mention2" => ""

                )
            )


        )
    );

    $client->addMembersToProcedure($members, 'Procédure test', 'Signature test.');
}

function procedureAvanceeComplete($apikey)
{
    $client = new YousignApiClient($apikey, 'test');


    // Paramètres de la procédure
    $parameters = array(
        'name' => "Ma procédure avancée",
        'description' => "Création d'une procédure avancée.",
        'start' => false
    );

    // Création de la procédure

    $emails = [
        "member.started" => array(
            "subject" => "Hey! You are invited to sign!",
            "message" => "Hello <tag data-tag-type=\"string\" data-tag-name=\"recipient.firstname\"></tag> <tag data-tag-type=\"string\" data-tag-name=\"recipient.lastname\"></tag>, <br><br> You have ben invited to sign a document, please click on the following button to read it: <tag data-tag-type=\"button\" data-tag-name=\"url\" data-tag-title=\"Access to documents\">Access to documents</tag>",
            "to" => ["@member"]),
        "procedure.started" => array(
            "subject" => "John, created a procedure your API have.",
            "message" => "The content of this email is totally awesome.",
            "to" => ["@creator", "@members"])
    ];
    $webhooks = [
        "member.started" => array(
            "url" => "https://testyousign.requestcatcher.com",
            "method" => "POST",
            "headers" => array(
                "X-Custom-Header" => 'test'
            )),
    ];

    $client->newAdvancedProcedure($parameters, $emails, $webhooks);

    $filePath = 'test.pdf';
    $fileName = 'test.pdf';


    //On ajoute le fichier à signer
    $client->advancedProcedureAddFile($filePath, $fileName);

    //On définit le·s différent·s emplacement·s de signature
    $emplacementsSignature = array(
        [
            'position' => "202,205,389,284",
            'page' => 1,
            'mention' => 'Certifié exact et sincère.',
            'mention2' => '',
            'reason' => ''
        ],
        [
            'position' => '386,205,548,284',
            'page' => 1,
            'mention' => 'Je certifie avoir bien reçu le document.',
            'mention2' => '',
            'reason' => ''
        ]
    );

    //On ajoute le·s membre·s à la procédure
    $client->advancedProcedureAddMember('Cyril', 'Bochet', getenv('EMAIL'), getenv('TEL'));

    // On ajoute les emplacements de signature à la procédure
    foreach ($emplacementsSignature as $emplacement) {
        $client->advancedProcedureFileObject($emplacement["position"], $emplacement["page"], $emplacement["mention"], $emplacement["mention2"], $emplacement["reason"]);
    }

    // On lance la procédure
    $client->advancedProcedureStart();
}

function procedureAvanceeWebhookSeulement($apikey)
{
    $client = new YousignApiClient($apikey, 'test');


    // Paramètres de la procédure
    $parameters = array(
        'name' => "Ma procédure avancée",
        'description' => "Création d'une procédure avancée.",
        'start' => false
    );

    // Création des webhooks
    $webhooks = [
        "member.started" => array(
            "url" => "https://testyousign.requestcatcher.com",
            "method" => "POST",
            "headers" => array(
                "X-Custom-Header" => 'test'
            )),
        //etc.
    ];

    // Création de la procédure
    $client->newAdvancedProcedure($parameters, array(), $webhooks);

    $filePath = 'test.pdf';
    $fileName = 'test.pdf';


    //On ajoute le fichier à signer
    $client->advancedProcedureAddFile($filePath, $fileName);

    //On définit le·s différent·s emplacement·s de signature
    $emplacementsSignature = array(
        [
            'position' => "202,205,389,284",
            'page' => 1,
            'mention' => 'Certifié exact et sincère.',
            'mention2' => '',
            'reason' => ''
        ],
        [
            'position' => '386,205,548,284',
            'page' => 1,
            'mention' => 'Je certifie avoir bien reçu le document.',
            'mention2' => '',
            'reason' => ''
        ]
    );

    //On ajoute le·s membre·s à la procédure
    $client->advancedProcedureAddMember('Cyril', 'Bochet', getenv('EMAIL'), getenv('TEL'));

    // On ajoute les emplacements de signature à la procédure
    foreach ($emplacementsSignature as $emplacement) {
        $client->advancedProcedureFileObject($emplacement["position"], $emplacement["page"], $emplacement["mention"], $emplacement["mention2"], $emplacement["reason"]);
    }

    // On lance la procédure
    $client->advancedProcedureStart();
}

function utilisateurs($apikey)
{

    $client = new YousignApiClient($apikey, 'test');
    $client->getUsers();
}

function telechargerFichier($apikey)
{
    $client = new YousignApiClient($apikey, 'test');

    // Paramètres de la procédure
    $parameters = array(
        'name' => "Ma procédure avancée",
        'description' => "Création d'une procédure avancée.",
        'start' => false
    );

    // Création de la procédure
    $client->advancedProcedureCreate($parameters);

    $filePath = 'test.pdf';
    $fileName = 'test.pdf';

    //On ajoute le fichier à signer
    $file = $client->advancedProcedureAddFile($filePath, $fileName);

    //On définit le·s différent·s emplacement·s de signature
    $emplacementsSignature = array(
        [
            'position' => "202,205,389,284",
            'page' => 1,
            'mention' => 'Certifié exact et sincère.',
            'mention2' => '',
            'reason' => ''
        ],
        [
            'position' => '386,205,548,284',
            'page' => 1,
            'mention' => 'Je certifie avoir bien reçu le document.',
            'mention2' => '',
            'reason' => ''
        ]
    );

    //On ajoute le·s membre·s à la procédure
    $client->advancedProcedureAddMember('Cyril', 'Bochet', getenv('EMAIL'), getenv('TEL'));

    // On ajoute les emplacements de signature à la procédure
    foreach ($emplacementsSignature as $emplacement) {
        $client->advancedProcedureFileObject($emplacement["position"], $emplacement["page"], $emplacement["mention"], $emplacement["mention2"], $emplacement["reason"]);
    }

    // On lance la procédure
    $client->advancedProcedureStart();

    // On récupère le fichier en base64
    $client->downloadFile($file['id'], false);
}