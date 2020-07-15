<template>
    <download-excel
            :data="json_data"
            :fields="xls_data.json_fields"
            :name="file_name"
            type="csv"
    >
        <span ref="csvDownload" style="display: none">Download XLS</span>
    </download-excel>
</template>
<script>
    import JsonExcel from 'vue-json-excel';

    export default {
        props: ['json_data', 'file_name', 'export_format'],
        components: {
            'download-excel': JsonExcel
        },
        watch: {
            file_name: function (newValue, oldValue) {
                // console.log(this.export_format);
                // console.log('New => '+newValue + '--' + 'OLD =>' + oldValue);
                // this.fileName = newValue;
                //this.$refs.csvDownload.click();
            },
        },
        data() {
            return {
                //  fileName: '',
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
