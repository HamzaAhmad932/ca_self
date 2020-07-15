/** Upsell Types State*/
const basic_data={
    serve_id:0,
    title:'',
    icon:'fas fa-info',
    priority:0,
    status:true,
};
const filters = {
    recordsPerPage:10,
    page:1,
    columns: '*',
    relations: [],
    sort:{
        sortOrder:'Desc',
        sortColumn:'id',
    },
    constraints:[],
    whereHas:[],
    search:{
        searchInColumn:['id','title'],
        searchStr:''
    },
    filter_count:0,
}; //Datatable filters Object End

/** Main STATE */
let state={
    types:[],
    formData:basic_data,
    paginationResponse: {}, // List Records With Pagination Meta & Links
    filters : filters, // ALL Regarding Filters for datatable.
};
export default state;
