import format from "date-fns/format";
<template>
    <div>
        <!-- User Account List-->
        <!-- BEGIN: Subheader -->
        <div class="m-subheader ">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="m-subheader__title m-subheader__title--separator">
                        <i class="m-menu__link-icon flaticon-users"></i> Audits
                    </h3>
                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                        <li class="m-nav__item">
                            <span class="m-nav__link-text">Audits </span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
        <!-- END: Subheader -->
        <div class="m-content">
            <div class="m-portlet m-portlet--mobile">

                <div class="m-portlet__body" id="b-list">
                    <!--begin: table -->
                    <div class="m-section">

                        <div class="m-section__content">
                            <div class="row filter-row">
                                <div class="form-group col-md-4 col-sm-4">
                                    <label for="from-date">From Date</label>
                                    <input type="date" class="form-control form-control-sm" id="from-date" placeholder="Start typing …" v-model="filters.fromDate" required>
                                </div>
                                <div class="form-group col-md-4 col-sm-4">
                                    <label for="to-date">To Date </label>
                                    <input type="date" class="form-control form-control-sm" id="to-date" placeholder="Start typing …" v-model="filters.toDate" required>
                                </div>
                                <div class="form-group col-md-4 col-sm-4">
                                    <label for="model-name">Model Name</label>
                                    <select @change.prevent="getColumnsName($event)" class="custom-select custom-select-sm" id="model-name" v-model="filters.modelName" required>
                                        <option value="all" selected>Select Model Name</option>
                                        <option :value="model_name" v-for="model_name in modelsName"> {{ model_name }}
                                        </option>
                                    </select>
                                    <span v-if="modelNameError != ''" class="alert-danger">{{ modelNameError }}</span>
                                </div>
                                <div class="form-group " :class="filters.columns.length == 1 ? 'col-md-12 col-sm-12' : 'col-md-6 col-sm-6'" v-for="(f , index) in filters.columns" >
                                    <div class="float-left pl-0" :class="filters.columns.length == 1 ? 'col-md-6 col-sm-6' : 'col-md-5 col-sm-5'">
                                        <label for="columns-name">Column Name</label>
                                        <select v-model="f.name" @change="keys(index)" class="form-control" id="columns-name" required>
                                            <option value="all" selected>All</option>
                                            <option v-for="(value, key, index) in columnsName" :value="{ columnName: value.Field, type:value.Type }"> {{ value.Field }} </option>
                                        </select>
                                    </div>
                                    <div class="float-left pr-0" :class="filters.columns.length == 1 ? 'col-md-6 col-sm-6' : 'col-md-5 col-sm-5'">
                                        <label for="search-value">Search Value</label>
                                        <input type="text" v-model="f.value" :placeholder="f.type" class="form-control m-input" id="search-value" required>
                                    </div>
                                    <div class="col-md-2 float-left text-center" v-if="filters.columns.length != 1">
                                        <br />
                                        <i class="flaticon-close" @click="removeColumn(index)" style="color: red;font-weight: bold;font-size: 25px;margin-left: 10px;cursor: pointer; line-height: 3rem;"></i>
                                    </div>
                                </div>

                                <div class="form-group col-md-12 col-sm-12">
                                    <div class="col-md-4 col-sm-4 float-left">
                                        <a @click.prevent="addColumn()"class="btn btn-sm btn-block btn-success float-right" href="javaScript:void(0)" id="add-column-btn">Add More</a>
                                    </div>
                                    <div class="col-md-4 col-sm-4 float-left">
                                        <a @click.prevent="getAuditData()"class="btn btn-sm btn-block btn-primary float-right" href="javaScript:void(0)" id="search-btn">Search</a>
                                    </div>
                                    <div class="col-md-4 col-sm-4 float-left">
                                        <a @click.prevent="resetFilters()"class="btn btn-sm btn-block btn-danger float-right" href="javaScript:void(0)" id="reset-btn">Reset</a>
                                    </div>
                                </div>
                                <div class="form-group col-md-12 col-sm-12 text-center" v-if="audtiData.length > 0"><strong>From: </strong> {{ fromDate }} <strong> To: </strong> {{ toDate }}</strong></div>
                            </div>
                            <div class="row" style="overflow: auto;">
                                <table class="table table-bordered">
                                    <thead class="heading-row">
                                        <tr v-if="audtiData.length == 0">
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr v-if="audtiData.length > 0">
                                            <th v-for="(value, column) in audtiData[0]">{{column}}</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="audtiData.length > 0">
                                        <tr v-for="(audit, index) in audtiData"  :class="index == 0 ? 'first-data-row' : 'data-row'">
                                            <td v-for="(value, column) in audit">{{value}}</td>
                                        </tr>
                                    </tbody>
                                    <tbody v-if="audtiData.length == 0">
                                    <tr class="text-center">
                                        <td class="alert alert-danger">Record not found</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                    <!--end: table -->
                </div>
            </div>
        </div>
        <!-- End Booking List-->

        <!-- Block UI Loader-->
        <BlockUI :html="html" :message="msg" v-if="block === true"></BlockUI>
        <!-- Block UI Loader-->

    </div>
