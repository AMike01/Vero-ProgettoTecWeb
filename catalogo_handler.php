<?php
require_once "PHP/class.php";

class Catalogo
{
    // funzioni che ritornano l'HTML esatto rispetto a che pulsante è stato premuto

    public static function show_allProducts()
    { // viene mostrato il catalogo con tutti i prodotti

        $result =
        '<form action="catalogo.php" method="GET">
            <div class="pulsanti_catalogo">
                <input type="submit" class="invio" id="category_list" name="lista_categorie" value="Lista delle Categorie" />
                <input type="submit" class="invio" name="new_product" value="Aggiungi nuovo prodotto"/>
            </div>
        </form>';

        $products = Access::getAllProducts();

        if (empty($products))
$result .=
        '<div id="wish-error" role="alert">
            Al momento non sono disponibili prodotti!
        </div>';
        else {
            for ($i = 0; $i < count($products); $i++) { // funzione per la creazione dell'inline

$result .=         
        '<form action="catalogo.php" method="POST">
            <fieldset class="prodotto">
                <legend>
                    <span class="nome">'
                        . ($products[$i]["Prod_Nome"]) . 
                    '</span>
                </legend>
                <div>
                    <input type="hidden" name="product_id" value="' . $products[$i]["id_prodotto"] . '"/>'   // mi salvo l'id_prodotto
                .'</div> 
                <div>
                    <input type="hidden" name="category_id" value="' . $products[$i]["id_categoria"] . '"/>' // e l'id_categoria
                .'</div>'; 

                // informazioni del prodotto
$result .= '
                <div>
                    <span class="nome">
                        Nome prodotto:
                    </span> ' . ($products[$i]["Prod_Nome"]) . 
                '</div>
                <div>
                    <span class="categoria">
                        Categoria:
                    </span> ' 
                    . ($products[$i]["Cat_Nome"]) . 
                '</div>
                <div>
                    <span class="descrizione">
                        Descrizione:
                    </span> ' 
                    . ($products[$i]["Descrizione"]) . 
                '</div>
                <label class="sr-only" for="' . $products[$i]["id_prodotto"] . '-' . $products[$i]["id_categoria"] . '">
                    Modifica prodotto ' . ($products[$i]["Prod_Nome"]) . 
                '</label>
                <div>
                    <input type="submit" class="modifica invio" name="modifica_prod" id="'. $products[$i]["id_prodotto"] . '-' . $products[$i]["id_categoria"] . 
                '" value="Modifica" />
                </div>
            </fieldset>
        </form>';
            }
        }
        return $result;
    }

