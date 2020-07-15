@extends('guest_panel_v2.layout.app')
@section('content')
<div class="page-content" id="email-settings">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="page-header mb-3">
                    <h1 class="page-title">Preferences Settings</h1>
                </div>
                <div class="page-body">
                    <div class="content-box">
                        <div class="setup-box">

                            <div class="tab-pane fade active show" id="preferences" role="tabpanel" aria-labelledby="preferences-tab">	
								<h4 class="setup-page-title text-muted">
									CHANGE BOOKING COLOR &amp; FLAG TEXT ON YOUR PMS
									<small class="blockquote-footer" style="background: none; color: #486581;">You can use following codes to make your information objects dynamic [paymentSplitType] , [chargedAtDateTime] , [transactionAmount] &nbsp; &nbsp;
										<a href="/client/v2/preferences-template-var-v2" target="_blank">(More Template Variables . . . )</a></small>
								</h4>

								<!-- Start of preference card -->
								<div class="accordion mb-2" id="sourceBooking_">
        							<div class="card">
        								<div class="card-header">
				                            <a id="click_id" class="booking-accordion-title collapsed" style="float:left" data-toggle="collapse" data-target="#collapse_" aria-expanded="true" aria-controls="collapse_">
				                                <span>
				                                	<i class="fa fa-comments"></i> New Chat Message
			                                	</span>
				                            </a>
				                        </div>
			                        </div>
					                        
			                        <div class="collapse show" id="collapse_" aria-labelledby="click_id">
			                            <div class="card-body">	                               	    
			                            	<div class="row">
			                                    <div class="col-md-6">
			                                        <div class="form-group m-form__group">
			                                            <label for="email_subject">Email Subject</label>
			                                            <textarea 
				                                            class="form-control form-control-sm" 
				                                            id="email_subject" 
				                                            aria-describedby="invoiceDescriptionHelp" 
				                                            placeholder="Email Subject"
				                                            v-model="subject"
			                                            >
			                                            </textarea>
			                                        </div>
			                                    </div>
			                                    <div class="col-md-6">
			                                        <div class="form-group m-form__group">
			                                            <label for="email_content">Email Content</label>
			                                            <textarea 
				                                            type="text" 
				                                            class="form-control form-control-sm" 
				                                            id="email_content" 
				                                            aria-describedby="notesHelp" 
				                                            placeholder="Payment"
				                                            v-model="content"
			                                            >
			                                            </textarea>
			                                        </div>
			                                    </div>
			                                </div>  
			                                <hr>
                            
		                                	<div class="row">
		                                    	<div class="col-md-2"></div>
			                                    <div class="col-md-10">
			                                        <div class="row">
			                                            <div class="col-md-4">
			                                                <button 
			                                                    type="button" 
			                                                    class="btn btn-sm btn-primary"
		                                                    >
		                                                    	<i class="fa fa-save"></i>
		                                                        Save Custom Text
		                                                    </button>
			                                            </div>
			                                            <div class="col-md-4">
			                                                <button 
			                                                    type="button"
			                                                    class="btn btn-sm btn-info"
		                                                    >
		                                                    	<i class="fa fa-undo"></i>
		                                                        Revert to Default
			                                                </button>
			                                            </div>
			                                            <div class="col-md-4">
			                                                <button 
			                                                    type="button"
			                                                    class="btn btn-sm btn-success"
			                                                    v-on:click="previewEmail"
		                                                    >
		                                                    	<i class="fa fa-eye"></i>
		                                                        Preview Email
			                                                </button>
			                                            </div>

			                                        </div>
			                                    </div>
			                                </div>					                            
			                            </div>
	                            	</div>
                            	</div>
                        		<!-- End of preference card -->

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

	<!-- Preview Email Modal starts-->
    <div class="modal fade" id="preview_email_modal" tabindex="-1" role="dialog" aria-labelledby="preview_email_modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">New Chat Message Email to Guest</h4>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fas fa-times"></i></span></button>
                </div>
                <div class="modal-body">

                	<div class="email_subject row">
                		<label for="email_subject" class="col-md-2 offset-2" style="
						    font-weight: 600;
						    font-size: 150%;
						">Subject:- </label>
                		<p id="email_subject" class="col-md-8" style="
						    font-size: 150%;
						">@{{ filtered_subject }}</p>
                	</div>
                	<br>
                	<br>

                	<!-- Dynamically add html here -->
                	<div class="email_content"></div>
                </div>
                <div class="modal-footer">
                	<div class="row">
                		<div class="col-7">
                			<input type="email" class="form-control" name="test_email" placeholder="Enter email to test" />
                		</div>
                		<div class="col-5">
                			<button type="button" class="btn btn-success">Send Test Email</button>
            			</div>	
                	</div>
                </div>
            </div>
        </div>
    </div>
    <!-- Preview Email Modal ends-->

</div>

@endsection
@push('below_script')

	<script src="{{asset('assetv2/js/vue.js')}}"></script>
	<script src="{{asset('assetv2/js/axios.min.js')}}"></script>
    <script type="application/javascript">

        new Vue({
            el: '#email-settings',
            data()  {
            	return {
            		preview_email_content: '',
            		open_preview_email_modal : 1,
            		subject : "{{ $subject }}",
            		content : "{{ $content }}",
            		filtered_subject : "dsfdsfs"
            	}
            },
            methods: {
                previewEmail() {
                    let th = this;

                    axios.get('/client/v2/preview-email', {
							params: {
								subject: th.subject,
								content: th.content
							}
						})
                        .then(function (response) {
                        	th.preview_email_content = response.data.email_content;
                        	th.filtered_subject = response.data.filtered_subject;
                        	//th.open_preview_email_modal = 1;

                        	$(".email_content").html('');
                        	$(".email_content").html(th.preview_email_content);
                        	$("#preview_email_modal").modal('show');

                        }).catch(function (error) {
                        
                    	});
                }
            },
            mounted() 
            {
            	//alert("yes mlounted")
            }
        })


    </script>
@endpush