</template>

<script scoped>
    import VueToast from 'vue-toast-notification';
    import 'vue-toast-notification/dist/index.css';
    Vue.use(VueToast);
    export default {
        props: ['user_account_id'],
        data() {
            return {
                msg: 'Please Wait...',
                block: false,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',  //this line demostrate how to use fontawesome animation icon
                action: '',
                selectedSortOrder: 'selected sort-asc',
                file_name: 'Audit',
                activate: 'all',
                modelsName: [],
                columnsName: [],
                audtiData: [],
                modelNameError: '',
                fromDate: '',
                toDate: '',
                filters: {
                    modelName: 'all',
                    columns: [
                        {
                            name: 'all',
                            value: '',
                            type:''
                        }
                    ],
                    fromDate: '',
                    toDate: '',
                    sortOrder: 'ASC',
                    sortColumn: 'id',
                },
            }
        },
        components: {
        },
        mounted() {
            this.getModelsName();
        },
        methods: {

            /**
             * Toaster View on any action
             * @param msg
             * @param status
             */
            toasterView(msg, status = false) {
                let type = (status ? 'success' : 'error');
                Vue.$toast.open({message: msg, duration: 3000, type: type, position: 'top-right',});
            },

            /**
             * Change Sort Order By Name Column ASc or DESc For Desktop
             */
            selectedSortOrderChanged() {
                this.filters.sortColumn = 'id';
                if (this.selectedSortOrder === 'selected sort-desc') {
                    this.selectedSortOrder = 'selected sort-asc';
                    this.filters.sortOrder = 'Asc';
                } else {
                    this.selectedSortOrder = 'selected sort-desc';
                    this.filters.sortOrder = 'Desc';
                }
                this.getModelsName();
            },

            addColumn: function () {
                this.filters.columns.push({
                    name: '',
                    value: '',
                });
            },
            removeColumn : function (index) {
                var column_length = this.filters.columns.length;
                if (column_length > 1) {
                    this.filters.columns.splice(index , 1);
                } else {
                    alert('One column name is compulsary');
                }
            },

            keys(index) {
                let column_type = this.filters.columns[index].name.type
                this.filters.columns[index].type = column_type
            },

            /**
             * Getting Models Name List
             */
            getModelsName() {
                this.block = true;
                var self = this;
                self.msg = "Loading Models name";

                axios.get('/admin/get-models-name')
                    .then(function (response) {
                        // console.error(response.data.status_code)
                        if (response.data.status_code == 200) {
                            self.modelsName = response.data.data;
                            self.block = false;
                            self.msg = "Please Wait";
                        }
                    })
                    .catch(function (error) {
                        toastr.error("Some error while fetching models name.");
                    });
            },

            /**
             * Getting Columns Name List
             */
            getColumnsName() {
                this.block = true;
                var self = this;
                self.msg = "Loading Columns name";

                axios.post('/admin/get-columns-name', {'model_name': self.filters.modelName})
                    .then(function (response) {
                        // console.error(response.data.data)
                        if (response.data.status_code == 200) {
                            self.columnsName = response.data.data;
                            // console.error(self.columnsName);
                            self.block = false;
                            self.msg = "Please Wait";
                        }
                    })
                    .catch(function (error) {
                        toastr.error("Some error while fetching columns name.");
                    });
            },

            /**
             * Getting Audit Data
             */
            getAuditData() {
                if (this.filters.modelName == 'all') {
                    this.modelNameError = 'Model name is required';
                    toastr.error("Please select a model name.");
                } else {
                    this.block = true;
                    var self = this;
                    self.modelNameError = ''
                    self.msg = "Loading Columns name";

                    axios.post('/admin/get-audit-data', {'filters': self.filters})
                        .then(function (response) {
                            console.error(response.data.data)
                            if (response.data.status_code == 200) {
                                self.audtiData = response.data.data.audits;
                                self.fromDate = response.data.data.fromDate;
                                self.toDate = response.data.data.toDate;
                                self.block = false;
                                self.msg = "Please Wait";
                                self.modelNameError = '';
                            } else if (response.data.status_code == 404) {
                                self.audtiData = [];
                                self.block = false;
                                self.msg = "Please Wait";
                                self.modelNameError = '';
                                toastr.error(response.data.message);
                            }
                        })
                        .catch(function (error) {
                            var errors = error.response
                            if (errors.status == 422) {
                                self.audtiData = [];
                                self.block = false;
                                self.msg = "Please Wait";
                                self.modelNameError = errors.data.errors.modelName[0];
                                toastr.error(self.modelNameError);
                            } else {
                                self.audtiData = [];
                                self.block = false;
                                self.msg = "Please Wait";
                                self.modelNameError = '';
                                toastr.error("Some error while fetching audit data.");
                            }

                        });
                }

            },

            resetFilters() {
                this.filters.modelName = 'all';
                this.filters.columns = [
                    {
                        name: 'all',
                        value: '',
                        type:''
                    }
                ];
                this.filters.fromDate = '';
                this.filters.toDate = '';
                this.filters.sortOrder = 'ASC';
                this.filters.sortColumn = 'id';
                this.getModelsName();
            },

        },
        filters: {
            formatDateTime(value) {
                var months = {
                    'jan': '01', 'feb': '02', 'mar': '03', 'apr': '04',
                    'may': '05', 'jun': '06', 'jul': '07', 'aug': '08',
                    'sep': '09', 'oct': '10', 'nov': '11', 'dec': '12'
                };
                var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                let date = new Date(value);
                let day = date.getDate();
                let monthIndex = date.getMonth();
                let year = date.getFullYear();
                let hours = date.getHours();
                let minutes = date.getMinutes();
                let seconds = date.getSeconds();

                return monthNames[monthIndex] + ' ' + day + ', ' + year + ', ' + hours + ':' + minutes + ':' + seconds;
            }

        },
    }
