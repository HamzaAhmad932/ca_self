<template>
    <div>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div v-if="showTitle" class="page-header has-border-bottom mb-4">
                        <div class="row"><div class="col-6"><a href="/client/v2/terms-and-conditions" class="text-muted d-block text-md mb-1"><i class="fas fa-arrow-circle-left"></i>&nbsp;<span class="hidden-xs">Back to </span>Terms list</a></div> </div>
                        <h1 class="page-title">Add Terms & Conditions</h1>
                    </div>
                    <div class="page-body">
                        <div class="content-box">
                            <div class="booking-details-tabs">
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                                        <div class="row  no-gutters">
                                            <div class="col-10 col-md-10 col-lg-10 ">
                                                <form>
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <div class="form-group">
                                                                <div class="form-group" id="internal_name_section">
                                                                    <div class="label-tooltip">
                                                                        <label for="internal_name" class="text-bold">Internal Name</label>
                                                                        <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Internal name will not display to guest. It is for internal use only."></i>
                                                                    </div>
                                                                    <input class="form-control form-control-sm" id="internal_name" type="text" @focus="focus('internal_name')" v-model="formData.internal_name" placeholder="Internal Name">
                                                                    <span v-if="errors.internal_name!=''" class="text-danger"><i class="fas fa-info-circle"></i> {{errors.internal_name}}</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <div class="form-group text-right">
                                                                <label for="status" class="text-bold">Publish Status</label>
                                                                <div>

                                                                    <div class="checkbox-toggle checkbox-choice">
                                                                        <input id="status" type="checkbox" name="status" checked="" v-model="formData.status"/>
                                                                        <label class="checkbox-label" for="status" data-off="OFF" data-on="ON"><span class="toggle-track"><span class="toggle-switch"></span></span><span class="toggle-title"></span></label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <div class="form-group" id="checkbox_text_section">
                                                                <div class="label-tooltip">
                                                                    <label for="checkbox_text" class="text-bold">Text With Check Box</label>
                                                                    <i class="fas fa-info-circle " data-toggle="tooltip" data-placement="top" title="This text will be shown with guest agreement checkbox on pre checkin summary page."></i>
                                                                </div>
                                                                <input class="form-control form-control-sm" id="checkbox_text" type="text" @focus="focus('checkbox_text')" v-model="formData.checkbox_text" placeholder="Text To Show With Check Box">
                                                                <span v-if="errors.checkbox_text!=''" class="text-danger"><i class="fas fa-info-circle"></i> {{errors.checkbox_text}}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group text-right">
                                                                <label for="showCheckBox" class="text-bold">Required Checkbox for Guest</label>
                                                                <div>
                                                                    <div class="checkbox-toggle checkbox-choice">
                                                                        <input id="showCheckBox" type="checkbox" name="showCheckBox" checked="" v-model="formData.required"/>
                                                                        <label class="checkbox-label" for="showCheckBox" data-off="OFF" data-on="ON"><span class="toggle-track"><span class="toggle-switch"></span></span><span class="toggle-title"></span></label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group" id="text_content_section">
                                                        <label for="editor-terms" class="text-bold"> Text To Show
                                                        </label>
                                                        <vue-editor
                                                                id="editor-terms"
                                                                ref="editor"
                                                                @focus="focus('text_content')"
                                                                v-model="formData.text_content"
                                                                :editor-toolbar="customToolbar">
                                                        </vue-editor>
                                                        <span v-if="errors.text_content!=''" class="text-danger"><i class="fas fa-info-circle"></i> {{errors.text_content}}</span>
                                                    </div>
                                                    <hr>
                                                    <div class="accordion mb-2" id="propertiesList" >
                                                        <div class="card"  style="overflow: visible !important; z-index: 7 !important;">
                                                            <div @click="openAccordion" class="card-header cursor-pointer"  id="headingOne" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                                <a class="booking-accordion-title collapsed d-xs-block overflow-xs-hidden">
                                                                    <span class="col-sm-9 col-xs-9 float-xs-left">
                                                                        <b style="font-weight:500">Select Properties</b>
                                                                    </span>
                                                                    <span class="col-sm-3 col-xs-3 float-xs-left text-right"><i :class="'fas '+iconAccordion" ></i> </span>
                                                                </a>
                                                            </div>

                                                            <div class="collapse" id="collapseOne" aria-labelledby="headingOne" data-parent="#propertiesList">
                                                                <div class="text-info" style="margin-top: 2.0% !important; margin-left: 2.5% !important; margin-right: 1.5% !important;" ><i class="fas fa-info-circle"></i> Some of your properties or rentals may not be available here because you have already attached terms and conditions with these. </div>
                                                                <div class="card-body"  >

                                                                    <list-select-properties :model_name="model_name" :serve_id="serve_id" :is_added="is_added"></list-select-properties>

                                                                </div>
                                                            </div>
                                                        </div>
                                                        <span id="selected_properties_section"></span>
                                                        <span id="attached_rooms_section"></span>
                                                        <span v-if="errors.selected_properties!=''" class="text-danger"><i class="fas fa-info-circle"></i> Must Select at least One Property</span>
                                                        <span v-if="errors.attached_rooms!=''" class="text-danger"><i class="fas fa-info-circle"></i> Must Select Rentals For All Selected Properties</span>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="col-2 col-md-2 col-lg-2">
                                                <div class="scroll-btn-group ml-3">
                                                    <list-select-dynamic-variables @selectVariable="addVariable"></list-select-dynamic-variables>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center"><a class="btn btn-success" href="javascript:void(0)" @click="addTac()"  >Save Changes</a></div>
                        </div>
                    </div>

                    <!-- Block UI Loader-->
                    <BlockUI :message="loader.msg" v-if="loader.block === true"  :html="loader.html"></BlockUI>
                    <!-- End Block UI-->
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import ListSelectProperties from "../reusables/ListSelectProperties";
    import ListSelectDynamicVariables from "../reusables/ListSelectDynamicVariables";
    import { VueEditor } from "vue2-editor";
    import {mapState} from "vuex";
    export default {
        components: {ListSelectDynamicVariables, ListSelectProperties, VueEditor},
        props:['serve_id'],
        computed: {
            ...mapState({
                loader : (state)=>{
                    return state.loader;
                },
                formData: (state) => {
                    return state.general.tac.formData;
                },
                properties_with_rooms : (state)=>{
                    return state.general.client_list_select_properties.properties_with_rooms;
                },
            })
        },
        data:function (){
            return {
                __range:0,
                __state:'',
                model_name: 'terms-and-conditions', // Need To Be Changed
                is_added:false,
                showTitle:true,
                iconAccordion:'fa-chevron-down',
                errors:'',
                customToolbar: [
                    [{ header: [false, 1, 2, 3, 4, 5, 6] }],
                    ["bold", "italic", "underline"],
                    ["blockquote"],
                    [{ list: "ordered" }, { list: "bullet" }, { list: "check" }],
                    [
                        { align: "" },
                        { align: "center" },
                        { align: "right" },
                        { align: "justify" }
                    ],
                    [{ script: "sub" }, { script: "super" }],
                    [{ indent: "-1" }, { indent: "+1" }],
                    [{ color: [] }, { background: [] }],
                    ["link", "video", "formula"],
                    [{ direction: "rtl" }],
                    ["clean"]
                ],
            }
        },
        methods:{
            focus(state) {
                this.__state = state;
            },
            addTac: async  function(){
                this.resetErrors();
                this.formData.selected_properties = await this.$store.dispatch('general/setPropertiesWithRoomsForRequest');
                let result=await this.$store.dispatch('general/addTac',this.formData);
                if(Object.keys(result).length > 0){
                    this.setErrors(result);
                }else if(result){
                    // this.is_added=(this.is_added?false:true);
                    // this.resetErrors();
                    if(this.serve_id > 0){

                    }else{
                        window.location.href='terms-and-conditions';
                    }

                }else if(!result){
                    this.$emit('updated');
                }

            },
            // addVariable:function (variable) {
            //     this.formData.text_content+=" "+variable;
            // },

            //this function is no longer used
            onSelectionChange(range = null, oldRange = null, source=null) {
                // if (range != null) {
                //     this.__range = range.index;
                // }
            },

            addVariable:async function (variable) {

                if(this.__state !== '' && typeof this.formData[this.__state] != "undefined"){
                    let d_var = ' '+ variable +' ';
                    if(this.__state === 'text_content'){
                        let range = this.$refs.editor.quill.getSelection(true);
                        this.$refs.editor.quill.insertText(range.index, d_var);
                    }else{
                        let elem = document.getElementById(this.__state);
                        let position = elem.selectionStart;
                        let input_value = this.formData[this.__state];
                        this.formData[this.__state] = [input_value.slice(0, position), d_var, input_value.slice(position)].join('').trim();
                    }
                }
                //this.__range += variable.length + 2;
            },

            openAccordion: function () {
                this.iconAccordion  = this.iconAccordion=="fa-chevron-down"?"fa-chevron-up":"fa-chevron-down";
            },
            loadOldData:function () {
                this.$store.dispatch('general/loadOldData',this.serve_id);
            },
            resetErrors:function () {
                this.errors = {
                    checkbox_text:'',
                    internal_name:'',
                    text_content:'',
                    selected_properties:'',
                    attached_rooms:'',
                };
            },
            setErrors:function (result) {
                let self=this;
                let firstErrorId = '';
                $.each(result, function(key, value){
                    key = ( key.split('.').length===3 ? 'attached_rooms' : key);
                    firstErrorId = (firstErrorId===''? key.toString()+'_section' :firstErrorId);
                    self.errors[key]=value[0];
                });
                /** Scroll To 1st Error Location */
                document.getElementById(firstErrorId).scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});

            }
        },
        created() {
            this.loadOldData();
        },
        mounted() {
            this.resetErrors();
        },
        watch: {
            'serve_id':function () {
                this.showTitle=false;
                this.loadOldData();

            },
        }
    }
</script>
<style scoped>
    .button-able{
        cursor: pointer;
    }
    .text-bold{
        /*font-weight: bold;*/
    }
</style>
