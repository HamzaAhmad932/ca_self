<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/4/18
 * Time: 1:16 PM
 */

namespace App\System\PMS\BookingAutomation;

use App\System\PMS\exceptions\PmsExceptions;

use SimpleXMLElement;

class BAParser {

    /**
     * @param string $pmsName
     * @param string $class
     * @param array $content
     * @return array
     * @throws PmsExceptions
     */
    public function parseJsonResponse(string $pmsName, string $class, array $content) {

        if($content === null)
            throw new PmsExceptions('Response Content is null');

        if(!is_array($content))
            throw new PmsExceptions('Invalid Content, Not array type');

        if($class === null)
            throw new PmsExceptions('Class path is null');

        if(!isset($pmsName))
            throw new PmsExceptions('PMS name not provided for parsing');

        if(!is_array($class::$M_KEYS) || count($class::$M_KEYS) == 0)
            throw new PmsExceptions('Parsing keys map is empty');

        if(!key_exists($pmsName, $class::$M_KEYS))
            throw new PmsExceptions($pmsName . ' does not exits in key map');

        if(!key_exists('json', $class::$M_KEYS[$pmsName]))
            throw new PmsExceptions('json key does not exits in key map for ' . $class);

        $output = array();

        for($i = 0; $i < count($content); $i++) {
            $obj = new $class;

            foreach ($class::$M_KEYS[$pmsName]['json'] as $map_key => $classVariable) {
                if (key_exists($map_key, $content[$i]))
                    $obj->$classVariable = $content[$i][$map_key];
            }

            if(key_exists('json_single_elements', $class::$M_KEYS[$pmsName])) {
                foreach ($class::$M_KEYS[$pmsName]['json_single_elements'] as $jseKey => $jseValue) {
                    if(property_exists($class, $jseValue))
                        $obj->$jseValue = $content[$i][$class::$M_KEYS[$pmsName]['json_single_elements']['RESPONSE_KEY']][$jseKey];
                }
            }

            if(key_exists('json_sub', $class::$M_KEYS[$pmsName])) {
                $subObjArray = array();
                foreach ($class::$M_KEYS[$pmsName]['json_sub'] as $subResponseKey => $subArray) {
                    if(key_exists($subResponseKey, $content[$i]))
                        $subObjArray = $this->parseJsonResponse($pmsName, $subArray['type'], $content[$i][$subResponseKey]);
                    $s = (string) $subArray['var'];
                    $obj->$s = $subObjArray;
                }

            }

            if(key_exists('json_sub_with_keys', $class::$M_KEYS[$pmsName])) {

                foreach ($class::$M_KEYS[$pmsName]['json_sub_with_keys'] as $subResponseKey => $subArray) {
                    if(key_exists($subResponseKey, $content[$i])) {

                        // Looping item to get object and ignoring keys
                        $objectArray = [];
                        foreach($content[$i][$subResponseKey] as $keysToIgnore => $object) {
                            $objectArray[] = $object;
                        }

                        if(count($objectArray) > 0) {
                            $subObjArray = $this->parseJsonResponse($pmsName, $subArray['type'], $objectArray);
                            if(!empty($subObjArray)) {
                                $s = (string)$subArray['var'];
                                $obj->$s = $subObjArray;
                            }
                        }

                    }
                }
            }

            $output[] = $obj;
        }

        return $output;
    }

    /**
     * @param string $pmsName
     * @param string $class
     * @param array $content
     * @return array
     * @throws PmsExceptions
     */
    public function parseXmlResponse(string $pmsName, string $class, array $content) {

        if($class === null)
            throw new PmsExceptions('Class path is null');

        if(!isset($pmsName))
            throw new PmsExceptions('PMS name not provided for parsing');

        if(!is_array($class::$M_KEYS) || count($class::$M_KEYS) == 0)
            throw new PmsExceptions('Parsing keys map is empty');

        if(!key_exists($pmsName, $class::$M_KEYS))
            throw new PmsExceptions($pmsName . ' does not exits in key map');

        if(!key_exists('xml', $class::$M_KEYS[$pmsName]))
            throw new PmsExceptions('xml key does not exits in key map for ' . $class);

        $output = array();

        for ($i = 0; $i < count($content); $i++) {
            $obj = new $class;

            // due to nodes which dont have value but only attributes
            if(count($content[$i]->children()) == 0 && count($content[$i]->attributes()) > 0) {
                $this->parse_XML_Attributes($pmsName, $class, $content[$i], $obj);
            }

            /**
             * @var SimpleXMLElement $child
             */
            foreach($content[$i]->children() as $cKey => $cValue) {

                if(key_exists($cKey, $class::$M_KEYS[$pmsName]['xml'])) {
                    $classVar = $class::$M_KEYS[$pmsName]['xml'][$cKey];
                    $obj->$classVar = (string) $cValue;
                }

                $this->parse_XML_Attributes($pmsName, $class, $content[$i], $obj);

                if(key_exists('xml_sub', $class::$M_KEYS[$pmsName])) {
                    foreach ($class::$M_KEYS[$pmsName]['xml_sub'] as $subResponseKey => $subArray) {

                        $subXmlChild = $content[$i]->$subResponseKey->xpath($subArray['child']);

                        if(count($subXmlChild) > 0) {
                            $subObjArray = $this->parseXmlResponse($pmsName, $subArray['type'], $subXmlChild);
                            $s = (string) $subArray['var'];
                            $obj->$s = $subObjArray;

                            // Searching for single elements in child nodes
                            if(key_exists('single_elements', $subArray))
                            if(is_array($subArray['single_elements'])) {
                                for($c = 0; $c < count($subXmlChild); $c++) {
                                    foreach ($subArray['single_elements'] as $seResponseKey => $seVar)
                                        $obj->$seVar = (string)$content[$i]->$subResponseKey->$seResponseKey;
                                }
                            }
                        }
                    }
                }

            }

            $output[] = $obj;
        }

        return $output;
    }

    private function parse_XML_Attributes(string $pmsName, string $class, $content, &$obj) {
        if(key_exists('xmlAttributes', $class::$M_KEYS[$pmsName])) {
            foreach ($content->attributes() as $aKey => $aValue) {
                if(key_exists($aKey, $class::$M_KEYS[$pmsName]['xmlAttributes']))
                    $oVar = $class::$M_KEYS[$pmsName]['xmlAttributes'][$aKey];
                $obj->$oVar = (string) $aValue;
            }
        }
    }

}