</script>

<style scoped>
    .m-nav .m-nav__item > .m-nav__link .m-nav__link-icon {
        width: auto;
    }

    .filter-row {
        background-color: rgb(188, 204, 220);
        box-sizing: border-box;
        padding: 1rem 0rem 1rem 0rem;
    }

    .heading-row {
        background-color: rgb(240, 244, 248);
        padding: 2rem 0rem 2rem 0rem;
    }

    .first-data-row {
        border-top: 1px solid #ebedf2;
        padding-top: 2rem;
    }

    .data-row {
        border-top: 1px solid #ebedf2;
        margin-top: 2rem;
        padding-top: 2rem;
    }

    .heading-row a span {
        display: inline-block;
        position: relative;
        color: #575962;
    }
    .heading-row a span:after {
        display: none;
        content: '';
        width: 0px;
        height: 0;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 10px solid #334E68;
        position: absolute;
        left: 40%;
        margin-left: -2px;
        margin-top: 10px;
    }
    .heading-row .selected {
        color: #102A43;
    }
    .heading-row .selected.sort-desc span:after {
        display: block;
    }
    .heading-row .selected.sort-asc span:after {
        display: block;
        transform: rotate(180deg);
    }
    .heading-row .selected .fas {
        margin-left: 0.5rem;
    }

/*  Property detail  */
    .open-div {
        background-color: #e6e6e6 !important;
    }

    .open-div-row {
        padding: 2rem !important;
        margin-top: 2rem !important;
    }
</style>