    public static function show_modifyProduct($product_id)
    {   // viene mostrata la pagina di modifica di un prodotto

        $product = Access::getProduct($product_id);
        $categories = Access::getAllCategories();

        // modifica dati prodotto
    $result = '
        <form action="catalogo.php" method="POST" enctype="multipart/form-data">
            <fieldset class="form_catalogo">
                <legend class="hide">
                    Modifica informazioni di prodotto
                </legend>
                <div>
                    <input type="hidden" name="prod_id" value="' . $product_id . '" />
                </div>
                <div>
                    <label class="lab" for="nome_prod">
                        Nome prodotto:
                    </label>
                </div>
                <div>
                    <input class="inp" type="text" id="nome_prod" name="nome_prod" value="' . Access::reverselang($product[0]["Nome"]) . '"/>
                </div>
                <div>        
                    <label class="lab" for="categories">
                        Categoria prodotto:
                    </label>
                </div>
                <select name="category_id" id="categories">
                    <option value="' . $product[0]["id_categoria"] . '">' .
            Access::deletelang(Access::getCategoryName($product[0]["id_categoria"])) . '</option>'; // mostra come selezionata la categoria del prodotto

        for ($i = 0; $i < count($categories); $i++)  // creazione del menu a tendina delle varie categorie
            if ($categories[$i]["id_categoria"] != $product[0]["id_categoria"])
                $result .=          
                    '<option value="' . $categories[$i]["id_categoria"] . '">' . Access::deletelang($categories[$i]["Nome"]) . '</option>';
        
            $result .=
                '</select>
                <div>
                    <label class="lab" for="desc_prod">Descrizione prodotto:</label>
                </div>
                <div>
                    <textarea class="inp" id="desc_prod" name="desc_prod" rows="10" cols="40"
                        maxlength="500">' . Access::reverselang($product[0]["Descrizione"]) . '</textarea>
                </div>
                <div>
                    <p>In caso di nomi o testi in lingua straniera è necessario scriverli così: [*LINGUA*]*Testo*[*LINGUA*]
                        Ad esempio per un testo in inglese: [EN]<span lang="en">Hello</span>[\EN].</p>
                </div>
                <div>
                    <input type="submit" class="invio" id="annulla_modifica_prod" name="annulla_modifica_prod"
                        value="Annulla modifiche" />
                    <input type="submit" class="invio" id="submit_modifica_prod" name="submit_modifica_prod"
                        value="Conferma modifiche" />
                </div>
            </fieldset>
        </form>

        <form action="catalogo.php" method="POST">
            <fieldset class="form_catalogo">
                <legend class="hide">
                    Elimina prodotto
                </legend>
                <div id="elimina_prod">
                    <input type="submit" class="invio" id="submit_elimina" onclick="confermaEliminazione();"
                        value="Elimina Prodotto" />
                </div>
                <div id="msg_confirm" role="alert"></div>
                    <div hidden id="elimina_utente_big-profilo">
                        <div id="messaggio_conferma" class="messaggio_elimina" role="alert">
                            Sei sicuro di voler eliminare il prodotto?
                        </div>
                            <div>
                                <input type="hidden" class="invio" id="si_elimina" name="si_elimina_prod" value="Si" />
                                <input type="hidden" class="invio" id="no_elimina" name="no_elimina_prod" value="No" />
                            </div>
                            <input type="hidden" name="prod_id_2" value="' . $product_id . '" />
                </div>
            </fieldset>
        </form> ' .
        
        // aggiunta ed eliminazione di immagini, oltre ai testi alternativi
        '
            <div>
                <h3 class="titolo_img_form">Aggiungi o elimina immagini del prodotto</h3>
            </div>

            <form action="catalogo.php" method="POST" enctype="multipart/form-data">
                <fieldset class="form_catalogo">
                    <legend class="hide">
                        Aggiungi e rimuovi immagini, o aggiorna i testi alternativi
                    </legend>';

        /* mostra tutte le immagini del prodotto */
        if ($product[0]["path"] == null)
            $result .=
                    '<div>
                        Non sono presenti immagini per questo prodotto.
                    </div>';
        else
            for ($i = 0; $i < count($product); $i++) {
                $result .= 
                    '<div class="clickImg">
                        <input type="checkbox" id="form-' . $product[$i]["path"] . '" name="check_img[]" value="' . $product[$i]["path"] . '"/>
                        <label class="lab" for="form-' . $product[$i]["path"] . '">
                            Seleziona 
                            <span class="sr-only">"' 
                                . substr($product[$i]["path"], 13) . '"
                            </span>
                        </label>
                        <img  src="' . $product[$i]["path"] . '" alt="' . Access::deletelang($product[$i]["alt_img"]) . '" width="100" height="100"/>
                    </div>
                    <div class="clickImg2">
                        <button class="open-button" aria-label="Ingrandisci" id="' . $product[$i]["path"] . '">
                            <img src="img/lente.png" alt="Ingrandisci immagine." />
                        </button>
                    </div>
                    
                    <input type="hidden" name="path_img[]" value="' . $product[$i]["path"] . '"/>
                    <div>
                        <label class="lab" for="alt-' . $product[$i]["path"] . '">
                            Alt immagine: 
                            <span class="sr-only">"'
                                 . substr($product[$i]["path"], 13) . 
                            '"</span>
                        </label>
                    </div>
                    <div>
                        <textarea class="limited-textarea inp" id="alt-' . $product[$i]["path"] . '" name="alt_img[]" rows="4" cols="30"
                        placeholder="Inserisci alt per immagine">' 
                            . Access::deletelang($product[$i]["alt_img"]) 
                       .'</textarea>   
                        <div class="charCountDiv" id="char-count-alt-' . $product[$i]["path"] . '">
                            Caratteri rimanenti: 75
                        </div>
                    </div>';
            }

        if ($product[0]["path"] != null)
            $result .=
                    '<div>
                        <input type="submit" class="invio" name="salva_alt_img" value="Salva alt immagine/i">
                    </div>';
$result .=
                    '<input type="hidden" name="product_id_img" value="' . $product_id . '" />
                    <input type="hidden" name="category_id_img" value="' . $product[0]["id_categoria"] . '"/>
                    <div>
                        <label for="carica_img" class="lab">
                            Carica una o più immagini per il prodotto (jpg, jpeg o png).
                        </label>
                        <input type="file" id="carica_img" name="img[]" multiple accept=".jpg, .jpeg, .png"/>
                        <input type="submit" class="modifica invio" name="upload_img" value="Carica"/>
                    </div>';
            
                    // eliminazione delle immagini selezionate
        if ($product[0]["path"] != null) {
            $result .= '         
                    <div id="elimina_img">
                        <input type="submit" class="invio" name="submit_elimina_img" id="submit_elimina_img"
                        onclick="confermaEliminazioneImg();" value="Elimina immagini selezionate" />
                    </div>
                    <div hidden id="elimina_utente_big-img-profilo">
                        <div id="messaggio_conferma-img" class="messaggio_elimina" role="alert">
                            Sei sicuro di voler eliminare questa/e immagini?
                        </div>
                        <div>
                            <input type="hidden" class="invio" id="si_elimina_img" name="si_elimina_img" value="Si" />
                            <input type="hidden" class="invio" id="no_elimina_img" name="no_elimina_img" value="No" />
                            <input type="hidden" name="prod_id_2" value="' . $product_id . '" />
                        </div>
                    </div>';
        }
$result .= 
                '</fieldset>
            </form>';

        return $result;
    }

