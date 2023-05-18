


<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">User Registration</h1>
    <div class="btn-toolbar mb-2 mb-md-0">

    </div>
</div>
<form id="register-user-form">
    <div class="row">
        <div class="col mb-1" id="div-alert">
        </div>
    </div>
    <div class="row">
        <div class="col mb-1">
            <div class="input-group input-group-sm mb-1">
                <span class="input-group-text" id="basic-addon4">User Name</span>
                <input type="text" id="userName" class="form-control" placeholder="User Name" aria-label="User Name" aria-describedby="basic-addon4">
            </div>
        </div>
        <div class="col mb-1">
            <div class="input-group input-group-sm mb-1">
                <span class="input-group-text" id="basic-addon1">Email</span>
                <input type="email" id="userEmail" class="form-control" placeholder="Email" aria-label="Email" aria-describedby="basic-addon1">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col mb-1">
            <div class="input-group input-group-sm mb-1">
                <span class="input-group-text" id="basic-addon2">Password</span>
                <input type="password" id="password" class="form-control" placeholder="Password" aria-label="Password" aria-describedby="basic-addon2">
            </div>
        </div>
        <div class="col mb-1">
            <div class="input-group input-group-sm mb-1">
                <span class="input-group-text" id="basic-addon3">Password Confirmation</span>
                <input type="password" id="passwordConfirmation" class="form-control" placeholder="Password Confirmation" aria-label="Password" aria-describedby="basic-addon3">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col mb-1">
            <div class="input-group input-group-sm mb-1">
                <span class="input-group-text" id="basic-addon2">Roles</span>
                <select class="form-select">
                    @foreach($RSRoles as $r)
                    <option value="{{ $r->name }}">{{ $r->description }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col mb-1">
            <button type="button" class="btn btn-sm btn-primary" onclick="btnSaveOnclick(this)">Save</button>
        </div>
    </div>
</form>
<script>
    function btnSaveOnclick(pthis) {
        if (password.value != passwordConfirmation.value) {
            passwordConfirmation.focus()
            alertify.warning('Please confirm password')
            return
        }
        if (password.value.trim().length === 0) {
            password.focus()
            alertify.warning(`Password is required`)
            return
        }
        if (userName.value.trim().length <= 1) {
            userName.focus()
            alertify.warning(`User name is required`)
            return
        }
        const data = {
            name: userName.value,
            email: userEmail.value,
            password: passwordConfirmation.value,
            _token: '{{ csrf_token() }}',
        }
        if (confirm(`Are you sure ?`)) {
            pthis.innerHTML = `Please wait...`
            pthis.disabled = true
            $.ajax({
                type: "post",
                url: "/api/user",
                data: data,
                dataType: "json",
                success: function(response) {
                    pthis.innerHTML = `Save`
                    alertify.success(response.msg)
                    pthis.disabled = false
                    document.getElementById('div-alert').innerHTML = ''
                },
                error: function(xhr, xopt, xthrow) {
                    const respon = Object.keys(xhr.responseJSON)
                    const div_alert = document.getElementById('div-alert')
                    let msg = ''
                    for (const item of respon) {
                        msg += `<p>${xhr.responseJSON[item]}</p>`
                    }
                    div_alert.innerHTML = `<div class="alert alert-warning alert-dismissible fade show" role="alert">
                    ${msg}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`
                    pthis.innerHTML = `Save`
                    alertify.warning(xthrow);
                    pthis.disabled = false
                }
            });
        }
    }
</script>
