 <script>

 $(document).ready(function(){
    $('.AdminComUpdateBtn').click(function(event){
    


  var id = $(this).data("id");
  axios.get('showcompanyinfo/'+id) 
    .then((response) => {
    console.log(response);
    //alert(response.data.name);
    $("#name").val(response.data.name);
    
    $("#email").val(response.data.email);
    $("#userid").val(response.data.id);
}, (error) => {
    console.log("Hi I'm Error ;-) ")
    console.log($("#name").val(response.data.name));
    // error callback
})

    });

});





  $(document).ready(function(){
    $('.AdminComDelBtn').click(function(event){
    


  var id = $(this).data("id");
  axios.delete('companydel/'+id) 
    .then((response) => {
    console.log(response);
    $(this).parent().parent().parent().parent().hide('slow');
}, (error) => {
    console.log("Hi I'm Error ;-) ");
    // error callback
})

    });

});




$(document).ready(function(){
    $('#ComAdminMember').click(function(event){
     event.preventDefault();
    var formdata = $("#AdminComForm").serialize();

    axios.post('addcompany/', formdata)
    .then(function (response) {
        //console.log(response);
        if(response.done = 1){
            window.location.reload();
        }
    }) .catch(function (error) {
    var errors = error.response
    if(errors.status == 422){
    if(errors.data){
toastr.options = {
  "closeButton": false,
  "debug": false,
  "newestOnTop": false,
  "progressBar": false,
  "positionClass": "toast-top-right",
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

        if(errors.data.errors.name){
            let err = errors.data.errors           
            let name = Array.isArray(err.name) ? err.name[0]: err.name
            toastr.error(name);
        }
       
        if(errors.data.errors.email){
            let err = errors.data.errors
           let email = Array.isArray(err.email) ? err.email[0]: err.email
           toastr.error(email);
        }
       
       
    }
    }
    });
 });
});





  // Start here for deleting 


  $(document).ready(function(){
    $('#AdminDelBtn').click(function(event){
    


  var id = $(this).data("id");
  axios.delete('admindelete/'+id) 
    .then((response) => {
    console.log(response);
}, (error) => {
    console.log("Hi I'm Error ;-) ");
    // error callback
})


    });

});





  

  // End here

$(document).ready(function(){
    $('#AdminMember').click(function(event){
     event.preventDefault();
    var formdata = $("#AdminMemForm").serialize();
    
    axios.post('adminmember', formdata)
    .then(function (response) {
        //console.log(response);
        if(response.done = 1){
            window.location.reload();
        }
    }) .catch(function (error) {
    var errors = error.response
    if(errors.status == 422){
    if(errors.data){
toastr.options = {
  "closeButton": false,
  "debug": false,
  "newestOnTop": false,
  "progressBar": false,
  "positionClass": "toast-top-right",
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

        if(errors.data.errors.name){
            let err = errors.data.errors           
            let name = Array.isArray(err.name) ? err.name[0]: err.name
            toastr.error(name);
        }
       
        if(errors.data.errors.email){
            let err = errors.data.errors
           let email = Array.isArray(err.email) ? err.email[0]: err.email
           toastr.error(email);
        }
        if(errors.data.errors.username){
           let err = errors.data.errors
         let username = Array.isArray(err.username) ? err.username[0]: err.username
         toastr.error(username);
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
});


// for active and deactive user


















$(document).ready(function(){
    $('#updatecompanybtn').click(function(event){
     event.preventDefault();
    var formdata = $("#updatecompanyform").serialize();
    var id = $("#userid").val();

    
    axios.post('companyupdate/'+id, formdata)
    .then(function (response) {
        //console.log(response);
        if(response.done = 1){
            window.location.reload();
        }
    }) .catch(function (error) {
    var errors = error.response
    if(errors.status == 422){
    if(errors.data){
toastr.options = {
  "closeButton": false,
  "debug": false,
  "newestOnTop": false,
  "progressBar": false,
  "positionClass": "toast-top-right",
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

        if(errors.data.errors.name){
            let err = errors.data.errors           
            let name = Array.isArray(err.name) ? err.name[0]: err.name
            toastr.error(name);
        }
       
        if(errors.data.errors.email){
            let err = errors.data.errors
           let email = Array.isArray(err.email) ? err.email[0]: err.email
           toastr.error(email);
        }
       
       
    }
    }
    });
 });
});

$(document).ready(function(){

$('#CompanyLogo').change(function(event){
    event.preventDefault();
//readURL(this);
      $('#loader').show();
    let formData = new FormData();
    formData.append('file', $('#CompanyLogo')[0].files[0]);
     let id =  $('#CompanyLogoID').val();
    axios.post('admincompanylogo/'+id , formData,
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
});






$('.AdminStatusBtn').click(function(event){
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
    axios.post('adminstatus/' + id + '/' + st)
            .then((response) => {
              alert(id);
                $('#spi_' + id).hide('slow');
                if (response.data.status == {{ config('db_const.user.status.active.value') }} ) {
                    th.attr('data-status', {{ config('db_const.user.status.deactive.value') }});
                    th.html('<i class="la la-toggle-off"></i>{{ config("db_const.user.status.deactive.label")}}');
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









</script>