    public static function show_newProduct()
    { // viene mostrata la pagina per la creazione di un nuovo prodotto

        $categories = Access::getAllCategories();

$result =   '<form action="catalogo.php" method="POST">
                <fieldset class="form_catalogo">
                    <legend class="hide">
                        Inserisci informazioni per nuovo prodotto
                    </legend>
                    <div>
                        <label class="lab" for="new_nome_prod">
                            Nome prodotto:
                        </label>
                    </div>
                    <div>
                        <input class="inp" type="text" id="new_nome_prod" name="new_nome_prod" value="" />
                    </div>
                    <div>
                        <label class="lab" for="categories">
                            Categoria prodotto:
                        </label>
                    </div>
                    <select name="new_category_id" id="categories">';

        if ($categories == null)
            return 0;

        for ($i = 0; $i < count($categories); $i++) // creazione del menu a tendina delle categorie
            $result .= 
                        '<option value="' . $categories[$i]["id_categoria"] . '">'
                            . Access::deletelang($categories[$i]["Nome"]) . 
                        '</option>';

    $result .=      '</select>
                    <div>
                        <label class="lab" for="new_desc_prod">
                            Descrizione prodotto:
                        </label>
                    </div>
                    <div>
                        <textarea class="inp" id="new_desc_prod" name="new_desc_prod" rows="10" cols="40" maxlength="500"></textarea>
                    </div>
                    <div>
                        E\' possibile aggiungere immagini al prodotto modificandolo successivamente.
                    </div>
                    <div>
                        In caso di nomi o testi in lingua straniera è necessario scriverli così: [*LINGUA*]*Testo*[*LINGUA*]
                        Ad esempio per un testo in inglese: [EN]<span lang="en">Hello</span>[\EN].
                    </div>
                    <div>
                        <input type="submit" class="invio" id="annulla_new_cat" name="annulla_new_prod"
                        value="Annulla creazione prodotto" />
                        <input type="submit" class="invio" id="submit_new_cat" name="submit_new_prod" value="Conferma creazione prodotto" />
                    </div>
                </fieldset>
            </form>';

        return $result;
    }

