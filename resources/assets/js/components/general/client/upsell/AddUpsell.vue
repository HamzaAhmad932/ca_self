<template>
    <div>
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div v-if="serve_id==0" class="page-header has-border-bottom mb-4 overflow-hidden">
                            <h1 class="page-title float-left">Add New Upsell</h1>
                        </div>
                        <div class="page-body">
                            <div class="content-box">
                                <div class="booking-details-tabs">
                                    <div class="tab-content" id="myTabContent">
                                        <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                                            <div class="row  no-gutters">
                                                <div class="col-10 col-md-10 col-lg-10">
                                                    <form id="form">
                                                        <div class="row">
                                                            <div class="col-sm-6 order-lg-1 order-md-1 order-sm-1 order-xs-2">
                                                                <div class="form-group">
                                                                    <label for="upsellType">Type</label>
                                                                    <select class="form-control form-control-sm" id="upsellType" v-model="formData.upsell_type_id">
                                                                        <option value="">Select Type</option>
                                                                        <option v-for="type in upsell_types" :value="type.id" >{{type.title}}</option>
                                                                    </select>
                                                                    <small class="text-error" v-if="formData.error_status.upsell_type_id">{{formData.error_message.upsell_type_id}}</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6 order-lg-2 order-md-2 order-sm-2 order-xs-1">
                                                                <div class="form-group upsellType float-sm-left">
                                                                    <button type="button" onclick="window.location.href='upsell-types-add'" class="btn btn-success btn-sm"><i class="fa fa-plus icon-upsell"></i>Upsell Type</button>
                                                                </div>
                                                                <div class="form-group float-sm-right">
                                                                    <!--<select-status></select-status>-->
                                                                    <div>
                                                                        <label for="status">Publish Status <small class="text-error" v-if="formData.error_status.status">{{formData.error_message.status}}</small></label>
                                                                        <div>
                                                                            <div class="checkbox-toggle checkbox-choice">
                                                                                <input id="status" type="checkbox" name="status" checked="" v-model="formData.status"/>
                                                                                <label class="checkbox-label" for="status" data-off="OFF" data-on="ON"><span class="toggle-track"><span class="toggle-switch"></span></span><span class="toggle-title"></span></label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-8">
                                                                <div class="form-group">
                                                                    <div class="label-tooltip">
                                                                        <label for="upsellName">Internal Name</label>
                                                                        <i class="fas fa-info-circle " data-toggle="tooltip" data-placement="top" title="Internal name for your ease, Breakfast Main Yard, or Breakfast Blue Yard."></i>
                                                                    </div>
                                                                    <input class="form-control form-control-sm" id="upsellName" type="text" v-model="formData.internal_name" ref="upsellName" @focus="setFocusedInput('upsellName', 'internal_name', 0)" placeholder="Upsell Name">
                                                                    <small class="text-error" v-if="formData.error_status.internal_name">{{formData.error_message.internal_name}}</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <div class="form-group">
                                                                    <div class="label-tooltip">
                                                                        <label for="upsellPeriod">Notify Guest</label>
                                                                        <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top"
                                                                           title="Remind Guest to purchase available upsells by email before check-in"></i>
                                                                    </div>
                                                                    <select class="form-control form-control-sm" id="notifyGuest" v-model="formData.notify_guest">
                                                                        <option value="0" >Do not Notify</option>
                                                                        <option :value="i" v-for="i in 10">{{i}} {{i > 1 ? 'days' :'day'}} before check-in</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-sm-4">
                                                                <div class="form-group">
                                                                    <label for="upsellPrice">Price</label>
                                                                    <div class="input-group input-group-sm">
                                                                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                                        <input step="any" id="upsellPrice" type="number" class="form-control form-control-sm" placeholder="Price" v-model="formData.value">
                                                                    </div>
                                                                    <small class="text-error" v-if="formData.error_status.value">{{formData.error_message.value}}</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <div class="form-group">
                                                                    <div class="label-tooltip">
                                                                        <label for="upsellPre">Per</label>
                                                                        <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Charge (amount per Booking * Period) + Commission Fee 10 % of whole amount Or per Guest (amount * Guest Count * Period) + Commission Fee 10 % of whole amount"></i>
                                                                    </div>
                                                                    <select class="form-control form-control-sm" id="upsellPre" v-model="formData.per">
                                                                        <option value="1">Booking</option>
                                                                        <option value="2">Guest</option>
                                                                    </select>
                                                                    <small class="text-error" v-if="formData.error_status.per">{{formData.error_message.per}}</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <div class="form-group">
                                                                    <div class="label-tooltip">
                                                                        <label for="upsellPeriod">Period</label>
                                                                        <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top"
                                                                        title="Charge Upsell One-time (amount) + Commission Fee Or Daily (amount * nights count) + Commission Fee"></i>
                                                                    </div>
                                                                    <select class="form-control form-control-sm" id="upsellPeriod" v-model="formData.period">
                                                                        <option value="1">One time</option>
                                                                        <option value="2">Daily</option>
                                                                    </select>
                                                                    <small class="text-error" v-if="formData.error_status.period">{{formData.error_message.period}}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="upsellDesc">Short Description</label>
                                                            <textarea class="form-control" id="upsellDesc" rows="4" v-model="formData.meta.description" ref="upsellDesc"  @focus="setFocusedInput('upsellDesc', 'description', 0)" placeholder="Short description..."></textarea>
                                                        </div>
                                                        <h4 class="mt-4">Time Frame</h4>
                                                        <div class="row align-items-end">
                                                            <div class="col">
                                                                <div class="form-group">
                                                                    <label>Time from</label>
                                                                    <div class="input-group input-group-sm">

                                                                        <!--<input class="form-control" id="upsellTimeFrameFrom" v-mask="'##:##'" type="text" placeholder="09:00"
                                                                               v-model="formData.meta.from_time" style="flex-grow:3;">-->
                                                                        <select class="custom-select-sm bg-light appearance-auto"
                                                                                style="flex-grow:1;" v-model="formData.meta.from_time">
                                                                            <option :value="time" v-for="time in time_frame_list"> {{ time }} </option>
                                                                        </select>

                                                                        <select class="custom-select custom-select-sm bg-light w-65" v-model="formData.meta.from_am_pm">
                                                                            <option value="am">AM</option>
                                                                            <option value="pm">PM</option>
                                                                        </select>
                                                                        <small class="text-error" v-if="formData.error_status.meta.from_time">{{formData.error_message.meta.from_time}}</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-1 text-center pb-4 hidden-xs text-muted">—</div>
                                                            <div class="col">
                                                                <div class="form-group">
                                                                    <label>Time to</label>
                                                                    <div class="input-group input-group-sm">
                                                                        <!--<input class="form-control" id="upsellTimeFrameTo" type="text" v-mask="'##:##'" placeholder="10:00" v-model="formData.meta.to_time" style="flex-grow:3;">-->
                                                                        <select class="custom-select-sm bg-light appearance-auto"
                                                                                style="flex-grow:1;" v-model="formData.meta.to_time">
                                                                            <option :value="time" v-for="time in time_frame_list"> {{ time }} </option>
                                                                        </select>

                                                                        <select class="custom-select custom-select-sm bg-light w-65" v-model="formData.meta.to_am_pm">
                                                                            <option value="am">AM</option>
                                                                            <option value="pm">PM</option>
                                                                        </select>
                                                                        <small class="text-error" v-if="formData.error_status.meta.to_time">{{formData.error_message.meta.to_time}}</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <h4 class="my-3">Upsell Rules</h4>
                                                        <div class="position-relative">
                                                            <div class="card mb-2"  style="z-index: 7 !important;" v-for="(rule,index) in formData.meta.rules" >
                                                                <div class="card-body" style="padding: 0.5rem !important;">
                                                                    <div class="form-group row">
                                                                        <div class="col-md-3">
                                                                            <label>Icon</label>
                                                                            <div class="dropdown dropdown-sm">
                                                                                <a class="mb-1 btn btn-xs dropdown-toggle" id="moreMenu3" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                    <i :class="rule.icon"></i></a>
                                                                                <div class="dropdown-menu" aria-labelledby="moreMenu3" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(31px, 24px, 0px); max-height: 150px !important; overflow: auto;">
                                                                                    <a v-for="(icon) in icons" @click="selectIcon(icon,index)" :class="'dropdown-item '+(rule.icon==icon?'active':'')" href="javascript:void(0)"  ><i :class="icon"></i></a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-9">
                                                                            <label >Title</label>
                                                                            <input class="form-control form-control-sm" :id="'ruleTitle'+index" :ref="'ruleTitle'+index" type="text"  v-model="rule.title"  @focus="setFocusedInput('ruleTitle'+index, 'rule.title', index)" placeholder="Title...">
                                                                        </div>

                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label >Description</label>
                                                                        <textarea class="form-control form-control-sm" :id="'ruleDesc'+index" :ref="'ruleDesc'+index" type="text" v-model="rule.description" @focus="setFocusedInput('ruleDesc'+index, 'rule.description', index)" placeholder="Description" rows="4"></textarea>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-9">
                                                                            <div class="form-group form-check">
                                                                                <input class="form-check-input" id="exampleCheck1" type="checkbox" v-model="rule.isHighlighted">
                                                                                <label class="form-check-label" for="exampleCheck1">Highlight section to make it more
                                                                                    <mark>prominent</mark>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-3 text-right">
                                                                            <div class="form-group">
                                                                            <span  class="btn-link btn-sm text-danger button-able" v-on:click="resetForm" >
                                                                            <span class="hidden-xs">Reset Form</span></span></div>

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="text-right pt-2"><span class="btn btn-sm btn-outline-secondary button-able" @click="addRule"><i class="fas fa-plus"> </i> Add More</span></div>
                                                        <hr>
                                                        <div class="accordion mb-2" id="propertiesList" >
                                                            <div class="card" style="z-index: 7 !important;">
                                                                <div @click="openAccordion" class="card-header cursor-pointer"  id="headingOne" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                                    <a class="booking-accordion-title collapsed d-xs-block overflow-xs-hidden">
                                                                        <span class="col-sm-9 col-xs-9 float-xs-left">
                                                                            <b style="font-weight:500">Select Properties</b>
                                                                        </span>
                                                                        <span class="col-sm-3 col-xs-3 float-xs-left text-right"><i :class="'fas '+iconAccordion" ></i> </span>
                                                                    </a>
                                                                </div>

                                                                <div class="collapse" id="collapseOne" aria-labelledby="headingOne" data-parent="#propertiesList">
                                                                    <div class="card-body">
                                                                        <list-select-properties :model_name="model_name" :serve_id="serve_id"></list-select-properties>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        </div>
                                                    </form>
                                                </div>

                                                <div class="col-2 col-md-2 col-lg-2">
                                                    <div class="row text-right">
                                                        <div class="col-lg-12">
                                                         <span class="h3 float-right">
                                                             <a id="preview2" href="#" style="z-index: 10;" role="button"
                                                                data-toggle="modal" data-target="#m_modal_preview"
                                                                title="Preview">
                                                                 <i class="fas fa-eye" title="preview"></i></a>
                                                         </span>
                                                        </div>
                                                    </div>
                                                   <div class="row">
                                                       <div class="col-lg-12">
                                                    <div class="scroll-btn-group ml-3">

                                                        <list-select-dynamic-variables @selectVariable="addVariable"></list-select-dynamic-variables>

                                                    </div>
                                                       </div>
                                                   </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <a class="btn btn-lg btn-success" href="#0" @click.prevent="addUpsell()">Save Changes</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <!-- Block UI Loader-->
        <BlockUI :message="loader.msg" v-if="loader.block === true"  :html="loader.html"></BlockUI>
        <!-- End Block UI-->
        <upsell-preview></upsell-preview>
    </div>
