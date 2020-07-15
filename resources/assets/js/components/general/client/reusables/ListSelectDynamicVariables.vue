<template>
    <div>
        <div class="d-block  dropdown dropdown-sm">
            <a aria-expanded="false" aria-haspopup="true" class="btn btn-xs dropdown-toggle" data-toggle="dropdown" href="#"
               role="button">
                <i class="fas fa-ellipsis-h d-inline-block d-md-none"></i>
                <span class="d-none d-md-inline text-bold">Dynamic Variables</span>
            </a>
            <div aria-labelledby="moreMenu" class="dropdown-menu dropdown-menu-right" style="max-height: 300px !important; overflow-y: auto; overflow-x: hidden ">
                <a @click="selectVariable(index)" class="dropdown-item" href="javascript:void(0)"
                   v-for="(variable,index) in dynamicVariables">{{variable}}</a>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ListSelectDynamicVariables",
        data: function () {
            return {
                dynamicVariables: []
            }
        },
        methods: {
            selectVariable: function (variable) {
                this.$emit('selectVariable',  this.dynamicVariables[variable]);
            },
            fetchVariables:async function (){
                self = this;
                await axios.get('/client/v2/get-template-variables').then((resp)=>{
                    self.dynamicVariables = resp.data.data;
                }).catch((err)=>{
                    console.log(err);
                });
            }
        },
        mounted() {
            this.fetchVariables();
        }
    }
</script>

<style scoped>

</style>
