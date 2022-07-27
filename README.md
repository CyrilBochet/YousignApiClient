## CyrilBochet/YousignApiClient

> Client API pour <a target="_blank" href="https://yousign.com/fr-fr"> Yousign</a> · solution de signature électronique française. 
>
>API client for <a target="_blank" href="https://yousign.com/fr-fr"> Yousign</a> · French eSignature solution.

### Sommaire

- [Procédure simple / Simple procedure](#simple-procedure)
- [Procédure avancée / Advanced procedure](#advanced-procedure)
- [Téléchargement d'un fichier / Downloading a file](#download-file)
- [Récupérer les utilisateurs / Get users](#users)
- [Gestion des tags (message du mail) / Tags management (email message)](#tags-management)
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

En créant une procédure avancée vous pouvez utiliser des <a target="_blank" href="https://fr.wikipedia.org/wiki/Webhook"> webhooks</a>. <br>
By creating an advanced procedure you can use <a target="_blank" href="https://en.wikipedia.org/wiki/Webhook"> webhooks</a>.
> Exemple : Yousign peut vous envoyer une notifcation webhook lorsqu'une personne signe votre document.
>
> Example : Yousign can send a webhook notification when a person signs your document.

Vous pouvez mettre en place une URL qui va traiter la notification envoyée par Yousign. <br>
You can set up an URL that will process the notification sent by Yousign.

Ensuite vous traiter la requête selon vos besoins. (mail, enregistrement du statut de la procédure, etc.) <br>
Then you process the request as needed. (email, saving the procedure status, etc.)

Une procédure avancée vous permet également de créer des <b>notifications mails</b>. <br>
An advanced procedure also allows you to create <b>email notifications</b>.

>Exemple : vous pouvez créer une notification lorsqu'une personne refuse de signer votre document. (déclencheur : "procedure.refused")<br>
> 
>Example : you can create a notification when someone refuses to sign your document. (trigger: "procedure.refused")

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

// Liste des déclencheurs (mails et webhooks) : "procedure.started", "procedure.finished", "procedure.refused", "member.started", "member.finished"
// Trigger list (emails and webhooks): "procedure.started", "procedure.finished", "procedure.refused", "member.started", "member.finished"
    
    $emails = [
        "member.started" => array(
            "subject" => "Hey! You are invited to sign!",
            "message" => "Hello <tag data-tag-type=\"string\" data-tag-name=\"recipient.firstname\"></tag> <tag data-tag-type=\"string\" data-tag-name=\"recipient.lastname\"></tag>, <br><br> You have ben invited to sign a document, please click on the following button to read it: <tag data-tag-type=\"button\" data-tag-name=\"url\" data-tag-title=\"Access to documents\">Access to documents</tag>",
            "to" => ["@member"]),
        "procedure.refused" => array(
            "subject" => "John, created a procedure.",
            "message" => "The content of this email is totally awesome.",
            "to" => ["@creator", "@members"]),
            //etc.
    ];
    
    $webhooks = [
        "member.started" => array(
            "url" => "https://testyousign.requestcatcher.com",
            "method" => "POST",
            "headers" => array(
                "X-Custom-Header" => 'test'
            )),
            //etc.
    ];


$client->newAdvancedProcedure($parameters);

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
<div id='tags-management'/></div>

### Gestion des tags (message du mail) / Tags management (email message)
<table>
   <thead>
      <tr>
         <th>Syntax</th>
         <th>Description</th>
      </tr>
   </thead>
   <tbody>
      <tr>
         <td><code>&lt;tag data-tag-type=\"button\" data-tag-name=\"url\" data-tag-title=\"Access to documents\"&gt;Access to documents&lt;/tag&gt;</code></td>
         <td>Show a stylized link to access to the procedure. If the recipent is a member, it will be redirect to the sign view. <code>url</code> params is required but you can change as your convenience the string "Access to documents"</td>
      </tr>
      <tr>
         <td><code>&lt;tag data-tag-type=\"string\" data-tag-name=\"recipient.firstname\"&gt;&lt;/tag&gt;</code></td>
         <td>It will display the firstname of the recipient.</td>
      </tr>
      <tr>
         <td><code>&lt;tag data-tag-type=\"string\" data-tag-name=\"recipient.lastname\"&gt;&lt;/tag&gt;</code></td>
         <td>It will display the lastname of the recipient.</td>
      </tr>
      <tr>
         <td><code>&lt;tag data-tag-name="procedure.files" data-tag-type="list"&gt;&lt;/tag&gt;</code></td>
         <td>It will list files that have been added to a procedure.</td>
      </tr>
      <tr>
         <td><code>&lt;tag data-tag-name="procedure.members" data-tag-type="list"&gt;&lt;/tag&gt;</code></td>
         <td>It will list members of a procedure.</td>
      </tr>
      <tr>
         <td><code>&lt;tag data-tag-name="procedure.expiresAt" data-tag-type="date" data-tag-date-format="SHORT" data-tag-time-format="NONE" data-tag-locale="fr_FR"&gt;&lt;/tag&gt;</code></td>
         <td>It will display the expiration date formatted like that : <code>04/31/2018</code>. Allowed values for data-tag-date-format and data-tag-time-format: <code>**NONE**</code>: Does not display the element // <code>**FULL**</code>: <code>Tuesday, April 12, 1952 AD</code> or <code>3:30:42pm PST</code> // <code>**LONG**</code>: <code>January 12, 1952</code> or <code>3:30:32pm</code> // <code>**MEDIUM**</code>: <code>Jan 12, 1952</code> // <code>**SHORT**</code> (default value for both parameters): <code>12/13/52</code> ou <code>3:30pm</code></td>
      </tr>
   </tbody>
</table>


<div id='useful-links'/></div>

### Liens utiles / Useful links

> Pour connaitre les coordonnées d'un emplacement de signature : https://placeit.yousign.fr
>
> To get the coordinates of a signature location: https://placeit.yousign.fr

> Documentation complète de l'API Yousign : https://dev.yousign.com
> 
> Complete Yousign API documentation here: https://dev.yousign.com

