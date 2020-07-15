<template>
    <div>
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card-section-title mt-2 mb-3">
                        <h4 class="mb-0">Property Details</h4>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 text-center mb-4">
                            <div class="user-image">
                                <img :src="p_img" v-if="p_initial == ''"/>
                                <!--<img :src="((propertyInfo.logo != null) && (propertyInfo.logo != '') ? '/storage/uploads/property_logos/'+propertyInfo.logo : '/storage/uploads/property_logos/no_image.png')"/>-->
                                <b style="font-size:14px;  font-weight: bold; !important">{{propertyInfo.name}}</b>
                            </div>
                            <a class="btn btn-xs propertyLogoBtn" id="propertyLogoBtn">
                                Upload Photo
                                <input @change="UpdatePropertyLogo()" class="propertyLogo" id="propertyLogo" name="propertyLogo"
                                       ref="propertyLogo" type="file"/>
                            </a>

                        </div>

                        <div class="col-sm-8">
                            <div class="form-group">
                                <label for="email">Property Email</label>
                            </div>
                            <div class="input-group mb-3" style="margin-top:-10px !important">
                                <input class="form-control" id="email" placeholder="Property Email" required="true"
                                       type="email" v-model="propertyInfo.property_email">
                                <div class="input-group-append">
                                    <button @click="updatePropertyEmail()" class="btn btn-success"
                                            title="Update & Share Property Email"><i class='fas fa-share-square'></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="propertyKey">Property key</label>
                                <input :value="propertyInfo.property_key" aria-describedby="propertyKey"
                                       class="form-control form-control-sm" id="propertyKey"
                                       readonly type="text">
                            </div>
                            <div class="form-row">
                                <div class="form-group col-6">
                                    <label for="longitude">Longitude</label>
                                    <input :value="propertyInfo.longitude" aria-describedby="longitude" class="form-control form-control-sm"
                                           id="longitude" readonly type="text">
                                </div>
                                <div class="form-group col-6">
                                    <label for="latitude">Latitude</label>
                                    <input :value="propertyInfo.latitude" aria-describedby="latitude" class="form-control form-control-sm"
                                           id="latitude"
                                           readonly type="text">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-6">
                                    <label for="currencyCode">Currency</label>
                                    <input :value="propertyInfo.currency_code" aria-describedby="currencyCode" class="form-control form-control-sm"
                                           id="currencyCode" readonly type="text">
                                </div>
                                <div class="form-group col-6">
                                    <label for="timeZone">TimeZone</label>
                                    <input :value="propertyInfo.time_zone" aria-describedby="timeZone" class="form-control form-control-sm"
                                           id="timeZone" readonly type="text">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer mt-3">
            <button class="btn btn-sm btn-secondary px-3" data-dismiss="modal" type="button">Cancel</button>
            <button @click.prevent="syncPropertyInfo()" class="btn btn-sm btn-success px-3" title="Sync Property Info from PMS"
                    type="button">
                Sync
            </button>
        </div>
        <BlockUI :html="html" :message="msg" v-if="block === true"></BlockUI>
    </div>
</template>

<script>
    export default {
        name: "PropertyInfoUpdateModal",
        props: ['propertyInfoId'],
        data() {
            return {
                msg: 'Please Wait',
                block: false,
                html: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',  //this line demostrate how to use fontawesome animation icon
                propertyInfo: {},

                p_initial: '',
                p_img: '/storage/uploads/property_logos/no_image.png',
            }
        },
        methods: {
            syncPropertyInfo() {
                let _this = this;
                _this._block('Synchronizing Property Info from PMS');
                axios.post('/client/v2/ba/sync-property-info/', {'propertyInfoId': _this.propertyInfoId})
                    .then(function (response) {
                        if (response.data.status) {
                            _this.propertyInfo = response.data.data;
                            toastr.success('Property Info Synced.');
                        } else
                            toastr.error(response.data.message);
                        _this._unBlock();
                    }).catch(function (error) {
                    _this._unBlock();
                    console.log(error);
                });
            },
            UpdatePropertyLogo() {
                let _this = this;
                _this._block('Uploading Property Image');
                _this.propertyLogo = _this.$refs.propertyLogo.files[0];
                let formData = new FormData();
                formData.append('propertyLogo', _this.propertyLogo);
                axios.post('/client/v2/update-property-logo/' + _this.propertyInfoId, formData, {headers: {'Content-Type': 'multipart/form-data'}})
                    .then(function (response) {
                        //console.log(response.data.data.path);
                        if (response.data.status)
                            toastr.success(response.data.message);
                        else
                            toastr.error(response.data.message);
                        //_this.propertyInfo.logo = response.data.data.path;
                        _this.p_img = '/storage/uploads/property_logos/' + response.data.data.path;
                        _this._unBlock();
                    }).catch(function (error) {
                    _this._unBlock();
                    console.log(error);
                });
            },
            updatePropertyEmail() {
                let _this = this;
                _this._block('Updating Property Email');
                axios.post('/client/v2/update-property-email/' + _this.propertyInfoId, {'email': _this.propertyInfo.property_email})
                    .then(function (response) {
                        if (response.data.status)
                            toastr.success(response.data.message);
                        else
                            toastr.error(response.data.message);

                        _this._unBlock();
                    }).catch(function (error) {
                    _this._unBlock();
                    console.log(error);
                });
            },

            /**
             *  Get Property Data
             */
            getPropertyInfo() {
                let _this = this;
                _this._block('Loading Property Info');
                axios.post('/client/v2/ba/get-property-info', {'propertyInfoId': _this.propertyInfoId})
                    .then(function (response) {
                        if (!response.data.status) {
                            toastr.error(response.data.message);
                        }
                        _this.propertyInfo = response.data.data;
                        if (_this.propertyInfo.property_initial === undefined || _this.propertyInfo.property_initial == '') {
                            _this.p_img = '/storage/uploads/property_logos/' + _this.propertyInfo.property_image;
                        } else {
                            _this.p_initial = response.data.user_initial;
                        }
                        _this._unBlock();
                    }).catch(function (error) {
                    _this._unBlock();
                    console.log(error);
                });
            },
            _block(msg) {
                this.msg = msg;
                this.block = true;
            },
            _unBlock() {
                this.msg = 'Please Wait';
                this.block = false;
            }
        },//methods End
        watch: {
            propertyInfoId: function () {
                if ((this.propertyInfoId != null) && (this.propertyInfoId != '')) {
                    this.propertyInfo = {};
                    this.getPropertyInfo();
                }
            },
        },
        mounted() {
        },
    }
</script>

<style scoped>
    a.propertyLogoBtn#propertyLogoBtn {
        cursor: pointer !important;
        position: relative !important;
    }

    input[type="file"].propertyLogo#propertyLogo {
        background: #D9E2EC;
        border: none;
        color: #D9E2EC;
        cursor: pointer;
        height: 100%;
        left: 0;
        opacity: 0;
        position: absolute;
        top: 0;
        width: 100%;
        z-index: 9999;
    }
</style>