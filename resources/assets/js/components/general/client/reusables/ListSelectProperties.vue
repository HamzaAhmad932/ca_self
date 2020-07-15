<template>
    <div>
        <div class="d-none d-md-block bg-light py-2 px-3 mb-2 fw-500 rounded" >
            <div class="row text-md">
                <div class="col-md-4">Property</div>
                <div class="col-md-6">Rentals</div>
                <div class="col-md-2 text-center">Status</div>
            </div>
        </div>
<!--        <div style="max-height: 243px !important; overflow-y: auto; overflow-x: hidden !important; ">-->
        <div v-if="properties_with_rooms.length > 0 " >
        <div class="border rounded  text-md mb-2" v-for="(property, index) in properties_with_rooms">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="py-1 fw-500" style="padding-left: 12px;" >{{property.name}}</div>
                </div>
                <div class="col-md-6">
                    <div class="form-group my-1">
                        <multiselect v-model="property.attached_rooms"  tag-placeholder="Select Rentals" placeholder="Search Rentals" label="name" track-by="code" :options="getMultiSelectOption(property.all_rooms)" @select="selectOption(index)" @remove="removeOption(index)" :multiple="true" :taggable="true"></multiselect>
                    </div>
                </div>
                <div class="col-md-2 text-center">
                    <div class="py-1">
                        <div class="checkbox-toggle checkbox-choice">
                            <input :id="'demo-toggle'+index" type="checkbox"  checked="" v-model="property.attach_status" @change="attachProperty(index)" />
                            <label class="checkbox-label" :for="'demo-toggle'+index" data-off="OFF" data-on="ON"><span class="toggle-track"><span class="toggle-switch"></span></span><span class="toggle-title"></span></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <div v-else >
        <div  class="border rounded  text-md mb-2" >
            <div class="row">
                <div class="col-md-12 text-center">
                    <div class="alert alert-info" style="font-weight: bold" >* You Have Already Attached Terms and Conditions to All Properties </div>
                </div>
            </div>
        </div>
        </div>
<!--        </div>-->
    </div>
</template>

<script>
    // import Select2 from 'v-select2-component';
    import {mapState} from "vuex";
    export default {
        name: "ListSelectProperties",
        props: ['model_name', 'serve_id','is_added'],
        data: function () {
            return {}
        },
        methods:{
            getAllPropertiesWithRooms(){
                this.$store.dispatch('general/getPropertiesWithRooms',
                    {'model_name' : this.model_name, 'serve_id': this.serve_id});
            },
            getMultiSelectOption(all_rooms){
                return all_rooms;
            },
            selectOption:function(index){
                this.properties_with_rooms[index].attach_status = true;
                let  self = this;
                setTimeout(function () {
                    /** Checking for All Rentals Tag in Attached Rooms Array */
                    let check = self.properties_with_rooms[index].attached_rooms.filter(function (selected){
                        return selected.code === 0;
                    });
                    /** If All Rentals Tag Found in Attached Rooms Array */
                    if(check.length > 0){
                        /** Check if AlL Rentals Tag is at 0 Index */
                        if(self.properties_with_rooms[index].attached_rooms[0].code === 0){
                            /** check if User has selected a tag other than All Rentals if true then it will remove All Rentals Tag */
                            if(self.properties_with_rooms[index].attached_rooms.length !== 1){
                                /**  Removing All Rentals Tag from Attached Rooms. */
                                self.properties_with_rooms[index].attached_rooms.shift();
                            }
                        }else{
                            /** If User Select All Rentals After Selecting Some Other Tags It Will Replaced With All Rentals Tag  */
                            self.properties_with_rooms[index].attached_rooms = [{"name":"All Rentals","code":0}]
                        }
                    }
                },100);

            },
            removeOption:function(index){
                let  self = this;
                setTimeout(function () {
                    /** Check if Attached Rooms Are Empty After Removing an tag if True It Will Remove The Property */
                   if(self.properties_with_rooms[index].attached_rooms.length <= 0){
                       self.properties_with_rooms[index].attach_status=false;
                   }
                },100);
            },
            attachProperty:function(index){
                let property = this.properties_with_rooms[index];
                /** If User Attach Property Without Selecting any Rental it will attach Add 1st Tag to Attached Rooms */
                if(property.attach_status && property.attached_rooms.length === 0){
                    this.properties_with_rooms[index].attached_rooms.push(property.all_rooms[0]);
                }
            },
            rentalChanged(index){
                //console.log(this.properties_with_rooms[index].attached_rooms);/
            },
            allRentalsCheck(index){


            }
        },
        computed : {
            ...mapState({
                properties_with_rooms : (state)=>{
                    return state.general.client_list_select_properties.properties_with_rooms;
                },
                // reload_properties_with_rooms : (state)=>{
                //     return state.general.reload_properties_with_rooms;
                // },
            }),
        },
        mounted() {
            this.getAllPropertiesWithRooms();
        },
        watch: {
            'model_name':function () {this.getAllPropertiesWithRooms();},
            'serve_id':function () { if (this.serve_id > 0) this.getAllPropertiesWithRooms();},
            'reload_properties_with_rooms':function () {this.getAllPropertiesWithRooms();},
            'is_added':function () { this.getAllPropertiesWithRooms();},
        },
    }
</script>
<style src="../../../../../../../node_modules//vue-multiselect/dist/vue-multiselect.min.css"></style>

<style scoped>
</style>