    public static function show_allCategories()
    { // viene mostrata la lista di tutte le categorie

        $categories = Access::getAllCategories();

        $result =
            '<form action="catalogo.php" method="GET">
                <div>
                    <input type="submit" class="invio" value="Torna al catalogo prodotti" />
                    <input type="submit" class="invio" id="new_category" name="new_category" value="Aggiungi nuova categoria" />
                </div>
            </form>';

        if (empty($categories))
            $result .= 
                '<div id="wish-error" role="alert">
                    Al momento non sono disponibili categorie!
                </div>';
        else {
            for ($i = 0; $i < count($categories); $i++) {

                $result .=
            '<form action="catalogo.php" class="flex-container" method="POST">
                <fieldset class="prodotto">
                    <legend>
                        <span class="nome">' . 
                            $categories[$i]["Nome"] . 
                        '</span>
                    </legend>
                    <div>
                        <span class="nome">
                            Nome:
                        </span> ' . 
                       $categories[$i]["Nome"] . '
                    </div>
                    <div>
                        <span class="descrizione">
                            Descrizione:
                        </span> ' .
                        $categories[$i]["Descrizione"] . '
                    </div>
                    <label class="sr-only" for="' . $categories[$i]["id_categoria"] . '">
                        Modifica categoria '. $categories[$i]["Nome"] . 
                    '</label>
                    <div>
                        <input type="submit" class="modifica invio" name="modifica_cat" 
                        id="' . $categories[$i]["id_categoria"] . '" value="Modifica" />
                    </div>
                    <div>
                        <input type="hidden" name="category_id" value="' . // mi salvo l'id_categoria
                        $categories[$i]["id_categoria"] . '"/>
                    </div>
                </fieldset>
            </form>'; 
            }
        }
        return $result;
    }
    public static function show_modifyCategory($category_id)
    { // viene mostrata la pagina di modifica di una categoria

        $categories = Access::getCategoryById($category_id);

        $result =
            '<form action="catalogo.php" method="POST">
                <fieldset class="form_catalogo">
                    <legend class="hide">
                        Modifica informazioni di categoria
                    </legend>
                    <div>
                        <input type="hidden" name="cat_id" value="' . $category_id . '" />
                    </div>
                    <div>
                        <label class="lab" for="nome_cat">
                            Nome categoria:
                        </label>
                    </div>
                    <div>
                        <input class="inp" type="text" id="nome_cat" name="nome_cat" value="' . 
                        Access::reverselang($categories[0]["Nome"]) . '"/>
                    </div>
                    <div>
                        <label class="lab" for="desc_cat">
                            Descrizione categoria:
                        </label>
                    </div>
                    <div>
                        <textarea class="inp" id="desc_cat" name="desc_cat" rows="10" cols="40" maxlength="500">' .
                            Access::reverselang($categories[0]["Descrizione"]) . 
                        '</textarea>
                    </div>
                    <div class="testo_regole">
                        In caso di nomi o testi in lingua straniera è necessario scriverli così: [*LINGUA*]*Testo*[*LINGUA*]
                        Ad esempio per un testo in inglese: [EN]<span lang="en">Hello</span>[\EN].
                    </div>
                    <input type="submit" class="invio" name="annulla_modifica_cat" value="Annulla modifiche"/>
                    <input type="submit" class="invio" name="submit_modifica_cat" value="Conferma modifiche"/>
                    <div id="elimina_prod">
                        <input type="submit" class="invio" id="submit_elimina" onclick="confermaEliminazione();"
                        value="Elimina Categoria" />
                    </div>
                </fieldset>
            </form>

            <form action="catalogo.php" method="POST">
                <input type="hidden" name="cat_id_2" value="' . $category_id . '" />
                <div id="msg_confirm" role="alert"></div>
                    <div hidden id="elimina_utente_big-profilo">
                        <div id="messaggio_conferma" class="messaggio_elimina" role="alert">
                            Sei sicuro di voler eliminare la categoria?
                        </div>
                        <div>
                            <input type="hidden" class="invio" id="si_elimina" name="si_elimina_cat" value="Si" />
                            <input type="hidden" class="invio" id="no_elimina" name="no_elimina_cat" value="No" />
                        </div>
                    
                </div>
            </form>';

        return $result;
    }

