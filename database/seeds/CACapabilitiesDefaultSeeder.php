<?php


use Illuminate\Database\Seeder;
use App\CaCapability;
use App\BookingSourceCapability;

class CACapabilitiesDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {



        //Run This Seeder After BookingSourceFormSeeder. Little Hotliler
        BookingSourceCapability::truncate();
        CaCapability::truncate();

        echo "All Booking Sources Capabilities Deleted \n \n";
        echo "All CA Capabilities Deleted \n \n";

        // To Support Auto Payments and SD add Channel code to $supportedChannels array.
        $supportedChannels = [
            'Booking.com' => 19, 'Agoda' => 17, 'Expedia' => 14, 'cTrip' => 53, 'Direct' => 0, 'Homeaway XML' => 30,
            'Homeaway iCal' => 40
        ];

        foreach (CaCapability::CA_CAPABILITIES_WITH_DESCRIPTION as $name => $description) {

            $capability =  CaCapability::create(['name' => $name, 'description' => $description, 'status' => 1 ]);

            echo "Capability $name Seeded to Ca_capabilities table\n \n";

            foreach (\App\BookingSourceForm::get() as $bookingSource) {

                $status = true; // Default CA Capable.

                /**
                 * If not in CA Payments Supported Channels
                 * and Capability name is AUTO_PAYMENTS OR SECURITY_DEPOSIT set status to false.
                 **/

                if (!in_array($bookingSource->channel_code, $supportedChannels)
                    && in_array($name, [CaCapability::AUTO_PAYMENTS, CaCapability::SECURITY_DEPOSIT])) {
                    $status = false;
                }

                BookingSourceCapability::create(
                    [
                        'booking_source_form_id' => $bookingSource->id,
                        'ca_capability_id' => $capability->id,
                        'status' => $status
                    ]
                );

                echo "Capability $name Seeded for Channel Code : $bookingSource->channel_code \n";
            }
        }
    }
}
