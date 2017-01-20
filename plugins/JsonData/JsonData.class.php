<?php

class JsonData
{

    protected
        $a_json_data;

    /**
     * Konstruktor k vytvoření instance třídy JsonData pro pro přípravu
     * a následné odeslání dat ve formátu JSON (JavaScript Object Notation)
     */
    public
    function __construct()
    {
        $this->a_json_data = array(
            "json" => array(
                "data" => null,
                "error" => array(),
                "status" => null,
                "status_message" => null,
                "time" => 0),
            "started" => microtime(true)
        );
    }

    /**
     * Metoda k vložení dat do objektu
     * @param $input_data               Data, jež mají být odeslána ve formátu
     *                                  JSON (JavaScript Object Notation).
     */
    public
    function setData($input_data)
    {
        $this->a_json_data["json"]["data"] = $input_data;
    }

    /**
     * Metoda k přidání chybové zprávy do objektu
     * @param $error_message            chybová zpráva
     * @param $error_number             dohodnuté číslo chyby
     *                                  (nepovinný parametr)
     * @return integer                  aktuální celkový počet nastavených
     *                                  chybových zpráv v objektu
     */
    public
    function addError($error_message, $error_number = 0)
    {
        return array_push($this->a_json_data["json"]["error"], array("id" => $error_number, "message" => $error_message));
    }

    /**
     * Metoda k přidání chybové zprávy podle ID chybové zprávy vyjádřeným
     * definovanou konstantou. Neexistuje-li proměnná $_SESSION nebo
     * $_SESSION["language_id"] nebo konstanta, text vložené chyby je
     * "General Error". V případě, že neexistuje pouze záznam překladu chyby
     * v tabulce `lang_global` v konkrétním jazyce, je text chyby
     * "Undefined Error".
     * @param $error_number             Dohodnuté číslo chyby vyjádřené
     *                                  definovanou konstantou. Protože jde
     *                                  o nepovinný parametr, jeho nezadání
     *                                  způsobí vložení chybové zprávy s textem
     *                                  "General Error".
     * @return integer                  Aktuální celkový počet nastavených
     *                                  chybových zpráv v objektu.
     */
    public
    function addErrorById($error_number = 0)
    {
        global $CONFIG;
        $defined_constants = get_defined_constants(true);
        $error_name = array_search($error_number, $defined_constants["user"]);
        $output_number = 0;
        $output_message = "General Error";
        if (isset($_SESSION) && isset($_SESSION["language_id"]) && $error_name) {
            $output_number = $error_number;
            $output_message = "Undefined Error";
            $lng = $_SESSION["language_id"];
            $t = core_set_query("SELECT `text` FROM `lang_global` WHERE `index` = '$error_name' AND `l` = '$lng'");
            if (is_array($t) && $t)
                $output_message = $t[0]["text"];
        }

        if (isset($CONFIG['development']) && $CONFIG['development']) {

            if (isset($_POST["ajax"])) {
                $output_message .= " ({$_POST["ajax"]})";
            }
        }

        return array_push($this->a_json_data["json"]["error"], array("id" => $output_number, "message" => $output_message));
    }

    /**
     * Metoda k odeslání obsahu (dat a chybových hlášení) ve formátu JSON
     * (JavaScript Object Notation) včetně odeslání odpovídající hlavičky.
     * Před ani po volání této metody by neměl být odeslán klientu žádný obsah.
     * V opačném případě nemusí komunikace proběhnout očekávaným způsobem.
     * @param     $status_message       Zpráva o průběhu (většinou s kladným
     *                                  výsledkem). Jse o nepovinný parametr.
     */
    public
    function sendJson($status_message = "")
    {
        $this->a_json_data["json"]["time"] = number_format(microtime(true) - $this->a_json_data["started"], 6, ".", "");
        $this->a_json_data["json"]["status"] = count($this->a_json_data["json"]["error"]) == 0;
        $this->a_json_data["json"]["status_message"] = $status_message;
        header("Content-Type: application/json; charset=utf-8");
        try {
            $json_encoded = false;
            $json_encoded = json_encode($this->a_json_data["json"]);
            if ($json_encoded !== false) {
                echo $json_encoded;
            } else {
                $this->a_json_data["json"]["data"] = array();
                array_push($this->a_json_data["json"]["error"], array("id" => -1, "message" => "Chyba json encode - pole data obsahuje chybu, kvuli ktere nelze prekodovat do json formatu. JSON last error: " . json_last_error()));
                echo json_encode($this->a_json_data["json"]);
            }
        } catch (Exception $e) {
            $this->a_json_data["json"]["data"] = array();
            array_push($this->a_json_data["json"]["error"], array("id" => -1, "message" => "Chyba json encode - pole data obsahuje chybu, kvuli ktere nelze prekodovat do json formatu. JSON last error: " . json_last_error() . " Kod vyjimky: $e"));
            echo json_encode($this->a_json_data["json"]);
        }

    }

    /**
     * Metoda k jednoduchému textovému zobrazení obsahu objektu
     */
    public
    function __toString()
    {
        ob_start();
        print_r($this->a_json_data);
        $var = ob_get_contents();
        ob_end_clean();
        return $var;
    }

    /**
     * Metoda pro zjištění aktuálního stavu podle toho zda objekt má nastavené
     * nějaké chyby nebo ne
     * @return boolean                  Pokud nebylo nastaveno žádné chybové
     *                                  hlášení, je vrácená hodnota true,
     *                                  v opačném případě false.
     */
    public
    function getStatus()
    {
        return count($this->a_json_data["json"]["error"]) == 0;
    }

}
