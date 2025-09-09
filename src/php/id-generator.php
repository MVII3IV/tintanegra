<?php

function generateFunnyOrderId() {

    $adjectives = [
        "perro","gato","elefante","chichihuilote","tigre",
        "mono","conejo","lobo","delfin","zorro",
        "oso","canguro","pinguino","leon","rinoceronte"
    ];

    $verbs = [
        "saltarin","bailador","piruteador","corredor","embrujado",
        "volador-no-indentificado","ratero","rodador","tragon","imitador",
        "estafador","enbalsamador","cosquilludo","deizquiera","ojon"
    ];

    $randomAdjective1 = $adjectives[array_rand($adjectives)];
    $randomVerb = $verbs[array_rand($verbs)];

    $dateTime = date('YmdH'); 

    $id = "{$randomAdjective1}-{$randomVerb}-{$dateTime}";

    return $id;
}

?>
