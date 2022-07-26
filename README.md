## CyrilBochet/YousignApiClient

> Client API pour <a target="_blank" href="https://yousign.com/fr-fr"> Yousign</a> · solution de signature électronique française. 
>
>API client for <a target="_blank" href="https://yousign.com/fr-fr"> Yousign</a> · French eSignature solution.

### Sommaire

- [Procédure simple / Simple procedure](#simple-procedure)
- [Procédure avancée / Advanced procedure](#advanced-procedure)
- [Procédure avec alerte mail de Yousign / Procedure with Yousign alert mail](#advanced-procedure-mail)
- [Procédure avancée avec Webhooks / Advanced procedure with Webhooks](#advanced-procedure-webhooks)
- [Téléchargement d'un fichier / Downloading a file](#download-file)
- [Récupérer les utilisateurs / Get users](#users)
- [Liens utiles / Useful links](#useful-links)

<div id='simple-procedure'/></div>

### Procédure simple / Simple procedure

 ```PHP
use YousignApiClient\YousignApiClient;

// Votre clé API
// Your API key
$apikey = 'API_KEY';
$env = 'test';

$client = new YousignApiClient($apikey, $env);


// Nouvelle procédure
// New procedure
$client->newProcedure('test.pdf');

$members = array(
    array(
        'firstname' => 'Cyril',
        'lastname' => 'Bochet',
        'email' => 'cyril@mail.com',
        'phone' => '0102030405',
        'fileObjects' => array(
            array(
            'file' => $client->getIdfile(),
                'page' => 1,
                'position' => "202,205,389,284",
                'mention' => "Lu et approuvé",
                "mention2" => ""
            )
        )
        // Autre membre, etc.
        // Other member, etc.
    )
);

$client->addMembersToProcedure($members, 'Procédure test', 'Signature test.');
```
<div id='advanced-procedure'/></div>

### Procédure avancée / Advanced procedure

```PHP
use YousignApiClient\YousignApiClient;

// Votre clé API
// Your API key
$apikey = 'API_KEY';
$env = 'test';

$client = new YousignApiClient($apikey, $env);


// Paramètres de la procédure
// Procedure parameters
$parameters = array(
    'name' => "Ma procédure avancée",
    'description' => "Création d'une procédure avancée.",
    'start' => false
);

// Création de la procédure
// Procedure creation
$client->advancedProcedureCreate($parameters);

$filePath = 'file.pdf';
$fileName = 'file.pdf';


// On ajoute le fichier à signer
// We add the file to sign
$client->advancedProcedureAddFile($filePath, $fileName);

// On définit le·s différent·s emplacement·s de signature
// We define the different signature locations
$emplacementsSignature = array(
  [
    'position' => '64,71,245,142',
    'page' => 2,
    'mention' => 'Certifié exact et sincère.',
    'mention2' => '',
    'reason' => ''
  ],
  [
    'position' => '87,297,270,369',
    'page' => 12,
    'mention' => 'Je certifie avoir bien reçu le document.',
    'mention2' => '',
    'reason' => ''
  ]
);

// On ajoute le·s membre·s à la procédure
// We add the member to the procedure
$member = $client->advancedProcedureAddMember($prenom, $nom, $mail, $tel, $type);

// On ajoute les emplacements de signature à la procédure
// We add the signature locations to the procedure
foreach ($emplacementsSignature as $emplacement) {
   $client->advancedProcedureFileObject($emplacement["position"], $emplacement["page"], $emplacement["mention"], $emplacement["mention2"], $emplacement["reason"]);
}

// On lance la procédure
// We start the procedure
$client->advancedProcedureStart();
```
<div id='advanced-procedure-mail'/></div>

### Procédure avec alerte mail de Yousign / Procedure with Yousign alert mail

```PHP
use YousignApiClient\YousignApiClient;

// Votre clé API
// Your API key
$apikey = 'API_KEY';
$env = 'test';

$client = new YousignApiClient($apikey, $env);

// Nouvelle procédure
// New procedure
$client->newProcedure('test.pdf');

$members = array(
    array(
        'firstname' => 'Cyril',
        'lastname' => 'Bochet',
        'email' => 'cyril@mail.com',
        'phone' => '0102030405',
        'fileObjects' => array(
            array(
            'file' => $client->getIdfile(),
                'page' => 1,
                'position' => "202,205,389,284",
                'mention' => "Lu et approuvé",
                "mention2" => ""
            )
        )
        // Autre membre, etc.
        // Other member, etc.
    )
);

// On prépare le mail
// We prepare the mail
$mailSubject = "Vous êtes invité à signer électroniquement un document !";
$mailMessage = "Bonjour <tag data-tag-type=\"string\" data-tag-name=\"recipient.firstname\"></tag> <tag data-tag-type=\"string\" data-tag-name=\"recipient.lastname\"></tag>, <br><br> Vous avez été invité à signer un document, veuillez cliquer sur le bouton suivant pour le lire : <tag data-tag-type=\"button\" data-tag-name=\"url\" data-tag-title=\"Accéder aux document\">Accéder aux document</tag>";
$mailTo = array("@creator", "@members");

// On ajoute le·s membre·s à la procédure et on déclenche le mail
// We add the member to the procedure and we trigger the mail
$client->addMemberAndTriggerMailAlert($members, $procedureName, $procedureDescription , $mailSubject, $mailMessage, $mailTo);
```
<div id='advanced-procedure-webhooks'/></div>

### Procédure avancée avec Webhooks / Advanced procedure with Webhooks

Un <a target="_blank" href="https://www.mailjet.com/fr/blog/bonnes-pratiques-emailing/webhook/#:~:text=En%20fait%2C%20un%20webhook%20est,que%20l'%C3%A9v%C3%A9nement%20se%20produit."> webhook</a> est un rappel HTTP (HTTP callback en anglais) : une requête POST qui se produit lorsque quelque chose se passe, une notification d'événement via HTTP POST. <br>
A <a target="_blank" href="https://zapier.com/blog/what-are-webhooks/"> webhook</a> is an HTTP callback: a POST request that occurs when something happens, an event notification via HTTP POST.
> Exemple : une personne signe votre document.
> 
> Example : someone signs your document.

Il vous faudra mettre en place une URL qui va traiter la requête envoyée par Yousign. <br>
You will need to set up an URL that will process the request sent by Yousign.
> Exemple : https://exemple.fr/webhook-yousign

Ensuite vous traiter la requête selon vos besoins. (mail, enregistrement du statut de la procedure, etc.) <br>
Then you process the request as needed. (email, saving the procedure status, etc.)

```PHP
<?php

use YousignApiClient\YousignApiClient;

// Votre clé API
// Your API key
$apikey = 'API_KEY';
$env = 'test';

$client = new YousignApiClient($apikey, $env);

// Paramètres de la procédure
// Procedure parameters
$parameters = array(
    'name' => "Ma procédure avancée",
    'description' => "Création d'une procédure avancée.",
    'start' => false
);

// Création de la procédure
// Procedure creation
$webhook = true;
$webhookMethod = 'POST';
$webhookUrl = 'https://example.com/webhook-yousign';
$webhookHeader = 'test';

$client->advancedProcedureCreate($parameters, $webhook, $webhookMethod, $webhookUrl, $webhookHeader);

$filePath = 'file.pdf';
$fileName = 'file.pdf';


//On ajoute le fichier à signer 
// We add the file to sign
$client->advancedProcedureAddFile($filePath, $fileName);

//On définit le·s différent·s emplacement·s de signature
// We define the different signature locations
$emplacementsSignature = array(
  [
    'position' => '64,71,245,142',
    'page' => 2,
    'mention' => 'Certifié exact et sincère.',
    'mention2' => '',
    'reason' => ''
  ],
  [
    'position' => '87,297,270,369',
    'page' => 12,
    'mention' => 'Je certifie avoir bien reçu le document.',
    'mention2' => '',
    'reason' => ''
  ]
);

//On ajoute le·s membre·s à la procédure
// We add the member to the procedure
$member = $client->advancedProcedureAddMember($prenom, $nom, $mail, $tel, $type);

// On ajoute les emplacements de signature à la procédure
// We add the signature locations to the procedure
foreach ($emplacementsSignature as $emplacement) {
   $client->advancedProcedureFileObject($emplacement["position"], $emplacement["page"], $emplacement["mention"], $emplacement["mention2"], $emplacement["reason"]);
}

// On lance la procédure
// We start the procedure
$client->advancedProcedureStart();
```

<div id='download-file'/></div>

### Téléchargement d'un fichier / Downloading a file

```PHP
// Pour l'exemple je télécharge le fichier directement après avoir lancé la procédure, mais en utilisant les webhooks vous pouvez le télécharger à n'importe quel moment.
// In this example I download the file directly after starting the procedure, but using webhooks you can download it anytime you want.

// le fichier que l'on souhaite télécharger
// the file we want to download
$file = $client->advancedProcedureAddFile($filePath, $fileName);
$client->advancedProcedureStart();

// On récupère le fichier en base64 ou en binaire
// We can get the file in base64 or binary
$binary=false;
$client->downloadFile($file['id'], $binary);

```

<div id='users'/></div>

### Récupérer les utilisateurs / Get users

```PHP
$client->getUsers();
```

<div id='useful-links'/></div>

### Liens utiles / Useful links

> Pour connaitre les coordonnées d'un emplacement de signature : https://placeit.yousign.fr
>
> To get the coordinates of a signature location: https://placeit.yousign.fr

> Documentation complète de l'API Yousign : https://dev.yousign.com
> 
> Complete Yousign API documentation here: https://dev.yousign.com
