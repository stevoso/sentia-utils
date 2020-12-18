<?php
namespace Sentia\Utils;

class CsvUtil {
    /**
     * pole hodnot, ktore predstavuju hlavicku (prvy riadok) CSV dat
     * @var array
     */
    private $headerRow = [];
    /**
     * pole riadkov. Kazdy riadok je tiez polom konkretnych hodnot
     * @var array
     */
    private $dataRows = [];

    /**
     * Nacita obsah suboru do pamate... TATO metoda nie je vhodna pri velmi velkych suboroch!!!
     * @param $file
     * @param bool $isFirstLineHeader
     * @return $this
     */
    public function loadCsvFromFile($file, $isFirstLineHeader = false, $delimiter = ','){
        $row = 0;
        if (($handle = fopen($file, "r")) !== false) {
            while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                $row++;
                if($isFirstLineHeader && $row == 1){
                    $this->headerRow = $data;
                    continue;
                }
                $this->dataRows[] = $data;
            }
            fclose($handle);
        }
        return $this;
    }

    /**
     * vracia hlavicku (prvy riadok)
     * @return array
     */
    public function getHeaderRow(){
        return $this->headerRow;
    }

    /**
     * vracia komplet csv data vo forme datovych riadkov
     * @return array
     */
    public function getRows(){
        return $this->dataRows;
    }

    /**
     * vracia jeden riadok
     * @param $key
     * @return array|null
     */
    public function getRow($key){
        return isset($this->dataRows[$key]) ? $this->dataRows[$key] : null;
    }

    /**
     * prida riadok na koniec pola riadkov
     * @param array $data
     * @param null $index
     */
    public function setRow(array $data, $index = null){
        if(null === $index){
            $this->dataRows[] = $data;
        }else{
            $this->dataRows[$index] = $data;
        }
    }

    /**
     * riadok z pola skonvertuje ako CSV ... neosetruje sa pripad, ked hodnota obsahuje delimiter.
     * @param $key
     * @param string $delimiter
     * @return string
     */
    public function getRowAsSimpleCsv($key, $delimiter = ';'){
        $csv = '';
        if(isset($this->dataRows[$key]) && is_array($this->dataRows[$key])){
            $tmpDelimiter = '';
            foreach ($this->dataRows[$key] as $value) {
                $csv .= $tmpDelimiter.$value;
                $tmpDelimiter = $delimiter;
            }
        }
        return $csv;
    }

    /**
     * z pola vygeneruje string (csv) nasledne ho vracia...
     * @param array $array
     * @param string $delimiter
     * @return string
     */
    public function arrayToCsv(array $array, $delimiter=';'){
        return implode($delimiter, $array);
    }
}
