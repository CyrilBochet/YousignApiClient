## CyrilBochet/YousignApiClient

### README translation
-   [English](README.en.md)

> Client API pour <a target="_blank" href="https://yousign.com/fr-fr"> Yousign</a> · solution de signature électronique française. 

### Sommaire

- [Procédure simple](#simple-procedure)
- [Procédure avancée](#advanced-procedure)
- [Téléchargement d'un fichier](#download-file)
- [Récupérer les utilisateurs](#users)
- [Gestion des tags (message du mail)](#tags-management)
- [Liens utiles](#useful-links)

<div id='simple-procedure'/></div>

### Procédure simple

 ```PHP
use YousignApiClient\YousignApiClient;

// Votre clé API
$apikey = 'API_KEY';
$env = 'test';

$client = new YousignApiClient($apikey, $env);


// Nouvelle procédure
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
    )
);

$client->addMembersToProcedure($members, 'Procédure test', 'Signature test.');
```
<div id='advanced-procedure'/></div>

### Procédure avancée

En créant une procédure avancée vous pouvez utiliser des <a target="_blank" href="https://fr.wikipedia.org/wiki/Webhook"> webhooks</a>. <br>
> Exemple : Yousign peut vous envoyer une notifcation webhook lorsqu'une personne signe votre document.

Vous pouvez mettre en place une URL qui va traiter la notification envoyée par Yousign. <br>
Ensuite vous traiter la requête selon vos besoins. (mail, enregistrement du statut de la procédure, etc.) <br>
Une procédure avancée vous permet également de créer des <b>notifications mails</b>. <br>

>Exemple : vous pouvez créer une notification lorsqu'une personne refuse de signer votre document. (déclencheur : "procedure.refused")<br>

```PHP
use YousignApiClient\YousignApiClient;

// Votre clé API
$apikey = 'API_KEY';
$env = 'test';

$client = new YousignApiClient($apikey, $env);


// Paramètres de la procédure
$parameters = array(
    'name' => "Ma procédure avancée",
    'description' => "Création d'une procédure avancée.",
    'start' => false
);

// Création de la procédure
// Liste des déclencheurs (mails et webhooks) : "procedure.started", "procedure.finished", "procedure.refused", "member.started", "member.finished"
    
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
$client->advancedProcedureAddFile($filePath, $fileName);

// On définit le·s différent·s emplacement·s de signature
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
$member = $client->advancedProcedureAddMember($prenom, $nom, $mail, $tel, $type);

// On ajoute les emplacements de signature à la procédure
foreach ($emplacementsSignature as $emplacement) {
   $client->advancedProcedureFileObject($emplacement["position"], $emplacement["page"], $emplacement["mention"], $emplacement["mention2"], $emplacement["reason"]);
}

// On lance la procédure
$client->advancedProcedureStart();
```

<div id='download-file'/></div>

### Téléchargement d'un fichier

```PHP
// Pour l'exemple je télécharge le fichier directement après avoir lancé la procédure, mais en utilisant les webhooks vous pouvez le télécharger à n'importe quel moment.
// le fichier que l'on souhaite télécharger
$file = $client->advancedProcedureAddFile($filePath, $fileName);
$client->advancedProcedureStart();

// On récupère le fichier en base64 ou en binaire
$binary=false;
$client->downloadFile($file['id'], $binary);

```

<div id='users'/></div>

### Récupérer les utilisateurs

```PHP
$client->getUsers();
```
<div id='tags-management'/></div>

### Gestion des tags (message du mail)
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

### Liens utiles

> Pour connaitre les coordonnées d'un emplacement de signature : https://placeit.yousign.fr

> Documentation complète de l'API Yousign : https://dev.yousign.com

