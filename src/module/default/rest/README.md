
# REST Module
## Description
Le REST Module permet d'ajouter à FirePHP une interface respectant le standard REST avec quelques fonctionnalités supplémentaires simplifiant son utilisation. 
## Installation
Pour installer le module, il faut ajouter le dossier complet du module dans `/src/module/` du projet. Puis compléter le fichier de route `/app/var/route.json` avec le code suivant :

    {
        "url" : "/rest/*/",
    	"names" : [
    		"__ressource"
    	],
        "controller" : "Default",
        "module" : "Rest",
        "action" : "index"
       },
    {
        "url" : "/rest/*/*/",
    	"names" : [
    		"__ressource",
    		"id"
    	],
        "controller" : "Default",
        "module" : "Rest",
        "action" : "index"
    }
## Utilisation
### Méthode GET
#### Récupération de l'information
Dans REST, si l'on veut récupérer de l'information, il faut utiliser la méthode HTTP `GET`. Par exemple, pour récupérer tous les `user` de la base on fera en local sur sa machine : `http://adresse-du-projet/rest/user/`
Pour un utilisateur en particulier qui porte l'identifiant 1 : `http://adresse-du-projet/rest/user/1/`

#### Informations de tables étrangères
Par défaut, le module charge les informations des tables étrangères, ainsi, si un utilisateur est lié à un autre utilisateur, il sera chargé également avec l'emsemble des champs préfixés par le nom de la ressource comme suit :

    {
        "id" : "1",
        "name" : "Toto",
        "id_user" : 
        {
            "id" : "2",
            "name" : "Titi",
            "id_user" : "3"
        }
    }
Ce comportement peut être désactivé grâce au paramètre GET : `foreign_table` en lui donnant la valeur "0" pour désactiver ou "1", par défaut, pour activer le chargement des informations.
Remarque : Seulement les clés étrangères directements liées à la ou les lignes seront chargées.
#### Filtre et trie 
##### Filtre de condition
Les conditions de recherche sont possible grâce au paramètre GET `filter` :
`http://adresse-du-projet/rest/user/1/?filter[name]=Toto&filter[id]=1`
Cette requête nous retournera l'utilisateur ayant pour identifiant "1" et pour nom "Toto".
Autres filtres possible :
* filtre_sup : Le champ doit être strictement supérieur à la valeur.
* filtre_sup_egal : Le champ doit être supérieur ou égal à la valeur.
* filtre_inf : Le champ doit être strictement inférieur à la valeur.
* filtre_inf_egal : Le champ doit être inférieur ou égal à la valeur.
* filtre_like : Le champ doit contenir la valeur peut importe son emplacement dans la chaîne du champ.
##### Trie 
Le retour de l'API peut être trié grâce au paramètre GET `order` de la façon suivante : `http://adresse-du-projet/rest/user/1/?order[name]=desc`
Tous les utilisateurs seront triés de façon décroisante par leur nom. Le paramètre ne peut prendre que les valeurs `asc` ou `desc` mais peut être chaîné avec plusieurs autres tries.
#### Code HTTP de retour
La méthode PUT ou POST renvera les codes suivant : 
* `200 Ok` : Cas de création.
### Méthode POST & PUT
Dans l'API, les méthodes `PUT` et `POST` sont confondues. On créé une ligne comme on la modifie.
Voici par exemple le code pour créer une ligne utilisateur : 

L'adresse : `http://adresse-du-projet/rest/user/`

Le contenu des paramètres envoyés : 

    {
        "name" : "Tutu"
        "id_user" : 3
    }

Remarque : Si toutes les informations nécessaires pour un utilisateur ne sont pas présentes, la création ou modification ne peut pas avoir lieu.
#### Code HTTP de retour
La méthode PUT ou POST renvera les codes suivant : 
* `201 Created` : Cas de création.
* `204 No Content` : Cas de modification.
* `400 Bad Request` : Ressource inexistante ou paramètre manquant.
### Méthode DELETE
La méthode `DELETE` permet la suppression d'une ligne en appelant l'URI correspondante comme suit : 
`http://adresse-du-projet/rest/user/1/`. Cela supprimera l'utilisateur avec l'identifiant "1". 
## Ajout en plus du standard
* Dans certains serveurs, l'envoi de requête HTTP `PUT`, `DELETE` et `PATCH` est impossible. L'API met à disposition le paramètre `method` qui prend les valeur suivantes selon la méthode à appeler : 
    * GET
    * PUT 
    * POST
    * DELETE
    * PATCH










