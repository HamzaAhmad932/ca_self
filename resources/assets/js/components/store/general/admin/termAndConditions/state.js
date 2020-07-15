/** Term and Conditions Component State*/
const basic_data = {
    serve_id: 0,
    status: false,
    required: false,
    checkbox_text: '',
    internal_name: '',
    text_content: '',
    selected_properties: [],
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
    whereHas: [],
    search: {
        searchInColumn: ['id', 'internal_name'],
        searchStr: ''
    },
    user_account_id: 'all',
    property_info_id: 'all',
    room_info_id: 'all',
    filter_count: 0,
}; //Datatable filters Object End

/** Main STATE */
let state = {
    formData: basic_data,
    paginationResponse: {}, // List Records With Pagination Meta & Links
    filters: filters, // ALL Regarding Filters for datatable.
};
export default state;