    public static function show_newCategory()
    { // viene mostrata la pagina per la creazione di un nuova categoria

        return 
        '<form action="catalogo.php" method="POST">
            <fieldset class="form_catalogo">
                <legend class="hide">
                    Inserisci informazioni per nuova categoria
                </legend>
                <div>
                    <label class="lab" for="new_nome_cat">
                        Nome categoria:
                    </label>
                </div>
                <div>
                    <input class="inp" type="text" id="new_nome_cat" name="new_nome_cat" value="" />
                </div>
                <div>
                    <label class="lab" for="new_desc_cat">
                        Descrizione categoria:
                    </label>
                </div>
                <div>
                    <textarea class="inp" id="new_desc_cat" name="new_desc_cat" rows="10" cols="40" maxlength="500"></textarea>
                </div>
                <div class="testo_regole">
                    In caso di nomi o testi in lingua straniera è necessario scriverli così: [*LINGUA*]*Testo*[*LINGUA*]
                    Ad esempio per un testo in inglese: [EN]<span lang="en">Hello</span>[\EN].
                </div>
                <input type="submit" class="invio" id="annulla_new_cat" name="annulla_new_cat"
                value="Annulla creazione categoria" />
                <input type="submit" class="invio" id="submit_new_cat" name="submit_new_cat"
                value="Conferma creazione categoria" />
                
            </fieldset>
        </form>';
    }

    // funzioni di supporto al catalogo

    public static function uploadImg($id_prodotto, $id_categoria)  // Va ad effettuare l'upload delle immagini su server
    {

        if (isset($_FILES['img'])) {

            $countfiles = count($_FILES['img']['name']);
            $maxsize = 1048576; // 512KB (1 byte * 1024 * 1024) 
            $response = 0;

            for ($i = 0; $i < $countfiles; $i++) {

                $filename = $_FILES['img']['name'][$i];
                $filesize = $_FILES['img']['size'][$i];

                if ($filesize > $maxsize)
                    return ["error", "Errore caricamento immagine:", "La dimensione dell'immagine è maggiore di 1MB"];

                // Posizione dell'immagine/i
                $location = "img/products/" . $filename;
                $extension = pathinfo($location, PATHINFO_EXTENSION);
                $extension = strtolower($extension);

                // Estensioni consentite
                $valid_extensions = array("jpg", "jpeg", "png");

                // Upload dell'immagine/i
                if (in_array(strtolower($extension), $valid_extensions)) { // controlla che l'estensione sia giusta
                    if (file_exists($location)) // e che l'immagine non sia già presente
                        return ["error", "Errore caricamento immagine:", "Immagine già presente in questo o in un altro prodotto"];
                    else
                        if (move_uploaded_file($_FILES['img']['tmp_name'][$i], $location)) {  // sposta l'immagine nella cartella 
                            Access::newImg($location, $id_prodotto, $id_categoria);
                            $response = 1;
                        } else
                            $response = 0;
                } else
                    return ["error", "Errore caricamento immagine:", "Tipo dell'immagine errato (usare jpg, jpeg o png)"];
            }

            if ($response)
                return ["success", "Caricamento riuscito:", "Immagine caricata correttamente"];
            else
                return ["error", "Errore caricamento immagine:", "Errore nel caricamento dell'immagine"];

        } else // in caso nessuna immagine sia stata caricata
            return ["error", "Errore caricamento immagine:", "Nessuna immagine è stata selezionata"];
    }


