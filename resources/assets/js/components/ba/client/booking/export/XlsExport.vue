<template>
    <download-excel
            :data="json_data"
            :fields="xls_data.json_fields"
            :name="file_name"
            type="xls"
    >
        <span ref="xlsDownload" style="display: none">Download XLS</span>
    </download-excel>
</template>
<script>
    import JsonExcel from 'vue-json-excel';

    export default {
        props: ['json_data', 'file_name'],
        components: {
            'download-excel': JsonExcel
        },
        data() {
            return {
//                file_name: 'booking-list-'+Date.now().toString()+'.xls',
                xls_data: {
                    json_fields: {
                        'Booking No.': 'pms_booking_id',
                        'Property No.': 'property.pms_property_id',
                        'Guest Name': 'guest_name',
                        'Guest Email': 'guest_email',
                        'Check-in': 'check_in_pdf',
                        'Check-out': 'check_out_pdf',
                        'Security Deposit': 'deposit',
                        'Deposit Status': 'deposit_status.deposit_status',
                        'Booking Amount': 'amount',
                        'Payment Status': 'payment_status.status',
                    },
                    json_data: [],
                    json_meta: [
                        [
                            {
                                'key': 'charset',
                                'value': 'utf-8'
                            }
                        ]
                    ],
                }
            }
        }
    }
</script>
