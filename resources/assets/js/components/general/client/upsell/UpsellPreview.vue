<template>
    <div>
        <!--    Modal Pop-up Begin-->
        <div class="modal fade show" id="m_modal_preview" tabindex="-1"  role="dialog" aria-labelledby="exampleModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content" >
                    <div class="modal-header">
                        <h5 class="modal-title">Upsell Preview</h5>
                        <button type="button" class="close" @click="focusOut()" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="addon-item">
                            <div class="addon-item-header">
                                <div class="addon-item-header-content">
                                    <div class="addon-item-header-text">
                                        <h4>{{getUpsellType(formData.upsell_type_id)}}</h4>
                                        <p class="text-muted">{{formData.meta.description}}</p>
                                    </div>
                                    <div class="addon-price"><span class="text-success h5 text-center">{{getLabel('value_type', 1)}} {{getCurrencySymbol(1)}}{{formData.value}} </span><span>{{getLabel('per', formData.per)}} {{getLabel('period', formData.period)}}</span>
                                    </div>
                                </div>
                                <a :aria-controls="'#addonCollapse_'" :href="'#addonCollapse_'"
                                   aria-expanded="false" class="link-overlay collapsed" data-toggle="collapse"
                                   role="button">
                                    <div class="addon-collapse-btn"><i class="fas fa-chevron-up"></i></div>
                                </a>
                            </div>
                            <div :id="'addonCollapse_'" class="addon-body collapse">
                                <div class="addon-body-content">
                                    <div class="addon-section-item">
                                        <div class="icon"><i class="fas fa-clock"></i></div>
                                        <h6>Time Frame</h6>{{formData.meta.from_time}}{{formData.meta.from_am_pm}} to
                                        {{formData.meta.to_time}}{{formData.meta.to_am_pm}}
                                    </div>
                                    <div :class="{'active': rule.isHighlighted}" v-if="(rule.title != '' && rule.title != null) || (rule.description != '' && rule.description != null)" class="addon-section-item mb-2"
                                         v-for="rule in formData.meta.rules">
                                        <div class="icon"><i :class="rule.icon"></i></div>
                                        <h6>{{rule.title}}</h6>
                                        {{rule.description}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer mt-1">
                        <button class="btn btn-sm btn-secondary px-3"  @click="focusOut()" type="button">Close</button>
                    </div>
                </div>

            </div>
        </div>
        <!--    Modal Pop-up  End-->
    </div>
</template>
<script>

import {mapState, mapActions} from "vuex";
export default {
    data:function (){
        return {
        }
    },
    methods: {
        getLabel(column_name, value){
            try {
                let key = this.upsell_config[column_name]['get_key'][value];
                return this.upsell_config[column_name][key]['label'];
            } catch(err) {
                return 'undefined';
            }
        },
        getCurrencySymbol(value_type){
            return value_type == 1 ? '$' : '';
        },
        getUpsellType(id){
            let title = 'un-defined';
            $.each(this.upsell_types, function (key, value) {
                if(value.id == id) {
                    title = value.title;
                    return false;
                }
            });
            return title;
        },
        focusOut() {
           // event.preventDefault();
            jQuery.noConflict();
            $('#m_modal_preview').modal('hide');
            //$('#m_modal_edit').focus();
        }

    },
    computed : {
        ...mapState({
            loader: (state) => {
                return state.loader;
            },
            upsell_types: (state) => {
                return state.general.upsell.upsell_types;
            },
            formData: (state) => {
                return state.general.upsell.form_data;
            },
            upsell_config: (state) => {
                console.log(state.general.upsell);
                console.log(state.general.upsell.upsell_config);
                return state.general.upsell.upsell_config;
            },

        }),
        // ...mapActions(['general/getUpsellConfig',])
    },
    mounted() {
        this.$store.dispatch('general/getUpsellConfig');
    }
}
</script>