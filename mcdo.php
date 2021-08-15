<?php


function get_All_Data() {
    $curl = curl_init('https://mcdonaldsfrance.webgeoservices.com/api/stores/search/?authToken=AIzaSyAiX19QNdei5Ja7TA2ahlg3Wb-p6eAUNOc&center=6.128885%3A45.899235&db=prod&dist=50000&limit=20&nb=20&orderDir=desc');

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  // Permet de désactiver le certificat (Verif SSL)
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Permet de ne pas afficher les données recup à l'écran et les stocker dans $data à la place

    $data_all_mcdo = curl_exec($curl);   // Execute l'url et renvoie true --> GOOD       OR      false --> BAD donc problèmes avec URL

    // Si problèmes avec l'URL 
    if($data_all_mcdo === false) {
        var_dump(curl_error($curl));    // Afficher l'erreur
    } else {
        $data_all_mcdo = json_decode($data_all_mcdo, true);   // On met les données JSON dans un tableau associatif
    }


    curl_close($curl);     // On ferme l'URL

    return $data_all_mcdo;
}





function clean_All_Data($data_all_mcdo) {

    $i = 0 ;

    $infoClean_All_Mcdo = [];

    foreach($data_all_mcdo['poiList'] as $unMcDo) {
        $infoClean_All_Mcdo[$i]["Distance"] = round( $data_all_mcdo['poiList'][$i]['dist'] / 1000, 1) ;
        $infoClean_All_Mcdo[$i]["Ville"] = $data_all_mcdo['poiList'][$i]['poi']['location']['city'];
        $infoClean_All_Mcdo[$i]["Adresse"] = $data_all_mcdo['poiList'][$i]['poi']['location']['streetLabel'];
        $infoClean_All_Mcdo[$i]["id"] = $data_all_mcdo['poiList'][$i]['poi']['id'];
        $infoClean_All_Mcdo[$i]["Location"]["latitude"] = $data_all_mcdo['poiList'][$i]['poi']['location']['coords']['lat'];
        $infoClean_All_Mcdo[$i]["Location"]["longitude"] = $data_all_mcdo['poiList'][$i]['poi']['location']['coords']['lon'];
    
        $i++;
    }
    
    return $infoClean_All_Mcdo;

}


