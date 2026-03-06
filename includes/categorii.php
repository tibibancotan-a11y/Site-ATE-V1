<?php
$grupe_utilaje = [
    "tractoare" => ["Tractoare"],
    "lucrari_sol" => ["Plug", "Scarificator", "Subsoiler", "Grapa cu discuri", "Combinator", "Cultivator", "Tavalug", "Freza de pamant", "Grapa/Freza rotativa"],
    "semanatori" => ["Semanatoare paioase", "Semanatoare prasitoare", "Dispozitive dozare seminte", "Masina de plantat cartofi", "Masina de plantat rasaduri"],
    "intretinere_culturi" => ["Masini de erbicidat(MET)", "Distribuitoare de ingrasaminte(MA)", "Pulverizatoare", "Atomizoare", "Sisteme GPS", "Cisterne / Vidanje", "Cultivatoare cu fertilizare"],
    "recoltare" => ["Combine", "Header grau", "Header porumb", "Header floarea-soarelui", "Carucior transport"],
    "fan_furaje" => ["Cositori cu tamburi", "Cositori cu discuri", "Tocatoare masa verde", "Masini de adunat fan", "Greble", "Prese baloti rotunzi", "Prese baloti paralelipipedici", "Masini in foliat baloti"],
    "transport_manipulare" => ["Remorci basculante", "Remorci tandem", "Remorci cu obloane", "Remorci transbordoare", "Incarcatoare frontale", "Telehandlere", "Furci pentru baloti", "Cupe", "Gheare", "Clesti"],
    "intretinere_terenuri" => ["Pluguri de zapada", "Freze de zapada", "Raspanditoare antiderapant", "Dispozitive intretinere drum", "Masini cosit margini"],
    "automatizare_control" => ["Sisteme de ghidare GPS", "Terminale ISOBUS", "Antene si senzori", "Unitati ECU", "Sisteme telemetrie"]
];

$categorii_utilaje = [];
$grupe_utilaje_sluguri = [];
$grupe_utilaje_struct = [];

foreach ($grupe_utilaje as $grupa => $categorii) {
    foreach ($categorii as $categorie) {
        $slug = strtolower(str_replace([' ', '/'], '_', $categorie));
        $categorii_utilaje[$slug] = $categorie;
        $grupe_utilaje_sluguri[$grupa][] = $slug;
        $grupe_utilaje_struct[$grupa][] = [
            'slug' => $slug,
            'nume' => $categorie
        ];
    }
}

$grupe_utilaje_nume = [
    "tractoare" => "Tractoare",
    "lucrari_sol" => "Lucrări Sol",
    "semanatori" => "Semănători",
    "intretinere_culturi" => "Întreținere Culturi",
    "recoltare" => "Recoltare",
    "fan_furaje" => "Fân și Furaje",
    "transport_manipulare" => "Transport și Manipulare",
    "intretinere_terenuri" => "Întreținere Terenuri / Drumuri",
    "automatizare_control" => "Automatizare și Control"
];
?>


