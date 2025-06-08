@extends('layouts.app')
@section('title', __("messages.setting-label", ["name" => __("profile")]))
@section('content')
<div class="container-fluid">
    <div class="row d-flex justify-content-center align-items-start w-100">
        <div class="col-lg-9 col-md-12">
            <div class="card p-3">
                <div class="card-header">
                    <div class="d-flex flex-column justify-content-center align-items-center w-100">
                        <label for="image" style="cursor: pointer;">
                            <img id="photo_profile" 
                                 src="{{ empty(Auth::user()->image) ? asset('user.png') : asset('storage/profile/'.Auth::user()->image) }}"  
                                 class="img-circle elevation-2" 
                                 style="width:100% !important;max-width:240px !important;aspect-ratio:1 !important;object-fit:cover !important;" 
                                 alt="profile">
                        </label>
                        <input class="d-none" type="file" accept="image/*" name="image" id="image">
                        <h1 class="h1 text-uppercase font-weight-bold" id="name_user">{{Auth::user()->name}}</h1>
                    </div>
                </div>
                <div class="card-body">
                    <input type="hidden" name="id" value="{{Auth::user()->id}}">
                    <div class="form-group mb-3">
                        <label for="name" class="form-label">{{ __("name") }}</label>
                        <input type="text" name="name" placeholder="name" id="name" value="{{Auth::user()->name}}" class="form-control">
                    </div>
                    <div class="form-group mb-3">
                        <label for="username" class="form-label">{{ __("username") }}</label>
                        <input type="text" name="username" placeholder="username" id="username" value="{{Auth::user()->username}}" class="form-control">
                    </div>
                    <div class="form-group mb-3">
                        <label for="password" class="form-label">{{ __("password") }}</label>
                        <input type="password" name="password" placeholder="password" id="password" class="form-control">
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end align-items-center">
                    <button class="btn btn-primary" id="simpan">{{ __("save") }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    const profile_image = $("#photo_profile");
    const image = $("input[name='image']");
    const id = $("input[name='id']");
    const name = $("input[name='name']");
    const username = $("input[name='username']");
    const password = $("input[name='password']");
    
    // Preview image when selected
    image.change(function(event){
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e){
                profile_image.attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Handle form submission
    $("#simpan").click(function(){
        const formData = new FormData();
        formData.append('id', id.val());
        formData.append('name', name.val());
        formData.append('username', username.val());
        
        // Only append password if it's not empty
        if (password.val().trim() !== '') {
            formData.append('password', password.val());
        }
        
        // Only append image if file is selected
        if (image[0].files.length > 0) {
            formData.append('image', image[0].files[0]);
        }

        $.ajax({
            url: "{{route('settings.profile.update')}}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res){
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: res.message,
                    showConfirmButton: false,
                    timer: 1500
                });
                
                // Update display elements
                $("#name_user").text(name.val());
                $("#user").text(name.val());
                
                // Update profile image after successful upload
                if (image[0].files.length > 0) {
                    const reader = new FileReader();
                    reader.onload = function(e){
                        $("#img_profile").attr('src', e.target.result);
                        profile_image.attr('src', e.target.result);
                    };
                    reader.readAsDataURL(image[0].files[0]);
                }
            },
            error: function(err){
                console.log(err.responseText);
                Swal.fire({
                    position: "center",
                    icon: "error", 
                    title: "Terjadi kesalahan saat menyimpan data",
                    showConfirmButton: true
                });
            }
        });
    });
});
</script>
@endsection