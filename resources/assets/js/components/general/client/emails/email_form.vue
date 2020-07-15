<template>
    <div class="col-12 col-md-12 col-lg-12">
        <div class="row">
            <div class="col-md-9">
                <div class="form-group m-form__group" :id="'subject_'+content.id">
                    <label  class="text-bold">Subject:</label>
                    <input name="subject" @focus="focus('subject')"  placeholder="Enter Subject"aria-describedby="subjectHelp" class="form-control m-input m-input--air" v-model="content.email_content.subject" >
                    <br>
                    <br>
                    <span v-if="errors.subject!=''" class="text-danger"><i class="fas fa-info-circle"></i> {{errors.subject}}</span>
                </div>
            </div>
        </div>
        <div class="row">

            <div class="col-md-9">
                <div class="form-group m-form__group" :id="'button_text_'+content.id">
                    <label for="btnText" class="text-bold">Button Text:</label>
                    <input name="btnText" id="btnText"  @focus="focus('button_text')" placeholder="Enter Text On Button"aria-describedby="btnTextHelp" class="form-control m-input m-input--air" v-model="content.email_content.button_text" >
                    <br>
                    <br>
                    <span v-if="errors.button_text!=''" class="text-danger"><i class="fas fa-info-circle"></i> {{errors.button_text}}</span>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group m-form__group" >
                    <label for="showBtn" class="text-bold">Show Button:</label>
                    <div>
                        <span class="m-switch m-switch--outline m-switch--icon m-switch--success">
                            <label>
                                <input v-model="content.email_content.show_button"  id="showBtn" name="show_button" type="checkbox">
                                <span></span>
                            </label>
                        </span>
                    </div>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-md-9">
                <div class="form-group m-form__group" :id="'message_'+content.id">
                    <label class="text-bold">Content:</label>
                    <vue-editor :editor-toolbar="customToolbar" @focus="focus('message')" v-model="content.email_content.message"></vue-editor>
                    <br>
                    <br>
                    <span v-if="errors.message!=''" class="text-danger"><i class="fas fa-info-circle"></i> {{errors.message}}</span>

                </div>
            </div>

            <div class=" col-md-3 float-right">
                <div class="form-group m-form__group">
                    <label class="text-bold">Template Variables:</label>
                    <admin-dynamic-variables-list @selectVariable="addVariable"></admin-dynamic-variables-list>
                </div>
            </div>
        </div>
        <hr>
        <div class="row"><div class="col-md-2"></div> <div class="col-md-10"><div class="row"><div class="col-md-4">
            <button type="button" class="btn btn-sm btn-primary" @click.prevent="updateEmail">
            Save Custom Settings
            </button>
            </div>
            <div class="col-md-4"><button type="button" class="btn btn-sm btn-info" @click.prevent="revertToDefault">
            Revert to Default Settings
            </button>
            </div>
            <div class="col-md-4"><button type="button" class="btn btn-sm btn-success" @click.prevent="resetForm">
            Reset Form
            </button></div></div></div>
        </div>
    </div>
</template>

<script>
    import { VueEditor } from "vue2-editor";
    import AdminDynamicVariablesList from "../../admin/reusables/AdminDynamicVariablesList";
    //import AdminDynamicVariablesList from "../reusables/AdminDynamicVariablesList";
    export default {
        name: "client_email_form",
        components: {AdminDynamicVariablesList, VueEditor},
        props:["content"],
        data: ()=> {
            return{
                _state:'message',
                errors:'',
                /** Vue Editor Custom Toolbar */
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
                this._state = state;
            },
            addVariable:function (variable) {
                if(this.content.email_content[this._state] !== undefined)
                    this.content.email_content[this._state] +=" "+variable;
                else
                    this.content.email_content.message +=" "+variable;
            },
            revertToDefault:async function (){
                this.resetErrors();
                let result = await this.$store.dispatch("general/revertToDefaultEmail",this.content);
                this.content.email_content = result.data[0]['content']['email_content'];
            },
            updateEmail:async function (){
                this.resetErrors();
                let result=await this.$store.dispatch("general/clientUpdateDefaultEmail",this.content);
                if(Object.keys(result).length > 0){
                    this.setErrors(result);
                }else if(result){
                    this.is_added=(this.is_added?false:true);
                    this.resetErrors();
                }
            },
            resetErrors:function () {
                this.errors = {
                    subject:'',
                    button_text:'',
                    message:'',
                };
            },
            setErrors:function (result) {
                let self=this;
                let firstErrorId = '';
                $.each(result, function(key, value){
                    key = key.split('.')[1];
                    firstErrorId = (firstErrorId===''? key.toString() :firstErrorId);
                    self.errors[key]=value[0];
                });
                /** Scroll To 1st Error Location */
                document.getElementById(firstErrorId+"_"+this.content.id).scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});

            },
            resetForm:function (){
                this.content.email_content.message = '';
                this.content.email_content.subject = '';
                this.content.email_content.button_text = '';
                this.resetErrors();
            },
        },
        mounted() {
            this.resetErrors();
        }
    }
</script>

<style scoped>

</style>
