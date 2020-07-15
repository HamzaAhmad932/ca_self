<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
    @include('includes.common.head')
    <style type="text/css">
        .page-title {
            width: 100%;
            text-align: center;
            background-color: #74bfdb;
            margin: 20px 0;
            border: 1px solid #000;
            font-size: 25px;
            color: #fff;
            padding: 10px;
        }
        .m-content.m-grid__item.m-grid__item--fluid.m-grid.m-grid--ver-desktop.m-grid--desktop.m-body {
            padding-top: 0 !important;
        }
        .m-portlet.m-portlet--full-height {
            padding: 25px;
        }
        .text-under-image {
            text-align: left;
            margin-top: 5px;
        }
        .step-title {
            color: #000;
            font-size: 18px;
        }
        .image-wrapper{margin:10px auto 0px;overflow:hidden;text-align:center;width:77%;}
        .image-wrapper img{max-width: 100%;}
    </style>
    <!-- begin::Body -->
    <body  class="m-page--fluid m--skin- m-content--skin-light2 m-header--fixed m-header--fixed-mobile m-footer--push m-scroll-top--shown">
        <h1 class="page-title">Beds 24 Integration Instructions</h1>
        <!-- begin:: Page -->
        <div class="m-grid m-grid--hor m-grid--root m-page">
            <!-- begin::Body -->
            <div class="m-content m-grid__item m-grid__item--fluid m-grid m-grid--ver-desktop m-grid--desktop m-body">
                <div class="m-grid__item m-grid__item--fluid m-wrapper">
                    <div class="m-content">
                        <!--Begin::Main Portlet-->
                        <div class="m-portlet m-portlet--full-height">
                            <a href="https://www.beds24.com/control2.php?pagetype=accountpassword" target="_blank" class="step-title" style="color: #1faca9; font-size: 1.5em;display: block;margin-bottom: 5px">
                                <strong>A:</strong> Click Settings -> Account Access
                            </a>
                            <div class="image-wrapper">
                                <img src="{{ asset('images/integration/beds24/step_A.png') }}"/>
                            </div>
                            <br>
                            <hr>
                            <br>
                            <p class="step-title" style="color: #1faca9; font-size: 1.5em;">
                                <strong>B:</strong> Scroll down to the API KEY 1 section of the page
                            </p>
                            <div class="image-wrapper">
                                <img src="{{ asset('images/integration/beds24/step_B.png') }}"/>
                            </div>
                            <br>
                            <hr>
                            <br>
                            <p class="step-title" style="color: #1faca9; font-size: 1.5em;">
                                <strong>C:</strong> Copy the unique Account API Key and paste it here.
                            </p>
                            <div class="image-wrapper">
                                <div class="form-group m-form__group" style="width: 50%; margin: auto; margin-bottom: 10px;">
                                    <label for="ba-api-key-gen"></label>
                                    <div class="input-group">
                                        <div title="Generate New Key" class="input-group-prepend" onclick="setApiKeyGen()"><span style="background-color: #34bfa3; cursor:pointer;" class="input-group-text" id="basic-addon2"><i style="color: #ffffff;" class="flaticon-refresh"></i></span></div>
                                        <input type="text" id="ba-api-key-gen" class="form-control m-input">
                                        <div class="input-group-append" title="Copy" onclick="copyPmsApiKey('ba-api-key-gen')"><span class="input-group-text" id="basic-addon2" style="background-color: #34bfa3; cursor:pointer;"><i style="color: #ffffff;" class="flaticon-file-1"></i></span></div>
                                    </div>
                                </div>
                                <img src="{{ asset('images/integration/beds24/step_C.png') }}"/>
                            </div>
                            <br>
                            <hr>
                            <br>
                            <p class="step-title" style="color: #1faca9; font-size: 1.5em;">
                                <strong>D:</strong>
                                Set values as follows & Enter our IP
                                <span style="color: #2ca189;color: #2ca189; border: 1px solid #000; border-radius: 5px; padding: 5px;">
                                    159.203.34.189
                                    <sup><i class="flaticon-file-1" onclick="copyIP()" style="color:blue;cursor: pointer;"></i></sup>
                                </span>
                                to the IP Whitelist box.
                            </p>
                            <input type="hidden" id="our_ip" value="159.203.34.189">
                            <div class="image-wrapper">
                                <img src="{{ asset('images/integration/beds24/step_D.png') }}"/>
                            </div>
                            <br>
                            <hr>
                            <br>
                            <p class="step-title" style="color: #1faca9; font-size: 1.5em;">
                                <strong>E:</strong> Each property requires a unique key. Please generate a new key for every property you have. Then copy & paste it to #5.
                            </p>
                            <br>
                            <div class="image-wrapper">
                                <div class="form-group m-form__group" style="width: 60%; margin: auto; margin-bottom: 10px;">
                                    <div class="input-group">
                                        <label for="ba-pro-key-gen"></label>
                                        <div title="Generate New Property Key" class="input-group-prepend" onclick="setProKeyGen()"><span style="background-color: #34bfa3; cursor:pointer;color: #ffffff;" class="input-group-text" id="basic-addon3">Generate Unique Property Key <i style="color: #ffffff;padding-left: 5px;" class="flaticon-refresh"></i></span></div>
                                        <input type="text" id="ba-pro-key-gen" class="form-control m-input">
                                        <div class="input-group-append" title="Copy" onclick="copyPmsApiKey('ba-pro-key-gen')"><span class="input-group-text" id="basic-addon2" style="background-color: #34bfa3; cursor:pointer;color: #ffffff;">Copy <i style="color: #ffffff;padding-left: 5px;" class="flaticon-file-1"></i></span></div>
                                    </div>
                                </div>
                                <br>
                                <img src="{{ asset('images/integration/beds24/step_E.png') }}"/>
                                <p class="step-title" style="color: #1faca9; font-size: 1.5em;margin-top: 2rem;">Repeat step E for every property.</p>
                            </div>
                            <br>
                            <hr>
                            <br>
                            <p class="step-title" style="color: #1faca9; font-size: 1.5em;">
                                <strong>F:</strong>  "Save all cards to Stripe" set to "No"
                            </p>
                            <br>
                            <div class="image-wrapper">
                                <img src="{{ asset('images/integration/beds24/step_F.png') }}"/>
                            </div>
                            <br>
                            <hr>
                            <br>
                            <p class="step-title" style="color: #1faca9; font-size: 1.5em;">
                                <strong>G:</strong> Copy the Account API key from step C and paste it in ChargeAutomation setup page.
                            </p>
                            <br>
                            <div class="image-wrapper">
                                <img src="{{ asset('images/integration/beds24/CA_pms_integration_page.png') }}"/>
                            </div>
                            <br>
                            <br>
                        </div>
                    </div>
                </div>
            </div>

            @include('includes.common.footer')
        </div>
    
        @include('includes.common.common_base_script')
        <script src="https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js"></script>
        <script>
            new ClipboardJS('#copyKey');
            function setApiKeyGen() {
                $("#ba-api-key-gen").val(uuidv4());
            }

            function setProKeyGen() {
                $("#ba-pro-key-gen").val(prokey12());
            }

            function copyPmsApiKey(id) {
                var copyText = document.getElementById(id);
                copyText.select();
                document.execCommand("copy");
                if(id == 'ba-api-key-gen')
                    toastr.info(copyText.value, 'API Key Copied to Clipboard');
                else
                    toastr.info(copyText.value, 'Property Unique Key Copied to Clipboard');
            }

            function copyIP() {
                var copyText = document.getElementById('our_ip');
                
                //create temporary input to copy text as our input is hidden so we need to create another input 
                var tempInput = document.createElement("input");
                tempInput.style = "position: absolute; left: -1000px; top: -1000px";
                tempInput.value = copyText.value;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand("copy");
                document.body.removeChild(tempInput);
                
                //show success message
                toastr.info(copyText.value, 'IP Copied to Clipboard');
            }

            function uuidv4() {
                return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
                    (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
                );
            }

            function prokey12() {
                return ([1e7]+-1e3+-4e3).replace(/[018]/g, c =>
                    (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(12)
                );
            }

            $( document ).ready(function() {

                setApiKeyGen();
                setProKeyGen();
            });
        </script>

    </body>
    <!-- end::Body -->
</html>