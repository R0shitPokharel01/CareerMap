function showPass(){

let pass=document.getElementById("password");

if(pass.type==="password"){
    pass.type="text";
}
else{
    pass.type="password";
}

}


function login(){

let email=document.getElementById("email").value;

let password=document.getElementById("password").value;


if(email==="" || password===""){

alert("Please fill all fields");

}

else{

alert("Login Successful");

}

}