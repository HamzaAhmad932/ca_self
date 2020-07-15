<template>
    <div>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div v-if="showTitle" class="page-header has-border-bottom mb-4">
                        <div class="row"><div class="col-6"><a href="/client/v2/guide-books" class="text-muted d-block text-md mb-1"><i class="fas fa-arrow-circle-left"></i>&nbsp;<span class="hidden-xs">Back to </span> Guide Books list</a></div> </div>
                        <h1 class="page-title">Add Guide Book</h1>
                    </div>
                    <div class="page-body">
                        <div class="content-box">
                            <div class="booking-details-tabs">
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                                        <div class="row  no-gutters">
                                            <div class="col-10 col-md-10 col-lg-10">
                                                <form>
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <div class="form-group">
                                                                <div class="form-group" id="type_id">
                                                                    <label for="type">Type</label>
                                                                    <select class="form-control form-control-sm" id="type" v-model="formData.type_id">
                                                                        <option value="">Select Type</option>
                                                                        <option v-for="type in types" :value="type.id" >{{type.title}}</option>
                                                                    </select>
                                                                    <span v-if="errors.type_id!=''" class="text-danger"><i class="fas fa-info-circle"></i> {{errors.type_id}}</span>

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
                                                            <div class="form-group">
                                                                <div class="label-tooltip">
                                                                    <label for="internal_name" class="text-bold">Internal Name</label>
                                                                    <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Optional Field: Internal name will not display to guest. It is for internal use only."></i>
                                                                </div>
                                                                <input class="form-control form-control-sm" id="internal_name" type="text" @focus="focus('internal_name')" v-model="formData.internal_name" placeholder="Internal Name">
                                                                <span v-if="errors.internal_name!=''" class="text-danger"><i class="fas fa-info-circle"></i> {{errors.internal_name}}</span>
                                                            </div>
                                                        </div>
                                                        <!--                                            <div class="col-md-4">-->
                                                        <!--                                                <div class="form-group text-right">-->
                                                        <!--                                                    <label>Icon</label>-->
                                                        <!--                                                    <div class="dropdown dropdown-sm">-->
                                                        <!--                                                        <a class="mb-1 btn btn-xs dropdown-toggle" id="moreMenu3" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">-->
                                                        <!--                                                            <i :class="formData.icon"></i></a>-->
                                                        <!--                                                        <div class="dropdown-menu" aria-labelledby="moreMenu3" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(31px, 24px, 0px); max-height: 150px !important; overflow: auto;">-->
                                                        <!--                                                            <a v-for="(icon) in icons" @click="selectIcon(icon)" :class="'dropdown-item '+(formData.icon==icon?'active':'')" href="javascript:void(0)"  ><i :class="icon"></i></a>-->
                                                        <!--                                                        </div>-->
                                                        <!--                                                    </div>-->
                                                        <!--                                                </div>-->
                                                        <!--                                            </div>-->
                                                    </div>
                                                    <div class="form-group" id="text_content">
                                                        <label class="text-bold">Text To Show
                                                        </label>
                                                        <vue-editor
                                                                ref="editor"
                                                                @focus="focus('text_content')"
                                                                v-model="formData.text_content"
                                                                :editor-toolbar="customToolbar">

                                                        </vue-editor>
                                                        <!--                                        <textarea class="form-control" id="desc" rows="2" v-model="formData.text_content"></textarea>-->
                                                        <span v-if="errors.text_content!=''" class="text-danger"><i class="fas fa-info-circle"></i> {{errors.text_content}}</span>
                                                    </div>
                                                    <hr>
                                                    <div class="accordion mb-2" id="propertiesList" >
                                                        <div class="card" style="overflow:visible!important; z-index:7!important;">
                                                            <div @click="openAccordion" class="card-header cursor-pointer"  id="headingOne" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                                <a class="booking-accordion-title collapsed d-xs-block overflow-xs-hidden">
                                                        <span class="col-sm-9 col-xs-9 float-xs-left">
                                                            <b style="font-weight:500">Select Properties</b>
                                                        </span>
                                                                    <span class="col-sm-3 col-xs-3 float-xs-left text-right"><i :class="'fas '+iconAccordion" ></i> </span>
                                                                </a>
                                                            </div>

                                                            <div class="collapse" id="collapseOne" aria-labelledby="headingOne" data-parent="#propertiesList">
                                                                <div class="card-body" >
                                                                    <list-select-properties :model_name="model_name" :serve_id="serve_id" :is_added="is_added"></list-select-properties>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <span id="selected_properties"></span>
                                                        <span id="attached_rooms"></span>
                                                        <span v-if="errors.selected_properties!=''" class="text-danger"><i class="fas fa-info-circle"></i> {{ errors.selected_properties }}</span>
                                                        <span v-if="errors.attached_rooms!=''" class="text-danger"><i class="fas fa-info-circle"></i> {{ errors.attached_rooms }}</span>
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
                            <div class="modal-footer justify-content-center"><a class="btn btn-success" href="javascript:void(0)" @click="addNew()"  >Save Changes</a></div>
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
    import {mapActions, mapState} from "vuex";
    export default {
        components: {ListSelectDynamicVariables, ListSelectProperties,VueEditor},
        props:['serve_id'],
        computed: {
            ...mapState({
                loader : (state)=>{
                    return state.loader;
                },
                types : (state)=>{
                    return state.general.guideBook.types
                },
                formData: (state) => {
                    return state.general.guideBook.formData;
                },
                properties_with_rooms : (state)=>{
                    return state.general.client_list_select_properties.properties_with_rooms;
                },
            }),
            ...mapActions([
                // 'general/getGuideBookTypes',
            ])
        },
        data:function (){
            return {
                __range:0,
                __state:'',
                model_name: 'guide-book', // Need To Be Changed
                is_added:false,
                showTitle:true,
                iconAccordion:'fa-chevron-down',
                errors:"",
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
                icons:["fas fa-info","fas fa-clock","fas fa-exclamation-circle","fas fa-grin","fas fa-home",
                    "fas fa-users","fas fa-bell","fas fa-car","fas fa-heart","fas fa-wheelchair","fas fa-paw",
                    "fas fa-bath","fas fa-door-open"]

            }
        },
        methods:{
            focus(state) {
                this.__state = state;
            },
            selectIcon:function(icon){
                this.formData.icon=icon;
            },
            addNew: async  function(){
                this.resetErrors();
                this.formData.selected_properties = await this.$store.dispatch('general/setPropertiesWithRoomsForRequest');
                let result=await this.$store.dispatch('general/addGuideBook',this.formData);
                if(Object.keys(result).length > 0){
                    this.setErrors(result);
                }else if(result){
                    // this.is_added=(this.is_added?false:true);
                    // this.resetErrors();
                    window.location.href='guide-books';
                }else if(!result){
                    this.$emit('updated');
                }
            },
            // addVariable:function (variable) {
            //     this.formData.text_content+=" "+variable;
            // },

            // this function no longer used
            onSelectionChange(delta = null, old_delta = null, source=null) {
                // if ( typeof delta.ops[0].retain != "undefined") {
                //     this.__range = parseInt(delta.ops[0].retain) + 1;
                // }
            },

            addVariable: function (variable) {

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
            },

            openAccordion: function () {
                this.iconAccordion  = this.iconAccordion=="fa-chevron-down"?"fa-chevron-up":"fa-chevron-down";
            },
            loadOldData:function () {
                this.$store.dispatch('general/loadGuideBookOldData',this.serve_id);
            },
            resetErrors:function () {
                this.errors = {
                    type_id:'',
                    internal_name: '',
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
                    firstErrorId = (firstErrorId===''? key.toString() :firstErrorId);
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
            if(this.serve_id > 0){
                this.title='Edit Terms & Conditions'
            }
            this.$store.dispatch('general/getGuideBookTypes');
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
