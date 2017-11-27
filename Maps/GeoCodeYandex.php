<?php
namespace maps;

class GeoCodeYandex
{
    //Переменные и параметры по умолчанию
    public $geocode = "";
    public $format = "json";
    public $results = 1;

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @return string
     */
    public function getGeocode()
    {
        return $this->geocode;
    }

    /**
     * @return int
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @param string $geocore
     */
    public function setGeocode($city,$street)
    {
        $this->geocode = $city.' '.$street;
    }

    /**
     * @param int $results
     */
    public function setResults($results)
    {
        $this->results = $results;
    }

    public function sendInformation(){
        $params = array(
            'geocode'=>$this->getGeocode(),
            'format'=>$this->getFormat(),
            'results'=>$this->getResults()
        );

        $response = json_encode(file_get_contents('http://geocode-maps.yandex.ru/1.x/?' . http_build_query($params, '', '&')));

    if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0)
    {
        $Coordinate = $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos;

        $duo = explode(" ",$Coordinate);
        $new_coor = $duo[1].",".$duo[0];
        return $new_coor;
    }

        return false;
    }

}