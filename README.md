test-dev
========

Un stagiaire a créé le code contenu dans le fichier src/Controller/Home.php.

Celui-ci permet de récupérer des URLs via un flux RSS ou un appel à l’API NewsApi.
Celles-ci sont filtrées (si elles contiennent une image) et dé-doublonnées.
Enfin, il faut récupérer une image sur chacune de ces pages.

Le lead dev n'est pas très satisfait du résultat, il va falloir améliorer le code.

Pratique :

1. Revoir complètement la conception du code (découper le code afin de pouvoir ajouter de nouveaux flux simplement).

Questions théoriques :

1. Que mettriez-vous en place afin d'améliorer les temps de réponse du script ?
2. Comment aborderiez-vous le fait de rendre scalable le script (plusieurs milliers de sources et images) ?

---

En parallèle de la refonte du code, j'ai également déplacé la clé d'API dans un fichier `.env` pour des raisons de sécurité. Il faudra donc créer un fichier `.env.local` à la racine du projet et y ajouter la clé d'API NewsApi sous la forme `NEWS_API_KEY` avant de lancer le script.

Voici mes réponses aux questions théoriques :
1. Pour améliorer les temps de réponses du script, on peut mettre en place :
- un système de cache pour les requêtes HTTP. Ainsi, si une requête a déjà été effectuée, on ne la refait pas et on utilise les données en cache. Si les données sont mises à jour régulièrement, on peut aussi mettre en place un système de cache avec expiration pour forcer le rafraîchissement des données après un certain temps.
- un traitement parallèle des requêtes pour accélérer le traitement.
- un processus asynchrone pour traiter les requêtes de manière non bloquante.

2. Pour rendre le script scalable, on peut mettre en place :
- un système de configuration pour ajouter facilement de nouvelles sources de données (flux RSS, API, etc.) sans avoir à modifier le code. On peut par exemple utiliser un fichier de configuration JSON ou YAML qui contient la liste des sources de données avec leurs paramètres.
- une décomposition en microservices pour traiter les différentes étapes du processus de récupération et de traitement des données de manière indépendante et parallèle. Chaque microservice peut être déployé et mis à l'échelle de manière autonome.