    public static function sendError($class, $sr, $text, $pagina)
    {
        if ($class == "success")
            $pagina = str_replace("catalogo_class", "success-message", $pagina);
        else
            $pagina = str_replace("catalogo_class", "error-message", $pagina);

        $pagina = str_replace("<!--Contenuto_sr-->", $sr, $pagina);
        $pagina = str_replace("<!--Contenuto_errors-->", $text, $pagina);

        return $pagina;
    }

    public static function getBreadCrumb($text, $pagina)
    {
        switch($text) {
            case("Nuovo_Prodotto"):
                $pagina = str_replace(" / Catalogo"," / <a href=\"catalogo.php\">Catalogo</a> / Creazione nuovo prodotto", $pagina);
                $pagina = str_replace("Catalogo prodotti", "Creazione nuovo prodotto", $pagina);
                $pagina = str_replace("<title>Catalogo - Véro</title>", "<title>Nuovo prodotto - Véro</title>", $pagina);
                $pagina = str_replace('<li id="currentLink">Catalogo</li>', '<li><a href="catalogo.php">Catalogo</a></li>', $pagina);

                break;
            case("Modifica_Prodotto"):
                $pagina = str_replace(" / Catalogo"," / <a href=\"catalogo.php\">Catalogo</a> / Modifica prodotto", $pagina);
                $pagina = str_replace("Catalogo prodotti", "Modifica Prodotto", $pagina);
                $pagina = str_replace("<title>Catalogo - Véro</title>", "<title>Modifica prodotto - Véro</title>", $pagina);
                $pagina = str_replace('<li id="currentLink">Catalogo</li>', '<li><a href="catalogo.php">Catalogo</a></li>', $pagina);
                break;
            case("Lista_Categorie"):
                $pagina = str_replace(" / Catalogo"," / <a href=\"catalogo.php\">Catalogo</a> / Categorie", $pagina);
                $pagina = str_replace("Catalogo prodotti", "Lista Categorie", $pagina);
                $pagina = str_replace("<title>Catalogo - Véro</title>", "<title>Lista categorie - Véro</title>", $pagina);
                $pagina = str_replace('<li id="currentLink">Catalogo</li>', '<li><a href="catalogo.php">Catalogo</a></li>', $pagina);
                break;
            case("Nuova_Categoria"):
                $pagina = str_replace(" / Catalogo"," / <a href=\"catalogo.php\">Catalogo</a> / <a href=\"catalogo.php?lista_categorie=Lista+delle+Categorie\">Categorie</a> / Creazione nuova categoria",
                $pagina);
                $pagina = str_replace("Catalogo prodotti", "Creazione nuova categoria", $pagina);
                $pagina = str_replace("<title>Catalogo - Véro</title>", "<title>Nuova categoria - Véro</title>", $pagina);
                $pagina = str_replace('<li id="currentLink">Catalogo</li>', '<li><a href="catalogo.php">Catalogo</a></li>', $pagina);
            case("Modifica_Categoria"):
                $pagina = str_replace(" / Catalogo"," / <a href=\"catalogo.php\">Catalogo</a> / <a href=\"catalogo.php?lista_categorie=Lista+delle+Categorie\">Categorie</a> / Modifica categoria",
                $pagina);
                $pagina = str_replace("Catalogo prodotti", "Modifica Categoria", $pagina);
                $pagina = str_replace("<title>Catalogo - Véro</title>", "<title>Modifica categoria - Véro</title>", $pagina);
                $pagina = str_replace('<li id="currentLink">Catalogo</li>', '<li><a href="catalogo.php">Catalogo</a></li>', $pagina);
                break;
        }
        return $pagina;
    }

}


?>