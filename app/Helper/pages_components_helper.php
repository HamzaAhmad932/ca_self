<?php
use App\User;
use App\System\FrontEndComponents\Components;
use Illuminate\Support\Facades\Log;

require_once (__DIR__ . '/../System/FrontEndComponents/ComponentKeys.php');

if (!function_exists('load_component')) {

    /**
     * This Function construct's HTML for vue component, which is shown on required page or place
     * @param string $componentToLoad
     * @param User|int $user_or_bookingInfoId
     * @param array $parameters
     * @return string
     */
    function load_component(string $componentToLoad, $user_or_bookingInfoId, array $parameters = []) {
        if($user_or_bookingInfoId instanceof User)
            return getPageComponent(SIDE_CLIENT, $componentToLoad, $user_or_bookingInfoId, $parameters, -1);
        else
            return getPageComponent(SIDE_GUEST, $componentToLoad, null, $parameters, $user_or_bookingInfoId);
    }

}

if (!function_exists('client_page_component')) {

    /**
     * This Function construct's HTML for vue component, which is shown on required page or place
     * @param string $componentToLoad
     * @param User $user
     * @param array $parameters
     * @return string
     */
    function client_page_component(string $componentToLoad, User $user, array $parameters = []) {
        return getPageComponent(SIDE_CLIENT, $componentToLoad, $user, $parameters, -1);
    }

}

if (!function_exists('guest_page_component')) {

    /**
     * This Function construct's HTML for vue component, which is shown on required page or place
     * @param string $componentToLoad
     * @param int $bookingInfoId
     * @param array $parameters
     * @return string
     */
    function guest_page_component(string $componentToLoad, int $bookingInfoId, array $parameters = []) {
        return getPageComponent(SIDE_GUEST, $componentToLoad, null, $parameters, $bookingInfoId);

    }

}

if (!function_exists('getPageComponent')) {

    /**
     * This Function construct's HTML for vue component, which is shown on required page or place
     * @param string $side
     * @param string $componentToLoad
     * @param User $user
     * @param array $parameters
     * @param int $bookingInfoId
     * @return string
     */
    function getPageComponent(string $side, string $componentToLoad, User $user = null, array $parameters = [], int $bookingInfoId = -1) {

        try {

            if($bookingInfoId != -1 && $user == null) {
                $bookingInfo = \App\BookingInfo::where('id', $bookingInfoId)->with('user_account.user')->first();
                $user = $bookingInfo->user_account->user;
            }

            $pc = new Components();
            $comp = $pc->getPageComponentClientSide($side, $componentToLoad, $user->user_account->pms);

            if(key_exists('name', $comp)) {
                $component = "<{$comp['name']} ";

                foreach($parameters as $key => $value)
                    $component .= "$key=\"$value\" ";

                $component .= ">";

                $component .= "</{$comp['name']}>";
                echo $component;

            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), [
                'File' => __FILE__,
                'Function' => __FUNCTION__,
                'Data' => [
                    'Side' => $side,
                    'Page' => $componentToLoad,
                    "User-Id" => empty($user) ? "guest-side-page-load" : $user->id,
                    "BookingInfoId" => $bookingInfoId
                ],
                'Trace' => $e->getTraceAsString()
            ]);
        }
        return '';
    }

}