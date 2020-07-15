<template>
    <div>
        <div class="card AdminDynamicVariablesList">
            <div class="card-header text-center" >
                <label class="text-bold">{{label}}</label>
            </div>
            <div class="card-body" :style="(label == undefined?'max-height: 250px !important; overflow: auto':'')">
                <a @click="selectVariable(index)" class="dropdown-item" style="padding: 0.25rem !important;" href="javascript:void(0)" v-for="(variable,index) in tempVars()">{{variable}}</a>
            </div>
        </div>
    </div>
</template>

        <script>
            import {mapActions, mapState} from "vuex";
            export default {
                name: "AdminDynamicVariablesList",
                props: ['label','vars'],
                computed:{
                    ...mapState({
                        dynamicVariables: (state) => {
                            return state.adv.variables;
                        },
                    }),
                    ...mapActions(["loadDynamicVariables",]),
                },
                methods: {
                    selectVariable: function (variable) {
                        this.$emit('selectVariable',  this.tempVars()[variable]);
                    },
                    tempVars:function () {
                        return (this.vars != undefined?this.vars:this.dynamicVariables);
                    }
                },
                mounted() {
                    if(this.vars == undefined){
                        this.loadDynamicVariables;
                    }
                }
            }
        </script>

        <style scoped>

        </style>
