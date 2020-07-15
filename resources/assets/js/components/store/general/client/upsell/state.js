export const init_state = ()=> {
    return {
        upsell_type_id: '',
        status: false,
        internal_name: '',
        value: 0,
        per: 1,
        period: 1,
        notify_guest: 0,
        meta: {
            description: '',
            from_time: '00:00',
            from_am_pm: 'am',
            to_time: '00:00',
            to_am_pm: 'am',
            rules: [],
        },
        selected_properties: [],

        error_message: {
            upsell_type_id: '',
            internal_name: '',
            status: '',
            value: '',
            per: '',
            period: '',
            meta: {
                from_time: '',
                to_time: ''
            }
        },
        error_status: {
            upsell_type_id: false,
            internal_name: false,
            status: false,
            value: false,
            per: false,
            period: false,
            meta: {
                from_time: false,
                to_time: false
            }
        }
    }
};

const filters = {
    recordsPerPage: 10,
    page: 1,
    columns: '*',
    relations: [],
    sort: {
        sortOrder: 'Desc',
        sortColumn: 'id',
    },
    constraints: [],
    search: {
        searchInColumn: ['id', 'internal_name'],
        searchStr: ''
    },
    upsell_type: 'all',
    filter_count: 0,
}; //Datatable filters Object End


let state = {
    upsell_types: [],
    form_data: init_state(),
    initial_state: init_state(), // TODO
    paginationResponse: {}, // List Records With Pagination Meta & Links
    filters: filters, // ALL Regarding Filters for datatable.
    upsell_config :{},
};


export default state;
