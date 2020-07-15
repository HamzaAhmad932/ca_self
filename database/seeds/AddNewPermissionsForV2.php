<?php


use App\RoleAndPermissions;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class AddNewPermissionsForV2 extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $memberUsers = User::where('parent_user_id', '>', 0)->get();

        foreach (RoleAndPermissions::$designV2ClientPermissions as $permission) {

            try{
                Permission::create(['guard_name' => 'client', 'name' => $permission]);
            }catch (Exception $e){
                echo $permission.' Permission name already inserted.';
            }

            $users = User::where('parent_user_id', 0)->get();

            /**
             * @var $user User
             */
            foreach($users as $user) {

                $user->givePermissionTo($permission);

            }

            /**
             * @var $user User
             */
            foreach ($memberUsers as $user) {

                switch ($permission) {

                    case 'bookings':
                        $booking = ['editBooking', 'viewBooking', 'editBookingSource', 'viewBookingSource', 'viewTransaction', 'editTransaction', 'charges', 'authorize', 'capture', 'refund'];
                        try {
                            if($user->hasAnyPermission($booking)) {

                                $user->revokePermissionTo($booking);
                                $user->givePermissionTo($permission);
                                echo $user->name . ", $permission Permission granted\n";
                            }
                        } catch (Exception $e) {
                            echo $user->name . ", $permission Permission not assigned\n";
                        }
                        break;

                    case 'properties':
                        $property = ['editProperty', 'syncProperties', 'viewProperty', 'editBookingSource', 'viewBookingSource', 'viewPaymentGateway', 'editPaymentGateway'];
                        try {
                            if($user->hasAnyPermission($property)) {

                                $user->revokePermissionTo($property);
                                $user->givePermissionTo($permission);
                                echo $user->name . ", $permission Permission granted\n";
                            }
                        } catch (Exception $e) {
                            echo $user->name . ", $permission Permission not assigned\n";
                        }
                        break;

                    case 'guestExperience':
                        $guestExperience = ['viewSetting', 'editSetting'];
                        try {
                            if($user->hasAnyPermission($guestExperience)) {

                                $user->revokePermissionTo($guestExperience);
                                $user->givePermissionTo($permission);
                                echo $user->name . ", $permission Permission granted\n";
                            }
                        } catch (Exception $e) {
                            echo $user->name . ", $permission Permission not assigned\n";
                        }
                        break;

                    case 'preferences':
                        $preference = ['viewSetting', 'editSetting'];
                        try {
                            if($user->hasAnyPermission($preference)) {

                                $user->revokePermissionTo($preference);
                                $user->givePermissionTo($permission);
                                echo $user->name . ", $permission Permission granted\n";
                            }
                        } catch (Exception $e) {
                            echo $user->name . ", $permission Permission not assigned\n";
                        }
                        break;

                    case 'accountSetup':
                        $account = ['editPMS', 'viewPMS'];
                        try {
                            if($user->hasAnyPermission($account)) {

                                $user->revokePermissionTo($account);
                                $user->givePermissionTo($permission);
                                echo $user->name . ", $permission Permission granted\n";
                            }
                        } catch (Exception $e) {
                            echo $user->name . ", $permission Permission not assigned\n";
                        }
                        break;
                }
            }

        }

        $permmissionToBeDeleted = [
            'editProperty','syncProperties','deleteProperty',
            'viewProperty','chargeBooking', 'editBooking',
            'deleteBooking','viewBooking', 'editSetting',
            'deleteSetting','viewSetting', 'editBookingSource',
            'deleteBookingSource','viewBookingSource','viewUser',
            'viewPaymentGateway','editPaymentGateway','deletePaymentGateway',
            'editPMS','deleteSource', 'editUser','deletePMS',
            'viewPMS', 'editTransaction','viewTransaction',
            'charges', 'authorize', 'capture', 'refund',
            'editProperty','deleteProperty','viewProperty','syncProperties',
            'chargeBooking', 'editBooking','deleteBooking','viewBooking',
            'editSetting','deleteSetting','viewSetting',
            'editBookingSource','deleteBookingSource','viewBookingSource',
            'editUser','deleteUser','viewUser',
            'editPMS','deletePMS','viewPMS',
            'editTransaction','deleteTransaction','viewTransaction'];

        $permissions_to_delete = Permission::whereIn('name', $permmissionToBeDeleted);
        DB::table('model_has_permissions')->whereIn('permission_id', $permissions_to_delete->pluck('id'))->delete();

        $permissions_to_delete->delete();

    }

}
