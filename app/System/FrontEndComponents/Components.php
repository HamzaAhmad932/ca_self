<?php

namespace App\System\FrontEndComponents;

use App\UserPms;
use Illuminate\Support\Facades\Log;

/**
 * Class Components
 * @package App\System\Components
 * This class is responsible for providing details about front end component according to selected pms of user account.
 */
class Components {

    function __construct() {

    }

    /**
     * @param string $side SIDE_CLIENT or SIDE_GUEST
     * @param string $component
     * @param UserPms $userPms
     * @return array
     */
    function getPageComponentClientSide(string $side, string $component, UserPms $userPms) {

        try {

            $filesToSearchIn = [];

            $pmsForm = $userPms->pms_form;

            if($pmsForm->pms_parent_id > 0) {
                // Pms Has Parent
                $pmsParent = $pmsForm->parent_pms;
                if(!empty($pmsParent->page_configuration))
                    $filesToSearchIn[] = $pmsParent->page_configuration; // Parent Pms page configuration file
            }

            if(!empty($pmsForm->page_configuration))
                $filesToSearchIn[] = $pmsForm->page_configuration; // Pms page configuration file

            $filesToSearchIn[] = 'general.json'; // General page configuration file

            foreach ($filesToSearchIn as $fileName) {
                $pageArray = $this->getArrayOfPageConfiguration($fileName);
                $pageComponent = $this->getComponent($side, $component, $pageArray);
                if(!empty($pageComponent) && !empty($pageComponent['name']))
                    return $pageComponent;
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'Data' => [
                    'Side' => $side,
                    'Component' => $component,
                    "UserPms" => $userPms->id
                ],
                'Trace' => $e->getTraceAsString()
            ]);
        }

        return [];
    }

    /**
     * @param string $configFileName
     * @return array
     */
    private function getArrayOfPageConfiguration(string $configFileName) {
        $fileContent = file_get_contents(__DIR__ . '/../../Json/pages_and_components/' . $configFileName);
        return json_decode($fileContent, true);
    }

    /**
     * @param string $side
     * @param string $component
     * @param array $pageArray
     * @return array|null
     */
    private function getComponent(string $side, string $component, array $pageArray) {

        if(key_exists($side, $pageArray)) {

            if(key_exists($component, $pageArray[$side]))
                return $pageArray[$side][$component];
            else
                return [];
        }

        return [];
    }

}