function clean_All_Data_Products($infoClean_All_Mcdo) {

    $index_in_JSON_STEP1 = [];
    $index_in_JSON_STEP2 = [];
    $index_in_JSON_STEP3 = [];


    $i = 0;
    

    $infoClean_All_Mcdo_Products = [];

    foreach($infoClean_All_Mcdo as $unMcDo) {
        /*---------------------------------------- On charge les données ----------------------------------------*/
        $curl = curl_init('https://ws.mcdonalds.fr/api/catalog/gomcdo?eatType=EAT_IN&responseGroups=RG.CATEGORY.PICTURES&responseGroups=RG.CATEGORY.POPINS&responseGroups=RG.PRODUCT.CAPPING&responseGroups=RG.PRODUCT.CHOICE_FINISHED_DETAILS&responseGroups=RG.PRODUCT.INGREDIENTS&responseGroups=RG.PRODUCT.PICTURES&responseGroups=RG.PRODUCT.POPINS&responseGroups=RG.PRODUCT.RESTAURANT_STATUS&responseGroups=RG.PROMOTION.POPINS&restaurantRef='.$infoClean_All_Mcdo[$i]['id']);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  // Permet de désactiver le certificat (Verif SSL)
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Permet de ne pas afficher les données recup à l'écran et les stocker dans $data à la place

        $data_all_mcdo_products_dict = curl_exec($curl);   // Execute l'url et renvoie true --> GOOD       OR      false --> BAD donc problèmes avec URL

        // Si problèmes avec l'URL 
        if($data_all_mcdo_products_dict === false) {
            var_dump(curl_error($curl));    // Afficher l'erreur
        } else {
            $data_all_mcdo_products_dict = json_decode($data_all_mcdo_products_dict, true);   // On met les données JSON dans un tableau associatif
        }


        curl_close($curl);     // On ferme l'URL
        /*---------------------------------------- FIN On charge les données ----------------------------------------*/
  
        

        /*---------------------------------------- On trouve les différents index pour trouver le chemin pour arriver au McFlurry ----------------------------------------*/

            //---------- STEP 1 On récupère chaque tableau "nos dessert" pour chaque Mcdo 

        $a_ref_15 = false;

                // On cherche le 1er index  children[index1] 
        foreach($data_all_mcdo_products_dict['children'] as $laKey => $leTab) {                                       
            
            if ($leTab['ref'] == 15) {
                $index_in_JSON_STEP1[] = $laKey;
                $a_ref_15 = true;
            } 
        }





            //---------- STEP 2 On récupère chaque tableau "Desserts Glaces" pour chaque Mcdo 

        $a_desserts_glaces = false;
        

                // On cherche le 2eme index  children[index1]children[index2] 
        foreach($data_all_mcdo_products_dict['children'][$index_in_JSON_STEP1[$i]]['children'] as $laKey => $leTab) {
            if ($leTab['ref'] == "DESSERTS_GLACES") {

                $index_in_JSON_STEP2[] = $laKey;
                $a_desserts_glaces = true;
            } 
            else if ( $a_desserts_glaces == false ) {                                  // Si on passe dans l'autre tab qui n'est pas 'dessert glacé" bah ça ajoute 15 comme index, (on rentre dans le else)
                $index_in_JSON_STEP2[] = null;
                
            }
        }
        


        


            //---------- STEP 3 On récupère chaque tableau "McFlurry" pour chaque Mcdo 

            // On cherche le 3eme index  children[index1]children[index2]products[index3]


        if ( !is_null($index_in_JSON_STEP2[$i]) )  {
            $refMcFlurry = [99000000, 99000095];            // Il peut y avoir 1 ou 2 McFlurry par Mcdo qui ont soit la ref 99000000, soit 99000095
            $permanent_or_not = [];                         // On crée un tableau pour stocker le tab McFlurry qui a comme attribut 'permanent' (Dans un Mcdo où il y a deux McFlurry)
    
            
            foreach($data_all_mcdo_products_dict['children'][$index_in_JSON_STEP1[$i]]['children'][$index_in_JSON_STEP2[$i]]['products'] as $laKey =>$leTab) {              //Pour chaque index => Tab dans $data_all_mcdo_products_dict['children'][$index_in_JSON_STEP1[$i]]['children'][$index_in_JSON_STEP2[$i]]['products']
                if ( in_array($leTab['ref'], $refMcFlurry) ) {                                                                                                                  //Si le ou les Tab McFlurry ont les ref du Tab $refMcFlurry
                    $permanent_or_not[] = $laKey;                                                                                                                                   //On ajoute la key du tableau (ex key : 0; 1; 3) ; la key qu'on récup c'est celle qu'on utilise pour index3
                }
            }
    
            if(sizeof($permanent_or_not) > 1) {                                                                                                                                                     //Si nb de Tab de McFlurry > 1
                foreach($permanent_or_not as $laKeyPerm ) {                                                                                                                                             //Pour chaque key qu'on a recup dans le Tab $permanent_or_not
                    if ( array_key_exists('permanent', $data_all_mcdo_products_dict['children'][$index_in_JSON_STEP1[$i]]['children'][$index_in_JSON_STEP2[$i]]['products'][$laKeyPerm]) ) {                //Si le tab McFlurry a comme attribut 'permanent'
                        $index_in_JSON_STEP3[] = $laKeyPerm;                                                                                                                                                    //On l'ajoute a notre tableau $index_in_JSON_STEP3[]
                        break;
                    } 
                }
            }
            else {                                                                                                                                                                                  //Si on a qu'un Tab de McFlurry
                $index_in_JSON_STEP3[] = $permanent_or_not[0];                                                                                                                                          //On l'ajoute a notre tableau $index_in_JSON_STEP3[]
            }

        } else {
            $index_in_JSON_STEP3[] = null;
        }
        


        if ( !is_null($index_in_JSON_STEP2[$i]) ) {
    
            $infoClean_All_Mcdo_Products[$i]['Produit'] = $data_all_mcdo_products_dict['children'][$index_in_JSON_STEP1[$i]]['children'][$index_in_JSON_STEP2[$i]]['products'][$index_in_JSON_STEP3[$i]]['designation'] ;
            $infoClean_All_Mcdo_Products[$i]['Disponible'] = $data_all_mcdo_products_dict['children'][$index_in_JSON_STEP1[$i]]['children'][$index_in_JSON_STEP2[$i]]['products'][$index_in_JSON_STEP3[$i]]['available'] ;

        } else {
            $infoClean_All_Mcdo_Products[$i]['Produit'] = "McFlurry inexistant" ;
            $infoClean_All_Mcdo_Products[$i]['Disponible'] = false ;
        }


        $i++;

    }

    return $infoClean_All_Mcdo_Products;
}




function save_to_json($infoClean_All_Mcdo, $infoClean_All_Mcdo_Products) {

    $i = 0;

    $allCleanInfo = [];

    foreach($infoClean_All_Mcdo as $unMcDo) {
        $allCleanInfo[$i]['Distance'] = $infoClean_All_Mcdo[$i]['Distance'];
        $allCleanInfo[$i]['Ville'] = $infoClean_All_Mcdo[$i]['Ville'];
        $allCleanInfo[$i]['Adresse'] = $infoClean_All_Mcdo[$i]['Adresse'];
        $allCleanInfo[$i]['id'] = $infoClean_All_Mcdo[$i]['id'];
        $allCleanInfo[$i]['Location']['latitude'] = $infoClean_All_Mcdo[$i]["Location"]["latitude"];
        $allCleanInfo[$i]['Location']['longitude'] = $infoClean_All_Mcdo[$i]["Location"]["longitude"];
    
        $allCleanInfo[$i]['Produit'] = $infoClean_All_Mcdo_Products[$i]['Produit'];
        $allCleanInfo[$i]['Disponible'] = $infoClean_All_Mcdo_Products[$i]['Disponible'];
    
        $i++;
    }


    $file = fopen("mcdoUnvailable.js", "w") or die("Unable to open file!");
    $infoClean_All_Mcdo_JSON = json_encode($allCleanInfo, JSON_PRETTY_PRINT);
    fwrite($file, "export const lesMcDo = ".$infoClean_All_Mcdo_JSON);
    fclose($file);

}






















// -------------------------------------------- Execution des fonctions

$data_all_mcdo = get_All_Data();

$infoClean_All_Mcdo = clean_All_Data($data_all_mcdo);

$infoClean_All_Mcdo_Products = clean_All_Data_Products($infoClean_All_Mcdo);

save_to_json($infoClean_All_Mcdo, $infoClean_All_Mcdo_Products);



