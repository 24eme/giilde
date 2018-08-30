<?php

function elision($motPrec,$chaine)
{
    $elisionsLetters = array('a','e','i','o','u','h', 'A', 'E', 'I', 'O', 'U', 'H');
    $c = in_array($chaine[0], $elisionsLetters);

    $mp = in_array($motPrec[strlen($motPrec)-1], $elisionsLetters);
    if($c && $mp){
        $motPrec[strlen($motPrec)-1] = "'";
        return $motPrec.$chaine;
    }
    return $motPrec.' '.$chaine;
}
