<?php
session_start(); // Démarrer la session

/*
    taskkill : C'est le verbe. "Tue la tâche". C'est l'outil Windows pour fermer un programme de force.

    /F (Force) : C'est le mode "brutal". On ne demande pas gentiment au programme de se fermer (il pourrait refuser 
    s'il est occupé avec l'Arduino), on lui coupe le courant immédiatement.

    /FI (Filter) : C'est pour dire "Ne tue pas tout le monde, cherche seulement ceux qui correspondent à mon critère".

    "IMAGENAME eq python.exe" : C'est le critère de recherche.

    IMAGENAME = le nom du programme.

    eq = equal (égal à).

    python.exe = la cible.

    En résumé : "Cherche tous les programmes qui s'appellent python.exe".

    /T (Tree) : C'est pour tuer "l'arbre" généalogique. Comme ton script a peut-être lancé d'autres petits sous-processus, 
    le /T s'assure de tout nettoyer pour ne rien laisser traîner en mémoire.
*/
exec("taskkill /F /FI \"IMAGENAME eq python.exe\"");
exec("taskkill /F /FI \"IMAGENAME eq streamlit.exe\"");

// Détruire toutes les variables de session
session_unset(); 

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
header("Location: ../login/FormulaireConnexion.php");
exit();

?>
