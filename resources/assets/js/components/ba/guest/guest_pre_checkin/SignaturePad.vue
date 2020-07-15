<template>
    <div>
        <div id="signaturePad-wrapper">
            <img v-if="showSignatureImage" :src="'/storage/uploads/guestImages/'+signatureImage"
                 alt="Image not found"/>
            <VueSignaturePad
                    height="170px"
                    ref="signaturePad"
                    v-bind:customStyle="{
                'background-color': '#fcfcfc',
                'border': '#9c9c9c 1px solid'}"
                    v-bind:options="{'penColor': '#479fd7'}"
                    width="100%"></VueSignaturePad>

            <button v-bind:data-status="showSignatureImage" @click.prevent="clear($event)" class="btn btn-sm btn-danger btn-signature-clear"><i class="fa fa-times"></i> Clear
            </button>
        </div>
    </div>
</template>
<script>

    export default {

        name: 'signature-pad',

        props: ['booking_id', 'type', 'signatureFind', 'signatureImage'],

        data() {
            return {
                isEmpty: true,
                image: null,
                showSignatureImage: this.signatureFind

            }
        },

        methods: {

            undo() {
                this.$refs.signaturePad.undoSignature();
            },

            save() {
                const {isEmpty, data} = this.$refs.signaturePad.saveSignature();

                this.isEmpty = isEmpty;
                this.image = data;

            },

            clear(event) {
                let st = event.target.dataset.status;
                if(st && st !== undefined){
                    this.showSignatureImage = false;
                }
                this.$refs.signaturePad.clearSignature();
            }
        }
    }
</script>
<style>
    .btn-signature-clear {
        margin-top: 10px;
        right: 0;
        float: right;
    }
</style>