<script>
$(document).ready(function(){
    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-top-center",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
// =======================================================
//  Super Client Profile Update request code below
// ====================================================== 

    $('#ClientProfileBtn').click(function(event){
    event.preventDefault();
    var formdata = $("#ClientProfileForm").serialize();
    let id = $("#ClientID").val();
    axios.post('profileupdate/'+id , formdata)
    .then(function (response) {
    if(response.data.done == 1){

         toastr.success("Profile Updated Successfully");
    }
  }) .catch(function (error) {
        var errors = error.response
        if(errors.status == 422){
        if(errors.data){

    if(errors.data.errors.name){
      let err = errors.data.errors
      let name = Array.isArray(err.name) ? err.name[0]: err.name
      toastr.error(name);
    }
    if(errors.data.errors.phone){
      let err = errors.data.errors
      let phone = Array.isArray(err.phone) ? err.phone[0]: err.phone
      toastr.error(phone);
    }  
    if(errors.data.errors.password){
      let err = errors.data.errors
      let password = Array.isArray(err.password) ? err.password[0]: err.password
      toastr.error(password);
    }
    }
    }
    });
  });

//=====================================================
// Company Profile Update code below
//======================================================

    $('#CompanyProfileBtn').click(function(event){
    event.preventDefault();
    var formdata = $("#CompanyProfileForm").serialize();
    let id = $("#CompanyID").val();
    axios.post('companyprofileupdate/'+id , formdata)
    .then(function (response) {
    if(response.data.done == 1){

         toastr.success("Profile Updated Successfully");
    }
  }) .catch(function (error) {
        var errors = error.response
        if(errors.status == 422){
        if(errors.data){

    if(errors.data.errors.name){
      let err = errors.data.errors
      let name = Array.isArray(err.name) ? err.name[0]: err.name
      toastr.error(name);
    }
    if(errors.data.errors.phone){
      let err = errors.data.errors
      let phone = Array.isArray(err.phone) ? err.phone[0]: err.phone
      toastr.error(phone);
    }  
    if(errors.data.errors.password){
      let err = errors.data.errors
      let password = Array.isArray(err.password) ? err.password[0]: err.password
      toastr.error(password);
    }
    }
    }
    });
  });


// =======================================================
//  New Member Register request code below
// ======================================================

    var NewMem = new Vue({
        el: '#memForm',
        data(){
            return{
                registerData:{
                    name:'',
                    phone:'',
                    email:'',
                    username:'',
                    password:''
                },
                hasErrors:{
                    name:false,
                    phone:false,
                    email:false,
                    username:false,
                    password:false
                },
                errorMessage:{
                    name:null,
                    phone:null,
                    email:null,
                    username:null,
                    password:null
                }
                //passwordMatch:null
            }
        },
        methods:{
            registerMem(){
                var _this = this
                var vm = this.hasErrors
                var _vm = this.errorMessage
                axios.post('member', _this.registerData)
                    .then(function (response) {
                        //console.log(response);
                        if(response.done = 1){
                            toastr.success("New Member Registered Successfully!");
                        window.location.reload();
                    }

                    })
                    .catch(function (error) {
                        var errors = error.response
                        if(errors.status == 422){
                            if(errors.data){
                                if(errors.data.errors.name){
                                    let err = errors.data.errors
                                    vm.name = true
                                    _vm.name = Array.isArray(err.name) ? err.name[0]: err.name
                                }

                                if(errors.data.errors.email){
                                    let err = errors.data.errors
                                    vm.email = true
                                    _vm.email = Array.isArray(err.email) ? err.email[0]: err.email
                                }
                                if(errors.data.errors.username){
                                    let err = errors.data.errors
                                    vm.username = true
                                    _vm.username = Array.isArray(err.username) ? err.username[0]: err.username
                                }
                                if(errors.data.errors.password){
                                    let err = errors.data.errors
                                    vm.password = true
                                    _vm.password = Array.isArray(err.password) ? err.password[0]: err.password
                                }
                                if(errors.data.errors.phone){
                                    let err = errors.data.errors
                                    vm.phone = true
                                    _vm.phone = Array.isArray(err.phone) ? err.phone[0]: err.phone
                                }
                            }
                        }
                    });
            }
        }

    })
 
// =======================================================
//  Member Update request code below
// ======================================================


    $('#MemUpdateBtn').click(function(event){
    event.preventDefault();
    var formdata = $("#MemUpdateForm").serialize();
    let id = $("#Memid").val();
    //alert(id);
    axios.post('/client/memberupdate/'+id , formdata)
    .then(function (response) {
    //console.log(response.data.done);

    if(response.data.done == 1){
         toastr.success("Profile Updated Successfully");
    }else{
        toastr.error("Somthing wrong.");

    }
  }) .catch(function (error) {
        var errors = error.response
        if(errors.status == 422){
        if(errors.data){

    if(errors.data.errors.name){
      let err = errors.data.errors
      let name = Array.isArray(err.name) ? err.name[0]: err.name
      toastr.error(name);
    }
    if(errors.data.errors.phone){
      let err = errors.data.errors
      let phone = Array.isArray(err.phone) ? err.phone[0]: err.phone
      toastr.error(phone);
    }
    if(errors.data.errors.password){
      let err = errors.data.errors
      let password = Array.isArray(err.password) ? err.password[0]: err.password
      toastr.error(password);
    }
    }
    }
    });
  });

//=================================
// Memeber status update code below
//===============================
    $('.MemStatusBtn').click(function(event){
    event.preventDefault();
    var th = $(this);
    var id = $(this).data("id");
    var st = $(this).data("status");

    swal({title:"Are you sure?",text:"You will send email to to this user!",
        type:"warning",
        showCancelButton:!0,
        confirmButtonText:"Yes, "+th.text()+" it!"
    }).then(function(e){
     if(e.value == true) {
         $('#spi_' + id).show();
    axios.post('memberstatus/' + id + '/' + st)
            .then((response) => {
                $('#spi_' + id).hide('slow');
                if (response.data.status == {{ config('db_const.user.status.active.value') }} ) {
                    th.attr('data-status', {{ config('db_const.user.status.deactive.value') }});
                    th.html('<i class="la la-toggle-off"></i>{{ config('db_const.user.status.deactive.label')}}');
                    swal("{{ config('db_const.user.status.active.label') }}!","Your file has been {{ config('db_const.user.status.active.label') }}.","success")
                } else if (response.data.status == {{ config('db_const.user.status.deactive.value') }}) {
                    th.attr('data-status', {{ config('db_const.user.status.active.value') }});
                    th.html('<i class="la la-toggle-on"></i>{{ config('db_const.user.status.active.label') }}');
                    swal("{{ config('db_const.user.status.deactive.label')}}!","Your file has been {{ config('db_const.user.status.deactive.label')}}.","success")
                }

            },(error) => {
                //console.log("Hi I'm Error   ");
                // error callback
            })

    }

    }) //this is swal end ;


    });

// =======================================================
//  Member Delete request code below
// ======================================================

    $('.MemDelBtn').click(function(event){
    event.preventDefault();
        let th = $(this);
       var id = $(this).data("id");

        swal({title:"Are you sure?",text:"You wont't revert it!",
            type:"warning",
            showCancelButton:!0,
            confirmButtonText:"Yes, "+$(this).text()+" it!"
        }).then(function(e){
            if(e.value == true){
                axios.delete('memberdestroy/' + id)
                    .then((response) => {

                        if(response.data.done == 1){
                            toastr.success("The user has been deleted!");
                            th.parent().parent().parent().parent().hide('slow');

                        }else{
                            toastr.error("Somthing wrong");
                        }

                    }, (error) => {
                        toastr.error("Somthing wrong");
                    })
            }
      }) //this is swal end ;
  });


// =======================================================
//  Super Client Company Logo request code below
// ======================================================

// function readURL(input) {
//         if (input.files && input.files[0]) {
//             var reader = new FileReader();
            
//             reader.onload = function (e) {
//                 $('#Displaylogo').attr('src', e.target.result);
//             }
            
//             reader.readAsDataURL(input.files[0]);
//         }
//     }
    $('#CompanyLogo').change(function(event){
    event.preventDefault();
//readURL(this);
      $('#loader').show();
    let formData = new FormData();
    formData.append('file', $('#CompanyLogo')[0].files[0]);
     let id =  $('#CompanyLogoID').val();
    axios.post('companylogo/'+id , formData,
          {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
          }
        )
    .then(function (response) {
    if(response.data.done){
      $('#loader').hide();
      let flnm = response.data.done;
     $('#Displaylogo').attr('src', '/storage/uploads/companylogos/'+flnm);
    }
  }) .catch(function (error) {
        var errors = error.response
        if(errors.status == 422){
        if(errors.data){
         
    if(errors.data.errors.name){
      let err = errors.data.errors
      let name = Array.isArray(err.name) ? err.name[0]: err.name
      toastr.error(name);
    }
    if(errors.data.errors.phone){
      let err = errors.data.errors
      let phone = Array.isArray(err.phone) ? err.phone[0]: err.phone
      toastr.error(phone);
    }  
    if(errors.data.errors.password){
      let err = errors.data.errors
      let password = Array.isArray(err.password) ? err.password[0]: err.password
      toastr.error(password);
    }
    }
    }
    });
  });

    /*
      Make the request to the POST /select-files URL
        */
        


//========================================================
// document ready end here
});

</script>