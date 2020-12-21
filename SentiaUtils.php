<?php
namespace Sentia\Utils;

use Sentia\Utils\logger\Logger;
use Sentia\Utils\payme\Payme;

class SentiaUtils
{
    public ArrayUtil $array;
    public BigFileUtil $bigFile;
    public BitUtil $bit;
    public CryptUtil $crypt;
    public CsvUtil $csv;
    public DateTimeUtil $dateTime;
    public FileUtil $file;
    public FileUpload $fileUpload;
    public HolidaysUtil $holiday;
    public ImageUtil $image;
    public JsonUtil $json;
    public JwtUtil $jwt;
    public LangUtil $lang;
    public Logger $logger;
    public Payme $payme;
    public PdoUtil $pdo;
    public PersonUtil $person;
    public StopWatch $stopWatch;
    public StringUtil $string;
    public Validator $validator;
    public ValidatorExternal $validatorExternal;
    public WebUtil $web;
    public VehicleUtil $vehicle;
    public ZipUtil $zip;

    public function __construct(ArrayUtil $arrayUtil, BigFileUtil $bigFileUtil, BitUtil $bitUtil, CryptUtil $cryptUtil, CsvUtil $csvUtil,
                                DateTimeUtil $dateTimeUtil, FileUtil $fileUtil, FileUpload $fileUpload, HolidaysUtil $holidaysUtil, ImageUtil $image,
                                JwtUtil $jwtUtil, LangUtil $langUtil, Logger $logger, Payme $payme, PdoUtil $pdoUtil, PersonUtil $personUtil,
                                JsonUtil $jsonUtil, StopWatch $stopWatch, StringUtil $stringUtil, Validator $validtor,
                                ValidatorExternal $validatorExternal, VehicleUtil $vehicleUtil, WebUtil $webUtil, ZipUtil $zipUtil){
        $this->array = $arrayUtil;
        $this->bigFile = $bigFileUtil;
        $this->bit = $bitUtil;
        $this->crypt = $cryptUtil;
        $this->csv = $csvUtil;
        $this->dateTime = $dateTimeUtil;
        $this->file = $fileUtil;
        $this->fileUpload = $fileUpload;
        $this->holiday = $holidaysUtil;
        $this->image = $image;
        $this->json = $jsonUtil;
        $this->jwt = $jwtUtil;
        $this->lang = $langUtil;
        $this->logger = $logger;
        $this->payme = $payme;
        $this->pdo = $pdoUtil;
        $this->person = $personUtil;
        $this->stopWatch = $stopWatch;
        $this->string = $stringUtil;
        $this->validator = $validtor;
        $this->validatorExternal = $validatorExternal;
        $this->vehicle = $vehicleUtil;
        $this->web = $webUtil;
        $this->zip = $zipUtil;
    }
}