</template>

<script>
    import UpsellPreview from './UpsellPreview'
    import ListSelectProperties from "../reusables/ListSelectProperties";
    import ListSelectDynamicVariables from "../reusables/ListSelectDynamicVariables";
    import {mapActions, mapState} from "vuex";

    export default {
        components: {ListSelectDynamicVariables, ListSelectProperties, UpsellPreview},
        props:['serve_id'], //serve_id => 0 if add new listing
        data:function (){
            return {
                model_name: "up-sell",
                icons:["fas fa-info","fas fa-clock","fas fa-exclamation-circle","fas fa-grin","fas fa-home",
                    "fas fa-users","fas fa-bell","fas fa-car","fas fa-heart","fas fa-wheelchair","fas fa-paw",
                    "fas fa-bath","fas fa-door-open"],
                iconAccordion:'fa-chevron-down',
                focusedInput: {},
                time_frame_list: [
                    "01:00", "01:30", "02:00", "02:30", "03:00", "03:30", "04:00", "04:30", "05:00", "05:30", "06:00", "06:30",
                    "07:00", "07:30", "08:00", "08:30", "09:00", "09:30", "10:00", "10:30", "11:00", "11:30", "12:00", "12:30",
                ],
                form:{},
            }
        },
        methods:{
            addRule:function () {
                let checkEmptyLines = this.formData.meta.rules.filter(rule => rule.number === null);
                if (checkEmptyLines.length >= 1 && this.formData.meta.rules > 0) return;
                this.formData.meta.rules.push({
                    'title':'',
                    'icon':'fas fa-info',
                    'description':'',
                    'isHighlighted': false,
                });
            },
            removeRule:function (index) {
                this.formData.meta.rules.splice(index, 1);
                if( this.formData.meta.rules==0) {
                    this.addRule();
                }
            },
            selectIcon:function(icon,forRule){
                this.formData.meta.rules[forRule].icon=icon;
            },
            setFocusedInput:function(DOM_id, form_data_name, index){
                //let text_area = document.querySelector('#'+DOM_id);
                // console.log(text_area);
                // console.log(text_area.selectionStart);
                this.focusedInput = {DOM_id : DOM_id, form_data_name : form_data_name, index : index};
            },
            appendDynamicVars:function (variable) {
                switch (this.focusedInput.form_data_name) {
                    case  'internal_name' :
                        this.formData.internal_name = this.getPreStr(this.formData.internal_name, variable);
                        break;
                    case  'description' :
                        this.formData.meta.description = this.getPreStr(this.formData.meta.description, variable);
                        break;
                    case  'rule.title' :
                        this.formData.meta.rules[this.focusedInput.index].title
                            = this.getPreStr(this.formData.meta.rules[this.focusedInput.index].title, variable);
                        break;
                    case  'rule.description' :
                        this.formData.meta.rules[this.focusedInput.index].description
                            = this.getPreStr(this.formData.meta.rules[this.focusedInput.index].description, variable);
                        break;
                    default:
                        break;
                }
            },
            getPreStr(str, variable) {
                let input = document.querySelector("#"+this.focusedInput.DOM_id);
                let position = 0;
                if (str != null) {
                    variable = str.substr(0, input.selectionStart) + ' ' + variable + ' '+str.substr(input.selectionStart, str.length);
                    position = ((input.selectionStart) + str.length + 3);
                }

                position = (position == 0 ? input.selectionStart : (position + str.length + 2));
                this.setCaretPosition(input,position, position);
                return variable;
            },
            setCaretPosition(ctrl, start, end) {
                // IE >= 9 and other browsers
                if (ctrl.setSelectionRange) {
                    ctrl.focus();
                    ctrl.setSelectionRange(start, end);
                }
                // IE < 9
                else if (ctrl.createTextRange) {
                    let range = ctrl.createTextRange();
                    range.collapse(true);
                    range.moveEnd('character', end);
                    range.moveStart('character', start);
                    range.select();
                }
            },
            addVariable:function (variable) {
                //let input = document.querySelector("#"+this.focusedInput.DOM_id);
                //input.focus();
                this.appendDynamicVars(variable);
            },
            openAccordion: function () {
                this.iconAccordion  = this.iconAccordion=="fa-chevron-down"?"fa-chevron-up":"fa-chevron-down";
            },
            addUpsell() {
                this.setPropertiesWithRoomsForRequest();
                this.$store.dispatch('general/addUpsell', this.serve_id);
                if (this.serve_id > 0)
                    this.$emit('updated');
            },
            setPropertiesWithRoomsForRequest(){
                let properties = [];
                $.each(this.properties_with_rooms, function (key, value) {
                    if(value.attach_status) {
                        let attached_rooms = [];
                        $.each(value.attached_rooms, function (key2, room) {
                            attached_rooms.push(room.code);
                        });
                        let attach_property = {id: value.id, attach_status: value.attach_status, attached_rooms: attached_rooms}
                        properties.push(attach_property);
                    }
                });
                this.formData.selected_properties = properties;
            },
            loadFormData() {
                this.$store.dispatch('general/loadUpsellFormData', this.serve_id);

                if(this.formData.meta.rules.length == 0)
                    this.addRule();
            },
            resetForm(){
                swal.fire({
                    title: "Are you sure you want to reset form?",
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: "Yes, do it!"
                }).then(function (e) {
                    if (e.value == true) {
                        $("#form")[0].reset();
                    }
                });


            }
        },
        computed : {
            ...mapState({
                loader : (state)=>{
                    return state.loader;
                },
                upsell_types : (state)=>{
                    return state.general.upsell.upsell_types;
                },
                formData : (state)=>{
                    console.log(state.general.upsell.form_data.error_status);

                    return state.general.upsell.form_data;
                },
                properties_with_rooms : (state)=> {
                    return state.general.client_list_select_properties.properties_with_rooms;
                },
            }),
            ...mapActions([
                'general/getUpsellTypes',
            ])
        },
        mounted() {
            this.addRule();
            this.$store.dispatch('general/getUpsellTypes',{
                for_filters:false ,
                serve_id:this.serve_id
            });
            //

        },
        watch: {
            'serve_id':function () {
                this.$store.dispatch('general/getUpsellTypes',{
                    for_filters:false,
                    serve_id:this.serve_id
                });
                this.loadFormData();

            },
        },
    }
</script>
<style scoped>
    .button-able{
        cursor: pointer;
    }
    .upsellType{
        padding-top:7%;
    }
    .btn-upsell , .icon-upsell{
        color:white;
    }
    .icon-upsell{
        font-size:10px;
    }
    .btn-upsell{
        background-color:white;
        border: none;
    }

</style>
