<template>
    <div>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div v-if="showTitle" class="page-header has-border-bottom mb-4">
                        <div class="row"><div class="col-6"><a href="/client/v2/upsell-types" class="text-muted d-block text-md mb-1"><i class="fas fa-arrow-circle-left"></i>&nbsp;<span class="hidden-xs">Back to </span> types list</a></div> </div>
                        <h1 class="page-title">Add Upsell Type</h1>
                    </div>
                    <div class="page-body">
                        <div class="content-box">
                            <div class="booking-details-tabs">
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                                        <div class="row no-gutters">
                                            <div class="col-12 col-md-12 col-lg-12">
                                                <form>
                                                    <div class="row h-100 justify-content-center align-items-center">
                                                        <div class="col-md-8">
                                                            <div class="form-group">
                                                                <div class="label-tooltip">
                                                                    <label for="title" class="text-bold">Title</label>
                                                                    <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Title Should Be Unique."></i>
                                                                </div>
                                                                <input class="form-control form-control-sm" id="title" type="text" v-model="formData.title" placeholder="Title">
                                                                <small v-if="errors.title!=''" class="text-danger">{{errors.title}}</small>
                                                            </div>
                                                        </div>
                                                        <!--                                            <div class="col-md-4">-->
                                                        <!--                                                <div class="form-group float-sm-right">-->
                                                        <!--                                                    &lt;!&ndash;                                                                        <select-status></select-status>&ndash;&gt;-->
                                                        <!--                                                    <div>-->
                                                        <!--                                                        <label for="status">Availability Status</label>-->
                                                        <!--                                                        <div>-->
                                                        <!--                                                            <div class="checkbox-toggle checkbox-choice">-->
                                                        <!--                                                                <input id="status" type="checkbox" name="status" checked="" v-model="formData.status"/>-->
                                                        <!--                                                                <label class="checkbox-label" for="status" data-off="OFF" data-on="ON"><span class="toggle-track"><span class="toggle-switch"></span></span><span class="toggle-title"></span></label>-->
                                                        <!--                                                            </div>-->
                                                        <!--                                                        </div>-->
                                                        <!--                                                    </div>-->
                                                        <!--                                                </div>-->

                                                        <!--                                            </div>-->
                                                    </div>
                                                    <div class="row h-100 justify-content-center align-items-center">
                                                        <div class="col-md-8">
                                                            <div class="form-group">
                                                                <div class="label-tooltip">
                                                                    <label for="priority" class="text-bold">Priority Order</label>
                                                                    <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="By Priority Order To Show To Guest. eg. 1,2,3.. 0 by Default"></i>
                                                                </div>
                                                                <select class="form-control form-control-sm" id="priority" type="text" v-model="formData.priority">
                                                                    <option value="0" >Low(Default)</option>
                                                                    <option value="1" >Medium</option>
                                                                    <option value="2" >High</option>
                                                                </select>
                                                                <!--                                                    <span v-if="errors.title!=''" class="text-danger"><i class="fas fa-info-circle"></i> {{errors.title}}</span>-->
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!--                                        <div class="row">-->
                                                    <!--                                            <div class="col-md-12">-->
                                                    <!--                                                <div class="form-group">-->
                                                    <!--                                                    <label class="text-bold ">Select Icon</label>-->
                                                    <!--                                                    <div>-->
                                                    <!--                                                        <button v-for="(icon) in icons" @click.prevent="selectIcon(icon)" :class="'btn btn-sm '+(formData.icon==icon?'btn-success':'')"><i :class="icon"></i></button>-->
                                                    <!--                                                    </div>-->
                                                    <!--                                                </div>-->
                                                    <!--                                            </div>-->
                                                    <!--                                        </div>-->
                                                </form>
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
    import {mapActions, mapState} from "vuex";
    export default {
        props:['serve_id'],
        computed: {
            ...mapState({
                loader : (state)=>{
                    return state.loader;
                },
                formData: (state) => {
                    return state.general.upsellTypes.formData;
                }
            })
        },
        data:function (){
            return {
                showTitle:true,
                errors:"",
                icons:["fas fa-info","fas fa-clock","fas fa-exclamation-circle","fas fa-grin","fas fa-home",
                    "fas fa-users","fas fa-bell","fas fa-car","fas fa-heart","fas fa-wheelchair","fas fa-paw",
                    "fas fa-bath","fas fa-door-open"]
            }
        },
        methods:{
            selectIcon:function(icon){
                this.formData.icon=icon;
            },
            addNew: async  function(){
                this.resetErrors();
                let result=await this.$store.dispatch('general/addUpsellType',this.formData);
                if(Object.keys(result).length > 0){
                    this.setErrors(result);
                }else if(result){
                    window.location.href='upsell-types';
                }else if(!result){
                    this.$emit('updated');
                }
            },
            loadOldData:function () {
                this.$store.dispatch('general/loadUpsellTypeOldData',this.serve_id);
            },
            resetErrors:function () {
                this.errors = {
                    title:'',
                };
            },
            setErrors:function (result) {
                let self=this;
                let firstErrorId = '';
                $.each(result, function(key, value){
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
