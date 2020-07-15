<template>
    <div>
        <div :id="['user-' + user_id]" class="user-card-body collapse">
            <div class="card-section mt-2">
                <div class="card-section-title">Activity Log</div>
                <div class="card-inset-table" style="padding:0px 0px 0.5rem 0px;">
                    <div class="table-responsive">
                        <table :id="['bg-pagination-table-' + user_id]" class="table user_activities">
                            <thead>
                            <tr>
                                <div class="table-header d-none d-lg-block">
                                    <div class="row no-gutters">
                                        <div class="col-4">
                                            <span>Reference</span>
                                        </div>
                                        <div class="col-4">
                                            <span>Action</span>
                                        </div>
                                        <div class="col-4">
                                            <span>Date & Time</span>
                                        </div>
                                    </div>
                                </div>
                                <!--<th style="border-bottom-color: #F0F4F8;">Field</th>
                                <th style="border-bottom-color: #F0F4F8;">Old Value</th>
                                <th style="border-bottom-color: #F0F4F8;">New Value</th>-->
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(user_log, sr) in data.data">
                                <td style="padding:0px;">
                                    <div class="card-pane card-pane-default">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col-4 col-lg-4">
                                                {{ user_log.id }}
                                            </div>
                                            <div class="col-4 col-lg-4">
                                                {{ user_log.event | capitalize }}
                                            </div>
                                            <div class="col-4 col-lg-4">
                                                {{ user_log.created_at }}
                                            </div>
                                        </div>
                                    </div>
                                    <a aria-expanded="false"
                                       class="card-collapse collapsed inner-card-collapse user_log-card-collapse" data-toggle="collapse" role="button"
                                       v-bind:aria-controls="['log_' + user_log.id]"
                                       v-bind:data-id="user_log.id"
                                       v-bind:href="['#log_' + user_log.id]">
                                        <i class="fas fa-chevron-up" v-bind:data-id="user_log.id"></i>
                                    </a>

                                    <div :id="['log_' + user_log.id]" class="user-card-body collapse">
                                        <div class="card-section mt-2">
                                            <div class="card-section-title">Logs Detail</div>
                                            <div class="table-responsive"
                                                 style="display: flex;padding: 15px 0px 0px 0px;">
                                                <div class="col-6">
                                                    <table class="table">
                                                        <tr>
                                                            <th><b>Old Values</b></th>
                                                        </tr>
                                                        <tr v-for="(action, field) in user_log.old_values"
                                                            v-if="field!='id' && field!='password' && field!='remember_token'">
                                                            <td> {{field}} = {{action}}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="col-6">
                                                    <table class="table">
                                                        <tr>
                                                            <th><b>New Values</b></th>
                                                        </tr>
                                                        <tr v-for="(action, field) in user_log.new_values"
                                                            v-if="field!='id' && field!='password' && field!='remember_token'">
                                                            <td> {{field}} = {{action}}</td>
                                                            <!--<td v-if="typeof(action)=='object' || Array.isArray(action)"> Object -> {{field}} = {{action}}</td>
                                                            <td v-else>String -> {{field}} : {{action}}</td>-->
                                                            <!--<td>
                                                                <p v-if="action != ''" v-for="(inaction, infield) in JSON.parse(action)"> {{infield}} {{inaction}} </p>
                                                            </td>-->
                                                        </tr>
                                                    </table>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!--<td>{{ user_log.id }}</td>
                                <td>{{ user_log.event | capitalize }}</td>
                                <td>{{ user_log.created_at }}</td>
                                <td>{{ user_log.dd }}</td>
                                <td>{{ user_log.old_values }}</td>
                                <td>{{ user_log.new_values }}</td>-->
                            </tr>
                            </tbody>
                        </table>
                        <div style="float:right;margin:15px 20px 0px 0px">
                            <pagination :data="data" :limit="1" @pagination-change-page="fetchLogs"
                                        align="right"></pagination>
                        </div>
                        <!--<div class="bg-pagination bg-pagination-controller-wrapper">
                            <div class="page-detail">
                                Showing {{from+1}} to {{to}} of {{ total_record }} entries
                            </div>
                            <ul class="list">
                                <li :class="'item ' + (current_page == pagination_buttons ? ' disabled ' : '') + (current_page == pagination_buttons && button_index!=0 && button_index!=(buttons_array.length-1)  ? ' active ' : '')" v-for="(pagination_buttons, button_index) in buttons_array">
                                    <a role="menuitem"
                                       :aria-controls="['bg-pagination-table-' + button_index==0  ? '«' :  button_index==(buttons_array.length-1) ? '»' : pagination_buttons]"
                                       :aria-label="['Go to page ' + pagination_buttons]"
                                       :aria-checked="false"
                                       :data-status="false"
                                       :aria-posinset="pagination_buttons"
                                       :data-id="pagination_buttons"
                                       :target="['_self' + button_index]"
                                       href="#" @click.prevent="changePage($event)" class="page-link">{{ button_index==0  ? '«' :  button_index==(buttons_array.length-1) ? '»' : pagination_buttons }}</a>
                                </li>
                            </ul>
                        </div>-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import {mapState} from 'vuex';
    import general from "../../../store/general";

    export default {
        props: ['user_id'],
        mounted() {
        },
        data() {
            return {
                current_page: 1,
                per_page_record: 10,
                total_record: 0,
                from: 0,
                to: 0,
                data: {
                    data: [],
                },
                number_of_page: 0,
                buttons_array: [],

                logFilter: {
                    recordsPerPage: 10,
                    page: 1,
                    columns: ["id", "event", "old_values", "new_values", "created_at"],
                    relations: [],
                    sort: {
                        sortOrder: "DESC",
                        sortColumn: "id",
                    },
                    constraints: [],
                    search: {
                        searchInColumn: ["id", "event", "old_values", "new_values"],
                        searchStr: ""
                    },
                },
            }
        },
        methods: {
            fetchLogs(page = 1) {
                this.logFilter.page = page;
                this.$store.dispatch('fetchUserLogs', {'log_filter': this.logFilter, 'user_id': this.user_id});
            },
        },
        computed: {
            ...mapState({
                user_logs: (state) => {
                    return state.general.team_member.user_logs;
                }
            })
        },
        filters: {
            capitalize: function (value) {
                if (!value) return '';
                //value = value.replace(/([A-Z])/g, ' $1').trim()
                value = value.toString();
                return value.charAt(0).toUpperCase() + value.slice(1)
            },
        },

        watch: {
            user_logs: {
                deep: true,
                immediate: true,
                handler(newVal, oldVal) {
                    if (newVal !== undefined && newVal[this.user_id] !== undefined) {
                        //this.data = newVal[this.user_id];
                        this.data = newVal[this.user_id]['data'];
                        //console.log(newVal);
                    }
                }
            }
        },


        /*watch: {
            user_logs: {
                deep: true,
                immediate: true,
                handler(newVal, oldVal){
                    if(newVal !== undefined){
                        let _this = this;
                        let my_data = newVal;
                        let my_data = [
                            { id: 1, first_name: 'Fred', last_name: 'Flintstone' },
                            { id: 2, first_name: 'Wilma', last_name: 'Flintstone' },
                            { id: 3, first_name: 'Barney', last_name: 'Rubble' },
                            { id: 4, first_name: 'Betty', last_name: 'Rubble' },
                            { id: 5, first_name: 'Pebbles', last_name: 'Flintstone' },
                            { id: 6, first_name: 'Bamm Bamm', last_name: 'Rubble' },
                            { id: 7, first_name: 'The Great', last_name: 'Gazzoo' },
                            { id: 8, first_name: 'Rockhead', last_name: 'Slate' },
                            { id: 9, first_name: 'Pearl', last_name: 'Slaghoople' },
                            { id: 10, first_name: 'Pearl', last_name: 'Slaghoople' },
                            { id: 11, first_name: 'Fred', last_name: 'Flintstone' },
                            { id: 12, first_name: 'Wilma', last_name: 'Flintstone' },
                            { id: 13, first_name: 'Barney', last_name: 'Rubble' },
                            { id: 14, first_name: 'Betty', last_name: 'Rubble' },
                            { id: 15, first_name: 'Pebbles', last_name: 'Flintstone' },
                            { id: 16, first_name: 'Bamm Bamm', last_name: 'Rubble' },
                            { id: 17, first_name: 'The Great', last_name: 'Gazzoo' },
                            { id: 18, first_name: 'Rockhead', last_name: 'Slate' },
                            { id: 19, first_name: 'Pearl', last_name: 'Slaghoople' },
                            { id: 20, first_name: 'Fred', last_name: 'Flintstone' },
                            { id: 21, first_name: 'Fred', last_name: 'Flintstone' },
                            { id: 22, first_name: 'Wilma', last_name: 'Flintstone' },
                            { id: 23, first_name: 'Barney', last_name: 'Rubble' },
                            { id: 24, first_name: 'Betty', last_name: 'Rubble' },
                            { id: 25, first_name: 'Pebbles', last_name: 'Flintstone' },
                            { id: 26, first_name: 'Bamm Bamm', last_name: 'Rubble' },
                            { id: 27, first_name: 'The Great', last_name: 'Gazzoo' },
                            { id: 28, first_name: 'Rockhead', last_name: 'Slate' },
                            { id: 29, first_name: 'Pearl', last_name: 'Slaghoople' },
                        ];
                        _this.total_record = my_data.length;
                        _this.to = _this.current_page*_this.per_page_record;
                        _this.from = _this.to-_this.per_page_record;
                        _this.data = my_data.slice(_this.from, _this.to);
                        let buttons_array = [];
                        let pages = Number(((_this.total_record)/(_this.per_page_record)).toFixed(1));
                        let str = '"'+pages+'"';
                        if(str.indexOf('.') !== -1){
                            let bf = pages.toString().split('.')[0];
                            //let af = pages.toString().split('.')[1];
                            _this.number_of_page = parseInt(bf,10)+1;
                        }
                        else{
                            _this.number_of_page = pages;
                        }
                        if(_this.number_of_page>0){
                            buttons_array.push(1);
                            for (let i = 1; i <= _this.number_of_page; i++) {
                                buttons_array.push(i);
                            }
                            buttons_array.push(_this.number_of_page);
                            _this.buttons_array = buttons_array;
                        }
                    }
                }
            }
        },*/
    }
</script>
